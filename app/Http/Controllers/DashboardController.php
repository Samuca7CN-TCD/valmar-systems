<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Http\Request;
use \Inertia\Inertia;
use Carbon\Carbon;
use Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hierarchy === 1)
        {
            return $this->renderDashboard(new Request);
        }

        return redirect()->route('warehouse.index');
    }


    public function filter_procedures(Request $request)
    {
        return $this->renderDashboard($request);
    }

    private function renderDashboard(Request $request)
    {
        $result = $this->get_procedures($request);

        $procedures = $result['procedures'];
        unset($result['procedures']);

        $page = Department::where('type', 'dashboard')->firstOrFail();

        $users = User::all();
        $actions = Action::all();
        $departments = Department::all();

        $payments_amount = Movement::where('type', 0)->orWhere('type', 2)
            ->whereHas('accounting', function ($query) {
                $query->where('partial_value', '>', 0);
            })
            ->with('accounting')
            ->get()
            ->sum(function ($movement) {
                return $movement->accounting->partial_value;
            });

        $services_amount = Movement::where('type', 1)
            ->where('ready', false)
            ->with('accounting')
            ->get()
            ->sum(function ($movement) {
                // Soma os valores parciais de cada item na contabilidade
                return $movement->accounting->partial_value;
            });


        // Soma dos valores dos itens multiplicando o preço pela quantidade
        $warehouse_amount = Item::all()->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Define o intervalo das últimas 12 semanas
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subWeeks(12);

        // Inicializa arrays para armazenar os valores semanais
        $weeklyPayments = [];
        $weeklyServices = [];
        $weeklyWarehouse = [];

        // Calcula os pagamentos semanais
        for ($i = 0; $i < 12; $i++)
        {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weeklyPayments[$weekStart->format('W')] = Movement::whereBetween('updated_at', [$weekStart, $weekEnd])
                ->where(function ($query) {
                    $query->where('type', 0)->orWhere('type', 2);
                })
                ->with('accounting')
                ->get()
                ->sum(function ($movement) {
                    return $movement->accounting->sum('partial_value');
                });

            $weeklyServices[$weekStart->format('W')] = Movement::whereBetween('updated_at', [$weekStart, $weekEnd])
                ->where('type', 1)
                ->where('ready', false)
                ->with('accounting')
                ->get()
                ->sum(function ($movement) {
                    return $movement->accounting->sum('partial_value');
                });

            $weeklyWarehouse[$weekStart->format('W')] = Item::whereBetween('updated_at', [$weekStart, $weekEnd])
                ->withTrashed() // Inclui soft-deleted registros
                ->get()
                ->sum(function ($item) {
                    return $item->price * $item->quantity;
                });
        }

        return Inertia::render('Dashboard', [
            'page' => $page,
            'title' => 'Dashboard',
            'procedures' => $procedures,
            'parameters' => $result,
            'users' => $users,
            'actions' => $actions,
            'departments' => $departments,
            'amount_values' => [
                'payments' => $payments_amount ?? 0,
                'services' => $services_amount ?? 0,
                'warehouse' => $warehouse_amount ?? 0,
            ],
            'weekly_amount_values' => [
                'weekly_payments' => $weeklyPayments ?? [],
                'weekly_services' => $weeklyServices ?? [],
                'weekly_warehouse' => $weeklyWarehouse ?? [],
            ],
        ]);

    }

    private function get_procedures(Request $request)
    {
        // Obtém os parâmetros de filtro
        $user = $request->input('user', 0);
        $action = $request->input('action', 0);
        $department = $request->input('department', 0);

        // Define as datas de início e fim
        $end_date = $request->input('end_date', Carbon::now()->endOfDay()->format('Y-m-d H:i:s'));
        $start_date = $request->input('start_date', Carbon::now()->subDays(2)->startOfDay()->format('Y-m-d H:i:s'));

        // Cria a consulta base
        $query = Procedure::whereBetween('created_at', [$start_date, $end_date])
            ->with(['user', 'action', 'department', 'movement', 'records']);

        // Aplica filtros, se os valores forem diferentes de 0
        if ($user > 0)
        {
            $query->where('user_id', $user);
        }
        if ($action > 0)
        {
            $query->where('action_id', $action);
        }
        if ($department > 0)
        {
            $query->where('department_id', $department);
        }

        // Ordena e retorna os resultados
        return [
            'user' => $user,
            'action' => $action,
            'department' => $department,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'procedures' => $query->orderBy('updated_at', 'desc')->get(),
        ];
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
        //
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
    public function destroy(string $id)
    {
        //
    }
}
