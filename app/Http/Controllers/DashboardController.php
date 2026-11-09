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
        $isSuperAdmin = in_array($user->role, ['owner', 'super_admin']);

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
        } elseif ($isSuperAdmin) {
            $stats = $this->statService->getTodayMetrics($branchId);
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

        if ($requireApprovalSetting && $closedSessionsTodayCount > 0 && !$activeSession && !$isSuperAdmin) {
            try {
                if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('branch_reopen_requests')) {
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
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Branch reopen check failed: ' . $e->getMessage());
            }
        }

        // ── Chart Data ──
        $chartData = $this->statService->getChartData($branchId);

        // ── Super Admin Global Stats ──
        if ($isSuperAdmin) {
            $data = app(\App\Services\SuperAdminDashboardService::class)->getGlobalDashboardData($branchName);
            return view('dashboard_superadmin', $data);
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
        return app(ShiftManagementController::class)->openShift($request);
    }

    public function updateAttendance(Request $request)
    {
        return app(ShiftManagementController::class)->updateAttendance($request);
    }

    public function closeShift(Request $request)
    {
        return app(ShiftManagementController::class)->closeShift($request);
    }
}

