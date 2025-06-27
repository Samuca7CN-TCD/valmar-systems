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
use App\Models\Client;

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
            ->with(['accounting', 'procedures.records', 'client']);
        
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
        return DB::transaction(function () use ($request) {
            $validated = $request->validate([
                'client_id' => ['required', 'exists:clients,id'],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
                'estimated_value' => ['required', 'numeric', 'gt:0'],
                'total_value' => ['required', 'numeric', 'gt:0'],
                'entry_value' => ['required', 'numeric', 'gte:0', 'lte:total_value'],
                'observations' => ['nullable', 'string'],
                'items_list' => ['required', 'array', 'min:1'],
                'items_list.*.id' => ['required', 'numeric', 'exists:items,id'],
                'items_list.*.name' => ['required', 'string'],
                'items_list.*.movement_quantity' => ['required', 'numeric', 'gt:0', 'lte:items_list.*.quantity'],
                'items_list.*.quantity' => ['required', 'numeric', 'gt:0'],
                'items_list.*.measurement_unit' => ['required', 'string'],
                'items_list.*.price' => ['required', 'numeric', 'gt:0'],
                'items_list.*.amount' => ['required', 'numeric', 'gt:0'],
            ]);

            $client = Client::find($validated['client_id']);

            $accounting = Accounting::create([
                'estimated_value' => $validated['estimated_value'],
                'total_value' => $validated['total_value'],
                'partial_value' => $validated['total_value'] - $validated['entry_value'],
            ]);

            $movement = Movement::create([
                'type' => 2,
                'accounting_id' => $accounting->id,
                'motive' => 'Venda de Material',
                'client_id' => $validated['client_id'],
                'entity_name' => $client->name, // Adicionado para compatibilidade
                'date' => $validated['date'],
                'observations' => $validated['observations'],
            ]);
            
            // O Trait Auditable no Movement já cuidou do log de criação do movimento.
            // No entanto, criamos uma Procedure separada para agrupar os múltiplos
            // 'records' que representam os itens vendidos e o pagamento de entrada.
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1, // 'create'
                'department_id' => 5, // 'Vendas'
                'movement_id' => $movement->id, // Mantido por compatibilidade
                // Associando a procedure ao movimento que a gerou
                'auditable_id' => $movement->id,
                'auditable_type' => Movement::class,
            ]);

            $items = Item::whereIn('id', collect($validated['items_list'])->pluck('id'))->lockForUpdate()->get()->keyBy('id');

            // Para cada item vendido, criar um registro
            collect($validated['items_list'])->map(function ($sellRecord) use ($procedure, $validated, $items, $movement) {
                $item = $items->get($sellRecord['id']);

                if (!$item) {
                    throw new \Exception("Item com ID {$sellRecord['id']} não existe.");
                }

                $item->quantity -= $sellRecord['movement_quantity'];
                $item->save();

                // **CORREÇÃO:** Adicionados auditable_id e auditable_type
                return Record::create([
                    'item_id' => $item->id,
                    'procedure_id' => $procedure->id,
                    'name' => $sellRecord['name'],
                    'quantity' => $sellRecord['quantity'],
                    'movement_quantity' => $sellRecord['movement_quantity'],
                    'measurement_unit' => $sellRecord['measurement_unit'],
                    'price' => $sellRecord['price'],
                    'amount' => $sellRecord['amount'],
                    'past' => true,
                    'content' => json_encode($sellRecord),
                    'register_date' => $validated['date'],
                    // Apontando para a venda (Movement) como a entidade auditável deste registro específico
                    'auditable_id' => $movement->id,
                    'auditable_type' => Movement::class,
                ]);
            });

            // Se houver pagamento de entrada, criar um registro para ele
            if ($validated['entry_value'] > 0) {
                 // **CORREÇÃO:** Adicionados auditable_id e auditable_type
                Record::create([
                    'procedure_id' => $procedure->id,
                    'amount' => $validated['entry_value'],
                    'past' => true,
                    'register_date' => $validated['date'],
                    // Apontando também para a venda (Movement)
                    'auditable_id' => $movement->id,
                    'auditable_type' => Movement::class,
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


            /*$procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 3,
                'department_id' => 5,
                'movement_id' => $movement->id,
            ]);*/

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
