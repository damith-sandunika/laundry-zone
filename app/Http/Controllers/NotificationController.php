<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;

class NotificationController extends Controller
{
    public function check()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['count' => 0, 'alerts' => []]);
        }

        $alerts = [];
        $totalCount = 0;

        if (in_array($user->role, ['workshop_admin', 'super_admin'])) {
            // 1. New Customer Orders (Online bookings waiting for action)
            $newOrders = Order::where('is_online_booking', true)
                ->where('status', 'received')
                ->where('payment_approved', false)
                ->get()->map(function($o) {
                    return ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)];
                })->toArray();
            if (count($newOrders) > 0) {
                $alerts[] = [
                    'id'            => 'new_customer_orders',
                    'label'         => 'New Customer Online Orders Received',
                    'count'         => count($newOrders),
                    'order_numbers' => $newOrders,
                    'icon'          => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                    'color'         => 'red',
                    'url'           => '#',
                ];
                $totalCount += count($newOrders);
            }

            // 2. Ready to Start (Payment Confirmed)
            $readyToStart = Order::where('is_online_booking', true)
                ->where('status', 'received')
                ->where(function ($query) {
                    $query->where('payment_approved', true)
                          ->orWhereRaw('paid_amount >= requested_advance AND requested_advance > 0');
                })
                ->get()->map(function($o) {
                    return ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)];
                })->toArray();
            if (count($readyToStart) > 0) {
                $alerts[] = [
                    'id'            => 'ready_to_start',
                    'label'         => 'Customer Payments Received - Ready to Start Work',
                    'count'         => count($readyToStart),
                    'order_numbers' => $readyToStart,
                    'icon'          => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'color'         => 'green',
                    'url'           => '#',
                ];
                $totalCount += count($readyToStart);
            }

            // 3. Incoming Dispatch (Branch -> Workshop)
            $incomingDispatchQuery = Order::where('status', 'dispatched_to_workshop');
            if ($user->role !== 'super_admin') {
                $incomingDispatchQuery->where('workshop_id', $user->branch_id);
            }
            $incomingDispatch = $incomingDispatchQuery->get()->map(function($o) {
                return ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)];
            })->toArray();
            if (count($incomingDispatch) > 0) {
                $alerts[] = [
                    'id'            => 'incoming_dispatch',
                    'label'         => 'Order Transfer from Branch to Workshop',
                    'count'         => count($incomingDispatch),
                    'order_numbers' => $incomingDispatch,
                    'icon'          => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                    'color'         => 'purple',
                    'url'           => '#',
                ];
                $totalCount += count($incomingDispatch);
            }
        }

        if (in_array($user->role, ['branch_admin', 'staff', 'admin', 'super_admin', 'workshop_admin'])) {
            // 4. New Pickup Requests
            $pendingPickupsQuery = \App\Models\DeliveryAppointment::with('customer')->where('status', 'pending');
            if ($user->role !== 'super_admin' && $user->role !== 'workshop_admin') {
                $pendingPickupsQuery->where('branch_id', $user->branch_id);
            }
            $pendingPickups = $pendingPickupsQuery->get()->map(function ($p) {
                $numberStr = $p->order_id && $p->order ? $p->order->order_number : ('Pickup: ' . ($p->customer->name ?? $p->contact_phone ?? 'Unknown'));
                $url = $p->order_id && $p->order ? route('orders.show', $p->order->order_number) : route('delivery.index');
                return ['number' => $numberStr, 'url' => $url];
            })->toArray();
            if (count($pendingPickups) > 0) {
                $alerts[] = [
                    'id'            => 'new_pickup_requests',
                    'label'         => 'New Customer Pickup Requests',
                    'count'         => count($pendingPickups),
                    'order_numbers' => $pendingPickups,
                    'icon'          => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
                    'color'         => 'red',
                    'url'           => '#',
                ];
                $totalCount += count($pendingPickups);
            }

            // 5. Pending Payment Approvals
            $pendingPaymentsQuery = Order::whereHas('paymentTransactions', function ($q) {
                $q->where('status', 'pending')->whereNotNull('receipt_path');
            })->whereNotIn('status', ['delivered', 'cancelled']);
            if ($user->role !== 'super_admin' && $user->role !== 'workshop_admin') {
                $pendingPaymentsQuery->where('branch_id', $user->branch_id);
            }
            $pendingPayments = $pendingPaymentsQuery->get()->map(function($o) {
                return ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)];
            })->toArray();
            if (count($pendingPayments) > 0) {
                $alerts[] = [
                    'id'            => 'pending_payments',
                    'label'         => 'Payment Slips Uploaded - Pending Approval',
                    'count'         => count($pendingPayments),
                    'order_numbers' => $pendingPayments,
                    'icon'          => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'color'         => 'blue',
                    'url'           => '#',
                ];
                $totalCount += count($pendingPayments);
            }

            // 5b. Inventory Low Stock Alerts
            $lowStockQuery = \App\Models\InventoryItem::whereColumn('current_stock', '<=', 'min_stock')->where('is_active', true);
            if ($user->role !== 'super_admin' && $user->role !== 'workshop_admin') {
                $lowStockQuery->where('branch_id', $user->branch_id);
            }
            
            $lowStockItems = $lowStockQuery->get()->map(function($item) {
                return ['number' => $item->name, 'url' => route('inventory.index')];
            })->toArray();

            if (count($lowStockItems) > 0) {
                $alerts[] = [
                    'id'            => 'low_stock_alerts',
                    'label'         => 'Inventory Alerts - Low Stock',
                    'count'         => count($lowStockItems),
                    'subtitle'      => 'Items Low',
                    'order_numbers' => $lowStockItems,
                    'icon'          => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'color'         => 'rose',
                    'url'           => route('inventory.index'),
                ];
                $totalCount += count($lowStockItems);
            }
        }

        if (in_array($user->role, ['branch_admin', 'staff', 'admin', 'super_admin'])) {
            // 6. Incoming Return (Workshop -> Branch)
            $incomingReturnQuery = Order::where('status', 'returned_from_workshop');
            if ($user->role !== 'super_admin') {
                $incomingReturnQuery->where('branch_id', $user->branch_id);
            }
            $incomingReturn = $incomingReturnQuery->get()->map(function($o) {
                return ['number' => $o->order_number, 'url' => route('orders.show', $o->order_number)];
            })->toArray();
            if (count($incomingReturn) > 0) {
                $alerts[] = [
                    'id'            => 'incoming_return',
                    'label'         => 'Order Transfer from Workshop to Branch',
                    'count'         => count($incomingReturn),
                    'order_numbers' => $incomingReturn,
                    'icon'          => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                    'color'         => 'amber',
                    'url'           => '#',
                ];
                $totalCount += count($incomingReturn);
            }
        }

        if ($user->role === 'rider') {
            // 7. New Rider Tasks
            $newTasks = \App\Models\DeliveryTask::where('rider_id', $user->id)
                ->where('status', 'assigned')->get()->map(function ($t) {
                    $numberStr = ucfirst($t->task_type) . ' - ' . substr($t->pickup_location ?? 'Unknown', 0, 15) . '...';
                    $url = $t->order_id && $t->order ? route('orders.show', $t->order->order_number) : '#';
                    if ($t->order_id && $t->order) {
                        $numberStr = $t->order->order_number . ' (' . ucfirst($t->task_type) . ')';
                    }
                    return ['number' => $numberStr, 'url' => $url];
                })->toArray();
            if (count($newTasks) > 0) {
                $alerts[] = [
                    'id'            => 'new_rider_tasks',
                    'label'         => 'New Delivery/Pickup Tasks Assigned',
                    'count'         => count($newTasks),
                    'order_numbers' => $newTasks,
                    'icon'          => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'color'         => 'indigo',
                    'url'           => '#',
                ];
                $totalCount += count($newTasks);
            }
        }

        return response()->json([
            'count'  => $totalCount,
            'alerts' => $alerts,
        ]);
    }

    public function markAsRead(Request $request)
    {
        // No longer using database notifications for read/unread state.
        return response()->json(['success' => true]);
    }
}
