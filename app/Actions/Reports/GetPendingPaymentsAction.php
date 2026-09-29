<?php

namespace App\Actions\Reports;

use App\Models\Order;

class GetPendingPaymentsAction
{
    public function execute(?int $branchId, string $startDate, string $endDate)
    {
        $query = Order::with(['customer', 'branch'])
            ->where(function($q) {
                $q->whereColumn('paid_amount', '<', 'total')
                  ->orWhereNull('paid_amount');
            })
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }
}
