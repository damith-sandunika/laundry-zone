<?php

namespace App\Actions\Reports;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class GetCreditOrdersAction
{
    public function execute(?int $branchId, string $startDate, string $endDate, bool $isAggregate = true)
    {
        $query = Order::with('customer')
            ->whereColumn('paid_amount', '<', 'total')
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('customer_id')
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($isAggregate) {
            $query->select('customer_id', DB::raw('SUM(total - paid_amount) as total_credit'), DB::raw('COUNT(id) as pending_orders'))
                  ->groupBy('customer_id');
        } else {
            $query->with('branch')->orderBy('customer_id')->orderBy('delivered_at', 'desc');
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }
}
