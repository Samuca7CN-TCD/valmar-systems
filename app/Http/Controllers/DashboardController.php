<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Procedure;
use App\Models\User;
use App\Models\Budget;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hierarchy === 1)
        {
            return $this->renderDashboard($request);
        }

        return redirect()->route('warehouse.index');
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

        return Inertia::render('Dashboard', [
            'page' => $page,
            'title' => 'Dashboard',
            'procedures' => $procedures, // Atividades ainda é carregado aqui
            'parameters' => $result,     // Parâmetros para filtros de Atividades
            'users' => $users,           // Para filtros de Atividades
            'actions' => $actions,       // Para filtros de Atividades
            'departments' => $departments, // Para filtros de Atividades
            // Nenhuma outra prop de dados é passada diretamente agora
        ]);
    }

    private function get_procedures(Request $request)
    {
        // Filters
        $user = (int) $request->input('user', 0);
        $action = (int) $request->input('action', 0);
        $department = (int) $request->input('department', 0);

        // Dates
        $start_date = Carbon::parse($request->input('start_date', now()->subDays(2)->startOfDay()))
            ->startOfDay()
            ->toDateTimeString();

        $end_date = Carbon::parse($request->input('end_date', now()->endOfDay()))
            ->endOfDay()
            ->toDateTimeString();

        // Query
        $procedures = Procedure::with(['user', 'action', 'department', 'movement', 'records'])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->when($user > 0, fn($q) => $q->where('user_id', (int)$user))
            ->when($action > 0, fn($q) => $q->where('action_id', (int)$action))
            ->when($department > 0, fn($q) => $q->where('department_id', (int)$department))
            ->orderByDesc('updated_at')
            ->get();

        return [
            'user' => $user,
            'action' => $action,
            'department' => $department,
            'start_date' => Carbon::parse($start_date)->toDateString(),
            'end_date' => Carbon::parse($end_date)->toDateString(),
            'procedures' => $procedures,
        ];
    }

    /**
     * Retorna os valores totais e semanais para os gráficos da seção Geral.
     */
    public function getGeneralData(Request $request)
    {
        // --- Current Amounts ---
        $payments_amount = Movement::whereIn('type', [0, 2])
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
                return $movement->accounting->partial_value;
            });

        $warehouse_amount = Item::all()->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // --- Weekly Amounts ---
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subWeeks(12);

        $weeklyPayments = [];
        $weeklyServices = [];
        $weeklyWarehouse = [];

        for ($i = 0; $i < 12; $i++)
        {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weeklyPayments[$weekStart->format('W')] = Movement::whereBetween('created_at', [$weekStart, $weekEnd])
                ->whereIn('type', [0, 2])
                ->with('accounting')
                ->get()
                ->sum(function ($movement) {
                    return $movement->accounting->partial_value;
                });

            $weeklyServices[$weekStart->format('W')] = Movement::whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('type', 1)
                ->where('ready', false)
                ->with('accounting')
                ->get()
                ->sum(function ($movement) {
                    return $movement->accounting->partial_value;
                });

            $weeklyWarehouse[$weekStart->format('W')] = Item::whereBetween('updated_at', [$weekStart, $weekEnd])
                ->withTrashed()
                ->get()
                ->sum(function ($item) {
                    return $item->price * $item->quantity;
                });
        }

        return response()->json([
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

   /**
     * Retorna os dados para a seção de Almoxarifado.
     * Inclui itens com baixo estoque, mais usados hoje, últimos movimentados,
     * e um resumo paginado de movimentações recentes com filtros.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWarehouseData(Request $request): \Illuminate\Http\JsonResponse
    {
        // --- Itens com Baixo Estoque ---
        $lowStockItems = Item::whereColumn('quantity', '<=', 'min_quantity') // 
                             ->orderBy('name')
                             ->limit(5)
                             ->get(['id', 'name', 'quantity', 'measurement_unit_id']); // 

        // --- Mais Usados/Vendidos Hoje ---
        $today = Carbon::today();
        $topItemsToday = Item::select('items.id', 'items.name') // 
            ->selectRaw('SUM(records.quantity) as total_quantity') // 
            ->join('records', 'items.id', '=', 'records.item_id') // 
            ->join('procedures', 'records.procedure_id', '=', 'procedures.id') // 
            ->join('movements', 'procedures.movement_id', '=', 'movements.id') // 
            ->whereDate('movements.date', $today) // 
            ->whereIn('movements.type', [3, 4]) // Assumindo 3 para uso, 4 para venda de materiais de almoxarifado 
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // --- Últimos Itens Movimentados ---
        // Pegando itens que tiveram registros de entrada/saída recentemente
        $latestItems = Item::orderByDesc('updated_at') // 
                            ->limit(5)
                            ->get(['id', 'name', 'updated_at']); // 

        // --- Resumo de Movimentações Recentes (últimos 7 dias, com paginação e filtros) ---
        $recentMovementsPerPage = $request->input('recent_movements_per_page', 10);
        $recentMovementsPage = $request->input('recent_movements_page', 1);
        $search = $request->input('search_recent_movements');
        $movementType = $request->input('movement_type', 'all'); // 'all', 'entrada', 'saida', 'venda', 'servico'
        $recentMovementsStartDate = Carbon::now()->subDays(7)->startOfDay(); // Padrão: últimos 7 dias
        $recentMovementsEndDate = Carbon::now()->endOfDay();

        $recentMovementsQuery = Movement::query() // 
            ->select(
                'movements.id',
                'movements.type', // 
                'movements.motive', // 
                'movements.entity_name', // 
                'movements.date', // 
                'procedures.user_id', // 
                'users.name as user_name', // Adiciona nome do usuário 
                'users.surname as user_surname' // Adiciona sobrenome do usuário 
            )
            ->leftJoin('procedures', 'movements.id', '=', 'procedures.movement_id') // 
            ->leftJoin('users', 'procedures.user_id', '=', 'users.id') // 
            ->whereBetween('movements.date', [$recentMovementsStartDate, $recentMovementsEndDate]) // 
            ->orderByDesc('movements.date') // 
            ->orderByDesc('movements.created_at') // 
            ->withTrashed(); // Inclui movimentos soft-deleted 

        // Filtro por tipo de movimentação
        if ($movementType !== 'all') {
            switch ($movementType) {
                case 'entrada':
                    $recentMovementsQuery->where('movements.type', 0); // Assumindo type 0 é entrada 
                    break;
                case 'saida':
                    $recentMovementsQuery->whereIn('movements.type', [3, 4]); // Assumindo 3=uso, 4=venda 
                    break;
                case 'servico':
                    $recentMovementsQuery->where('movements.type', 1); // Assumindo 1 é serviço 
                    break;
                // Adicione outros tipos se houver
            }
        }

        // Filtro por pesquisa (motivo, entidade, nome do usuário)
        if ($search) {
            $recentMovementsQuery->where(function ($query) use ($search) {
                $query->where('movements.motive', 'like', '%' . $search . '%') // 
                      ->orWhere('movements.entity_name', 'like', '%' . $search . '%') // 
                      ->orWhere(DB::raw("CONCAT(users.name, ' ', users.surname)"), 'like', '%' . $search . '%'); // 
            });
        }

        $recentMovements = $recentMovementsQuery->paginate($recentMovementsPerPage, ['*'], 'recent_movements_page', $recentMovementsPage);

        return response()->json([
            'lowStockItems' => $lowStockItems,
            'topItemsToday' => $topItemsToday,
            'latestItems' => $latestItems,
            'recentMovements' => $recentMovements, // Novo dado
        ]);
    }

    /**
     * Retorna os dados para a seção Financeiro (antigo Serviço).
     */
    public function getFinanceiroData(Request $request)
    {
        $latestPayments = Movement::whereIn('type', [0, 2])
                                  ->orderByDesc('date')
                                  ->limit(5)
                                  ->with('accounting')
                                  ->get(['id', 'entity_name', 'date', 'accounting_id', 'type']);

        $latestServices = Movement::where('type', 1)
                                  ->orderByDesc('date')
                                  ->limit(5)
                                  ->with('accounting')
                                  ->get(['id', 'entity_name', 'date', 'accounting_id', 'type']);

        $upcomingDeadlines = Movement::whereNotNull('deadline')
                                     ->where('deadline', '>=', Carbon::now()->toDateString())
                                     ->where('deadline', '<=', Carbon::now()->addDays(7)->toDateString())
                                     ->whereIn('type', [0, 1, 2])
                                     ->orderBy('deadline')
                                     ->limit(5)
                                     ->with('accounting')
                                     ->get(['id', 'entity_name', 'deadline', 'accounting_id', 'type']);

        return response()->json([
            'latestPayments' => $latestPayments,
            'latestServices' => $latestServices,
            'upcomingDeadlines' => $upcomingDeadlines,
        ]);
    }

    /**
      * Retorna dados de relatórios e listas paginadas para serviços e orçamentos.
      * Utiliza requisições AJAX do frontend (para o componente Servico.vue).
      *
      * @param \Illuminate\Http\Request $request
      * @return \Illuminate\Http\JsonResponse
      */
      public function getServicesAndBudgetsReports(Request $request): \Illuminate\Http\JsonResponse
      {
          // Definindo os status esperados para serviços e orçamentos
          $allServiceStatuses = ['Não Iniciado', 'Em Andamento', 'Finalizado', 'Cancelado'];
          $allBudgetStatuses = ['Criado', 'Enviado', 'Aprovado', 'Rejeitado', 'Cancelado'];
 
          // --- Período de Filtragem para Relatórios Agregados (Charts e Combinados) ---
          $startDate = null;
          $endDate = Carbon::now()->endOfDay(); // Padrão: até o final do dia atual
 
          //if ($request->input('start_date') === 'last_30_days') {
              $startDate = Carbon::now()->subDays(30)->startOfDay();
          //}
          // Você pode adicionar mais condições para outros filtros de data aqui.
 
          // --- Relatórios de Serviço por Status (Quantidade e Valor) ---
          // Utiliza LEFT JOIN manual para agregação, contornando ONLY_FULL_GROUP_BY e com withTrashed()
          $serviceAggregatesQuery = Movement::query()
              ->select('movements.service_status')
              ->selectRaw('COUNT(movements.id) AS count')
              ->selectRaw('SUM(COALESCE(accountings.partial_value, 0)) AS total_value')
              ->leftJoin('accountings', 'movements.accounting_id', '=', 'accountings.id')
              ->where('movements.type', 1)
              ->groupBy('movements.service_status')
              ->withTrashed(); // Inclui movimentos soft-deleted
 
          // Aplica filtro de data aos relatórios de serviços agregados
          if ($startDate) {
              $serviceAggregatesQuery->whereBetween('movements.created_at', [$startDate, $endDate]); // Pode ser 'updated_at' ou 'date'
          }
          $serviceAggregates = $serviceAggregatesQuery->get()->keyBy('service_status');
 
          $serviceReportsByCount = [];
          $serviceReportsByValue = [];
          foreach ($allServiceStatuses as $status) {
              $item = $serviceAggregates->get($status);
              $serviceReportsByCount[$status] = (int)($item['count'] ?? 0);
              $serviceReportsByValue[$status] = (float)($item['total_value'] ?? 0);
          }
 
          // --- Relatórios de Orçamento por Status (Quantidade e Valor) ---
          // Agregação direta na tabela Budget, com withTrashed()
          $budgetAggregatesQuery = Budget::query()
              ->select('status')
              ->selectRaw('COUNT(id) AS count')
              ->selectRaw('SUM(COALESCE(total_value, 0)) AS total_value')
              ->groupBy('status')
              ->withTrashed(); // Inclui orçamentos soft-deleted
 
          // Aplica filtro de data aos relatórios de orçamentos agregados
          if ($startDate) {
              $budgetAggregatesQuery->whereBetween('budgets.budget_date', [$startDate, $endDate]); // Ou 'budgets.created_at' / 'updated_at'
          }
          $budgetAggregates = $budgetAggregatesQuery->get()->keyBy('status');
 
          $budgetReportsByCount = [];
          $budgetReportsByValue = [];
          foreach ($allBudgetStatuses as $status) {
              $item = $budgetAggregates->get($status);
              $budgetReportsByCount[$status] = (int)($item['count'] ?? 0);
              $budgetReportsByValue[$status] = (float)($item['total_value'] ?? 0);
          }
 
          // --- Relatórios Combinados ---
          // As contagens aqui devem ser filtradas por 'updated_at' no período definido
          $baseCombinedBudgetQuery = Budget::query()->withTrashed(); // Query base para orçamentos, incluindo soft-deleted
 
          if ($startDate) {
              // Aplica o filtro de data 'updated_at' para os relatórios combinados
              $baseCombinedBudgetQuery->whereBetween('updated_at', [$startDate, $endDate]);
          }
 
          // Contar o total de orçamentos (já filtrado por data)
          $totalBudgets = $baseCombinedBudgetQuery->count();
 
          // Contar orçamentos que geraram serviço (baseado na query filtrada, depois adiciona a condição)
          $budgetsWithService = (clone $baseCombinedBudgetQuery)->whereNotNull('generated_service_id')->count();
          // Uso de (clone $baseCombinedBudgetQuery) é crucial para não adicionar whereNotNull à query de totalBudgets
 
          // Garante que 'Aprovado' existe em $budgetReportsByCount antes de usar no cálculo da taxa
          $approvedBudgetsCount = $budgetReportsByCount['Aprovado'] ?? 0;
          $approvalRate = ($totalBudgets > 0) ? round(($approvedBudgetsCount / $totalBudgets) * 100, 2) . '%' : '0.00%';
 
          $combinedReports = [
              'total_budgets' => $totalBudgets,
              'budgets_with_service' => $budgetsWithService,
              'approval_rate' => $approvalRate,
          ];
 
          // --- Lista Paginada de Serviços ---
          // Esta lista mostra serviços para a tabela, não necessariamente filtrados por data,
          // a menos que você queira. Padrão para não filtrar por data para o histórico completo.
          $servicesQuery = Movement::where('type', 1)
              ->with('accounting:id,partial_value')
              ->withTrashed(); // Inclui movimentos soft-deleted na lista
 
          if ($request->filled('service_status') && $request->input('service_status') !== 'all') {
              $servicesQuery->where('service_status', $request->input('service_status'));
          }
          $servicesQuery->orderByDesc('id'); // Ordenação padrão para a lista de serviços
 
          // Se quiser aplicar filtro de data aqui também, descomente e ajuste:
          // if ($startDate) {
          //     $servicesQuery->whereBetween('movements.updated_at', [$startDate, $endDate]); // Ou 'created_at' / 'date'
          // }
 
          $services = $servicesQuery->paginate(
              $request->input('service_per_page', 10),
              ['*'],
              'service_page',
              $request->input('service_page', 1)
          );
 
          // --- Lista Paginada de Orçamentos ---
          // Esta lista mostra orçamentos para a tabela, não necessariamente filtrados por data.
          $budgetsQuery = Budget::query()
              ->withTrashed(); // Inclui orçamentos soft-deleted na lista
 
          if ($request->filled('budget_status') && $request->input('budget_status') !== 'all') {
              $budgetsQuery->where('status', $request->input('budget_status'));
          }
          $budgetsQuery->orderByDesc('id'); // Ordenação padrão para a lista de orçamentos
 
          // Se quiser aplicar filtro de data aqui também, descomente e ajuste:
          // if ($startDate) {
          //     $budgetsQuery->whereBetween('budgets.updated_at', [$startDate, $endDate]); // Ou 'created_at' / 'budget_date'
          // }
 
          $budgets = $budgetsQuery->paginate(
              $request->input('budget_per_page', 10),
              ['*'],
              'budget_page',
              $request->input('budget_page', 1)
          );
 
          // Retorna a resposta JSON com todos os dados processados
          return response()->json([
              'serviceReports' => [
                  'byCount' => $serviceReportsByCount,
                  'byValue' => $serviceReportsByValue,
              ],
              'budgetReports' => [
                  'byCount' => $budgetReportsByCount,
                  'byValue' => $budgetReportsByValue,
              ],
              'combinedReports' => $combinedReports,
              'services' => $services,
              'budgets' => $budgets,
          ]);
      }

    /**
     * Retorna uma lista paginada de serviços concluídos que ainda não foram pagos.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnpaidCompletedServices(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('unpaid_per_page', 10);
        $page = $request->input('unpaid_page', 1);

        $unpaidServicesQuery = Movement::where('type', 1) // Serviços
            ->where('service_status', 'Finalizado') // Concluídos (ajuste o status se for diferente)
            ->whereHas('accounting', function ($query) {
                // O serviço foi concluído, mas o valor parcial pago é 0 ou menor que o total
                // Assumindo que partial_value > 0 significa que alguma parte foi paga.
                // Se partial_value === 0 significa "não pago".
                // Se você tiver um campo total_value na contabilidade, pode ser:
                // $query->whereColumn('partial_value', '<', 'total_value')
                // Ou simplesmente where('partial_value', 0) se não houver pagamentos parciais
                $query->whereNot('partial_value', 0); // Ex: onde o valor parcial pago é zero
            })
            ->with('accounting:id,partial_value') // Carrega apenas o id e partial_value do accounting
            ->orderByDesc('completion_date') // Ordena pelos mais recentemente concluídos
            ->withTrashed(); // Inclui soft-deleted se desejado para auditoria. Remova se não quiser.

        $unpaidServices = $unpaidServicesQuery->paginate($perPage, ['*'], 'unpaid_page', $page);

        return response()->json([
            'unpaid_services_list' => $unpaidServices,
        ]);
    }
}