<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display the Mock Payment Gateway Checkout page.
     */
    public function checkout(Order $order)
    {
        // If order is already paid/approved, don't allow paying again
        if ($order->payment_approved) {
            return redirect()->route('customer.orders')->with('error', 'This order is already paid.');
        }

        // The amount to pay can be passed via query string (e.g. ?amount=1500)
        // If not passed, default to the full order total.
        $amount = request()->query('amount', $order->total);

        // Fetch mock gateway credentials just to show it's connected
        $merchantId = \App\Models\Setting::get('gateway_merchant_id', 'Not Configured');

        return view('payment.checkout', compact('order', 'amount', 'merchantId'));
    }

    /**
     * Process the payment from the Mock Gateway.
     */
    public function process(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'card_number' => 'required|string', // mocked validation
        ]);

        $order = Order::findOrFail($request->order_id);

        // In a real gateway (e.g., PayHere), this logic would be in a Webhook
        // that receives the secure signature from the bank.
        // For the mock, we just approve it directly.
        $order->update([
            'payment_approved' => true,
            'paid_amount' => $order->paid_amount + $request->amount,
        ]);

        // You might also want to create a PaymentTransaction record here in a real scenario
        
        // Notify Admins
        $branchAdmins = \App\Models\User::whereIn('role', ['branch_admin', 'staff'])->where('branch_id', $order->branch_id)->get();
        \Illuminate\Support\Facades\Notification::send($branchAdmins, new \App\Notifications\SystemAlertNotification('Payment Received for Order #' . $order->order_number, route('orders.show', $order->order_number), 'payment'));

        if ($order->workshop_id) {
            $workshopAdmins = \App\Models\User::whereIn('role', ['workshop_admin', 'staff'])->where('branch_id', $order->workshop_id)->get();
            \Illuminate\Support\Facades\Notification::send($workshopAdmins, new \App\Notifications\SystemAlertNotification('Payment Received for Order #' . $order->order_number, route('workshop.history'), 'payment'));
        }

        return redirect()->route('payment.success', ['order' => $order->id]);
    }

    public function success(Order $order)
    {
        return view('payment.success', compact('order'));
    }
}
