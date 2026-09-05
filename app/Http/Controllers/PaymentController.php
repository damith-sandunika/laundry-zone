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
    public function webhook(Request $request)
    {
        // 1. Extract PayHere Webhook Payload
        $merchantId     = $request->input('merchant_id');
        $orderId        = $request->input('order_id');
        $payhereAmount  = $request->input('payhere_amount');
        $payhereCurrency= $request->input('payhere_currency');
        $statusCode     = $request->input('status_code');
        $md5sig         = $request->input('md5sig');

        $merchantSecret = \App\Models\Setting::get('gateway_merchant_secret', ''); // Configured via settings

        // 2. Signature Validation (PayHere Standard Security)
        $localMd5sig = strtoupper(
            md5(
                $merchantId . 
                $orderId . 
                $payhereAmount . 
                $payhereCurrency . 
                $statusCode . 
                strtoupper(md5($merchantSecret)) 
            ) 
        );

        if (($localMd5sig !== $md5sig) || empty($merchantSecret)) {
            \Illuminate\Support\Facades\Log::warning("Payment signature validation failed for order {$orderId}");
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // 3. Process Valid Payment
        if ($statusCode == 2) { // 2 = Success
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($orderId, $payhereAmount) {
                    $order = Order::find($orderId);
                    if ($order && !$order->payment_approved) {
                        $order->update([
                            'payment_approved' => true,
                            'paid_amount'      => $order->paid_amount + $payhereAmount,
                        ]);

                        // Notify Admins
                        $branchAdmins = \App\Models\User::whereIn('role', ['branch_admin', 'staff'])
                            ->where('branch_id', $order->branch_id)
                            ->get();

                        try {
                            \Illuminate\Support\Facades\Notification::send(
                                $branchAdmins,
                                new \App\Notifications\SystemAlertNotification(
                                    'Payment Received for Order #' . $order->order_number,
                                    route('orders.show', $order->order_number),
                                    'payment'
                                )
                            );
                        } catch (\Exception $notifEx) {
                            // Notification failure must NOT roll back the payment update
                            \Illuminate\Support\Facades\Log::warning(
                                "Payment notification failed for order {$orderId}: " . $notifEx->getMessage()
                            );
                        }
                    }
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error(
                    "Payment webhook DB error for order {$orderId}: " . $e->getMessage()
                );
                // Return 500 so PayHere retries the webhook
                return response()->json(['error' => 'Payment processing failed, will retry'], 500);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    public function success(Order $order)
    {
        return view('payment.success', compact('order'));
    }
}

