<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackagePayment;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use App\Services\DashboardStatService;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardStatService $statService;

    public function __construct(DashboardStatService $statService)
    {
        $this->statService = $statService;
    }
    public function index()
    {
        $user = auth()->user();

        // Redirect riders to their own dashboard
        if ($user->role === 'rider') {
            return redirect()->route('rider.dashboard');
        }

        // Redirect workshop admins or staff assigned to a workshop to workshop dashboard
        if ($user->role === 'workshop_admin' || ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop')) {
            return redirect()->route('workshop.dashboard');
        }


        $branchId = $user->branch_id;
        $isSuperAdmin = $user->role === 'super_admin';

        $activeOrders = Order::whereNotIn('status', ['delivered', 'cancelled']);
        $pendingDeliveries = Order::where('status', 'done');
        $completedToday = Order::where('status', 'delivered')->whereDate('delivered_at', today());

        $pendingCorrections = \App\Models\OrderCorrectionRequest::with('order', 'requestedBy')
            ->where('status', 'pending')
            ->whereHas('order', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->get();
        $totalCustomers = Customer::query();

        if (! $isSuperAdmin && $branchId) {
            $activeOrders->where('branch_id', $branchId);
            $pendingDeliveries->where('branch_id', $branchId);
            $completedToday->where('branch_id', $branchId);
        }

        // Active register session details
        $activeSession = RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $suggestedOpeningCash = 0;
        if (!$activeSession) {
            $lastSession = RegisterSession::where('branch_id', $branchId)
                ->where('status', 'closed')
                ->orderBy('closed_at', 'desc')
                ->first();
            $suggestedOpeningCash = $lastSession ? $lastSession->closing_cash : 0;
        }

        if ($activeSession) {
            $stats = $this->statService->getShiftMetrics($activeSession, $branchId);
        } else {
            $stats = $this->statService->getEmptyMetrics();
        }

        // Add additional stats not covered by shift metrics
        $stats['active_orders'] = $activeOrders->count();
        $stats['pending_deliveries'] = $pendingDeliveries->count();
        $stats['completed_today'] = $completedToday->count();
        $stats['total_customers'] = $totalCustomers->count();

        // Active orders sorted by nearest due_date first
        $recentOrders = (clone $activeOrders)->with('customer')
            ->orderByRaw('due_date IS NULL') // Put null due dates at the end
            ->orderBy('due_date', 'asc')
            ->take(50)
            ->get();

        // Low stock items
        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold');
        if ($branchId) {
            $lowStockItems->where('branch_id', $branchId);
        }
        $lowStockItems = $lowStockItems->get();

        // All inventory items for popup
        $allInventoryQuery = InventoryItem::query();
        if ($branchId) {
            $allInventoryQuery->where('branch_id', $branchId);
        }
        $allInventory = $allInventoryQuery->get();

        // Fetch Branch
        $currentBranch = Branch::find($branchId);
        $branchName = $currentBranch->name ?? 'Smart Laundry';

        // Fetch Branch Staff for assignment when opening shift
        $branchStaff = \App\Models\User::where('branch_id', $branchId)->where('role', 'staff')->get();
        
        $activeAttendances = collect();
        if ($activeSession) {
            $activeAttendances = \App\Models\Attendance::where('register_session_id', $activeSession->id)->get()->keyBy('user_id');
        }

        // ── Chart Data ──
        $chartData = $this->statService->getChartData($branchId);

        // ── Super Admin Global Stats ──
        if ($isSuperAdmin) {
            $today = now()->format('Y-m-d');

            // Daily Global Revenue (Orders + Packages)
            $todayOrderSales = Order::whereDate('created_at', $today)->sum('total');
            $todayPackageSales = PackagePayment::whereDate('created_at', $today)->sum('amount');
            $todayRevenue = $todayOrderSales + $todayPackageSales;

            $superAdminStats = [
                'total_revenue_today' => $todayRevenue,
                'today_present_staff' => Attendance::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
                'opening_branches' => RegisterSession::where('status', 'open')->distinct('branch_id')->count('branch_id'),
                'new_orders_today' => Order::whereDate('created_at', $today)->count(),
            ];

            // Chart 1: Sales by Branch (Today)
            $branches = Branch::all();
            $branchLabels = [];
            $branchSales = [];
            foreach ($branches as $b) {
                $branchLabels[] = $b->name;
                $branchSales[] = Order::where('branch_id', $b->id)->whereDate('created_at', $today)->sum('total')
                               + PackagePayment::whereHas('customerPackage', fn ($q) => $q->where('branch_id', $b->id))->whereDate('created_at', $today)->sum('amount');
            }
            $chartBranchSales = ['labels' => $branchLabels, 'data' => $branchSales];

            // Chart 2: Branch Cash Balances (Replacing Order Status)
            $balanceLabels = [];
            $balanceData = [];
            foreach ($branches as $b) {
                $balanceLabels[] = $b->name;
                $balanceData[] = $b->cash_balance;
            }
            $chartBranchBalances = ['labels' => $balanceLabels, 'data' => $balanceData];

            // Get recent global activity
            $recentGlobalOrders = Order::with(['customer', 'branch'])
                ->latest()
                ->take(10)
                ->get();

            return view('dashboard_superadmin', compact('superAdminStats', 'recentGlobalOrders', 'branchName', 'chartBranchSales', 'chartBranchBalances'));
        }

        return view('dashboard', compact('stats', 'recentOrders', 'lowStockItems', 'allInventory', 'activeSession', 'suggestedOpeningCash', 'branchName', 'chartData', 'currentBranch', 'branchStaff', 'activeAttendances', 'pendingCorrections'));
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'assigned_staff' => 'nullable|array',
            'assigned_staff.*.clock_in' => 'nullable|string',
            'assigned_staff.*.clock_out' => 'nullable|string',
        ]);

        $branchId = $this->getActiveBranchId();

        // Check if there is already an open session
        $existing = RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Branch is already open.');
        }

        $newSession = RegisterSession::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'opening_cash' => $request->opening_cash,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        // Mark Attendance for assigned staff
        $assignedStaffDesc = [];
        if ($request->has('assigned_staff')) {
            foreach ($request->assigned_staff as $staffId => $data) {
                $status = $data['status'] ?? 'hold';
                $isAssigned = !empty($data['is_assigned']);
                
                $clockIn = ($status === 'present' && !empty($data['clock_in'])) ? $data['clock_in'] : null;
                $clockOut = ($status === 'present' && !empty($data['clock_out'])) ? $data['clock_out'] : null;

                \App\Models\Attendance::create([
                    'user_id' => $staffId,
                    'date' => now()->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                    'register_session_id' => $newSession->id,
                    'is_assigned' => $isAssigned,
                ]);

                if ($status === 'hold') {
                    \App\Models\Leave::updateOrCreate(
                        ['user_id' => $staffId, 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString()],
                        ['reason' => 'Auto-marked as absent during shift', 'type' => 'casual', 'status' => 'approved', 'approved_by' => auth()->id()]
                    );
                } else {
                    \App\Models\Leave::where('user_id', $staffId)->where('start_date', now()->toDateString())->delete();
                }

                if ($isAssigned) {
                    $staff = \App\Models\User::find($staffId);
                    if ($staff) {
                        $assignedStaffDesc[] = $staff->name;
                    }
                }
            }
        }

        // Log the event
        $logDesc = 'Shift opened with Rs. ' . number_format($request->opening_cash, 2);
        if (!empty($assignedStaffDesc)) {
            $logDesc .= '. Staff Assigned: ' . implode(', ', $assignedStaffDesc);
        }

        \App\Models\BranchLog::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'action' => 'shift_opened',
            'description' => $logDesc,
        ]);

        return redirect()->back()->with('success', 'Branch shift opened successfully.');
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'assigned_staff' => 'required|array',
            'assigned_staff.*.status' => 'required|in:present,hold',
            'assigned_staff.*.clock_in' => 'nullable|string',
            'assigned_staff.*.clock_out' => 'nullable|string',
        ]);

        $branchId = auth()->user()->branch_id ?? 1;
        $activeSession = RegisterSession::where('branch_id', $branchId)->where('status', 'open')->first();

        if (!$activeSession) {
            return redirect()->back()->with('error', 'No active shift found.');
        }

        foreach ($request->assigned_staff as $key => $data) {
            $staffId = $data['staff_id'] ?? null;
            if (!$staffId) continue;

            $status = $data['status'];
            $isAssigned = !empty($data['is_assigned']);
            $clockIn = !empty($data['clock_in']) ? $data['clock_in'] : null;
            $clockOut = !empty($data['clock_out']) ? $data['clock_out'] : null;

            if ($status === 'hold') {
                $clockIn = null;
                $clockOut = null;
            } else if ($status === 'present' && !$clockIn) {
                $clockIn = now()->format('H:i');
            }

            if (str_starts_with($key, 'new_')) {
                \App\Models\Attendance::create([
                    'user_id' => $staffId,
                    'date' => now()->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                    'register_session_id' => $activeSession->id,
                    'is_assigned' => $isAssigned,
                ]);
            } else {
                $attendance = \App\Models\Attendance::find($key);
                if ($attendance && $attendance->register_session_id === $activeSession->id) {
                    $attendance->status = $status;
                    $attendance->is_assigned = $isAssigned;
                    $attendance->clock_in = $clockIn;
                    $attendance->clock_out = $clockOut;
                    $attendance->save();
                }
            }

            if ($status === 'hold') {
                \App\Models\Leave::updateOrCreate(
                    ['user_id' => $staffId, 'start_date' => now()->toDateString(), 'end_date' => now()->toDateString()],
                    ['reason' => 'Auto-marked as absent during shift', 'type' => 'casual', 'status' => 'approved', 'approved_by' => auth()->id()]
                );
            } else {
                \App\Models\Leave::where('user_id', $staffId)->where('start_date', now()->toDateString())->delete();
            }
        }

        \App\Models\BranchLog::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'action' => 'attendance_updated',
            'description' => 'Staff attendance updated during shift.',
        ]);

        return redirect()->back()->with('success', 'Staff attendance updated successfully.');
    }

    public function closeShift(Request $request)
    {
        $branchId = $this->getActiveBranchId();
        $session = RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return redirect()->back()->with('error', 'No active branch shift found.');
        }

        // Calculate closing balance accurately
        $cashCollected = \App\Models\PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', $session->opened_at)
            ->where('payment_method', 'cash')
            ->sum('amount');
            
        $directCashOrders = \App\Models\Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->where('payment_method', 'cash')
            ->whereDoesntHave('paymentTransactions')
            ->sum('paid_amount');
            
        $cashCollected += $directCashOrders;

        $expensesLogged = \App\Models\Expense::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');
            
        $bankDepositsLogged = \App\Models\BankDeposit::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $finalCash = $session->opening_cash + $cashCollected - $expensesLogged - $bankDepositsLogged;

        $session->update([
            'closing_cash' => $finalCash,
            'closed_at' => now(),
            'status' => 'closed',
            'closed_by' => auth()->id(),
        ]);

        \App\Models\BranchLog::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'action' => 'shift_closed',
            'description' => 'Shift closed. Final Cash: Rs. ' . number_format($finalCash, 2),
        ]);

        return redirect()->back()->with('success', 'Shift closed. Final Cash: Rs.'.number_format($finalCash, 2).'.');
    }
}

