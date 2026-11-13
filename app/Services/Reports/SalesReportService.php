<?php

namespace App\Services\Reports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function buildUnionQuery(?int $branchId, string $startDate, string $endDate, ?string $customerType = null): Builder
    {
        $paymentsBase = DB::table('payment_transactions')
            ->join('orders', 'payment_transactions.order_id', '=', 'orders.id')
            ->where('payment_transactions.status', 'completed')
            ->whereNull('payment_transactions.deleted_at')
            ->whereBetween('payment_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw("
                'payment' as type,
                payment_transactions.id as ref_id,
                payment_transactions.created_at as date,
                payment_transactions.amount as amount,
                payment_transactions.payment_method as payment_method,
                orders.id as order_id
            ");

        if ($branchId) $paymentsBase->where('orders.branch_id', $branchId);
        $this->applyCustomerTypeFilter($paymentsBase, $customerType);

        $creditsBase = DB::table('orders')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.payment_method', 'credit')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw("
                'credit_sale' as type,
                orders.id as ref_id,
                orders.created_at as date,
                orders.total as amount,
                'credit' as payment_method,
                orders.id as order_id
            ");

        if ($branchId) $creditsBase->where('orders.branch_id', $branchId);
        $this->applyCustomerTypeFilter($creditsBase, $customerType);

        $unionQuery = $paymentsBase->unionAll($creditsBase);
        $unionSql = $unionQuery->toSql();

        return DB::table(DB::raw("({$unionSql}) as activities"))
            ->mergeBindings($unionQuery)
            ->orderBy('date', 'desc');
    }

    public function getTotals(?int $branchId, string $startDate, string $endDate, ?string $customerType = null): array
    {
        $totalsQuery = DB::table('payment_transactions')
            ->join('orders', 'payment_transactions.order_id', '=', 'orders.id')
            ->where('payment_transactions.status', 'completed')
            ->whereNull('payment_transactions.deleted_at')
            ->whereBetween('payment_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId) $totalsQuery->where('orders.branch_id', $branchId);
        $this->applyCustomerTypeFilter($totalsQuery, $customerType);

        $paymentTotals = (clone $totalsQuery)
            ->selectRaw("payment_transactions.payment_method, SUM(payment_transactions.amount) as total_amount")
            ->groupBy('payment_transactions.payment_method')
            ->pluck('total_amount', 'payment_transactions.payment_method');

        $totalCash = (float) ($paymentTotals['cash'] ?? 0);
        $totalCard = (float) ($paymentTotals['card'] ?? 0);
        $totalBank = (float) ($paymentTotals['bank'] ?? 0);

        $totalCreditQuery = DB::table('orders')
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', 'credit')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($branchId) $totalCreditQuery->where('branch_id', $branchId);
        $this->applyCustomerTypeFilter($totalCreditQuery, $customerType);

        $totalCredit = (float) $totalCreditQuery->sum('total');

        return [
            'totalCash'        => $totalCash,
            'totalCard'        => $totalCard,
            'totalBank'        => $totalBank,
            'totalCredit'      => $totalCredit,
            'totalSalesAmount' => $totalCash + $totalCard + $totalBank + $totalCredit,
        ];
    }

    protected function applyCustomerTypeFilter($query, ?string $customerType): void
    {
        if ($customerType === 'corporate') {
            $query->whereExists(fn($sub) => $sub->select(DB::raw(1))->from('customers')
                ->whereColumn('customers.id', 'orders.customer_id')
                ->where(fn($q) => $q->where('customers.customer_type', 'corporate')->orWhere('customers.is_corporate', true)));
        } elseif ($customerType === 'online') {
            $query->whereExists(fn($sub) => $sub->select(DB::raw(1))->from('customers')
                ->whereColumn('customers.id', 'orders.customer_id')
                ->where('customers.customer_type', 'online'));
        } elseif ($customerType === 'walkin') {
            $query->whereExists(fn($sub) => $sub->select(DB::raw(1))->from('customers')
                ->whereColumn('customers.id', 'orders.customer_id')
                ->where(fn($q) => $q->where('customers.customer_type', 'walkin')->orWhere(fn($s2) => $s2->whereNull('customers.customer_type')->where('customers.is_corporate', false))));
        }
    }
}

