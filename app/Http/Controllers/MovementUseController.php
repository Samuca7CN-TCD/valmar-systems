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

class MovementUseController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $page = Department::find(4);
        $items = Item::with('measurement_unit')->orderBy('name')->get();

        // Recebe os parâmetros da consulta, ou define os valores padrão
        $parameters = [
            'default' => count($request->query->all()) > 0 ? false : true,
            'employee_id' => $request->query('employee_id', 0),
            'motive' => $request->query('motive', ''),
            'start_date' => $request->query('start_date', Carbon::today()->toDateString()),
            'end_date' => $request->query('end_date', Carbon::today()->toDateString()),
        ];

        // Aplica os filtros baseados nos parâmetros
        $uses = Movement::where('type', 3)
            ->whereBetween('date', [$parameters['start_date'], $parameters['end_date']])
            ->with(['accounting', 'procedures.records']);

        if ($parameters['employee_id'])
        {
            $uses->whereHas('procedures.records', function ($query) use ($parameters) {
                $query->where('employee_id', $parameters['employee_id']);
            });
        }
        if ($parameters['motive'])
        {
            $uses->where('motive', 'LIKE', "%{$parameters['motive']}%");
        }

        $uses = $uses->get()->map(function ($movement) {
            $records = $movement->procedures->flatMap->records;
            $movement->items = $records->filter(fn($record) => $record->item_id !== null);
            return $movement;
        });

        $employees = Employee::orderBy('name')->orderBy('surname')->get();
        $services = Movement::where('type', 1)->get();

        return Inertia::render('Movements/Uses', [
            'page' => $page,
            'uses_list' => $uses,
            'items' => $items,
            'employees_list' => $employees,
            'parameters' => $parameters,
            'services_list' => $services,
        ]);
    }


    public function filter(Request $request)
    {
        $parameters = [
            'employee_id' => $request->input('employee_id', 0),
            'motive' => $request->input('motive', ''),
            'start_date' => $request->input('start_date', Carbon::today()->toDateString()),
            'end_date' => $request->input('end_date', Carbon::today()->toDateString()),
        ];

        return redirect()->route('uses.index', $parameters);
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
        return DB::transaction(function () use ($request) {
            $validated = $request->validate([
                // 'employee_id' => ['nullable', 'numeric'],
                // 'employee' => ['required', 'string'],
                'motive' => ['required', 'string'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
                'observations' => ['nullable', 'string'],
                'total_value' => ['required', 'numeric', 'gt:0'],
                'items_list' => ['required', 'array', 'min:1'],
                'items_list.*.id' => ['required', 'numeric', 'exists:items,id'],
                'items_list.*.name' => ['required', 'string'],
                'items_list.*.movement_quantity' => ['required', 'numeric', 'gt:0'],
                'items_list.*.quantity' => ['required', 'numeric', 'gt:0'],
                'items_list.*.measurement_unit' => ['required', 'string'],
                'items_list.*.price' => ['required', 'numeric', 'gt:0'],
                'items_list.*.amount' => ['required', 'numeric', 'gt:0'],
                'items_list.*.employee_id' => ['required', 'numeric', 'gt:0'],
            ]);

            $accounting = Accounting::create([
                'estimated_value' => $validated['total_value'],
                'total_value' => $validated['total_value'],
                'partial_value' => $validated['total_value'],
            ]);

            $movement = Movement::create([
                'type' => 3,
                'accounting_id' => $accounting->id,
                'motive' => $validated['motive'],
                //'employee_id' => $validated['employee_id'],
                //'entity_name' => $validated['employee'],
                'date' => $validated['date'],
                'observations' => $validated['observations'],
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 4,
                'movement_id' => $movement->id,
            ]);

            $items = Item::whereIn('id', collect($validated['items_list'])->pluck('id'))->lockForUpdate()->get()->keyBy('id');

            $records = collect($validated['items_list'])->map(function ($useRecord) use ($procedure, $validated, $items) {
                $itemId = $useRecord['id'];
                $item = $items->get($itemId);

                if (!$item)
                {
                    throw new \Exception("Item com ID $itemId não existe.");
                }

                $item_previous_quantity = $item->quantity;
                $item->quantity -= $useRecord['movement_quantity'];
                $item->save();

                return Record::create([
                    'item_id' => $itemId,
                    'employee_id' => $useRecord['employee_id'],
                    'name' => $useRecord['name'],
                    'quantity' => $item_previous_quantity,
                    'movement_quantity' => $useRecord['movement_quantity'],
                    'measurement_unit' => $useRecord['measurement_unit'],
                    'price' => $useRecord['price'],
                    'amount' => $useRecord['amount'],
                    'procedure_id' => $procedure->id,
                    'past' => true,
                    'content' => json_encode($useRecord),
                    'register_date' => $validated['date'],
                ]);
            });

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
                'department_id' => 4,
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
