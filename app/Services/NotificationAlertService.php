<?php

namespace App\Services;

use App\Models\Order;

class NotificationAlertService
{
    public function getWorkshopAlerts($user, bool $isSuper): array
    {
        $alerts = [];
        $count = 0;

        // 1. New Customer Online Orders
        $newOrdersQuery = Order::where('is_online_booking', true)->where('status', 'received')->where('payment_approved', false);
        if (!$isSuper) $newOrdersQuery->where('workshop_id', $user->branch_id);
        $newOrders = $newOrdersQuery->limit(50)->get()->map(fn($o) => ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)])->toArray();
        if (count($newOrders) > 0) {
            $alerts[] = [
                'id' => 'new_customer_orders', 'label' => 'New Customer Online Orders Received',
                'count' => count($newOrders), 'order_numbers' => $newOrders,
                'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                'color' => 'red', 'url' => '#',
            ];
            $count += count($newOrders);
        }

        // 2. Ready to Start Work
        $readyQuery = Order::where('is_online_booking', true)->where('status', 'received')
            ->where(fn($q) => $q->where('payment_approved', true)->orWhereRaw('paid_amount >= requested_advance AND requested_advance > 0'));
        if (!$isSuper) $readyQuery->where('workshop_id', $user->branch_id);
        $readyOrders = $readyQuery->limit(50)->get()->map(fn($o) => ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)])->toArray();
        if (count($readyOrders) > 0) {
            $alerts[] = [
                'id' => 'ready_to_start', 'label' => 'Customer Payments Received - Ready to Start Work',
                'count' => count($readyOrders), 'order_numbers' => $readyOrders,
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'green', 'url' => '#',
            ];
            $count += count($readyOrders);
        }

        // 3. Incoming Dispatch
        $incomingQuery = Order::where('status', 'dispatched_to_workshop');
        if (!$isSuper) $incomingQuery->where('workshop_id', $user->branch_id);
        $incoming = $incomingQuery->limit(50)->get()->map(fn($o) => ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)])->toArray();
        if (count($incoming) > 0) {
            $alerts[] = [
                'id' => 'incoming_dispatch', 'label' => 'Order Transfer from Branch to Workshop',
                'count' => count($incoming), 'order_numbers' => $incoming,
                'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'color' => 'purple', 'url' => '#',
            ];
            $count += count($incoming);
        }

        return ['alerts' => $alerts, 'count' => $count];
    }

    public function getBranchAlerts($user, bool $isSuper): array
    {
        $alerts = [];
        $count = 0;

        $receivedAtBranchQuery = Order::where('status', 'received_at_branch');
        if (!$isSuper) $receivedAtBranchQuery->where('branch_id', $user->branch_id);
        $receivedAtBranch = $receivedAtBranchQuery->limit(50)->get()->map(fn($o) => ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)])->toArray();
        if (count($receivedAtBranch) > 0) {
            $alerts[] = [
                'id' => 'received_at_branch', 'label' => 'Orders Received from Workshop - Ready for Customer',
                'count' => count($receivedAtBranch), 'order_numbers' => $receivedAtBranch,
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald', 'url' => '#',
            ];
            $count += count($receivedAtBranch);
        }

        return ['alerts' => $alerts, 'count' => $count];
    }
}

