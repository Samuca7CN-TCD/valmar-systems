<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Bank;
use App\Models\Department;
use App\Models\PaymentMethod;
use App\Models\Procedure;
use App\Models\Record;
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
        $employees = Employee::all();
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

    public function overtime_calculation()
    {
        $page = Department::where('type', 'employee')->first();
        $employees = Employee::with('overtimePaymentMethod')->where('id', '<>', 1)->get();
        $account_types = AccountType::all();
        $payment_method = PaymentMethod::all();
        $banks = Bank::all();

        $employees = $employees->map(function ($employee) {
            $salary = $employee->salary; // Assumindo que há um campo de salário no modelo Employee
            $total_time = 8.75;
            $hourly_rate = $salary / 220;
            $fifty_percent_value = ($hourly_rate * $total_time) * 1.5; // + 50%
            $hundred_percent_value = ($hourly_rate * $total_time) * 2; // + 100%

            $employee->show = true;
            $employee->check_in_time = "07:00";
            $employee->leave_for_lunch_break = "12:00";
            $employee->check_in_after_lunch_break = "13:15";
            $employee->check_out_time = "17:00";
            $employee->total_time = $total_time;
            $employee->fifty_percent_value = $fifty_percent_value;
            $employee->hundred_percent_value = $hundred_percent_value;

            return $employee;
        });

        $total_fifty_percent_value = $employees->sum('fifty_percent_value');
        $total_hundred_percent_value = $employees->sum('hundred_percent_value');

        return Inertia::render('Employees/OvertimeCalculation', [
            'page' => $page,
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
                'account_number' => ['nullable', 'string', 'unique:employees'],
            ]);

            $contactsWithPrefix = array_map(function ($contact) {
                return '+55' . $contact;
            }, $validated->contacts);

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

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 1,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $validated->name . ' ' . $validated->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $validated->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

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
                'pix_cpf' => ['nullable', 'string', 'unique:employees,pix_cpf,' . $id],
                'pix_email' => ['nullable', 'email', 'unique:employees,pix_email,' . $id],
                'pix_phone_number' => ['nullable', 'string', 'unique:employees,pix_phone_number,' . $id],
                'pix_token' => ['nullable', 'string', 'unique:employees,pix_token,' . $id],
                'bank_ag' => ['nullable', 'string', 'unique:employees,bank_ag,' . $id],
                'account_type_id' => ['nullable', 'exists:account_types,id'],
                'account_number' => ['nullable', 'string', 'unique:employees,account_number,' . $id],
            ]);

            $contactsWithPrefix = array_map(function ($contact) {
                // Adiciona "+55" apenas se o número não começar com "+55"
                return strpos($contact, '+55') === 0 ? $contact : '+55' . $contact;
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

            $employee->save(); // Salva as alterações no banco de dados

            // Cria os registros relacionados
            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 2,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $validated->name . ' ' . $validated->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $validated->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            return back();
        });
    }


    public function fire(string $id)
    {
        return DB::transaction(function () use ($id) {
            $employee = Employee::findOrFail($id);

            $employee->fired = true;
            $employee->save();

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 6,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $employee->name . ' ' . $employee->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $employee->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

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

            $procedure = Procedure::create([
                'user_id' => Auth::id(),
                'action_id' => 3,
                'department_id' => 9,
                'movement_id' => null,
            ]);

            $record = Record::create([
                'procedure_id' => $procedure->id,
                'item_id' => null,
                'name' => $employee->name . ' ' . $employee->surname,
                'quantity' => 0,
                'measurement_unit' => null,
                'price' => null,
                'movement_quantity' => $employee->salary,
                'amount' => 0,
                'past' => true,
                'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            $employee->delete();
            return back();
        });
    }
}
