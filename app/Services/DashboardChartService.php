<?php

namespace App\Services;

use App\Models\Order;

class DashboardChartService
{
    public function getChartData(?int $branchId): array
    {
        // Today: hourly breakdown (last 8 hours in 3h blocks)
        $todayLabels = [];
        $todayPending = [];
        $todayDelivered = [];
        for ($h = 0; $h < 8; $h++) {
            $start = now()->startOfDay()->addHours($h * 3);
            $end = $start->copy()->addHours(3);
            $todayLabels[] = $start->format('H:i');
            $q = Order::whereBetween('created_at', [$start, $end]);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $todayPending[] = (clone $q)->where('status', 'received')->count();
            $todayDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        // Weekly: last 7 days
        $weeklyLabels = [];
        $weeklyPending = [];
        $weeklyDelivered = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $weeklyLabels[] = $day->format('D');
            $q = Order::whereDate('created_at', $day->toDateString());
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $weeklyPending[] = (clone $q)->whereNotIn('status', ['delivered', 'cancelled'])->count();
            $weeklyDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        // Monthly: last 4 weeks
        $monthlyLabels = [];
        $monthlyPending = [];
        $monthlyDelivered = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end = $start->copy()->endOfWeek();
            $monthlyLabels[] = 'Wk '.$start->format('d/m');
            $q = Order::whereBetween('created_at', [$start, $end]);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            $monthlyPending[] = (clone $q)->whereNotIn('status', ['delivered', 'cancelled'])->count();
            $monthlyDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        return [
            'today' => ['labels' => $todayLabels, 'pending' => $todayPending, 'delivered' => $todayDelivered],
            'weekly' => ['labels' => $weeklyLabels, 'pending' => $weeklyPending, 'delivered' => $weeklyDelivered],
            'monthly' => ['labels' => $monthlyLabels, 'pending' => $monthlyPending, 'delivered' => $monthlyDelivered],
        ];
    }
}
