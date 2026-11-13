<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Customer\ProcessCustomerPaymentAction;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $activeOrders = $customer->orders()->with(['branch', 'items.serviceType'])
            ->whereNotIn('status', ['delivered', 'completed', 'cancelled'])->latest()->get();
        $appointments = $customer->deliveryAppointments()->with(['branch', 'order'])
            ->whereNotIn('status', ['completed', 'cancelled', 'order_created', 'delivered'])
            ->whereNull('order_id')->latest()->get();
        $orderHistory = $customer->orders()->with(['branch'])
            ->whereIn('status', ['delivered', 'completed', 'cancelled'])->latest()->take(5)->get();

        $totalOrders = $customer->orders()->count() + $customer->deliveryAppointments()->whereNull('order_id')->count();
        $totalSpent = $customer->orders()->sum('paid_amount');
        $activeCount = $activeOrders->count() + $appointments->count();

        return view('customer.dashboard.index', compact('customer', 'activeOrders', 'appointments', 'orderHistory', 'totalOrders', 'totalSpent', 'activeCount'));
    }

    public function orders()
    {
        $customer = Auth::guard('customer')->user();
        $orders = $customer->orders()->with(['branch', 'items.serviceType'])->get();
        $appointments = $customer->deliveryAppointments()->with('branch')->get();
        $unifiedOrders = collect();

        foreach ($orders as $order) {
            $unifiedOrders->push((object)[
                'id' => $order->id, 'type' => 'order', 'order_number' => $order->order_number,
                'created_at' => $order->created_at, 'items_count' => $order->items->count(),
                'total' => $order->total, 'status' => $order->status, 'payment_status' => $order->payment_status,
                'is_delivery' => $order->is_delivery_order, 'original' => $order
            ]);
        }

        foreach ($appointments as $appointment) {
            if ($orders->contains('delivery_appointment_id', $appointment->id)) continue;
            $unifiedOrders->push((object)[
                'id' => $appointment->id, 'type' => 'appointment', 'order_number' => '#' . $appointment->id,
                'created_at' => $appointment->created_at, 'items_count' => null, 'total' => null,
                'status' => $appointment->status, 'payment_status' => null, 'is_delivery' => true,
                'original' => $appointment
            ]);
        }

        $unifiedOrders = $unifiedOrders->sortByDesc('created_at')->values();
        return view('customer.dashboard.orders', compact('customer', 'unifiedOrders'));
    }

    public function showOrder(Order $order)
    {
        $customer = Auth::guard('customer')->user();
        if ($order->customer_id !== $customer->id) abort(403);
        $order->load(['branch', 'items.serviceType', 'items.turnaroundType', 'items.extras.extra', 'deliveryAppointment']);

        $paymentMethods = collect();
        if (Setting::get('payment_bank_transfer_active', '1') == '1') $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        if (Setting::get('payment_online_active', '0') == '1') $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);

        $bankName = Setting::get('bank_name', '');
        $bankAccountName = Setting::get('bank_account_name', '');
        $bankAccountNumber = Setting::get('bank_account_number', '');
        $bankDetails = ($bankName || $bankAccountNumber) ? "Bank: {$bankName}\nAccount: {$bankAccountName}\nNo: {$bankAccountNumber}" : '';

        return view('customer.dashboard.show', compact('customer', 'order', 'paymentMethods', 'bankDetails'));
    }

    public function showAppointment(DeliveryAppointment $appointment)
    {
        $customer = Auth::guard('customer')->user();
        if ($appointment->customer_id !== $customer->id) abort(403);
        $appointment->load(['branch']);
        return view('customer.dashboard.show-appointment', compact('customer', 'appointment'));
    }

    public function pay(Order $order)
    {
        $customer = Auth::guard('customer')->user();
        if ($order->customer_id !== $customer->id) abort(403);
        if ($order->payment_status === 'paid') return redirect()->route('customer.orders.show', $order)->with('error', 'This order is already paid.');

        $paymentMethods = collect();
        if (Setting::get('payment_bank_transfer_active', '1') == '1') $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        if (Setting::get('payment_online_active', '0') == '1') $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        if (Setting::get('payment_cod_active', '1') == '1') $paymentMethods->push((object)['id' => 'cash_on_delivery', 'name' => 'Cash on Delivery', 'type' => 'cash_on_delivery']);

        return view('customer.dashboard.pay', compact('customer', 'order', 'paymentMethods'));
    }

    public function submitPayment(Request $request, Order $order, ProcessCustomerPaymentAction $action)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:bank,bank_transfer,gateway',
            'receipt_image' => 'required_if:payment_method,bank,bank_transfer|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if (PaymentTransaction::where('order_id', $order->id)->where('created_at', '>=', now()->subSeconds(30))->exists()) {
            return redirect()->back()->with('success', 'Payment received. Please wait a moment.');
        }

        $action->execute($order, (float) $request->amount, $request->payment_method, $request->file('receipt_image'));
        return redirect()->back()->with('success', 'Payment submitted successfully. Thank you!');
    }
}
