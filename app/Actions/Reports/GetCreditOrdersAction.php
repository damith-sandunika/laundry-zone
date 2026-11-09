<?php

namespace App\Actions\Reports;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class GetCreditOrdersAction
{
    public function execute(?int $branchId, string $startDate, string $endDate, bool $isAggregate = true, ?string $customerType = null)
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

        if ($customerType === 'corporate') {
            $query->whereHas('customer', fn($q) => $q->where('customer_type', 'corporate')->orWhere('is_corporate', true));
        } elseif ($customerType === 'online') {
            $query->whereHas('customer', fn($q) => $q->where('customer_type', 'online'));
        } elseif ($customerType === 'walkin') {
            $query->whereHas('customer', fn($q) => $q->where('customer_type', 'walkin')->orWhere(function($sub) {
                $sub->whereNull('customer_type')->where('is_corporate', false);
            }));
        }

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
