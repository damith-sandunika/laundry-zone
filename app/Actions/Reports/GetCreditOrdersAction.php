<?php

namespace App\Actions\Reports;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class GetCreditOrdersAction
{
    public function execute(?int $branchId, string $startDate, string $endDate, bool $isAggregate = true)
    {
        $query = Order::with('customer')
            ->where(function($q) {
                $q->whereColumn('paid_amount', '<', 'total')
                  ->orWhereNull('paid_amount');
            })
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('customer_id')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($isAggregate) {
            $query->select('customer_id', DB::raw('SUM(total - COALESCE(paid_amount, 0)) as total_credit'), DB::raw('COUNT(id) as pending_orders'))
                  ->groupBy('customer_id');
        } else {
            $query->with('branch')->orderBy('customer_id')->orderBy('created_at', 'desc');
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }
}
