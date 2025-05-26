<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Department;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Auth;

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

        // --- Current Amounts ---
        $payments_amount = Movement::whereIn('type', [0, 2]) // Assuming type 0 and 2 are payments
            ->whereHas('accounting', function ($query) {
                $query->where('partial_value', '>', 0);
            })
            ->with('accounting')
            ->get()
            ->sum(function ($movement) {
                return $movement->accounting->partial_value;
            });

        $services_amount = Movement::where('type', 1) // Assuming type 1 is services
            ->where('ready', false) // Only include services that are not ready (i.e., ongoing/receivable)
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

            // Weekly Payments
            $weeklyPayments[$weekStart->format('W')] = Movement::whereBetween('created_at', [$weekStart, $weekEnd])
                ->whereIn('type', [0, 2])
                ->with('accounting')
                ->get()
                ->sum(function ($movement) {
                    return $movement->accounting->partial_value;
                });

            // Weekly Services
            $weeklyServices[$weekStart->format('W')] = Movement::whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('type', 1)
                ->where('ready', false)
                ->with('accounting')
                ->get()
                ->sum(function ($movement) {
                    return $movement->accounting->partial_value;
                });

            // Weekly Warehouse (Snapshot of current stock value)
            // This might need adjustment if you want historical warehouse value.
            // For now, it sums up the value of items *created/updated* in that week.
            // A more accurate historical warehouse value would require daily/weekly snapshots of inventory.
            $weeklyWarehouse[$weekStart->format('W')] = Item::whereBetween('updated_at', [$weekStart, $weekEnd])
                ->withTrashed()
                ->get()
                ->sum(function ($item) {
                    return $item->price * $item->quantity;
                });
        }

        // --- NEW DASHBOARD DATA ---

        // 1. Low Stock Items
        $lowStockItems = Item::whereColumn('quantity', '<=', 'min_quantity')
                             ->orderBy('name')
                             ->limit(5) // Get top 5 low stock items
                             ->get(['id', 'name', 'quantity', 'measurement_unit_id']); // Select necessary columns

        // 2. Most Used/Sold Items Today
        // Assuming 'type' for use/sale movements are defined (e.g., 3 for usage, 4 for sale)
        // You'll need to adjust these types based on your `Movement` type definitions
        $today = Carbon::today();
        $topItemsToday = Item::select('items.id', 'items.name')
            ->selectRaw('SUM(records.quantity) as total_quantity') // Sum quantity from records
            ->join('records', 'items.id', '=', 'records.item_id')
            ->join('procedures', 'records.procedure_id', '=', 'procedures.id')
            ->join('movements', 'procedures.movement_id', '=', 'movements.id')
            ->whereDate('movements.date', $today) // Filter for today's movements
            ->whereIn('movements.type', [3, 4]) // Assuming 3 for use, 4 for sale movements
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();


        // 3. Latest Added/Updated Items
        $latestItems = Item::orderByDesc('updated_at')
                            ->limit(5)
                            ->get(['id', 'name', 'updated_at']);

        // 4. Latest Payments (type 0 and 2)
        $latestPayments = Movement::whereIn('type', [0, 2])
                                  ->orderByDesc('date')
                                  ->limit(5)
                                  ->with('accounting') // Eager load accounting details
                                  ->get(['id', 'entity_name', 'date', 'accounting_id', 'type']); // Select relevant columns

        // 5. Latest Services (type 1)
        $latestServices = Movement::where('type', 1)
                                  ->orderByDesc('date')
                                  ->limit(5)
                                  ->with('accounting') // Eager load accounting details
                                  ->get(['id', 'entity_name', 'date', 'accounting_id', 'type']);

        // 6. Upcoming Deadlines (Payments/Services due in next 7 days)
        $upcomingDeadlines = Movement::whereNotNull('deadline')
                                     ->where('deadline', '>=', Carbon::now()->toDateString())
                                     ->where('deadline', '<=', Carbon::now()->addDays(7)->toDateString())
                                     ->whereIn('type', [0, 1, 2]) // Payments and Services
                                     ->orderBy('deadline')
                                     ->limit(5)
                                     ->with('accounting')
                                     ->get(['id', 'entity_name', 'deadline', 'accounting_id', 'type']);


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
            // --- NEW DATA PROPS ---
            'lowStockItems' => $lowStockItems,
            'topItemsToday' => $topItemsToday,
            'latestItems' => $latestItems,
            'latestPayments' => $latestPayments,
            'latestServices' => $latestServices,
            'upcomingDeadlines' => $upcomingDeadlines,
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

    // Unused methods omitted for brevity as per your prompt.
}