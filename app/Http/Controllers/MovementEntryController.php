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

class MovementEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $page = Department::find(3);
        $items = Item::with('measurement_unit')->orderBy('name')->get();

        $entries = Movement::where('type', 4)
            ->with(['accounting', 'procedures.records'])
            ->get()
            ->map(function ($movement) {
                $records = $movement->procedures->flatMap(function ($procedure) {
                    return $procedure->records;
                });

                // Filtra os registros com item_id diferente de null e mapeia para o item correspondente
                $movement->items = $records->filter(function ($record) {
                    return $record->item_id !== null;
                });

                return $movement;
            });


        // dd($entries);

        return Inertia::render('Movements/Entries', [
            'page' => $page,
            'entries_list' => $entries,
            'items' => $items,
        ]);
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
                'employee' => ['required', 'string'],
                'motive' => ['required', 'string'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
                'observations' => ['nullable', 'string'],
                'total_value' => ['required', 'numeric', 'gt:0'],
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
                'estimated_value' => $validated['total_value'],
                'total_value' => $validated['total_value'],
                'partial_value' => $validated['total_value'],
            ]);

            $movement = Movement::create([
                'type' => 4,
                'accounting_id' => $accounting->id,
                'motive' => $validated['motive'],
                'entity_name' => $validated['employee'],
                'date' => $validated['date'],
                'observations' => $validated['observations'],
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 3,
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

                $item->quantity += $useRecord['movement_quantity'];
                $item->save();

                return Record::create([
                    'item_id' => $itemId,
                    'name' => $useRecord['name'],
                    'quantity' => $useRecord['quantity'],
                    'movement_quantity' => $useRecord['movement_quantity'],
                    'measurement_unit' => $useRecord['measurement_unit'],
                    'price' => $useRecord['price'],
                    'amount' => $useRecord['amount'],
                    'procedure_id' => $procedure->id,
                    'past' => true,
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
                'department_id' => 3,
                'movement_id' => $movement->id,
            ]);

            // Buscar todos os procedimentos associados ao movimento
            //$procedures = Procedure::where('movement_id', $movement->id)->get();

            // Para cada procedimento, excluir os registros associados
            /*foreach ($procedures as $procedure) {
                $records = Record::where('procedure_id', $procedure->id)->get();
                
                foreach($records as $record){
                    if($record->item_id !== null){
                        $item = Item::find($record->item_id);
                        $item->quantity += $record->quantity;
                        $item->save();
                    }
                    $record->delete();
                }
                $procedure->delete();
            }*/

            // Excluir a contabilidade e o movimento
            //$accounting->delete();
            $movement->delete();

            return back();
        });
    }
}