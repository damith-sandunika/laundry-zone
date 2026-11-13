<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OrderQueryFilterService
{
    public function getCounts(Builder $baseQuery): array
    {
        $statusCountsRaw = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'received'   => ($statusCountsRaw['received'] ?? 0) + ($statusCountsRaw['picked_up'] ?? 0) + ($statusCountsRaw['dispatched_to_workshop'] ?? 0),
            'processing' => ($statusCountsRaw['processing'] ?? 0) + ($statusCountsRaw['completed_at_workshop'] ?? 0),
            'arriving'   => $statusCountsRaw['returned_from_workshop'] ?? 0,
            'done'       => $statusCountsRaw['done'] ?? 0,
            'delivered'  => ($statusCountsRaw['delivered'] ?? 0) + ($statusCountsRaw['completed'] ?? 0),
        ];
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (empty($filters['q'])) {
            $tab = $filters['tab'] ?? 'received';
            if ($tab === 'received') {
                $query->whereIn('status', ['received', 'picked_up', 'dispatched_to_workshop']);
            } elseif ($tab === 'processing') {
                $query->whereIn('status', ['processing', 'completed_at_workshop']);
            } elseif ($tab === 'arriving') {
                $query->where('status', 'returned_from_workshop');
            } elseif ($tab === 'done') {
                $query->where('status', 'done');
            } elseif ($tab === 'delivered') {
                $query->whereIn('status', ['delivered', 'completed']);
            }
        }

        if (!empty($filters['start_date'])) $query->whereDate('created_at', '>=', $filters['start_date']);
        if (!empty($filters['end_date'])) $query->whereDate('created_at', '<=', $filters['end_date']);
        if (!empty($filters['is_online'])) $query->where('is_online_booking', true);

        if (!empty($filters['payment_ready'])) {
            $query->where(fn($q) => $q->where('payment_approved', true)->orWhereRaw('paid_amount >= requested_advance AND requested_advance > 0'));
        }

        if (!empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(fn($qb) => $qb->where('order_number', 'like', "%{$search}%")->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%")));
        }

        return $query;
    }
}

