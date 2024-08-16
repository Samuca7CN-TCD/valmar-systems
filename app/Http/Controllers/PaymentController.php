<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\Action;

use App\Models\Department;
use App\Models\Accounting;
use App\Models\Movement;
use App\Models\Procedure;
use App\Models\Record;

use App\Models\Employee;
use App\Models\Item;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = Department::find(7);

        $payments = Movement::where('type', '>', -1)->where('type', '<', 3)->with(['accounting', 'procedures.records'])
            ->whereHas('accounting', function ($query) {
                $query->where('partial_value', '>', 0.009);
            })
            ->get()
            ->map(function ($movement) {
                $records = $movement->procedures->flatMap(function ($procedure) {
                    return $procedure->records;
                });
                $movement->records = $records->filter(function ($record) {
                    return !$record->item_id;
                })->values()->all();
                return $movement;
            });

        return Inertia::render('Payment', [
            'page' => $page,
            'payments_list' => $payments,
        ]);
    }

    public function previous()
    {
        $page = Department::find(7);

        $payments = Movement::where('type', 0)->with(['accounting', 'procedures.records'])
            ->whereHas('accounting', function ($query) {
                $query->where('partial_value', 0);
            })
            ->get()
            ->map(function ($movement) {
                $records = $movement->procedures->flatMap(function ($procedure) {
                    return $procedure->records;
                });
                $movement->records = $records;
                return $movement;
            });

        return Inertia::render('Payments/Previous', [
            'page' => $page,
            'payments_list' => $payments,
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
            //dd($request);
            $validated = $request->validate([
                'debt' => ['required', 'string'],
                'debtor' => ['required', 'string'],
                'total_value' => ['required', 'numeric'],
                'observations' => ['nullable', 'string'],
                'records_list' => ['required', 'array'],
                'records_list.enable_records' => ['required', 'boolean'],
                'records_list.data' => ['nullable', 'array'],
                'records_list.data.*.amount' => ['required', 'numeric'],
                'records_list.data.*.register_date' => ['required', 'date_format:Y-m-d'],
                'records_list.data.*.filepath' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            ]);

            $accounting = Accounting::create([
                'estimated_value' => $validated['total_value'],
                'total_value' => $validated['total_value'],
                'partial_value' => $validated['total_value'],
            ]);

            $movement = Movement::create([
                'type' => 0,
                'accounting_id' => $accounting->id,
                'motive' => $validated['debt'],
                'entity_name' => $validated['debtor'],
                'observations' => $validated['observations'],
                'date' => now()->format('Y-m-d'),
            ]);

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 7,
                'movement_id' => $movement->id,
            ]);

            $records = collect();

            if ($request->records_list['enable_records'])
            {
                $records = collect($request->records_list['data'])->map(function ($payRecord) use ($procedure) {
                    return Record::create([
                        'procedure_id' => $procedure->id,
                        'amount' => $payRecord['amount'],
                        'past' => true,
                        'register_date' => $payRecord['register_date'],
                    ]);
                });

                // Calcula a soma total dos valores dos registros
                $totalRecordAmount = $records->sum('amount');

                // Atualiza o campo partial_value da contabilidade após o loop
                $accounting->partial_value -= $totalRecordAmount;
                $accounting->save();
            }

            // Salva todos os modelos de uma vez
            // $accounting->save();
            // $movement->save();
            // $procedure->save();
            // $records->each->save();

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

    public function get_record($procedure_id)
    {
        return Record::where('procedure_id', $procedure_id)->get();
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
    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $validated = $request->validate([
                'id' => 'required|numeric',
                'debt' => 'required|string',
                'debtor' => 'required|string',
                'total_value' => 'required|numeric',
                'observations' => 'nullable|string',
                'records_list' => 'required|array',
                'records_list.enable_records' => 'required|boolean',
                'records_list.data' => 'nullable|array',
                'records_list.data.*.id' => 'required|numeric',
                'records_list.data.*.amount' => 'required|numeric',
                'records_list.data.*.past' => 'required|boolean',
                'records_list.data.*.register_date' => 'required|date_format:Y-m-d',
                'records_list.data.*.filepath' => 'nullable|file|mimes:pdf|max:2048',
            ]);

            // Recupera o recurso existente pelo ID
            $accounting = Accounting::findOrFail($id);
            $paid_amount = $accounting->total_value - $accounting->partial_value;
            $accounting->update([
                'total_value' => $validated['total_value'],
                'partial_value' => $validated['total_value'] - $paid_amount,
            ]);

            // Garante que o movimento exista antes de atualizar
            $movement = $accounting->movement;
            if ($movement)
            {
                $movement->update([
                    'motive' => $validated['debt'],
                    'entity_name' => $validated['debtor'],
                    'observations' => $validated['observations'],
                ]);

                // Cria um novo procedimento para registrar a ação de atualização
                $procedure = Procedure::create([
                    'user_id' => Auth::id(),
                    'action_id' => 2, // Ajuste conforme necessário
                    'department_id' => 7, // Ajuste conforme necessário
                    'movement_id' => $movement->id,
                ]);


            }

            // Atualiza os registros
            /*
            $recordsToDelete = array_filter($validated['records_list']['data'], function ($recordData) {
                return $recordData['deleted_at'] !== null;
            });
            
            $recordIds = array_column($recordsToDelete, 'id');
            
            if (!empty($recordIds)) {
                // Carregar todos os registros a serem excluídos em uma única consulta
                $records = Record::whereIn('id', $recordIds)->get();
            
                foreach ($records as $record) {
                    $accounting->update([
                        'partial_value' => $accounting->partial_value + $record->amount,
                    ]);
            
                    $record->update([
                        'procedure_id' => $procedure->id,
                    ]);
            
                    $record->delete();
                }
            }
            */

            return back();
        });
    }


    public function pay(Request $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $validated = $request->validate([
                'id' => 'required|numeric',
                'records_list' => 'required|array',
                'records_list.enable_records' => 'required|boolean',
                'records_list.data' => 'nullable|array',
                'records_list.data.*.id' => 'required|numeric',
                'records_list.data.*.amount' => 'required|numeric',
                'records_list.data.*.past' => 'required|boolean',
                'records_list.data.*.register_date' => 'required|date_format:Y-m-d',
                'records_list.data.*.filepath' => 'nullable|file|mimes:pdf|max:2048',
            ]);
            // Recupera o recurso existente pelo ID
            $accounting = Accounting::findOrFail($id);

            // Cria um novo procedimento para registrar a ação de pagamento
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 4, // Ajuste conforme necessário
                'department_id' => 7, // Ajuste conforme necessário
                'movement_id' => $accounting->movement->id,
            ]);

            $records = collect($validated['records_list']['data'])
                ->filter(function ($payRecord) {
                    return !$payRecord['past'];
                })
                ->map(function ($payRecord) use ($procedure) {
                    return Record::create([
                        'procedure_id' => $procedure->id,
                        'amount' => $payRecord['amount'],
                        'register_date' => $payRecord['register_date'],
                    ]);
                });

            // Calcula a soma total dos valores dos registros
            $totalRecordAmount = $records->sum('amount');

            // Atualiza o modelo Accounting
            $accounting->update([
                'partial_value' => $accounting->partial_value - $totalRecordAmount,
            ]);

            return back();
        });
    }


    /*
    public function recreate(Request $request, $id)
    {
        $previous_movement = Movement::findOrFail($id);
        $previous_accounting = Accounting::findOrFail($previous_movement->accounting_id);
        $previous_procedure = Procedure::where('movement_id', $previous_movement->id)->first();
        $previous_records = Record::where('procedure_id', $previous_procedure->id)->get();

        $validated = $request->validate([
            'debt' => ['required', 'string'],
            'debtor' => ['required', 'string'],
            'total_amount' => ['required', 'numeric'],
            'observations' => ['nullable', 'string'],
            'records_list' => ['required','array'],
            'records_list.enable_records' => ['required', 'boolean'],
            'records_list.data' => ['nullable', 'array'],
            'records_list.data.*.amount' => ['required', 'numeric'],
            'records_list.data.*.register_date' => ['required', 'date_format:Y-m-d'],
            'records_list.data.*.filepath' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        $accounting = Accounting::create([
            'estimated_value' => $validated['total_amount'],
            'total_value' => $validated['total_amount'],
            'partial_value' => $validated['total_amount'],
        ]);

        $movement = Movement::create([
            'previous_id' => $previous_movement->id,
            'accounting_id' => $accounting->id,
            'motive' => $validated['debt'],
            'entity_name' => $validated['debtor'],
            'observations' => $validated['observations'],
            'date' => Carbon::now()->format('Y-m-d'),
        ]);
        $previous_movement->remaked = true;

        $procedure = Procedure::create([
            'previous_id' => $previous_procedure->id,
            'user_id' => Auth::id(),
            'action_id' => 1,
            'department_id' => 7,
            'movement_id' => $movement->id,
        ]);
        $previous_procedure->remaked = true;

        $records = [];
        if ($validated['records_list']['enable_records']) {
            $i = 0;
            foreach ($validated['records_list']['data'] as $record_item) {

                $record = Record::create([
                    'procedure_id' => $procedure->id,
                    'amount' => $record_item['amount'],
                    'register_date' => $record_item['register_date'],
                ]);
                if ($record) {
                    $accounting->partial_value -= $record->amount;
                    array_push($records, $record);
                }
                $i++;
            }
        }
        $records = collect($records);

        $accounting->save();
        $movement->save();
        $previous_movement->save();
        $procedure->save();
        $previous_procedure->save();
        $records?->each->save();

        $previous_records->each->delete();
        $previous_procedure->delete();
        $previous_accounting->delete();
        $previous_movement->delete();

        return back();        
    }
    */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $movement = Movement::findOrFail($id);
        $accounting = Accounting::findOrFail($movement->accounting_id);

        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 3,
            'department_id' => 7,
            'movement_id' => $movement->id,
        ]);

        // Buscar todos os procedimentos associados ao movimento
        //$procedures = Procedure::where('movement_id', $movement->id)->get();

        // Para cada procedimento, excluir os registros associados
        /*foreach ($procedures as $procedure) {
            $records = Record::where('procedure_id', $procedure->id)->get();
            $records->each->delete();
            $procedure->delete();
        }*/

        // Excluir a contabilidade e o movimento
        // $accounting->delete();
        $movement->delete();

        return back();
    }
}
