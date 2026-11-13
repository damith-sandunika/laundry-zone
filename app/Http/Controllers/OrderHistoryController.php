<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Scopes\BranchScope;
use App\Services\OrderHistoryCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderHistoryController extends Controller
{
    protected OrderHistoryCalculationService $calculator;

    public function __construct(OrderHistoryCalculationService $calculator)
    {
        $this->calculator = $calculator;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $isSuperAdmin = $user->isSuperAdmin();
        $isWorkshop = $user->isWorkshopAdmin() || ($user->branch && $user->branch->type === 'workshop');

        $query = Order::withoutGlobalScope(BranchScope::class)->withTrashed()
            ->with(['customer', 'branch', 'workshop', 'latestWorkshopDispatch.workshopBranch', 'deletedBy'])
            ->orderByDesc('id');
            
        $statsQuery = Order::withoutGlobalScope(BranchScope::class);

        if (!$isSuperAdmin && $branchId) {
            $workshopCondition = function ($q) use ($branchId) {
                $q->where('orders.workshop_id', $branchId)->orWhere('orders.branch_id', $branchId)
                  ->orWhereExists(fn($sub) => $sub->select(DB::raw(1))->from('workshop_dispatches')
                      ->whereColumn('workshop_dispatches.order_id', 'orders.id')
                      ->where('workshop_dispatches.workshop_branch_id', $branchId));
            };
            if ($isWorkshop) {
                $query->where($workshopCondition);
                $statsQuery->where($workshopCondition);
            } else {
                $query->where('orders.branch_id', $branchId);
                $statsQuery->where('orders.branch_id', $branchId);
            }
        }

        if ($isSuperAdmin && $request->filled('branch_id')) {
            $query->where('orders.branch_id', $request->branch_id);
            $statsQuery->where('orders.branch_id', $request->branch_id);
        }

        if ($isSuperAdmin && $request->filled('workshop_id')) {
            $wId = $request->workshop_id;
            $workshopSuperCondition = function ($q) use ($wId) {
                $q->where('orders.workshop_id', $wId)->orWhere('orders.branch_id', $wId)
                  ->orWhereExists(fn($sub) => $sub->select(DB::raw(1))->from('workshop_dispatches')
                      ->whereColumn('workshop_dispatches.order_id', 'orders.id')
                      ->where('workshop_dispatches.workshop_branch_id', $wId));
            };
            $query->where($workshopSuperCondition);
            $statsQuery->where($workshopSuperCondition);
        }

        if ($request->filled('status')) {
            $query->where('orders.status', $request->status);
            $statsQuery->where('orders.status', $request->status);
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('orders.created_at', '>=', $startDate)->whereDate('orders.created_at', '<=', $endDate);
            $statsQuery->whereDate('orders.created_at', '>=', $startDate)->whereDate('orders.created_at', '<=', $endDate);
        }

        $ordersBase = clone $statsQuery;

        $rangePaymentsQuery = PaymentTransaction::where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 'rejected'));
        if (!$isSuperAdmin && $branchId) {
            if ($isWorkshop) {
                $rangePaymentsQuery->whereHas('order', fn($q) => $q->where('orders.workshop_id', $branchId)->orWhere('orders.branch_id', $branchId));
            } else {
                $rangePaymentsQuery->whereHas('order', fn($q) => $q->where('orders.branch_id', $branchId));
            }
        } elseif ($isSuperAdmin) {
            if ($request->filled('branch_id')) $rangePaymentsQuery->whereHas('order', fn($q) => $q->where('orders.branch_id', $request->branch_id));
            if ($request->filled('workshop_id')) $rangePaymentsQuery->whereHas('order', fn($q) => $q->where('orders.workshop_id', $request->workshop_id));
        }

        if (!empty($startDate) && !empty($endDate)) {
            $rangePaymentsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $stats = $this->calculator->calculateStats($ordersBase, $rangePaymentsQuery, [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $completedOrders = $query->paginate(15);
        $cancelledBookings = DeliveryAppointment::with(['customer', 'cancelledBy', 'branch'])
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('branch_id', $branchId))
            ->when($user->isSuperAdmin() && $request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($user->isSuperAdmin() && $request->filled('workshop_id'), fn($q) => $q->where('branch_id', $request->workshop_id))
            ->where('status', 'cancelled')->orderByDesc('updated_at')->paginate(15, ['*'], 'cancelled_page');
        
        $allBranches = $user->isSuperAdmin() ? Branch::where('type', 'branch')->orWhereNull('type')->orderBy('name')->get() : collect();
        $allWorkshops = $user->isSuperAdmin() ? Branch::where('type', 'workshop')->orderBy('name')->get() : collect();

        return view('orders.history', compact('completedOrders', 'cancelledBookings', 'stats', 'allBranches', 'allWorkshops', 'startDate', 'endDate', 'isWorkshop'));
    }
}
