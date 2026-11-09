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
use Illuminate\Support\Facades\DB;

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

        // Redirect HR managers to HR dashboard
        if ($user->role === 'hr_manager') {
            return redirect()->route('hr.dashboard');
        }

        // Redirect accountants to account dashboard
        if ($user->role === 'accountant' || $user->role === 'accountant_and_hr_manager') {
            return redirect()->route('accountant.dashboard');
        }

        // Redirect workshop admins or staff assigned to a workshop to workshop dashboard
        if ($user->role === 'workshop_admin' || ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop')) {
            return redirect()->route('workshop.dashboard');
        }


        $branchId = $this->getActiveBranchId();
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
        $activeSession = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $suggestedOpeningCash = 0;
        if (!$activeSession) {
            $lastSession = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->where('branch_id', $branchId)
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

        // Active orders sorted by nearest due_date first — limit to 50 to prevent memory exhaustion at scale
        $recentOrders = (clone $activeOrders)->with('customer')
            ->orderByRaw('due_date IS NULL') // Put null due dates at the end
            ->orderBy('due_date', 'asc')
            ->limit(50)
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

        // ── Check if shift was already opened and closed today ──
        $today = now()->format('Y-m-d');
        $closedSessionsTodayCount = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'closed')
            ->whereDate('closed_at', $today)
            ->count();

        $requireApprovalSetting = \App\Models\Setting::get('require_superadmin_approval_to_reopen_shift', '1') == '1';
        $requiresApprovalToOpen = false;
        $pendingReopenRequest = null;
        $hasApprovedReopenRequest = false;

        if ($requireApprovalSetting && $closedSessionsTodayCount > 0 && !$activeSession) {
            // Check if there is an approved reopen request for today that hasn't been used
            $approvedRequest = \App\Models\BranchReopenRequest::where('branch_id', $branchId)
                ->where('request_date', $today)
                ->where('status', 'approved')
                ->latest()
                ->first();

            // Compare with open/closed sessions after approval
            if ($approvedRequest) {
                $sessionsAfterApproval = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                    ->where('branch_id', $branchId)
                    ->where('opened_at', '>=', $approvedRequest->reviewed_at)
                    ->count();

                if ($sessionsAfterApproval === 0) {
                    $hasApprovedReopenRequest = true;
                } else {
                    $requiresApprovalToOpen = true;
                }
            } else {
                $requiresApprovalToOpen = true;
            }

            if ($requiresApprovalToOpen) {
                $pendingReopenRequest = \App\Models\BranchReopenRequest::where('branch_id', $branchId)
                    ->where('request_date', $today)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
            }
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
                'opening_branches' => RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)->where('status', 'open')->distinct('branch_id')->count('branch_id'),
                'new_orders_today' => Order::whereDate('created_at', $today)->count(),
            ];

            // Chart 1: Sales by Branch (Today)
            $branches = Branch::all();
            
            $branchSalesMap = Order::whereDate('created_at', $today)
                ->selectRaw('branch_id, SUM(total) as total_sales')
                ->groupBy('branch_id')
                ->pluck('total_sales', 'branch_id');
                
            $packageSalesMap = PackagePayment::whereDate('package_payments.created_at', $today)
                ->join('customer_packages', 'package_payments.customer_package_id', '=', 'customer_packages.id')
                ->selectRaw('customer_packages.branch_id, SUM(package_payments.amount) as total_amount')
                ->groupBy('customer_packages.branch_id')
                ->pluck('total_amount', 'branch_id');

            $branchLabels = [];
            $branchSales = [];
            foreach ($branches as $b) {
                $branchLabels[] = $b->name;
                $orderSales = $branchSalesMap->get($b->id, 0);
                $pkgSales = $packageSalesMap->get($b->id, 0);
                $branchSales[] = $orderSales + $pkgSales;
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

        return view('dashboard', compact(
            'stats',
            'recentOrders',
            'lowStockItems',
            'allInventory',
            'activeSession',
            'suggestedOpeningCash',
            'branchName',
            'chartData',
            'currentBranch',
            'branchStaff',
            'activeAttendances',
            'pendingCorrections',
            'requiresApprovalToOpen',
            'pendingReopenRequest',
            'hasApprovedReopenRequest',
            'closedSessionsTodayCount'
        ));
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash'  => 'required|numeric|min:0',
            'assigned_staff' => 'nullable|array',
        ]);

        $branchId = $this->getActiveBranchId();

        \Log::info('openShift: user=' . auth()->id() . ' branch=' . $branchId);

        // Check if there is already an open session
        $existing = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            \Log::info('openShift: session already open id=' . $existing->id);
            return $this->dashboardRedirect()->with('info', 'Branch shift is already open.');
        }

        // Check if shift was already closed today and requires Super Admin approval
        $today = now()->format('Y-m-d');
        $requireApprovalSetting = \App\Models\Setting::get('require_superadmin_approval_to_reopen_shift', '1') == '1';
        $closedSessionsTodayCount = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'closed')
            ->whereDate('closed_at', $today)
            ->count();

        if ($requireApprovalSetting && $closedSessionsTodayCount > 0) {
            $approvedRequest = \App\Models\BranchReopenRequest::where('branch_id', $branchId)
                ->where('request_date', $today)
                ->where('status', 'approved')
                ->latest()
                ->first();

            $canReopen = false;
            if ($approvedRequest) {
                $sessionsAfterApproval = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                    ->where('branch_id', $branchId)
                    ->where('opened_at', '>=', $approvedRequest->reviewed_at)
                    ->count();

                if ($sessionsAfterApproval === 0) {
                    $canReopen = true;
                }
            }

            if (!$canReopen) {
                return $this->dashboardRedirect()->with('error', 'Today\'s shift was previously closed. Re-opening shift on the same day requires Super Admin approval.');
            }
        }

        try {
            DB::transaction(function () use ($branchId, $request) {
                $newSession = RegisterSession::create([
                    'branch_id'    => $branchId,
                    'user_id'      => auth()->id(),
                    'opening_cash' => $request->opening_cash,
                    'opened_at'    => now(),
                    'status'       => 'open',
                ]);

                \Log::info('openShift: created session id=' . $newSession->id . ' branch=' . $branchId);

                // ── Sync branches.cash_balance to the opening cash amount ──
                Branch::where('id', $branchId)->update(['cash_balance' => $request->opening_cash]);

                // Mark assigned staff for system access
                $assignedStaffDesc = [];
                if ($request->has('assigned_staff')) {
                    foreach ($request->assigned_staff as $staffId) {
                        \App\Models\Attendance::create([
                            'user_id'             => $staffId,
                            'date'                => now()->toDateString(),
                            'register_session_id' => $newSession->id,
                            'is_assigned'         => true,
                            'status'              => 'present',
                        ]);

                        $staff = \App\Models\User::find($staffId);
                        if ($staff) {
                            $assignedStaffDesc[] = $staff->name;
                        }
                    }
                }

                // Log the event
                $logDesc = 'Shift opened with Rs. ' . number_format($request->opening_cash, 2);
                if (!empty($assignedStaffDesc)) {
                    $logDesc .= '. Staff Assigned: ' . implode(', ', $assignedStaffDesc);
                }

                \App\Models\BranchLog::create([
                    'branch_id'   => $branchId,
                    'user_id'     => auth()->id(),
                    'action'      => 'shift_opened',
                    'description' => $logDesc,
                ]);

                // Clear cached shift state in session for instant reactivity
                session()->forget("branch_shift_open_{$branchId}");
            });

            return $this->dashboardRedirect()->with('success', 'Branch shift opened successfully.');

        } catch (\Exception $e) {
            \Log::error('openShift error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Shift open failed: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to the correct dashboard based on the logged-in user's role.
     * Prevents double-redirect for workshop users.
     */
    private function dashboardRedirect()
    {
        $user = auth()->user();
        if (
            $user->role === 'workshop_admin' ||
            ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop')
        ) {
            return redirect()->route('workshop.dashboard');
        }
        return redirect()->route('dashboard');
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'assigned_staff' => 'required|array',
        ]);

        $branchId = $this->getActiveBranchId();
        $activeSession = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)->where('branch_id', $branchId)->where('status', 'open')->first();

        if (!$activeSession) {
            return redirect()->back()->with('error', 'No active shift found.');
        }

        DB::transaction(function () use ($request, $activeSession, $branchId) {
            foreach ($request->assigned_staff as $key => $data) {
                $staffId = $data['staff_id'] ?? null;
                if (!$staffId) continue;

                $isAssigned = !empty($data['is_assigned']);

                if (str_starts_with($key, 'new_')) {
                    \App\Models\Attendance::create([
                        'user_id' => $staffId,
                        'date' => now()->toDateString(),
                        'status' => 'present',
                        'register_session_id' => $activeSession->id,
                        'is_assigned' => $isAssigned,
                    ]);
                } else {
                    $attendance = \App\Models\Attendance::find($key);
                    if ($attendance && $attendance->register_session_id === $activeSession->id) {
                        $attendance->is_assigned = $isAssigned;
                        $attendance->save();
                    }
                }
            }

            \App\Models\BranchLog::create([
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'action' => 'attendance_updated',
                'description' => 'Staff attendance updated during shift.',
            ]);
        });

        return redirect()->back()->with('success', 'Staff attendance updated successfully.');
    }

    public function closeShift(Request $request)
    {
        $branchId = $this->getActiveBranchId();
        $session = RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return redirect()->route('dashboard')->with('error', 'No active branch shift found.');
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

        $bankDepositsLogged = \App\Models\BankDeposit::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $finalCash = $session->opening_cash + $cashCollected - $bankDepositsLogged;

        DB::transaction(function () use ($session, $finalCash, $branchId) {
            $session->update([
                'closing_cash' => $finalCash,
                'closed_at'    => now(),
                'status'       => 'closed',
                'closed_by'    => auth()->id(),
            ]);

            // ── Sync branches.cash_balance so it stays accurate across sessions ──
            Branch::where('id', $branchId)->update(['cash_balance' => $finalCash]);

            \App\Models\BranchLog::create([
                'branch_id'   => $branchId,
                'user_id'     => auth()->id(),
                'action'      => 'shift_closed',
                'description' => 'Shift closed. Final Cash: Rs. ' . number_format($finalCash, 2),
            ]);

            // Clear cached shift state in session for instant reactivity
            session()->forget("branch_shift_open_{$branchId}");
        });

        // Redirect to dashboard directly (not back()) for clean state
        return $this->dashboardRedirect()->with('success', 'Shift closed. Final Cash: Rs.' . number_format($finalCash, 2) . '.');
    }
}

