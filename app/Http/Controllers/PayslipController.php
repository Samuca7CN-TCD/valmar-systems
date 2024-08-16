<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Observation;
use App\Models\Procedure;
use App\Models\Record;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \Inertia\Inertia;

use App\Models\Payslip;

class PayslipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($employee_id = null, $month = null, $year = null)
    {
        // Obter dados gerais
        $page = Department::where('type', 'payslip')->first();
        $employees = Employee::where('id', '>', 1)->orderBy('name')->orderBy('surname')->get();
        [$current_month, $current_year] = $this->getCurrentMonthYear($month, $year);
        $employee = $this->getEmployee($employee_id);

        $payslips = Payslip::where('employee_id', $employee->id)
            ->where('month', $current_month)
            ->where('year', $current_year)
            ->get();

        if ($payslips->isEmpty() && $this->isCurrentMonth($current_month, $current_year))
        {
            $payslips = $this->createPayslips($employee, $current_month, $current_year);
        }

        $observations = Observation::where('employee_id', $employee->id)
            ->where('month', $current_month)
            ->where('year', $current_year)
            ->first();

        // Criar observação se não existir
        if (!$observations)
        {
            $observations = Observation::create([
                'employee_id' => $employee->id,
                'month' => $current_month,
                'year' => $current_year,
                'description' => '', // ou qualquer valor padrão que você desejar
            ]);
        }

        return Inertia::render('Employees/Payslips', [
            'page' => $page,
            'page_options' => $employees,
            'payslips_list' => $payslips,
            'employee' => $employee,
            'payslip_month' => $current_month,
            'payslip_year' => $current_year,
            'observations' => $observations ? $observations->description : '',
        ]);
    }

    public function print_payslips($mode = null, $employee_id = 0, $month = null, $year = null)
    {
        $page = Department::where('type', 'payslip')->first();

        [$current_month, $current_year] = $this->getCurrentMonthYear($month, $year);

        $mode = $mode ?? 'payslip';

        $employees = Employee::with(['paymentMethod', 'bank', 'accountType'])
            ->where('id', '>', 1)
            ->when($mode === 'transportation-voucher', function ($query) {
                return $query->where('transportation_voucher', true);
            })
            ->orderBy('name')->orderBy('surname')
            ->get();


        $payslips = Payslip::where('month', $current_month)
            ->where('year', $current_year)
            ->get()
            ->groupBy('employee_id');

        $transportation_voucher_value = $this->calculateValeTransporte($current_month, $current_year);

        return Inertia::render('Employees/PrintPayments', [
            'page' => $page,
            'payslips_list' => $payslips,
            'employees' => $employees,
            'employee_id' => intval($employee_id),
            'mode' => $mode,
            'payslip_month' => $current_month,
            'payslip_year' => $current_year,
            'transportation_voucher_value' => $transportation_voucher_value,
        ]);
    }


    private function getEmployee($employee_id)
    {
        if ($employee_id)
        {
            return Employee::findOrFail($employee_id);
        } else
        {
            return Employee::where('id', '>', 1)->orderBy('name')->orderBy('surname')->first();
        }
    }

    private function getCurrentMonthYear($month, $year)
    {
        $target_month = intval(date('m'));
        $target_year = intval(date('Y'));

        $current_month = intval($month ?? $target_month);
        $current_year = intval($year ?? $target_year);

        return [$current_month, $current_year];
    }

    private function isCurrentMonth($current_month, $current_year)
    {
        $target_month = intval(date('m'));
        $target_year = intval(date('Y'));

        return $current_month === $target_month && $current_year === $target_year;
    }

    private function createPayslips($employee, $current_month, $current_year)
    {
        $payslips = collect();

        // Criação do holerite de salário
        $payslip = Payslip::create([
            'employee_id' => $employee->id,
            'description' => 'Salário',
            'value' => $employee->salary,
            'detail' => false,
            'month' => $current_month,
            'year' => $current_year,
        ]);
        $payslips->push($payslip);

        // Criar registro de INSS
        $inssValue = $this->calculateInss($employee->salary);
        $payslip = Payslip::create([
            'employee_id' => $employee->id,
            'description' => 'INSS',
            'value' => -$inssValue,
            'detail' => true,
            'month' => $current_month,
            'year' => $current_year,
        ]);
        $payslips->push($payslip);

        // Criar registro de vale transporte
        if ($employee->transportation_voucher)
        {
            $valeTransporteValue = $this->calculateValeTransporte($current_month, $current_year);
            $payslip = Payslip::create([
                'employee_id' => $employee->id,
                'description' => 'Vale Transporte',
                'value' => $valeTransporteValue,
                'detail' => false,
                'month' => $current_month,
                'year' => $current_year,
            ]);
            $payslips->push($payslip);

            $valeTransporteValue = $this->calculateValeTransporte($current_month, $current_year);
            $payslip = Payslip::create([
                'employee_id' => $employee->id,
                'description' => 'Vale Transporte',
                'value' => -($employee->salary * 0.06),
                'detail' => true,
                'month' => $current_month,
                'year' => $current_year,
            ]);
            $payslips->push($payslip);
        }

        return $payslips;
    }

    private function calculateValeTransporte($month, $year)
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $workingDays = 0;
        for ($day = 1; $day <= $daysInMonth; $day++)
        {
            $date = mktime(0, 0, 0, $month, $day, $year);
            $weekday = date('N', $date);
            if ($weekday < 6)
            { // 1 (Monday) to 5 (Friday)
                $workingDays++;
            }
        }
        return $workingDays * 7.6;
    }

    private function calculateInss($salary)
    {
        if ($salary <= 1412.00)
        {
            return $salary * 0.075;
        } elseif ($salary <= 2666.68)
        {
            return (1412.00 * 0.075) + (($salary - 1412.00) * 0.09);
        } elseif ($salary <= 4000.03)
        {
            return (1412.00 * 0.075) + (1254.68 * 0.09) + (($salary - 2666.68) * 0.12);
        } elseif ($salary <= 7786.02)
        {
            return (1412.00 * 0.075) + (1254.68 * 0.09) + (1333.35 * 0.12) + (($salary - 4000.03) * 0.14);
        } else
        {
            return (1412.00 * 0.075) + (1254.68 * 0.09) + (1333.35 * 0.12) + (3785.99 * 0.14);
        }
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
        // Validação dos dados
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'description' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric'],
            'detail' => ['required', 'boolean'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2011'],
        ]);

        // Criação da ficha de pagamento
        $payslip = Payslip::create($validated);

        // Opcional: Criação de procedimento ou registro associado
        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 1,
            'department_id' => 12,
            'movement_id' => null,
        ]);

        $record = Record::create([
            'procedure_id' => $procedure->id,
            'name' => $payslip->description,
            'price' => $payslip->value,
            'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return back();
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
    public function update(Request $request, $id)
    {
        // Encontra o payslip pelo ID ou lança uma exceção se não for encontrado
        $payslip = Payslip::findOrFail($id);

        // Validação dos dados
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'description' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric'],
            'detail' => ['required', 'boolean'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2011'],
        ]);

        // Atualiza a ficha de pagamento com os dados validados
        $payslip->update($validated);

        // Opcional: Criação de procedimento ou registro associado
        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 2,
            'department_id' => 12,
            'movement_id' => null,
        ]);

        $record = Record::create([
            'procedure_id' => $procedure->id,
            'name' => $payslip->description,
            'price' => $payslip->value,
            'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        // Redireciona para a página anterior com uma mensagem de sucesso
        return back();
    }


    public function update_detail($id)
    {
        // Encontra o payslip pelo ID ou lança uma exceção se não for encontrado
        $payslip = Payslip::findOrFail($id);

        // Atualiza a ficha de pagamento com os dados validados
        $payslip->detail = !$payslip->detail;
        $payslip->save();

        // Redireciona para a página anterior com uma mensagem de sucesso
        return back();
    }

    public function update_observations(Request $request, $employee_id, $month, $year)
    {
        // Validação dos dados recebidos
        $validated = $request->validate([
            'payslip_observations' => 'nullable|string|max:1000', // Permite string vazia
        ]);

        // Tente encontrar a observação existente
        $observation = Observation::firstOrNew([
            'employee_id' => $employee_id,
            'month' => $month,
            'year' => $year,
        ]);

        // Atualize ou crie a observação
        $observation->description = $validated['payslip_observations'] ?? ''; // Garante que o valor seja uma string
        $observation->save();

        return back();
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paysplip = Payslip::find($id);
        $procedure = Procedure::create([
            'user_id' => Auth::id(),
            'action_id' => 3,
            'department_id' => 12,
            'movement_id' => null,
        ]);

        $record = Record::create([
            'procedure_id' => $procedure->id,
            'name' => $paysplip->description,
            'price' => $paysplip->value,
            'content' => json_encode($paysplip),
            'register_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $paysplip->delete();
    }
}
