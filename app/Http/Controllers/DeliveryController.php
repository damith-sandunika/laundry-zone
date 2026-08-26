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
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    // ─── Branch Admin: View All Delivery Appointments ───
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = DeliveryAppointment::with(['customer', 'branch', 'deliveryTasks.rider'])
            ->orderByDesc('pickup_scheduled_at');

        if (! $user->isSuperAdmin()) {
            $query->where('branch_id', $branchId);
        }

        $pickupsQuery = clone $query;
        $pickups = $pickupsQuery->whereIn('status', ['pending', 'confirmed', 'rider_assigned', 'picked_up', 'order_created', 'cancelled'])->get();

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

        $branches = $user->isSuperAdmin() ? Branch::where('is_active', true)->get() : collect();

        $ridersQuery = User::where('role', 'rider');
        if (! $user->isSuperAdmin()) {
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

    public function confirmAction(DeliveryAppointment $appointment, WhatsAppService $waService)
    {
        $appointment->update(['status' => 'confirmed']);
        $waService->notifyDeliveryConfirmed($appointment);

        return redirect()->route('delivery.index')->with('success', 'Appointment confirmed. Customer notified.');
    }

    public function cancel(Request $request, DeliveryAppointment $appointment, WhatsAppService $waService)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);
        $appointment->update(['status' => 'cancelled']);

        $waService->notifyBookingCancelled($appointment, $request->reason ?? 'Branch cancelled.');

        return redirect()->route('delivery.index')->with('success', 'Appointment cancelled. Customer notified.');
    }

    public function assignRider(Request $request, DeliveryAppointment $appointment, WhatsAppService $waService)
    {
        $request->validate([
            'delivery_method' => 'nullable|string|in:in_house,pickme_flash',
            'rider_id' => 'required_if:delivery_method,in_house',
        ]);

        $deliveryMethod = $request->delivery_method ?? 'in_house';

        if ($deliveryMethod === 'pickme_flash') {
            $appointment->update(['status' => 'picked_up']);
            return redirect()->route('delivery.index')->with('success', "PickMe Flash assigned. Waiting to receive at workshop.");
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

        // WA notification
        $waService->notifyRiderAssigned($appointment, $rider);

        return redirect()->route('delivery.index')->with('success', "Rider {$rider->name} assigned for pickup.");
    }

    // ─── Branch Admin: Mark order received at branch, assign delivery rider ───
    public function markAtBranch(Request $request, DeliveryAppointment $appointment, WhatsAppService $waService)
    {
        $appointment->update(['status' => 'at_branch']);

        if ($request->filled('pickme_flash_cost')) {
            \App\Models\Expense::create([
                'branch_id' => auth()->user()->branch_id, // Workshop's branch
                'category' => 'PickMe Flash',
                'description' => 'PickMe Flash payment for pickup delivery #' . $appointment->id,
                'amount' => $request->pickme_flash_cost,
                'expense_date' => now(),
                'created_by' => auth()->id(),
            ]);
        }

        // Create the official Order stub for the Workshop if not exists
        if (! $appointment->order_id) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $appointment->customer_id,
                'branch_id' => $appointment->branch_id,
                'status' => 'received',
                'subtotal' => 0,
                'total' => 0,
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
                        'total' => $subtotal,
                    ]);
                }
            }
        }

        WorkshopDispatch::create([
            'order_id' => $appointment->order_id,
            'branch_id' => $appointment->branch_id,
            'workshop_branch_id' => $appointment->branch_id,
            'dispatched_by' => auth()->id(),
            'dispatched_at' => now(),
            'status' => 'received_at_workshop',
            'received_by' => auth()->id(),
            'received_at_workshop_at' => now(),
        ]);

        return redirect()->route('workshop.operations')->with('success', 'Delivery arrived! Order has been moved directly to Arrival & Processing.');
    }

    public function assignDeliveryRider(Request $request, DeliveryAppointment $appointment)
    {
        $request->validate([
            'rider_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:orders,id',
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

        $appointment->update(['status' => 'out_for_delivery']);
        Order::find($request->order_id)?->update(['status' => 'out_for_delivery']);

        return redirect()->back()->with('success', "Rider {$rider->name} assigned for delivery.");
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

        $appointment->update(['status' => 'completed']);
        $order->update(['status' => 'completed']);

        return redirect()->back()->with('success', 'Verified and collected cash from rider. Order marked as completed.');
    }
}
