<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CustomerPackage;
use App\Models\DeliveryAppointment;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackagePayment;
use App\Models\PaymentTransaction;
use App\Models\BankDeposit;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSalesData($branchId, $startDate, $endDate)
    {
        $salesQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }

        $totalSales = (clone $salesQuery)->sum('total');

        $maxFunc = DB::getDriverName() === 'sqlite'
            ? 'MAX(0, total - paid_amount)'
            : 'GREATEST(0, total - paid_amount)';

        $totalCredit  = (clone $salesQuery)->where('payment_method', 'credit')->selectRaw("SUM({$maxFunc})")->value(DB::raw("SUM({$maxFunc})")) ?? 0;
        $totalPending = (clone $salesQuery)->where(function ($q) {
            $q->where('payment_method', '!=', 'credit')->orWhereNull('payment_method');
        })->selectRaw("SUM({$maxFunc})")->value(DB::raw("SUM({$maxFunc})")) ?? 0;

        $deliveryRevenue = (clone $salesQuery)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('delivery_appointments')
                      ->whereColumn('delivery_appointments.order_id', 'orders.id');
            })->sum('total');

        $walkinRevenue = (clone $salesQuery)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('delivery_appointments')
                      ->whereColumn('delivery_appointments.order_id', 'orders.id');
            })->sum('total');

        return compact('salesQuery', 'totalSales', 'totalCredit', 'totalPending', 'deliveryRevenue', 'walkinRevenue', 'maxFunc');
    }

    public function getPaymentsData($branchId, $startDate, $endDate)
    {
        $paymentsQuery = PaymentTransaction::with(['order', 'order.branch'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        
        if ($branchId) {
            $paymentsQuery->whereHas('order', fn ($q) => $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId));
        }

        $packagePaymentsQuery = PackagePayment::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', fn ($q) => $q->where('branch_id', $branchId));
        }

        $packagePaymentsSum  = (clone $packagePaymentsQuery)->sum('amount');
        $packagePaymentsCash = (clone $packagePaymentsQuery)->where('payment_method', 'cash')->sum('amount');
        $packagePaymentsCard = (clone $packagePaymentsQuery)->where('payment_method', 'card')->sum('amount');
        $packagePaymentsBank = (clone $packagePaymentsQuery)->where('payment_method', 'bank')->sum('amount');

        $totalCollected = (clone $paymentsQuery)->sum('amount') + $packagePaymentsSum;
        $collectedCash  = (clone $paymentsQuery)->where('payment_method', 'cash')->sum('amount') + $packagePaymentsCash;
        $collectedCard  = (clone $paymentsQuery)->where('payment_method', 'card')->sum('amount') + $packagePaymentsCard;
        $collectedBank  = (clone $paymentsQuery)->where('payment_method', 'bank')->sum('amount') + $packagePaymentsBank;

        return compact(
            'packagePaymentsSum', 'packagePaymentsCash', 'packagePaymentsCard', 'packagePaymentsBank',
            'totalCollected', 'collectedCash', 'collectedCard', 'collectedBank'
        );
    }

    public function getProfitData($branchId, $startDate, $endDate, $packagePaymentsSum)
    {
        $profitQuery = "
            SELECT 
                SUM(
                    CASE 
                        WHEN paid_before >= delivery_fee THEN 0
                        ELSE LEAST(amount, delivery_fee - paid_before)
                    END
                ) as total_delivery_fees,
                
                SUM(
                    CASE 
                        WHEN branch_id = workshop_id OR workshop_id IS NULL THEN 0
                        ELSE 
                            (amount - CASE WHEN paid_before >= delivery_fee THEN 0 ELSE LEAST(amount, delivery_fee - paid_before) END) * (COALESCE(branch_profit_percentage, 0) / 100.0)
                    END
                ) as branch_profit,
                
                SUM(
                    CASE 
                        WHEN branch_id = workshop_id OR workshop_id IS NULL THEN 
                            (amount - CASE WHEN paid_before >= delivery_fee THEN 0 ELSE LEAST(amount, delivery_fee - paid_before) END)
                        ELSE 
                            (amount - CASE WHEN paid_before >= delivery_fee THEN 0 ELSE LEAST(amount, delivery_fee - paid_before) END) * (1 - COALESCE(branch_profit_percentage, 0) / 100.0)
                    END
                ) as workshop_profit
            FROM (
                SELECT 
                    pt1.amount,
                    COALESCE(orders.delivery_fee, 0) as delivery_fee,
                    orders.branch_id,
                    orders.workshop_id,
                    branches.branch_profit_percentage,
                    (SELECT COALESCE(SUM(pt2.amount), 0) FROM payment_transactions as pt2 WHERE pt2.order_id = pt1.order_id AND pt2.id < pt1.id) as paid_before
                FROM payment_transactions as pt1
                JOIN orders ON orders.id = pt1.order_id
                LEFT JOIN branches ON branches.id = orders.branch_id
                WHERE pt1.created_at BETWEEN ? AND ?
                " . ($branchId ? " AND (orders.branch_id = ? OR orders.workshop_id = ?)" : "") . "
            ) as derived_table
        ";

        $bindings = [$startDate.' 00:00:00', $endDate.' 23:59:59'];
        if ($branchId) {
            $bindings[] = $branchId;
            $bindings[] = $branchId;
        }

        $profitResult = DB::selectOne($profitQuery, $bindings);

        $totalDeliveryFees   = $profitResult->total_delivery_fees ?? 0;
        $totalBranchProfit   = ($profitResult->branch_profit ?? 0) + $packagePaymentsSum;
        $totalWorkshopProfit = $profitResult->workshop_profit ?? 0;

        return compact('totalDeliveryFees', 'totalBranchProfit', 'totalWorkshopProfit');
    }

    public function getServiceBreakdown($branchId, $startDate, $endDate)
    {
        $serviceBreakdown = OrderItem::select(
                'service_types.name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'),
                DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) as done_payment'),
                DB::raw('SUM(CASE WHEN orders.payment_method = "credit" THEN order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * (1 - IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) ELSE 0 END) as credit_payment'),
                DB::raw('SUM(CASE WHEN (orders.payment_method IS NULL OR orders.payment_method != "credit") THEN order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * (1 - IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) ELSE 0 END) as pending_payment')
            )
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        
        if ($branchId) {
            $serviceBreakdown->where('orders.branch_id', $branchId);
        }
        
        return $serviceBreakdown->groupBy('service_types.name')->get();
    }
}
