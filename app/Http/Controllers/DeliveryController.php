<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryAppointment;
use App\Models\DeliveryTask;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    // ─── Branch Admin: View All Delivery Appointments ───
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Super admins and workshop admins have full access
        $hasFullAccess = $user->isSuperAdmin() || $user->role === 'workshop_admin';

        // Branch admins and staff can only access if delivery is enabled for their branch
        if (! $hasFullAccess && (! $user->branch || ! $user->branch->is_delivery_enabled)) {
            abort(403, 'Delivery operations are not enabled for your branch.');
        }

        $query = DeliveryAppointment::with(['customer', 'branch', 'deliveryTasks.rider', 'order.paymentTransactions', 'cancelledBy'])
            ->orderByDesc('pickup_scheduled_at');

        if (! $hasFullAccess) {
            $query->where('branch_id', $branchId);
        }

        $pickupsQuery = clone $query;
        $pickups = $pickupsQuery->whereIn('status', ['pending', 'confirmed', 'rider_assigned', 'picked_up', 'order_created'])->get();

        $returnsQuery = clone $query;
        $returns = $returnsQuery->whereIn('status', ['ready', 'out_for_delivery', 'delivered'])
            ->where('delivery_preference', 'deliver_to_address')
            ->get()
            ->filter(function ($appt) {
                if ($appt->status === 'delivered') {
                    $order = $appt->order;
                    if (! $order) {
                        return false;
                    }

                    if ($order->payment_status !== 'paid') {
                        return true;
                    }

                    $pendingCashTx = PaymentTransaction::where('order_id', $order->id)
                        ->where('payment_method', 'cash')
                        ->whereNull('collected_from_rider_at')
                        ->whereNotNull('created_by')
                        ->exists();

                    return $pendingCashTx;
                }

                return true;
            });

        $branches = $hasFullAccess ? Branch::where('is_active', true)->get() : collect();

        $ridersQuery = User::where('role', 'rider');
        if (! $hasFullAccess) {
            $ridersQuery->where('branch_id', $branchId);
        }
        $riders = $ridersQuery->get();

        return view('delivery.index', compact('pickups', 'returns', 'branches', 'riders'));
    }

    // ─── Public routes moved to CustomerSiteController ───

    // ─── Branch Admin: Confirm Appointment & Assign Rider ───
    public function confirm(DeliveryAppointment $appointment)
    {
        $branchId = $appointment->branch_id;
        $riders = User::where('role', 'rider')->where('branch_id', $branchId)->where('is_active', true)->get();

        return view('delivery.confirm', compact('appointment', 'riders'));
    }

    public function confirmAction(DeliveryAppointment $appointment)
    {
        $appointment->update(['status' => 'confirmed']);

        $commService = new \App\Services\CommunicationService();
        $commService->sendBookingConfirmation($appointment);

        return redirect()->route('delivery.index')->with('success', 'Appointment confirmed. Customer notified.');
    }

    public function cancel(Request $request, DeliveryAppointment $appointment)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);
        $appointment->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->reason,
            'cancelled_by' => auth()->id(),
        ]);

        return redirect()->route('delivery.index')->with('success', 'Appointment cancelled. Customer notified.');
    }

    public function assignRider(Request $request, DeliveryAppointment $appointment)
    {
        $request->validate([
            'delivery_method' => 'nullable|string|in:in_house,pickme_flash',
            'rider_id' => 'required_if:delivery_method,in_house',
        ]);

        $deliveryMethod = $request->delivery_method ?? 'in_house';

        if ($deliveryMethod === 'pickme_flash') {
            $appointment->update(['status' => 'rider_assigned']);
            return redirect()->route('delivery.index')->with('success', "PickMe assigned. Rider is on the way to customer.");
        }

        $rider = User::findOrFail($request->rider_id);

        // Create pickup task for rider
        DeliveryTask::create([
            'delivery_appointment_id' => $appointment->id,
            'rider_id' => $rider->id,
            'branch_id' => $appointment->branch_id,
            'task_type' => 'pickup',
            'status' => 'assigned',
            'pickup_location' => $appointment->pickup_address,
            'dropoff_location' => Branch::find($appointment->branch_id)->address ?? 'Workshop',
        ]);

        $appointment->update(['status' => 'rider_assigned']);

        \Illuminate\Support\Facades\Notification::send($rider, new \App\Notifications\SystemAlertNotification('New Pickup Task Assigned: #' . $appointment->id, route('rider.tasks')));

        // Notification
        $commService = new CommunicationService();
        $commService->sendPickupConfirmation($appointment);

        return redirect()->route('delivery.index')->with('success', "Rider {$rider->name} assigned for pickup.");
    }

    // ─── Branch Admin: Mark order received at branch, assign delivery rider ───
    public function markAtBranch(Request $request, DeliveryAppointment $appointment)
    {
        $appointment->update(['status' => 'at_branch']);

        if ($request->filled('pickme_flash_cost')) {
            \App\Models\Expense::create([
                'branch_id' => auth()->user()->branch_id, // Workshop's branch
                'category' => 'PickMe',
                'description' => 'PickMe payment for pickup delivery #' . $appointment->id,
                'amount' => $request->pickme_flash_cost,
                'expense_date' => now(),
                'created_by' => auth()->id(),
            ]);
        }

        $dispatch = null;
        DB::transaction(function () use ($request, $appointment, &$dispatch) {
            // Create the official Order stub for the Workshop if not exists
            if (! $appointment->order_id) {
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber($appointment->branch_id, true),
                    'customer_id' => $appointment->customer_id,
                    'branch_id' => $appointment->branch_id,
                    'status' => 'received',
                    'subtotal' => 0,
                    'total' => $appointment->delivery_cost ?? 0,
                    'delivery_fee' => $appointment->delivery_cost ?? 0,
                    'paid_amount' => 0,
                    'payment_status' => 'pending',
                    'payment_method' => $appointment->payment_method ?? 'cash',
                    'due_date' => \Carbon\Carbon::now()->addDays(2), // Default 2 days
                    'created_by' => auth()->id(),
                    'is_delivery_order' => true,
                    'delivery_appointment_id' => $appointment->id,
                ]);

                $appointment->update(['order_id' => $order->id]);

                if ($appointment->cart_json) {
                    $cart = json_decode($appointment->cart_json, true);
                    if (is_array($cart)) {
                        $subtotal = 0;
                        foreach ($cart as $item) {
                            if (isset($item['service']['id']) && isset($item['quantity'])) {
                                $qty = $item['quantity'];
                                $price = $item['service']['base_price'] ?? 0;
                                
                                \App\Models\OrderItem::create([
                                    'order_id' => $order->id,
                                    'service_type_id' => $item['service']['id'],
                                    'item_description' => $item['service']['name'] ?? '',
                                    'quantity' => $qty,
                                    'unit_price' => $price,
                                    'total_price' => $qty * $price,
                                ]);
                                $subtotal += $qty * $price;
                            }
                        }
                        $order->update([
                            'subtotal' => $subtotal,
                            'total' => $subtotal + ($appointment->delivery_cost ?? 0),
                            'delivery_fee' => $appointment->delivery_cost ?? 0,
                        ]);
                    }
                }
                
                // Calculate Profit Share
                $branch = Branch::find($appointment->branch_id);
                if ($branch && $branch->branch_profit_percentage > 0) {
                    $subtotal = $order->subtotal ?? 0;
                    $branchProfit = $subtotal * ($branch->branch_profit_percentage / 100);
                    $workshopProfit = $subtotal - $branchProfit;
                    $order->update([
                        'branch_profit' => $branchProfit,
                        'workshop_profit' => $workshopProfit,
                    ]);
                }
            }

            $dispatch = WorkshopDispatch::create([
                'order_id' => $appointment->order_id,
                'branch_id' => $appointment->branch_id,
                'workshop_branch_id' => $appointment->branch_id,
                'dispatched_by' => auth()->id(),
                'dispatched_at' => now(),
                'status' => 'received_at_workshop',
                'received_by' => auth()->id(),
                'received_at_workshop_at' => now(),
            ]);
        });

        $workshopAdmins = \App\Models\User::where('role', 'workshop_admin')->where('branch_id', $appointment->branch_id)->get();
        $targetRouteName = auth()->user()->role === 'workshop_admin' ? 'workshop.operations' : 'orders.index';
        
        \Illuminate\Support\Facades\Notification::send($workshopAdmins, new \App\Notifications\SystemAlertNotification('New Delivery Order Arrived: #' . $dispatch->order->order_number, route($targetRouteName), 'delivery_arrived'));

        return redirect()->route($targetRouteName)->with('success', 'Delivery arrived! Order has been moved to the Operations page.');
    }

    public function assignDeliveryRider(Request $request, DeliveryAppointment $appointment)
    {
        $request->validate([
            'rider_id' => 'required',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($request->rider_id === 'pickme_flash') {
            $order = Order::find($request->order_id);
            if ($order && $order->paid_amount < $order->total) {
                return redirect()->back()->with('error', 'PickMe cannot be used for unpaid or partially paid orders. Customer must pay in full first.');
            }

            $appointment->update([
                'status' => 'delivered', 
                'delivery_method' => 'pickme_flash',
                'delivery_cost' => $request->filled('pickme_flash_cost') ? $request->pickme_flash_cost : 0
            ]);
            
            if ($order) {
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
                
                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'changed_by' => auth()->id(),
                    'note' => 'Delivered via PickMe',
                ]);
                
                $commService = new CommunicationService();
                $commService->sendOrderDelivered($order);
                
                // For PickMe: balance remaining means customer pays COD via PickMe
                // Do NOT auto-mark as paid - branch admin should handle cash collection
                // Just record a pending cash transaction if balance > 0
                $amountToPay = $order->total - $order->paid_amount;
                if ($amountToPay > 0 && in_array($order->payment_method, ['cash', null])) {
                    \App\Models\PaymentTransaction::create([
                        'order_id' => $order->id,
                        'amount' => $amountToPay,
                        'payment_method' => 'cash',
                        'created_by' => auth()->id(),
                        'collected_from_rider_at' => null, // Admin collects from PickMe
                        'status' => 'completed',
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    ]);
                    $order->update([
                        'paid_amount' => $order->total,
                        'payment_status' => 'paid'
                    ]);
                } elseif ($amountToPay <= 0) {
                    // Already fully paid (bank transfer) - just mark paid
                    $order->update(['payment_status' => 'paid']);
                }

                // Log histories to make timeline look complete
                foreach (['out_for_delivery', 'delivered', 'completed'] as $st) {
                    \App\Models\OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => $st,
                        'changed_by' => auth()->id(),
                        'note' => 'Auto-updated via PickMe/Flash dispatch',
                    ]);
                }
            }

            if ($request->filled('pickme_flash_cost')) {
                \App\Models\Expense::create([
                    'branch_id' => $appointment->branch_id,
                    'category' => 'PickMe',
                    'description' => 'PickMe payment for delivery dropoff #' . $appointment->id,
                    'amount' => $request->pickme_flash_cost,
                    'expense_date' => now(),
                    'created_by' => auth()->id(),
                ]);
            }

            return redirect()->back()->with('success', "Assigned to PickMe/Flash. Order automatically marked as Delivered & Completed.")->with('print_url', route('orders.printChit', $request->order_id));
        }

        $request->validate([
            'rider_id' => 'exists:users,id',
        ]);

        $rider = User::findOrFail($request->rider_id);

        DeliveryTask::create([
            'delivery_appointment_id' => $appointment->id,
            'order_id' => $request->order_id,
            'rider_id' => $rider->id,
            'branch_id' => $appointment->branch_id,
            'task_type' => 'delivery',
            'status' => 'assigned',
            'pickup_location' => Branch::find($appointment->branch_id)->address ?? 'Branch',
            'dropoff_location' => $appointment->delivery_address,
        ]);

        $appointment->update(['status' => 'out_for_delivery', 'delivery_method' => 'in_house']);
        
        $order = Order::find($request->order_id);
        if ($order) {
            $order->update(['status' => 'out_for_delivery']);
            $commService = new CommunicationService();
            $commService->sendDeliveryConfirmation($order);
        }

        \Illuminate\Support\Facades\Notification::send($rider, new \App\Notifications\SystemAlertNotification('New Delivery Drop-off Task Assigned: Order #' . $request->order_id, route('rider.tasks')));

        return redirect()->back()->with('success', "Rider {$rider->name} assigned for delivery.")->with('print_url', route('orders.printChit', $request->order_id));
    }

    public function collectCash(Request $request, DeliveryAppointment $appointment)
    {
        if (! $appointment->order_id) {
            return redirect()->back()->with('error', 'No order found for this appointment.');
        }

        $order = Order::find($appointment->order_id);
        if (! $order) {
            return redirect()->back()->with('error', 'Order not found.');
        }

        // Find pending cash transactions created by riders
        $transactions = PaymentTransaction::where('order_id', $order->id)
            ->where('payment_method', 'cash')
            ->whereNull('collected_from_rider_at')
            ->whereNotNull('created_by')
            ->get();

        $cashCollected = $request->input('cash_collected');

        foreach ($transactions as $tx) {
            $user = User::find($tx->created_by);
            if ($user && $user->role === 'rider') {
                $tx->update([
                    'collected_from_rider_at' => now(),
                    'amount' => $cashCollected ?? $tx->amount
                ]);
            }
        }
        
        // Find and complete the rider's task
        $task = \App\Models\DeliveryTask::where('delivery_appointment_id', $appointment->id)
            ->where('task_type', 'delivery')
            ->where('status', 'waiting_for_payment')
            ->first();
        if ($task) {
            $task->update(['status' => 'completed', 'completed_at' => now()]);
        }

        $appointment->update(['status' => 'delivered']);
        $order->update(['status' => 'delivered']);

        return redirect()->back()->with('success', 'Verified and collected cash from rider. Order marked as completed.');
    }
}
