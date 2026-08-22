<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryAppointment;
use App\Models\DeliveryTask;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
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
        $pickups = $pickupsQuery->whereIn('status', ['pending', 'confirmed', 'rider_assigned', 'picked_up', 'cancelled'])->get();

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
            'rider_id' => 'required|exists:users,id',
        ]);

        $rider = User::findOrFail($request->rider_id);

        // Create pickup task for rider
        DeliveryTask::create([
            'delivery_appointment_id' => $appointment->id,
            'rider_id' => $rider->id,
            'branch_id' => $appointment->branch_id,
            'task_type' => 'pickup',
            'status' => 'assigned',
            'pickup_location' => $appointment->pickup_address,
            'dropoff_location' => Branch::find($appointment->branch_id)->address ?? 'Branch',
        ]);

        $appointment->update(['status' => 'rider_assigned']);

        // WA notification
        $waService->notifyRiderAssigned($appointment, $rider);

        return redirect()->route('delivery.index')->with('success', "Rider {$rider->name} assigned for pickup.");
    }

    // ─── Branch Admin: Mark order received at branch, assign delivery rider ───
    public function markAtBranch(DeliveryAppointment $appointment, WhatsAppService $waService)
    {
        $appointment->update(['status' => 'at_branch']);

        // Create the official Order stub for the Workshop
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
                'due_date' => Carbon::now()->addDays(2), // Default 2 days
            ]);

            $appointment->update(['order_id' => $order->id]);

            // Notify customer that order is at branch
            // $waService->notifyOrderReceived($order);
        }

        return redirect()->back()->with('success', 'Order received at branch and moved to Workshop Orders.');
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

        foreach ($transactions as $tx) {
            $user = User::find($tx->created_by);
            if ($user && $user->role === 'rider') {
                $tx->update(['collected_from_rider_at' => now()]);
            }
        }

        return redirect()->back()->with('success', 'Cash marked as received from rider.');
    }
}
