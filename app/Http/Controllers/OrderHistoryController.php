<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $isSuperAdmin = $user->isSuperAdmin();
        $isWorkshop = $user->isWorkshopAdmin() || ($user->branch && $user->branch->type === 'workshop');

        $query = Order::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->withTrashed()
            ->with(['customer', 'branch', 'workshop', 'latestWorkshopDispatch.workshopBranch', 'deletedBy'])
            ->orderByDesc('id');
            
        $statsQuery = Order::withoutGlobalScope(\App\Models\Scopes\BranchScope::class);

        if (!$isSuperAdmin && $branchId) {
            if ($isWorkshop) {
                $workshopCondition = function ($q) use ($branchId) {
                    $q->where('orders.workshop_id', $branchId)
                      ->orWhere('orders.branch_id', $branchId)
                      ->orWhereExists(function ($sub) use ($branchId) {
                          $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('workshop_dispatches')
                              ->whereColumn('workshop_dispatches.order_id', 'orders.id')
                              ->where('workshop_dispatches.workshop_branch_id', $branchId);
                      });
                };
                $query->where($workshopCondition);
                $statsQuery->where($workshopCondition);
            } else {
                $query->where('orders.branch_id', $branchId);
                $statsQuery->where('orders.branch_id', $branchId);
            }
        }

        if ($isSuperAdmin && $request->filled('branch_id')) {
            $branchIdFilter = $request->branch_id;
            $query->where('orders.branch_id', $branchIdFilter);
            $statsQuery->where('orders.branch_id', $branchIdFilter);
        }

        if ($isSuperAdmin && $request->filled('workshop_id')) {
            $workshopIdFilter = $request->workshop_id;
            $workshopSuperCondition = function ($q) use ($workshopIdFilter) {
                $q->where('orders.workshop_id', $workshopIdFilter)
                  ->orWhere('orders.branch_id', $workshopIdFilter)
                  ->orWhereExists(function ($sub) use ($workshopIdFilter) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('workshop_dispatches')
                          ->whereColumn('workshop_dispatches.order_id', 'orders.id')
                          ->where('workshop_dispatches.workshop_branch_id', $workshopIdFilter);
                  });
            };
            $query->where($workshopSuperCondition);
            $statsQuery->where($workshopSuperCondition);
        }

        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $query->where('status', $statusFilter);
            $statsQuery->where('status', $statusFilter);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$request->has('start_date') && !$request->has('end_date')) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->toDateString();
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
            $statsQuery->whereDate('created_at', '>=', $startDate)
                       ->whereDate('created_at', '<=', $endDate);
        }

        $ordersBase = clone $statsQuery;
        
        $totalOrderValue = (float) ((clone $ordersBase)->sum('total') ?? 0);
        $walkIn = (float) ((clone $ordersBase)->where('is_delivery_order', false)->sum('total') ?? 0);
        $delivery = (float) ((clone $ordersBase)->where('is_delivery_order', true)->sum('total') ?? 0);
        
        $pendingDue = (float) ((clone $ordersBase)->selectRaw('SUM(GREATEST(0, total - COALESCE(paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);
        
        $regularPending = (float) ((clone $ordersBase)->where(function($q) {
            $q->where('payment_method', '!=', 'credit')->orWhereNull('payment_method');
        })->selectRaw('SUM(GREATEST(0, total - COALESCE(paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);

        $creditOrders = (float) ((clone $ordersBase)->where('payment_method', 'credit')->selectRaw('SUM(GREATEST(0, total - COALESCE(paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);

        $collectedPayments = (float) ((clone $ordersBase)->sum('paid_amount') ?? 0);

        $orderIdsSubquery = (clone $ordersBase)->select('orders.id');

        $directCash = (float) ((clone $ordersBase)->where('payment_method', 'cash')->whereDoesntHave('paymentTransactions')->sum('paid_amount') ?? 0);
        $directCard = (float) ((clone $ordersBase)->where('payment_method', 'card')->whereDoesntHave('paymentTransactions')->sum('paid_amount') ?? 0);
        $directBank = (float) ((clone $ordersBase)->where('payment_method', 'bank')->whereDoesntHave('paymentTransactions')->sum('paid_amount') ?? 0);

        $txSums = PaymentTransaction::whereIn('order_id', $orderIdsSubquery)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) as cash_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'card' THEN amount ELSE 0 END), 0) as card_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'bank' THEN amount ELSE 0 END), 0) as bank_sum
            ")->first();

        $cashTotal = (float) (($txSums->cash_sum ?? 0) + $directCash);
        $cardTotal = (float) (($txSums->card_sum ?? 0) + $directCard);
        $bankTotal = (float) (($txSums->bank_sum ?? 0) + $directBank);

        $orderQty = (int) (clone $ordersBase)->count();
        $totalCustomerCount = (int) (clone $ordersBase)->distinct()->count('customer_id');
        $itemQty = (int) (\App\Models\OrderItem::whereIn('order_id', $orderIdsSubquery)->sum('quantity') ?? 0);

        $stats = [
            'total_order_value' => $totalOrderValue,
            'walk_in' => $walkIn,
            'delivery' => $delivery,
            'pending_due' => $pendingDue,
            'regular_pending' => $regularPending,
            'credit_orders' => $creditOrders,
            'collected_payments' => $collectedPayments,
            'cash' => $cashTotal,
            'card' => $cardTotal,
            'bank' => $bankTotal,
            'order_qty' => $orderQty,
            'total_customer_count' => $totalCustomerCount,
            'item_qty' => $itemQty,
        ];

        $completedOrders = $query->paginate(15);

        $cancelledBookings = \App\Models\DeliveryAppointment::with(['customer', 'cancelledBy', 'branch'])
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('branch_id', $branchId))
            ->when($user->isSuperAdmin() && $request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($user->isSuperAdmin() && $request->filled('workshop_id'), fn($q) => $q->where('branch_id', $request->workshop_id))
            ->where('status', 'cancelled')
            ->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'cancelled_page');
        
        $allBranches = collect();
        $allWorkshops = collect();
        if ($user->isSuperAdmin()) {
            $allBranches = \App\Models\Branch::where('type', 'branch')->orWhereNull('type')->orderBy('name')->get();
            $allWorkshops = \App\Models\Branch::where('type', 'workshop')->orderBy('name')->get();
        }

        return view('orders.history', compact('completedOrders', 'cancelledBookings', 'stats', 'allBranches', 'allWorkshops', 'startDate', 'endDate', 'isWorkshop'));
    }
}

