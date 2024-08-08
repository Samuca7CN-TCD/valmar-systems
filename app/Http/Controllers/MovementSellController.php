<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\Department;
use App\Models\Movement;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Action;
use App\Models\Accounting;
use App\Models\Procedure;
use App\Models\Record;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MovementSellController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $page = Department::find(5);
        $items = Item::with('measurement_unit')->orderBy('name')->get();

        // Recebe os parâmetros da consulta, ou define os valores padrão
        $parameters = [
            'entity_name' => $request->query('entity_name', ''),
            'start_date' => $request->query('start_date', Carbon::today()->toDateString()),
            'end_date' => $request->query('end_date', Carbon::today()->toDateString()),
        ];

        $sells = Movement::where('type', 2)
            ->whereBetween('date', [$parameters['start_date'], $parameters['end_date']])
            ->with(['accounting', 'procedures.records']);

        if ($parameters['entity_name'])
        {
            $sells->whereHas('procedures.records', function ($query) use ($parameters) {
                $query->where('entity_name', 'LIKE', "%{$parameters['entity_name']}%");
            });
        }

        $sells = $sells->get()->map(function ($movement) {
            $records = $movement->procedures->flatMap->records;
            $movement->items = $records->filter(fn($record) => $record->item_id !== null);
            return $movement;
        });

        return Inertia::render('Movements/Sells', [
            'page' => $page,
            'sells_list' => $sells,
            'items' => $items,
            'parameters' => $parameters,
        ]);
    }

    public function filter(Request $request)
    {
        $parameters = [
            'entity_name' => $request->input('entity_name', 0),
            'start_date' => $request->input('start_date', Carbon::today()->toDateString()),
            'end_date' => $request->input('end_date', Carbon::today()->toDateString()),
        ];

        return redirect()->route('sells.index', $parameters);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request);
        return DB::transaction(function () use ($request) {
            $validated = $request->validate([
                'client' => ['required', 'string'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
                'estimated_value' => ['required', 'numeric', 'gt:0'],
                'total_value' => ['required', 'numeric', 'gt:0', 'lte:estimated_value'],
                'entry_value' => ['required', 'numeric', 'gte:0', 'lte:total_value'],
                'observations' => ['nullable', 'string'],
                'items_list' => ['required', 'array', 'min:1'],
                'items_list.*.id' => ['required', 'numeric', 'exists:items,id'],
                'items_list.*.name' => ['required', 'string', 'exists:items,name'],
                'items_list.*.movement_quantity' => ['required', 'numeric', 'gt:0', 'lte:items_list.*.quantity'],
                'items_list.*.quantity' => ['required', 'numeric', 'gt:0'],
                'items_list.*.measurement_unit' => ['required', 'string'],
                'items_list.*.price' => ['required', 'numeric', 'gt:0'],
                'items_list.*.amount' => ['required', 'numeric', 'gt:0'],
            ]);

            $accounting = Accounting::create([
                'estimated_value' => $validated['estimated_value'],
                'total_value' => $validated['total_value'],
                'partial_value' => $validated['total_value'] - $validated['entry_value'],
            ]);

            $movement = Movement::create([
                'type' => 2,
                'accounting_id' => $accounting->id,
                'motive' => 'Venda de Material',
                'entity_name' => $validated['client'],
                'date' => $validated['date'],
                'observations' => $validated['observations'],
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 5,
                'movement_id' => $movement->id,
            ]);

            $items = Item::whereIn('id', collect($validated['items_list'])->pluck('id'))->lockForUpdate()->get()->keyBy('id');

            $records = collect($validated['items_list'])->map(function ($sellRecord) use ($procedure, $validated, $items) {
                $itemId = $sellRecord['id'];
                $item = $items->get($itemId);

                if (!$item)
                {
                    throw new \Exception("Item com ID $itemId não existe.");
                }

                $item->quantity -= $sellRecord['movement_quantity'];
                $item->save();

                return Record::create([
                    'item_id' => $itemId,
                    'name' => $sellRecord['name'],
                    'quantity' => $sellRecord['quantity'],
                    'movement_quantity' => $sellRecord['movement_quantity'],
                    'measurement_unit' => $sellRecord['measurement_unit'],
                    'price' => $sellRecord['price'],
                    'amount' => $sellRecord['amount'],
                    'procedure_id' => $procedure->id,
                    'past' => true,
                    'register_date' => $validated['date'],
                ]);
            });

            if ($validated['entry_value'] > 0)
            {
                Record::create([
                    'procedure_id' => $procedure->id,
                    'amount' => $validated['entry_value'],
                    'past' => true,
                    'register_date' => $validated['date'],
                ]);
            }

            return back();
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $movement = Movement::findOrFail($id);
            $accounting = Accounting::findOrFail($movement->accounting_id);


            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 3,
                'department_id' => 5,
                'movement_id' => $movement->id,
            ]);

            // Buscar todos os procedimentos associados ao movimento
            $procedures = Procedure::where('movement_id', $movement->id)->get();

            // Para cada procedimento, excluir os registros associados
            foreach ($procedures as $procedure)
            {
                $records = Record::where('procedure_id', $procedure->id)->get();

                foreach ($records as $record)
                {
                    if ($record->item_id !== null)
                    {
                        $item = Item::find($record->item_id);
                        if ($item)
                        {
                            $item->quantity += $record->movement_quantity;
                            $item->save();
                        }
                    }
                    //$record->delete();
                }
                //$procedure->delete();
            }

            // Excluir a contabilidade e o movimento
            //$accounting->delete();
            $movement->delete();

            return back();
        });
    }
}
