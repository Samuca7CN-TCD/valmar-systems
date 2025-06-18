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

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = Department::find(8);

        $services = Movement::where('type', 1)->where('ready', false)->where('ready', false)->orderBy('deadline')->with(['accounting', 'procedures.records', 'procedures.records.procedure.user', 'procedures.user', 'budget'])->get()
            ->map(function ($movement) {
                $records = $movement->procedures->flatMap(function ($procedure) {
                    return $procedure->records;
                });
                $movement->records = $records;
                return $movement;
            });

        $sells = Movement::with(['accounting:id,total_value'])
            ->where('type', 2)
            ->get(['id', 'entity_name', 'date', 'accounting_id']); 
        

        return Inertia::render('Service', [
            'page' => $page,
            'services_list' => $services,
            'sells_list' => $sells,
        ]);
    }

    public function previous(Request $request)
    {
        $page = Department::find(8);

        $perPage = 80;
        $search = $request->input('search');

        $servicesQuery = Movement::where('type', 1)
        ->whereIn('service_status', ['Finalizado', 'Cancelado'])
            ->orderByDesc('completion_date')
            ->orderByDesc('deleted_at')
            ->with([
                'accounting',
                'procedures.records.procedure.user',
                'procedures.user',
                'budget'
            ])
            ->withTrashed();

        if ($search) {
            $servicesQuery->where(function ($query) use ($search) {
                // Filtros para colunas diretamente na tabela movements
                $query->where('id', 'like', '%' . $search . '%')
                      ->orWhere('motive', 'like', '%' . $search . '%')
                      ->orWhere('entity_name', 'like', '%' . $search . '%')
                      ->orWhere('observations', 'like', '%' . $search . '%')
                      ->orWhere('service_status', 'like', '%' . $search . '%');

                $query->orWhereHas('accounting', function ($q) use ($search) {
                    $q->where('total_value', 'like', '%' . $search . '%')
                      ->orWhere('partial_value', 'like', '%' . $search . '%');
                });
            });
        }

        $servicesQuery->whereHas('accounting', function ($query) {
            $query->where('partial_value', 0);
        });


        $services = $servicesQuery->paginate($perPage);

        $services->through(function ($movement) {
            $records = $movement->procedures->flatMap(function ($procedure) {
                return $procedure->records;
            });
            $movement->records = $records;
            return $movement;
        });

        $sells = Movement::with(['accounting:id,movement_id,total_value'])
            ->where('type', 2)
            ->get(['id', 'entity_name', 'date']);


        return Inertia::render('Services/Previous', [
            'page' => $page,
            'services_list' => $services,
            'sells_list' => $sells,
            'filters' => $request->only(['search']), // Passa os filtros para o frontend
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
                'title' => ['required', 'string'],
                'client' => ['required', 'string'],
                'total_value' => ['required', 'numeric'],
                'deadline' => ['required', 'date_format:Y-m-d'],
                'observations' => ['nullable', 'string'],
                'previous_id' => ['nullable', 'numeric'],
                'records_list' => ['required', 'array'],
                'records_list.enable_records' => ['required', 'boolean'],
                'records_list.data' => ['nullable', 'array'],
                'records_list.data.*.amount' => ['required', 'numeric'],
                'records_list.data.*.payment_method' => ['required', 'string'],
                'records_list.data.*.register_date' => ['required', 'date_format:Y-m-d'],
                'records_list.data.*.filepath' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
            ]);

            $accounting = Accounting::create([
                'estimated_value' => $validated['total_value'],
                'total_value' => $validated['total_value'],
                'partial_value' => $validated['total_value'],
            ]);

            $movement = Movement::create([
                'type' => 1,
                'accounting_id' => $accounting->id,
                'motive' => $validated['title'],
                'deadline' => $validated['deadline'],
                'entity_name' => $validated['client'],
                'observations' => $validated['observations'],
                'previous_id' => $validated['previous_id'],
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
                        'payment_method' => $payRecord['payment_method'],
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

            return back();
        });
    }

    /**
     * Exibe os detalhes de um serviço específico em um modal,
     * ou a lista de serviços com o modal pré-aberto se um ID for fornecido.
     *
     * @param string $id O ID do serviço a ser exibido. Pode ser 'null' ou um ID válido.
     * @param Request $request O objeto Request para lidar com filtros de pesquisa.
     * @return \Inertia\Response
     */
    public function show(string $id = null, Request $request)
    {
        // Verifica se o ID fornecido corresponde a um serviço não concluído ou já concluído.
        // A lógica atual do seu método show possui duas ramificações.
        // Vamos unificar a forma como o ID é passado para o frontend,
        // mas manter as duas lógicas de busca de dados, decidindo qual página renderizar.

        // Tentativa de buscar o serviço para determinar se ele é 'não concluído' ou 'concluído'
        // para renderizar a página correta.
        $targetService = null;
        if ($id !== null && $id !== 'null') { // 'null' como string pode vir da rota
            $targetService = Movement::where('id', $id)->first();
        }

        if ($targetService && $targetService->type == 1 && !$targetService->ready) {
            // Se for um serviço ainda não concluído
            $page = Department::find(8);

            $services = Movement::where('type', 1)->where('ready', false)->orderBy('deadline')->with(['accounting', 'procedures.records.procedure.user', 'procedures.user', 'budget'])->get()
                ->map(function ($movement) {
                    $records = $movement->procedures->flatMap(function ($procedure) {
                        return $procedure->records;
                    });
                    $movement->records = $records;
                    return $movement;
                });

            $sells = Movement::with(['accounting:id,movement_id,total_value'])
                ->where('type', 2)
                ->get(['id', 'entity_name', 'date']);

            return Inertia::render('Service', [
                'page' => $page,
                'services_list' => $services,
                'sells_list' => $sells,
                'show_service_id' => $id, // Passa o ID para o frontend
            ]);
        } else {
            // Se for serviço já concluído ou se o ID não foi encontrado ou é nulo
            // (aqui também será a rota padrão se nenhum ID for fornecido na URL)
            $page = Department::find(8);

            $perPage = 80;
            $search = $request->input('search');

            $servicesQuery = Movement::where('type', 1)
                ->whereIn('service_status', ['Finalizado', 'Cancelado'])
                ->orderByDesc('completion_date')
                ->orderByDesc('deleted_at')
                ->with([
                    'accounting',
                    'procedures.records.procedure.user',
                    'procedures.user',
                    'budget'
                ])
                ->withTrashed();

            if ($search) {
                $servicesQuery->where(function ($query) use ($search) {
                    // Filtros para colunas diretamente na tabela movements
                    $query->where('id', 'like', '%' . $search . '%')
                        ->orWhere('motive', 'like', '%' . $search . '%')
                        ->orWhere('entity_name', 'like', '%' . $search . '%')
                        ->orWhere('observations', 'like', '%' . $search . '%')
                        ->orWhere('service_status', 'like', '%' . $search . '%');

                    $query->orWhereHas('accounting', function ($q) use ($search) {
                        $q->where('total_value', 'like', '%' . $search . '%')
                            ->orWhere('partial_value', 'like', '%' . $search . '%');
                    });
                });
            }

            $servicesQuery->whereHas('accounting', function ($query) {
                $query->where('partial_value', 0);
            });


            $services = $servicesQuery->paginate($perPage);

            $services->through(function ($movement) {
                $records = $movement->procedures->flatMap(function ($procedure) {
                    return $procedure->records;
                });
                $movement->records = $records;
                return $movement;
            });

            $sells = Movement::with(['accounting:id,movement_id,total_value'])
                ->where('type', 2)
                ->get(['id', 'entity_name', 'date']);


            return Inertia::render('Services/Previous', [
                'page' => $page,
                'services_list' => $services,
                'sells_list' => $sells,
                'filters' => $request->only(['search']), // Passa os filtros para o frontend
                'show_service_id' => $id, // Passa o ID para o frontend
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function get_record($procedure_id)
    {
        return Record::where('procedure_id', $procedure_id)->get();
    }

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
                'title' => 'required|string',
                'client' => 'required|string',
                'total_value' => 'required|numeric',
                'deadline' => 'required|date_format:Y-m-d',
                'previous_id' => 'nullable|numeric',
                'observations' => 'nullable|string',
                'delay_reason' => 'nullable|string',
                'delayed' => 'nullable|boolean',
                'completion_date' => 'nullable|date_format:Y-m-d',
                'records_list' => 'required|array',
                'records_list.enable_records' => 'required|boolean',
                'records_list.data' => 'nullable|array',
                'records_list.data.*.amount' => 'required|numeric',
                'records_list.data.*.payment_method' => 'nullable|string',
                'records_list.data.*.register_date' => 'required|date_format:Y-m-d',
                'records_list.data.*.filepath' => 'nullable|file|mimes:pdf|max:2048',
            ]);

            // Recupera o recurso existente pelo ID
            $movement = Movement::findOrFail($id);
            $accounting = $movement->accounting;

            
            if ($movement && $accounting)
            {
                $paid_amount = $accounting->total_value - $accounting->partial_value;
                $accounting->update([
                    'total_value' => $validated['total_value'],
                    'partial_value' => $validated['total_value'] - $paid_amount,
                ]);

                // Garante que o movimento exista antes de atualizar
                $old_movement = $movement;
                
                $movement->update([
                    'motive' => $validated['title'],
                    'entity_name' => $validated['client'],
                    'observations' => $validated['observations'],
                    'deadline' => $validated['deadline'],
                    'previous_id' => $validated['previous_id'],
                    'delay_reason' => $validated['delay_reason'],
                    'delayed' => !empty($validated['delay_reason']) && empty($validated['delayed']),
                    'completion_date' => $validated['completion_date']
                ]);

                // Cria um novo procedimento para registrar a ação de atualização
                $procedure = Procedure::create([
                    'user_id' => Auth::id(),
                    'action_id' => 2, // Ajuste conforme necessário
                    'department_id' => 7, // Ajuste conforme necessário
                    'movement_id' => $movement->id,
                    //'old_movement' => json_encode($old_movement),
                    //'new_movement' => json_encode($movement)
                ]);
            }
            return back();
        });
    }

    public function pay(Request $request, $id)
    {
        return DB::transaction(function () use ($id, $request) {
            $validated = $request->validate([
                'records_list' => 'required|array',
                'records_list.enable_records' => 'required|boolean',
                'records_list.data' => 'nullable|array',
                'records_list.data.*.id' => 'nullable|numeric',
                'records_list.data.*.amount' => 'required|numeric',
                'records_list.data.*.payment_method' => 'nullable|string',
                'records_list.data.*.past' => 'required|boolean',
                'records_list.data.*.register_date' => 'required|date_format:Y-m-d',
                // 'records_list.data.*.filepath' => 'nullable|file|mimes:pdf|max:2048',
            ]);
            
            $movement = Movement::findOrFail($id);

            // Recupera o recurso existente pelo ID
            $accounting = $movement->accounting;

            if ($movement && $accounting)
            {
                // Cria um novo procedimento para registrar a ação de pagamento
                $procedure = Procedure::create([
                    'user_id' => Auth::id(),
                    'action_id' => 4, // Ajuste conforme necessário
                    'department_id' => 8, // Ajuste conforme necessário
                    'movement_id' => $accounting->movement ? $accounting->movement->id : null,
                ]);

                $records = collect($validated['records_list']['data'])
                    ->filter(function ($payRecord) {
                        return !$payRecord['past'];
                    })
                    ->map(function ($payRecord) use ($procedure) {
                        return Record::create([
                            'procedure_id' => $procedure->id,
                            'amount' => $payRecord['amount'],
                            'payment_method' => $payRecord['payment_method'],
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
            }
        });
    }


    public function conclude($id)
    {
        return DB::transaction(function () use ($id) {
            // Recupera o recurso existente pelo ID
            $movement = Movement::findOrFail($id);

            $movement->update([
                'ready' => true,
                'service_status' => 'Finalizado',
            ]);

            // Cria um novo procedimento para registrar a ação de pagamento
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 5, // Ajuste conforme necessário
                'department_id' => 8, // Ajuste conforme necessário
                'movement_id' => $movement->id,
            ]);

            return back();
        });
    }

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
            'department_id' => 8,
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
        //$accounting->delete();
        $movement->delete();

        return back();
    }

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'service_status' => ['required', 'string'],
        ]);

        $movement = Movement::findOrFail($id);

        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 3,
            'department_id' => 8,
            'movement_id' => $movement->id,
        ]);

        $movement->service_status = $request->service_status;
        $movement->save();

        return back()->with('success', 'Status alterado com sucesso!');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:10'],
        ]);

        $movement = Movement::findOrFail($id);

        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 3,
            'department_id' => 8,
            'movement_id' => $movement->id,
        ]);

        $movement->cancellation_reason = $request->cancellation_reason;
        $movement->service_status = 'Cancelado';
        $movement->save();

        $movement->delete();

        return back()->with('success', 'Serviço cancelado com sucesso!');
    }
}
