<?php

namespace App\Actions\Reports;

use App\Models\Order;

class GetPendingPaymentsAction
{
    public function execute(?int $branchId, string $startDate, string $endDate, ?string $customerType = null)
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

        if ($customerType === 'corporate') {
            $query->whereHas('customer', fn($q) => $q->where('customer_type', 'corporate')->orWhere('is_corporate', true));
        } elseif ($customerType === 'online') {
            $query->whereHas('customer', fn($q) => $q->where('customer_type', 'online'));
        } elseif ($customerType === 'walkin') {
            $query->whereHas('customer', fn($q) => $q->where('customer_type', 'walkin')->orWhere(function($sub) {
                $sub->whereNull('customer_type')->where('is_corporate', false);
            }));
        }

        return $query;
    }
}
