<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder;

class OrderHistoryCalculationService
{
    public function calculateStats(Builder $ordersBase, Builder $rangePaymentsQuery, array $filters): array
    {
        $totalOrderValue = (float) ((clone $ordersBase)->sum('orders.total') ?? 0);
        $walkIn = (float) ((clone $ordersBase)->where('orders.is_delivery_order', false)->sum('orders.total') ?? 0);
        $delivery = (float) ((clone $ordersBase)->where('orders.is_delivery_order', true)->sum('orders.total') ?? 0);

        $pendingDue = (float) ((clone $ordersBase)->selectRaw('SUM(GREATEST(0, orders.total - COALESCE(orders.paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);
        $regularPending = (float) ((clone $ordersBase)->where(fn($q) => $q->where('orders.payment_method', '!=', 'credit')->orWhereNull('orders.payment_method'))
            ->selectRaw('SUM(GREATEST(0, orders.total - COALESCE(orders.paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);
        $creditOrders = (float) ((clone $ordersBase)->where('orders.payment_method', 'credit')
            ->selectRaw('SUM(GREATEST(0, orders.total - COALESCE(orders.paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);

        $fromTheseOrders = (float) ((clone $ordersBase)->sum('orders.paid_amount') ?? 0);
        $orderIdsSubquery = (clone $ordersBase)->select('orders.id');

        // Direct payments on these orders
        $directCash = (float) ((clone $ordersBase)->where('orders.payment_method', 'cash')->whereDoesntHave('paymentTransactions')->sum('orders.paid_amount') ?? 0);
        $directCard = (float) ((clone $ordersBase)->where('orders.payment_method', 'card')->whereDoesntHave('paymentTransactions')->sum('orders.paid_amount') ?? 0);
        $directBank = (float) ((clone $ordersBase)->where('orders.payment_method', 'bank')->whereDoesntHave('paymentTransactions')->sum('orders.paid_amount') ?? 0);

        $txSums = PaymentTransaction::whereIn('order_id', $orderIdsSubquery)
            ->where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 'rejected'))
            ->selectRaw("
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) as cash_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'card' THEN amount ELSE 0 END), 0) as card_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'bank' THEN amount ELSE 0 END), 0) as bank_sum
            ")->first();

        $cashTotal = (float) (($txSums->cash_sum ?? 0) + $directCash);
        $cardTotal = (float) (($txSums->card_sum ?? 0) + $directCard);
        $bankTotal = (float) (($txSums->bank_sum ?? 0) + $directBank);

        // Payments collected in date range
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

        // Commissions and branch breakdown
        $profitQuery = (clone $ordersBase)
            ->leftJoin('branches as collection_branches', 'collection_branches.id', '=', 'orders.branch_id')
            ->selectRaw("
                COALESCE(SUM(CASE 
                    WHEN orders.branch_profit > 0 THEN orders.branch_profit
                    WHEN orders.workshop_profit > 0 THEN (orders.total - orders.workshop_profit)
                    WHEN orders.branch_id != orders.workshop_id AND orders.workshop_id IS NOT NULL AND collection_branches.branch_profit_percentage > 0 
                        THEN (orders.total * (collection_branches.branch_profit_percentage / 100.0))
                    ELSE 0 END), 0) as total_branch_commission,
                COALESCE(SUM(CASE 
                    WHEN orders.workshop_profit > 0 THEN orders.workshop_profit
                    WHEN orders.branch_profit > 0 THEN (orders.total - orders.branch_profit)
                    WHEN orders.branch_id != orders.workshop_id AND orders.workshop_id IS NOT NULL AND collection_branches.branch_profit_percentage > 0 
                        THEN (orders.total * (1 - (collection_branches.branch_profit_percentage / 100.0)))
                    ELSE orders.total END), 0) as total_workshop_net
            ")->first();

        $collectionPointBreakdown = (clone $ordersBase)
            ->join('branches', 'branches.id', '=', 'orders.branch_id')
            ->where(fn($q) => $q->whereColumn('orders.branch_id', '!=', 'orders.workshop_id')->orWhereNull('orders.workshop_id'))
            ->where('branches.type', '!=', 'workshop')
            ->selectRaw("
                branches.id as branch_id, branches.name as branch_name,
                COALESCE(branches.branch_profit_percentage, 0) as commission_rate,
                COUNT(orders.id) as order_count, COALESCE(SUM(orders.total), 0) as gross_total,
                COALESCE(SUM(CASE 
                    WHEN orders.branch_profit > 0 THEN orders.branch_profit
                    WHEN orders.workshop_profit > 0 THEN (orders.total - orders.workshop_profit)
                    WHEN branches.branch_profit_percentage > 0 THEN (orders.total * (branches.branch_profit_percentage / 100.0))
                    ELSE 0 END), 0) as branch_commission,
                COALESCE(SUM(CASE 
                    WHEN orders.workshop_profit > 0 THEN orders.workshop_profit
                    WHEN orders.branch_profit > 0 THEN (orders.total - orders.branch_profit)
                    WHEN branches.branch_profit_percentage > 0 THEN (orders.total * (1 - (branches.branch_profit_percentage / 100.0)))
                    ELSE orders.total END), 0) as workshop_net
            ")->groupBy('branches.id', 'branches.name', 'branches.branch_profit_percentage')->get();

        $workshopBreakdown = (clone $ordersBase)
            ->leftJoin('branches as workshop_branches', 'workshop_branches.id', '=', 'orders.workshop_id')
            ->whereNotNull('orders.workshop_id')
            ->selectRaw("
                workshop_branches.id as workshop_id, COALESCE(workshop_branches.name, 'Default Workshop') as workshop_name,
                COUNT(orders.id) as order_count, COALESCE(SUM(orders.total), 0) as gross_total,
                COALESCE(SUM(CASE WHEN orders.workshop_profit > 0 THEN orders.workshop_profit WHEN orders.branch_profit > 0 THEN (orders.total - orders.branch_profit) ELSE orders.total END), 0) as workshop_share,
                COALESCE(SUM(CASE WHEN orders.branch_profit > 0 THEN orders.branch_profit WHEN orders.workshop_profit > 0 THEN (orders.total - orders.workshop_profit) ELSE 0 END), 0) as branch_commission
            ")->groupBy('workshop_branches.id', 'workshop_branches.name')->get();

        $directWorkshopOrders = (clone $ordersBase)->where(fn($q) => $q->whereColumn('orders.branch_id', '=', 'orders.workshop_id')->orWhereHas('branch', fn($b) => $b->where('type', 'workshop')))
            ->selectRaw("COUNT(orders.id) as order_count, COALESCE(SUM(orders.total), 0) as gross_total")->first();

        $directWorkshopGross = (float) ($directWorkshopOrders->gross_total ?? 0);
        $directWorkshopCount = (int) ($directWorkshopOrders->order_count ?? 0);
        $collectionPointsGross = max(0, (float) ($totalOrderValue - $directWorkshopGross));

        $workshopDirectOrdersQuery = (clone $ordersBase)->where(fn($q) => $q->whereColumn('orders.branch_id', '=', 'orders.workshop_id')->orWhereHas('branch', fn($b) => $b->where('type', 'workshop')));
        $workshopDirectCollected = (float) ((clone $workshopDirectOrdersQuery)->sum('orders.paid_amount') ?? 0);

        $collectionPointOrdersQuery = (clone $ordersBase)->where(fn($q) => $q->whereColumn('orders.branch_id', '!=', 'orders.workshop_id')->orWhereNull('orders.workshop_id'))->whereHas('branch', fn($b) => $b->where('type', '!=', 'workshop'));
        $collectionPointsCollected = (float) ((clone $collectionPointOrdersQuery)->sum('orders.paid_amount') ?? 0);

        return [
            'total_order_value' => $totalOrderValue,
            'walk_in' => $walkIn,
            'delivery' => $delivery,
            'pending_due' => $pendingDue,
            'regular_pending' => $regularPending,
            'credit_orders' => $creditOrders,
            'collected_payments' => $totalCollectedInRange,
            'from_these_orders' => $fromTheseOrders,
            'cash' => $rangeCashTotal,
            'card' => $rangeCardTotal,
            'bank' => $rangeBankTotal,
            'these_orders_cash' => $cashTotal,
            'these_orders_card' => $cardTotal,
            'these_orders_bank' => $bankTotal,
            'total_branch_commission' => (float) ($profitQuery->total_branch_commission ?? 0),
            'total_workshop_net' => (float) ($profitQuery->total_workshop_net ?? 0),
            'collection_point_breakdown' => $collectionPointBreakdown,
            'workshop_breakdown' => $workshopBreakdown,
            'direct_workshop_count' => $directWorkshopCount,
            'direct_workshop_gross' => $directWorkshopGross,
            'collection_points_gross' => $collectionPointsGross,
            'workshop_direct_collected' => $workshopDirectCollected,
            'collection_points_collected' => $collectionPointsCollected,
            'order_qty' => (int) (clone $ordersBase)->count(),
            'total_customer_count' => (int) (clone $ordersBase)->distinct()->count('customer_id'),
            'item_qty' => (int) (OrderItem::whereIn('order_id', $orderIdsSubquery)->sum('quantity') ?? 0),
        ];
    }
}

