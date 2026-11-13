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
            $query->where('orders.status', $statusFilter);
            $statsQuery->where('orders.status', $statusFilter);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$request->has('start_date') && !$request->has('end_date')) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->toDateString();
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('orders.created_at', '>=', $startDate)
                  ->whereDate('orders.created_at', '<=', $endDate);
            $statsQuery->whereDate('orders.created_at', '>=', $startDate)
                       ->whereDate('orders.created_at', '<=', $endDate);
        }

        $ordersBase = clone $statsQuery;
        
        $totalOrderValue = (float) ((clone $ordersBase)->sum('orders.total') ?? 0);
        $walkIn = (float) ((clone $ordersBase)->where('orders.is_delivery_order', false)->sum('orders.total') ?? 0);
        $delivery = (float) ((clone $ordersBase)->where('orders.is_delivery_order', true)->sum('orders.total') ?? 0);
        
        // Zero-RAM SQL aggregations for pending amounts
        $pendingDue = (float) ((clone $ordersBase)->selectRaw('SUM(GREATEST(0, orders.total - COALESCE(orders.paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);
        
        $regularPending = (float) ((clone $ordersBase)->where(function($q) {
            $q->where('orders.payment_method', '!=', 'credit')->orWhereNull('orders.payment_method');
        })->selectRaw('SUM(GREATEST(0, orders.total - COALESCE(orders.paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);

        $creditOrders = (float) ((clone $ordersBase)->where('orders.payment_method', 'credit')->selectRaw('SUM(GREATEST(0, orders.total - COALESCE(orders.paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);

        $fromTheseOrders = (float) ((clone $ordersBase)->sum('orders.paid_amount') ?? 0);

        // SQL aggregations for payment totals using subquery
        $orderIdsSubquery = (clone $ordersBase)->select('orders.id');

        // 1. Payments collected from these specific date-filtered orders
        $directCash = (float) ((clone $ordersBase)->where('orders.payment_method', 'cash')->whereDoesntHave('paymentTransactions')->sum('orders.paid_amount') ?? 0);
        $directCard = (float) ((clone $ordersBase)->where('orders.payment_method', 'card')->whereDoesntHave('paymentTransactions')->sum('orders.paid_amount') ?? 0);
        $directBank = (float) ((clone $ordersBase)->where('orders.payment_method', 'bank')->whereDoesntHave('paymentTransactions')->sum('orders.paid_amount') ?? 0);

        $txSums = PaymentTransaction::whereIn('order_id', $orderIdsSubquery)
            ->where(function($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'rejected');
            })
            ->selectRaw("
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) as cash_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'card' THEN amount ELSE 0 END), 0) as card_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'bank' THEN amount ELSE 0 END), 0) as bank_sum
            ")->first();

        $cashTotal = (float) (($txSums->cash_sum ?? 0) + $directCash);
        $cardTotal = (float) (($txSums->card_sum ?? 0) + $directCard);
        $bankTotal = (float) (($txSums->bank_sum ?? 0) + $directBank);

        // 2. Total payments actually received/collected DURING THIS DATE RANGE (irrespective of when order was placed)
        $rangePaymentsQuery = PaymentTransaction::where(function($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'rejected');
            });

        if (!$isSuperAdmin && $branchId) {
            if ($isWorkshop) {
                $rangePaymentsQuery->whereHas('order', function ($q) use ($branchId) {
                    $q->where('orders.workshop_id', $branchId)
                      ->orWhere('orders.branch_id', $branchId);
                });
            } else {
                $rangePaymentsQuery->whereHas('order', fn ($q) => $q->where('orders.branch_id', $branchId));
            }
        } elseif ($isSuperAdmin) {
            if ($request->filled('branch_id')) {
                $rangePaymentsQuery->whereHas('order', fn ($q) => $q->where('orders.branch_id', $request->branch_id));
            }
            if ($request->filled('workshop_id')) {
                $rangePaymentsQuery->whereHas('order', fn ($q) => $q->where('orders.workshop_id', $request->workshop_id));
            }
        }

        if (!empty($startDate) && !empty($endDate)) {
            $rangePaymentsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Direct orders (legacy/POS without transaction records) created in this date range
        $rangeDirectOrders = (clone $ordersBase)->whereDoesntHave('paymentTransactions');
        $rangeDirectCash = (float) ((clone $rangeDirectOrders)->where('orders.payment_method', 'cash')->sum('orders.paid_amount') ?? 0);
        $rangeDirectCard = (float) ((clone $rangeDirectOrders)->where('orders.payment_method', 'card')->sum('orders.paid_amount') ?? 0);
        $rangeDirectBank = (float) ((clone $rangeDirectOrders)->where('orders.payment_method', 'bank')->sum('orders.paid_amount') ?? 0);

        $rangeTxSums = (clone $rangePaymentsQuery)->selectRaw("
            COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) as cash_sum,
            COALESCE(SUM(CASE WHEN payment_method = 'card' THEN amount ELSE 0 END), 0) as card_sum,
            COALESCE(SUM(CASE WHEN payment_method = 'bank' THEN amount ELSE 0 END), 0) as bank_sum,
            COALESCE(SUM(amount), 0) as total_sum
        ")->first();

        $rangeCashTotal = (float) (($rangeTxSums->cash_sum ?? 0) + $rangeDirectCash);
        $rangeCardTotal = (float) (($rangeTxSums->card_sum ?? 0) + $rangeDirectCard);
        $rangeBankTotal = (float) (($rangeTxSums->bank_sum ?? 0) + $rangeDirectBank);
        $totalCollectedInRange = (float) (($rangeTxSums->total_sum ?? 0) + $rangeDirectCash + $rangeDirectCard + $rangeDirectBank);

        // 3. Workshop vs Collection Points Profit & Commission Breakdown
        $profitQuery = (clone $ordersBase)
            ->leftJoin('branches as collection_branches', 'collection_branches.id', '=', 'orders.branch_id')
            ->selectRaw("
                COALESCE(SUM(
                    CASE 
                        WHEN orders.branch_profit > 0 THEN orders.branch_profit
                        WHEN orders.workshop_profit > 0 THEN (orders.total - orders.workshop_profit)
                        WHEN orders.branch_id != orders.workshop_id AND orders.workshop_id IS NOT NULL AND collection_branches.branch_profit_percentage > 0 
                            THEN (orders.total * (collection_branches.branch_profit_percentage / 100.0))
                        ELSE 0 
                    END
                ), 0) as total_branch_commission,
                COALESCE(SUM(
                    CASE 
                        WHEN orders.workshop_profit > 0 THEN orders.workshop_profit
                        WHEN orders.branch_profit > 0 THEN (orders.total - orders.branch_profit)
                        WHEN orders.branch_id != orders.workshop_id AND orders.workshop_id IS NOT NULL AND collection_branches.branch_profit_percentage > 0 
                            THEN (orders.total * (1 - (collection_branches.branch_profit_percentage / 100.0)))
                        ELSE orders.total 
                    END
                ), 0) as total_workshop_net
            ")->first();

        $totalBranchCommission = (float) ($profitQuery->total_branch_commission ?? 0);
        $totalWorkshopNet = (float) ($profitQuery->total_workshop_net ?? 0);

        // Branch-by-branch commission breakdown for collection points
        $collectionPointBreakdown = (clone $ordersBase)
            ->join('branches', 'branches.id', '=', 'orders.branch_id')
            ->where(function($q) {
                $q->whereColumn('orders.branch_id', '!=', 'orders.workshop_id')
                  ->orWhereNull('orders.workshop_id');
            })
            ->where('branches.type', '!=', 'workshop')
            ->selectRaw("
                branches.id as branch_id,
                branches.name as branch_name,
                COALESCE(branches.branch_profit_percentage, 0) as commission_rate,
                COUNT(orders.id) as order_count,
                COALESCE(SUM(orders.total), 0) as gross_total,
                COALESCE(SUM(
                    CASE 
                        WHEN orders.branch_profit > 0 THEN orders.branch_profit
                        WHEN orders.workshop_profit > 0 THEN (orders.total - orders.workshop_profit)
                        WHEN branches.branch_profit_percentage > 0 THEN (orders.total * (branches.branch_profit_percentage / 100.0))
                        ELSE 0 
                    END
                ), 0) as branch_commission,
                COALESCE(SUM(
                    CASE 
                        WHEN orders.workshop_profit > 0 THEN orders.workshop_profit
                        WHEN orders.branch_profit > 0 THEN (orders.total - orders.branch_profit)
                        WHEN branches.branch_profit_percentage > 0 THEN (orders.total * (1 - (branches.branch_profit_percentage / 100.0)))
                        ELSE orders.total 
                    END
                ), 0) as workshop_net
            ")
            ->groupBy('branches.id', 'branches.name', 'branches.branch_profit_percentage')
            ->get();

        // Workshop-by-workshop breakdown (Which workshops processed this collection branch's orders)
        $workshopBreakdown = (clone $ordersBase)
            ->leftJoin('branches as workshop_branches', 'workshop_branches.id', '=', 'orders.workshop_id')
            ->whereNotNull('orders.workshop_id')
            ->selectRaw("
                workshop_branches.id as workshop_id,
                COALESCE(workshop_branches.name, 'Default Workshop') as workshop_name,
                COUNT(orders.id) as order_count,
                COALESCE(SUM(orders.total), 0) as gross_total,
                COALESCE(SUM(
                    CASE 
                        WHEN orders.workshop_profit > 0 THEN orders.workshop_profit
                        WHEN orders.branch_profit > 0 THEN (orders.total - orders.branch_profit)
                        ELSE orders.total 
                    END
                ), 0) as workshop_share,
                COALESCE(SUM(
                    CASE 
                        WHEN orders.branch_profit > 0 THEN orders.branch_profit
                        WHEN orders.workshop_profit > 0 THEN (orders.total - orders.workshop_profit)
                        ELSE 0 
                    END
                ), 0) as branch_commission
            ")
            ->groupBy('workshop_branches.id', 'workshop_branches.name')
            ->get();

        // Direct Workshop Orders (originated directly at workshop without collection branch commission)
        $directWorkshopOrders = (clone $ordersBase)
            ->where(function($q) {
                $q->whereColumn('orders.branch_id', '=', 'orders.workshop_id')
                  ->orWhereHas('branch', fn($b) => $b->where('type', 'workshop'));
            })
            ->selectRaw("
                COUNT(orders.id) as order_count,
                COALESCE(SUM(orders.total), 0) as gross_total
            ")->first();

        $directWorkshopCount = (int) ($directWorkshopOrders->order_count ?? 0);
        $directWorkshopGross = (float) ($directWorkshopOrders->gross_total ?? 0);
        $collectionPointsGross = max(0, (float) ($totalOrderValue - $directWorkshopGross));

        // Collected Payments split strictly for orders in this date range:
        // 1. From Workshop Direct Orders
        $workshopDirectOrdersQuery = (clone $ordersBase)->where(function($q) {
            $q->whereColumn('orders.branch_id', '=', 'orders.workshop_id')
              ->orWhereHas('branch', fn($b) => $b->where('type', 'workshop'));
        });
        $workshopDirectCollected = (float) ((clone $workshopDirectOrdersQuery)->sum('orders.paid_amount') ?? 0);

        // 2. From Collection Point Orders
        $collectionPointOrdersQuery = (clone $ordersBase)->where(function($q) {
            $q->whereColumn('orders.branch_id', '!=', 'orders.workshop_id')
              ->orWhereNull('orders.workshop_id');
        })->whereHas('branch', fn($b) => $b->where('type', '!=', 'workshop'));
        $collectionPointsCollected = (float) ((clone $collectionPointOrdersQuery)->sum('orders.paid_amount') ?? 0);

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
            'collected_payments' => $totalCollectedInRange, // Total collected in date range
            'from_these_orders' => $fromTheseOrders,       // Collected from these orders specifically
            'cash' => $rangeCashTotal,
            'card' => $rangeCardTotal,
            'bank' => $rangeBankTotal,
            'these_orders_cash' => $cashTotal,
            'these_orders_card' => $cardTotal,
            'these_orders_bank' => $bankTotal,
            'total_branch_commission' => $totalBranchCommission, // Collection Point Commissions
            'total_workshop_net' => $totalWorkshopNet,           // Workshop Net Share
            'collection_point_breakdown' => $collectionPointBreakdown,
            'workshop_breakdown' => $workshopBreakdown,
            'direct_workshop_count' => $directWorkshopCount,
            'direct_workshop_gross' => $directWorkshopGross,
            'collection_points_gross' => $collectionPointsGross,
            'workshop_direct_collected' => $workshopDirectCollected,
            'collection_points_collected' => $collectionPointsCollected,
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

