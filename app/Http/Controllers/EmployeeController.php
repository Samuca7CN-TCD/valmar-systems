<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Bank;
use App\Models\Department;
use App\Models\PaymentMethod;
use App\Models\Procedure;
use App\Models\Record;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Employee;
use Inertia\Inertia;

use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = Department::where('type', 'employee')->first();
        $employees = Employee::orderBy('name')->orderBy('surname')->get();
        $account_types = AccountType::all();
        $payment_method = PaymentMethod::all();
        $banks = Bank::all();
        return Inertia::render('Employee', [
            'page' => $page,
            'employees_list' => $employees,
            'account_types_list' => $account_types,
            'payment_methods_list' => $payment_method,
            'banks_list' => $banks,
        ]);
    }

    public function previous()
    {
        $page = Department::find(8);

        $employees = Employee::onlyTrashed()->where('fired', true)->get();

        return Inertia::render('Employees/Previous', [
            'page' => $page,
            'employees_list' => $employees,
        ]);
    }

    public function overtime_calculation_list($date = null)
    {
        // Se a data não for fornecida, obtém a última data de criação de um registro específico
        if (!$date)
        {
            $latestRecord = Procedure::where('department_id', 9)
                ->where('action_id', 7)->latest('created_at')->first();
            $search_date = $latestRecord? $latestRecord->created_at->format('Y-m-d') : date('Y-m-d');
        } else
        {
            $search_date = $date;
        }

        // Consulta na tabela records, com relacionamento com procedures e users
        $records = Record::with(['procedure.user'])
            ->whereHas('procedure', function ($query) {
                $query->where('department_id', 9)
                    ->where('action_id', 7);
            })
            ->whereDate('register_date', $search_date)
            ->orderByDesc('id')
            ->get();

        return $records;
    }



    public function overtime_calculation_save(Request $request)
    {
        // Obtém os dados dos funcionários a partir da requisição
        $employees = $request->input('employees', '');

        return DB::transaction(function () use ($employees) {
            /*
            * NOTA DE ARQUITETURA:
            * O logging manual foi mantido aqui intencionalmente.
            * Esta ação ('save' para a tabela de horas extras) não opera sobre um único
            * modelo Eloquent (como Employee ou Item), mas sim salva um payload de dados.
            * O Trait 'Auditable' é projetado para ser acionado por eventos de modelo
            * (created, updated, deleted). Como esta operação não dispara um desses eventos
            * em um modelo auditável, o log manual continua sendo a abordagem correta.
            */

            // Cria um novo procedimento
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 10, // Ação: save
                'department_id' => 11,
                'movement_id' => null,
            ]);

            // Cria um novo registro
            $record = Record::create();

            // Redireciona para a rota de cálculo de horas extras do empregado
            return redirect()->route('employee.overtime_calculation');
        });
    }


    public function overtime_calculation($id = null)
    {
        // Carregando dados essenciais de uma vez
        $page = Department::find(11);
        $employees = Employee::with('overtimePaymentMethod')->where('id', '<>', 1)->orderBy('name')->orderBy('surname')->get();
        $account_types = AccountType::all();
        $payment_method = PaymentMethod::all();
        $banks = Bank::all();
        $page_mode = $id? 'old' : 'new';
        $user = null;
        $procedure_date = null;

        if ($page_mode === 'new')
        {
            // Calculando valores e adicionando atributos diretamente no loop
            $employees->transform(function ($employee) {
                $salary = $employee->salary;
                $total_time = 8.75;
                $hourly_rate = $salary / 220;
                $employee->fifty_percent_value = ($hourly_rate * $total_time) * 1.5; // +50%
                $employee->hundred_percent_value = ($hourly_rate * $total_time) * 2; // +100%

                // Atributos fixos
                $employee->show = true;
                $employee->check_in_time = "07:00";
                $employee->leave_for_lunch_break = "12:00";
                $employee->check_in_after_lunch_break = "13:15";
                $employee->check_out_time = "17:00";
                $employee->total_time = $total_time;

                return $employee;
            });
        } else
        {
            $procedure = Procedure::findOrFail($id);
            $record = Record::where('procedure_id', $procedure->id)->first();
            $employees = $record? collect(json_decode($record->content)) : collect();
            $user = User::findOrFail($procedure->user_id);
            $procedure_date = $procedure->created_at;
        }

        // Calculando valores totais apenas se $employees não estiver vazio
        $total_fifty_percent_value = $employees->sum('fifty_percent_value')?? 0;
        $total_hundred_percent_value = $employees->sum('hundred_percent_value')?? 0;

        // Retornando os dados para a renderização
        return Inertia::render('Employees/OvertimeCalculation', [
            'page' => $page,
            'page_mode' => $page_mode,
            'user' => $user,
            'procedure_date' => $procedure_date,
            'employees_list' => $employees,
            'account_types_list' => $account_types,
            'payment_methods_list' => $payment_method,
            'banks_list' => $banks,
            'total_fifty_percent_value' => $total_fifty_percent_value,
            'total_hundred_percent_value' => $total_hundred_percent_value,
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
        return DB::transaction(function () use ($request) {
            $validated = (object) $request->validate([
                'name' => ['required', 'string'],
                'surname' => ['required', 'string'],
                'salary' => ['nullable', 'numeric'],
                'agreement' => ['nullable', 'numeric'],
                'contacts' => ['required', 'array', 'min:1'],
                'contacts.*' => ['string'],
                'function_name' => ['required', 'string'],
                'transportation_voucher' => ['required', 'boolean'],
                'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
                'overtime_payment_method_id' => ['nullable', 'exists:payment_methods,id'],
                'bank_id' => ['nullable', 'exists:banks,id'],
                'pix_cpf' => ['nullable', 'string', 'unique:employees'],
                'pix_email' => ['nullable', 'email', 'unique:employees'],
                'pix_phone_number' => ['nullable', 'string', 'unique:employees'],
                'pix_token' => ['nullable', 'string', 'unique:employees'],
                'bank_ag' => ['nullable', 'string', 'unique:employees'],
                'account_type_id' => ['nullable', 'exists:account_types,id'],
                'account_number' => ['nullable', 'string', 'unique:employees']
            ]);

            $contactsWithPrefix = array_map(function ($contact) {
                return '+55'. $contact;
            }, $validated->contacts);

            // A chamada `Employee::create` irá disparar o evento 'created' do Eloquent.
            // O Trait 'Auditable' no modelo Employee irá interceptar este evento
            // e chamar `recordActivity('create')` automaticamente.
            $employee = Employee::create([
                'name' => $validated->name,
                'surname' => $validated->surname,
                'salary' => $validated->salary,
                'agreement' => $validated->agreement,
                'contacts' => json_encode($contactsWithPrefix),
                'function_name' => $validated->function_name,
                'transportation_voucher' => $validated->transportation_voucher,
                'payment_method_id' => $validated->payment_method_id,
                'overtime_payment_method_id' => $validated->overtime_payment_method_id,
                'bank_id' => $validated->bank_id,
                'pix_cpf' => $validated->pix_cpf,
                'pix_email' => $validated->pix_email,
                'pix_phone_number' => $validated->pix_phone_number,
                'pix_token' => $validated->pix_token,
                'bank_ag' => $validated->bank_ag,
                'fired' => false,
                'account_type_id' => $validated->account_type_id,
                'account_number' => $validated->account_number,
            ]);

            /*
            // Bloco de log manual removido. O Trait Auditable cuidará disso.
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $validated->name. ' '. $validated->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $validated->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            */

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
        return DB::transaction(function () use ($request, $id) {
            $employee = Employee::findOrFail($id);

            $validated = (object) $request->validate([
                'name' => ['required', 'string'],
                'surname' => ['required', 'string'],
                'salary' => ['nullable', 'numeric'],
                'agreement' => ['nullable', 'numeric'],
                'contacts' => ['required', 'array', 'min:1'],
                'contacts.*' => ['string'],
                'function_name' => ['required', 'string'],
                'transportation_voucher' => ['required', 'boolean'],
                'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
                'overtime_payment_method_id' => ['nullable', 'exists:payment_methods,id'],
                'bank_id' => ['nullable', 'exists:banks,id'],
                'pix_cpf' => ['nullable', 'string', 'unique:employees,pix_cpf,'. $id],
                'pix_email' => ['nullable', 'email', 'unique:employees,pix_email,'. $id],
                'pix_phone_number' => ['nullable', 'string', 'unique:employees,pix_phone_number,'. $id],
                'pix_token' => ['nullable', 'string', 'unique:employees,pix_token,'. $id],
                'bank_ag' => ['nullable', 'string', 'unique:employees,bank_ag,'. $id],
                'account_type_id' => ['nullable', 'exists:account_types,id'],
                'account_number' => ['nullable', 'string', 'unique:employees,account_number,'. $id]
            ]);

            $contactsWithPrefix = array_map(function ($contact) {
                // Adiciona "+55" apenas se o número não começar com "+55"
                return strpos($contact, '+55') === 0? $contact : '+55'. $contact;
            }, $validated->contacts);

            // Atualiza os dados do empregado
            $employee->name = $validated->name;
            $employee->surname = $validated->surname;
            $employee->salary = $validated->salary;
            $employee->agreement = $validated->agreement;
            $employee->contacts = json_encode($contactsWithPrefix);
            $employee->function_name = $validated->function_name;
            $employee->transportation_voucher = $validated->transportation_voucher;
            $employee->payment_method_id = $validated->payment_method_id;
            $employee->overtime_payment_method_id = $validated->overtime_payment_method_id;
            $employee->bank_id = $validated->bank_id;
            $employee->pix_cpf = $validated->pix_cpf;
            $employee->pix_email = $validated->pix_email;
            $employee->pix_phone_number = $validated->pix_phone_number;
            $employee->pix_token = $validated->pix_token;
            $employee->bank_ag = $validated->bank_ag;
            $employee->account_type_id = $validated->account_type_id;
            $employee->account_number = $validated->account_number;

            // A chamada `$employee->save()` irá disparar o evento 'updated' do Eloquent.
            // O Trait 'Auditable' no modelo Employee irá interceptar este evento
            // e chamar `recordActivity('update')` automaticamente.
            $employee->save();

            /*
            // Bloco de log manual removido. O Trait Auditable cuidará disso.
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 2,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $validated->name. ' '. $validated->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $validated->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            */

            return back();
        });
    }


    public function fire(string $id)
    {
        return DB::transaction(function () use ($id) {
            $employee = Employee::findOrFail($id);

            $employee->fired = true;
            $employee->save(); // O save() dispara o evento 'update' automaticamente.

            // Chamamos manualmente a atividade 'fire' para registrar este evento específico.
            // Isso cria um log de auditoria claro para a demissão, separado da simples atualização.
            $employee->recordActivity('fire');

            /*
            // Bloco de log manual removido. A chamada acima para recordActivity substitui isso.
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 6,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $employee->name. ' '. $employee->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $employee->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            */

            // O delete() dispara o evento 'deleted' automaticamente, que também será logado.
            // Isso é útil para ter um registro completo do ciclo de vida do objeto.
            $employee->delete();
            return back();
        });
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return DB::transaction(function () use ($id) {
            $employee = Employee::findOrFail($id);

            /*
            // Bloco de log manual removido. O Trait Auditable cuidará disso.
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 3,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $employee->name. ' '. $employee->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $employee->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
            */

            // A chamada `$employee->delete()` irá disparar o evento 'deleted' do Eloquent.
            // O Trait 'Auditable' no modelo Employee irá interceptar este evento
            // e chamar `recordActivity('delete')` automaticamente.
            $employee->delete();
            return back();
        });
    }
}