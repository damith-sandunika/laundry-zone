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
            $newOrdersCount = Order::where('is_online_booking', true)
                ->where('status', 'received')
                ->where('payment_approved', false)
                ->count();
            
            if ($newOrdersCount > 0) {
                $alerts[] = [
                    'id' => 'new_customer_orders',
                    'label' => 'New Customer Online Orders Received',
                    'count' => $newOrdersCount,
                    'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                    'color' => 'red',
                    'url' => '#'
                ];
                $totalCount += $newOrdersCount;
            }

            // 2. Ready to Start (Payment Confirmed)
            $readyToStartCount = Order::where('is_online_booking', true)
                ->where('status', 'received')
                ->where(function($query) {
                    $query->where('payment_approved', true)
                          ->orWhereRaw('paid_amount >= requested_advance AND requested_advance > 0');
                })
                ->count();

            if ($readyToStartCount > 0) {
                $alerts[] = [
                    'id' => 'ready_to_start',
                    'label' => 'Customer Payments Received - Ready to Start Work',
                    'count' => $readyToStartCount,
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'color' => 'green',
                    'url' => '#'
                ];
                $totalCount += $readyToStartCount;
            }

            // 3. Incoming Dispatch (Branch -> Workshop)
            $incomingDispatchQuery = Order::where('status', 'dispatched_to_workshop');
            if ($user->role !== 'super_admin') {
                $incomingDispatchQuery->where('workshop_id', $user->branch_id);
            }
            $incomingDispatchCount = $incomingDispatchQuery->count();

            if ($incomingDispatchCount > 0) {
                $alerts[] = [
                    'id' => 'incoming_dispatch',
                    'label' => 'Order Transfer from Branch to Workshop',
                    'count' => $incomingDispatchCount,
                    'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                    'color' => 'purple',
                    'url' => '#'
                ];
                $totalCount += $incomingDispatchCount;
            }
        }

        if (in_array($user->role, ['branch_admin', 'staff', 'admin', 'super_admin', 'workshop_admin'])) {
            // 4. New Pickup Requests (Pending Delivery Appointments)
            $pendingPickupsQuery = \App\Models\DeliveryAppointment::where('status', 'pending');
            if ($user->role !== 'super_admin' && $user->role !== 'workshop_admin') {
                $pendingPickupsQuery->where('branch_id', $user->branch_id);
            }
            $pendingPickupsCount = $pendingPickupsQuery->count();

            if ($pendingPickupsCount > 0) {
                $alerts[] = [
                    'id' => 'new_pickup_requests',
                    'label' => 'New Customer Pickup Requests',
                    'count' => $pendingPickupsCount,
                    'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                    'color' => 'red',
                    'url' => '#'
                ];
                $totalCount += $pendingPickupsCount;
            }

            // 5. Pending Payment Approvals (Customer uploaded a slip)
            $pendingPaymentsQuery = Order::whereHas('paymentTransactions', function ($q) {
                $q->where('status', 'pending')->whereNotNull('receipt_path');
            })->whereIn('status', ['pending', 'received', 'dispatched_to_workshop', 'received_at_workshop']);
            
            if ($user->role !== 'super_admin' && $user->role !== 'workshop_admin') {
                $pendingPaymentsQuery->where('branch_id', $user->branch_id);
            }
            $pendingPaymentsCount = $pendingPaymentsQuery->count();

            if ($pendingPaymentsCount > 0) {
                $alerts[] = [
                    'id' => 'pending_payments',
                    'label' => 'Payment Slips Uploaded - Pending Approval',
                    'count' => $pendingPaymentsCount,
                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', // Document icon
                    'color' => 'blue',
                    'url' => '#'
                ];
                $totalCount += $pendingPaymentsCount;
            }
        }

        if (in_array($user->role, ['branch_admin', 'staff', 'admin', 'super_admin'])) {
            // 6. Incoming Return (Workshop -> Branch)
            $incomingReturnQuery = Order::where('status', 'returned_from_workshop');
            if ($user->role !== 'super_admin') {
                $incomingReturnQuery->where('branch_id', $user->branch_id);
            }
            $incomingReturnCount = $incomingReturnQuery->count();

            if ($incomingReturnCount > 0) {
                $alerts[] = [
                    'id' => 'incoming_return',
                    'label' => 'Order Transfer from Workshop to Branch',
                    'count' => $incomingReturnCount,
                    'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                    'color' => 'amber',
                    'url' => '#'
                ];
                $totalCount += $incomingReturnCount;
            }
        }

        if ($user->role === 'rider') {
            // 7. New Rider Tasks
            $newTasksCount = \App\Models\DeliveryTask::where('rider_id', $user->id)
                ->where('status', 'assigned')
                ->count();
            
            if ($newTasksCount > 0) {
                $alerts[] = [
                    'id' => 'new_rider_tasks',
                    'label' => 'New Delivery/Pickup Tasks Assigned',
                    'count' => $newTasksCount,
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', // Lightning icon
                    'color' => 'indigo',
                    'url' => '#'
                ];
                $totalCount += $newTasksCount;
            }
        }

        return response()->json([
            'count' => $totalCount,
            'alerts' => $alerts
        ]);
    }

    public function markAsRead(Request $request)
    {
        // No longer using database notifications for read/unread state.
        return response()->json(['success' => true]);
    }
}
