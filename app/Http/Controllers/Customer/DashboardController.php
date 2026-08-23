<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        // Fetch active orders (not delivered or cancelled)
        $activeOrders = $customer->orders()
            ->with(['branch', 'items.serviceType'])
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->get();

        // Fetch online bookings (appointments)
        $appointments = $customer->deliveryAppointments()
            ->with('branch')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        // Fetch order history (delivered or cancelled)
        $orderHistory = $customer->orders()
            ->with(['branch'])
            ->whereIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->take(5)
            ->get();

        // Calculate stats
        $totalOrders = $customer->orders()->count() + $customer->deliveryAppointments()->count();
        $totalSpent = $customer->orders()->where('payment_status', 'paid')->sum('total');
        $activeCount = $activeOrders->count() + $appointments->count();

        return view('customer.dashboard.index', compact('customer', 'activeOrders', 'appointments', 'orderHistory', 'totalOrders', 'totalSpent', 'activeCount'));
    }

    public function orders()
    {
        $customer = Auth::guard('customer')->user();
        
        $orders = $customer->orders()
            ->with(['branch', 'items.serviceType'])
            ->get();
            
        $appointments = $customer->deliveryAppointments()
            ->with('branch')
            ->get();

        $unifiedOrders = collect();

        foreach ($orders as $order) {
            $unifiedOrders->push((object)[
                'id' => $order->id,
                'type' => 'order',
                'order_number' => $order->order_number,
                'created_at' => $order->created_at,
                'items_count' => $order->items->count(),
                'total' => $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'is_delivery' => $order->is_delivery_order,
                'original' => $order
            ]);
        }

        foreach ($appointments as $appointment) {
            // If the appointment already has a corresponding order created, skip it to avoid duplicates.
            // But wait, the system doesn't link order->appointment_id explicitly unless we added it?
            // Actually, we did add delivery_appointment_id to orders table.
            if ($orders->contains('delivery_appointment_id', $appointment->id)) {
                continue;
            }

            $unifiedOrders->push((object)[
                'id' => $appointment->id,
                'type' => 'appointment',
                'order_number' => 'B-' . str_pad($appointment->id, 4, '0', STR_PAD_LEFT),
                'created_at' => $appointment->created_at,
                'items_count' => null,
                'total' => null,
                'status' => $appointment->status,
                'payment_status' => null,
                'is_delivery' => true,
                'original' => $appointment
            ]);
        }

        // Sort by created_at desc
        $unifiedOrders = $unifiedOrders->sortByDesc('created_at')->values();

        return view('customer.dashboard.orders', compact('customer', 'unifiedOrders'));
    }

    public function showOrder(\App\Models\Order $order)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($order->customer_id !== $customer->id) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['branch', 'items.serviceType']);
        
        return view('customer.dashboard.show', compact('customer', 'order'));
    }

    public function showAppointment(\App\Models\DeliveryAppointment $appointment)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($appointment->customer_id !== $customer->id) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->load(['branch']);
        
        return view('customer.dashboard.show-appointment', compact('customer', 'appointment'));
    }

    public function pay(\App\Models\Order $order)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($order->customer_id !== $customer->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('customer.orders.show', $order)->with('error', 'This order is already paid.');
        }

        $paymentMethods = collect();
        if (\App\Models\Setting::get('payment_bank_transfer_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        }
        if (\App\Models\Setting::get('payment_online_active', '0') == '1') {
            $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        }
        if (\App\Models\Setting::get('payment_cod_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'cash_on_delivery', 'name' => 'Cash on Delivery', 'type' => 'cash_on_delivery']);
        }

        return view('customer.dashboard.pay', compact('customer', 'order', 'paymentMethods'));
    }

    public function processPayment(Request $request, \App\Models\Order $order)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($order->customer_id !== $customer->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'payment_method' => 'required|string',
            'receipt_image' => 'required_if:payment_method,Bank Transfer|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($request->payment_method === 'Bank Transfer' && $request->hasFile('receipt_image')) {
            $path = $request->file('receipt_image')->store('receipts', 'public');
            $order->update([
                'receipt_path' => $path,
                'payment_method' => 'bank_transfer',
            ]);
            return redirect()->route('customer.orders.show', $order)->with('success', 'Receipt uploaded successfully. We will verify and update your payment status.');
        }

        // Simulate successful card payment
        if ($request->payment_method === 'Card') {
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => 'card',
            ]);
            return redirect()->route('customer.orders.show', $order)->with('success', 'Payment successful! Thank you.');
        }

        return back()->with('error', 'Invalid payment method selected.');
    }
}
