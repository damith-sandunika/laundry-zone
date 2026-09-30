<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentLinkController extends Controller
{
    /**
     * Show the public invoice and payment page for the order.
     */
    public function show($token)
    {
        $order = Order::with(['branch', 'items.serviceType', 'items.turnaroundType', 'items.extras.extra', 'customer'])
            ->where('payment_token', $token)
            ->firstOrFail();

        $paymentMethods = collect();
        if (\App\Models\Setting::get('payment_bank_transfer_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        }
        if (\App\Models\Setting::get('payment_online_active', '0') == '1') {
            $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        }

        $bankName = \App\Models\Setting::get('bank_name', '');
        $bankBranch = \App\Models\Setting::get('bank_branch', '');
        $bankAccountName = \App\Models\Setting::get('bank_account_name', '');
        $bankAccountNumber = \App\Models\Setting::get('bank_account_number', '');
        
        $bankDetails = '';
        if ($bankName || $bankAccountNumber) {
            $bankDetails = "Bank: {$bankName}\nBranch: {$bankBranch}\nAccount Name: {$bankAccountName}\nAccount No: {$bankAccountNumber}";
        }

        $merchantId = \App\Models\Setting::get('gateway_merchant_id', 'Not Configured');

        // Check if there's any pending payment transaction
        $hasPendingPayment = PaymentTransaction::where('order_id', $order->id)
            ->where('status', 'pending')
            ->exists();

        return view('payment_link.show', compact('order', 'paymentMethods', 'bankDetails', 'merchantId', 'hasPendingPayment'));
    }

    /**
     * Process Bank Transfer Slip Upload via public link
     */
    public function submitBankTransfer(Request $request, $token)
    {
        $order = Order::where('payment_token', $token)->firstOrFail();

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($order->payment_status === 'paid' || $order->payment_approved) {
            return redirect()->back()->with('error', 'This order is already fully paid.');
        }

        $recentPayment = PaymentTransaction::where('order_id', $order->id)
            ->where('created_at', '>=', now()->subSeconds(30))
            ->exists();
            
        if ($recentPayment) {
            return redirect()->back()->with('success', 'Payment received. Please wait a moment before submitting again.');
        }

        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'laundry_zone/receipts',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $receiptPath = $result['secure_url'];
                } catch (\Exception $e) {
                    $receiptPath = $file->store('receipts', 'public');
                }
            } else {
                $receiptPath = $file->store('receipts', 'public');
            }
        }

        DB::transaction(function() use ($order, $request, $receiptPath) {
            $order->update([
                'receipt_path' => $receiptPath,
                'payment_method' => 'bank',
            ]);

            PaymentTransaction::create([
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => 'bank',
                'receipt_path' => $receiptPath,
                'status' => 'pending', 
            ]);
        });

        return redirect()->back()->with('success', 'Bank slip uploaded successfully. We will verify and update your order soon.');
    }

    /**
     * Webhook for online payment via public link
     * Here we just create a PENDING transaction instead of auto-approving it.
     */
    public function webhook(Request $request, $token)
    {
        $order = Order::where('payment_token', $token)->firstOrFail();

        // 1. Extract Webhook Payload
        $merchantId     = $request->input('merchant_id');
        $orderId        = $request->input('order_id'); // This should match our order id
        $payhereAmount  = $request->input('payhere_amount');
        $payhereCurrency= $request->input('payhere_currency');
        $statusCode     = $request->input('status_code');
        $md5sig         = $request->input('md5sig');

        $merchantSecret = \App\Models\Setting::get('gateway_merchant_secret', '');

        // 2. Signature Validation
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
            Log::channel('payments')->warning("Payment link signature validation failed for order {$order->id}");
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // 3. Process Valid Payment - JUST PENDING
        if ($statusCode == 2) { 
            try {
                DB::transaction(function () use ($order, $payhereAmount) {
                    if (!$order->payment_approved) {
                        PaymentTransaction::create([
                            'order_id'       => $order->id,
                            'amount'         => $payhereAmount,
                            'payment_method' => 'gateway',
                            'status'         => 'pending', // VERY IMPORTANT: IT REMAINS PENDING FOR ADMIN APPROVAL
                            'transaction_id' => 'IPG-' . strtoupper(uniqid()),
                        ]);
                        
                        $order->update([
                            'payment_method' => 'card', // Indicate intended payment method
                        ]);
                    }
                });
            } catch (\Exception $e) {
                Log::channel('payments')->error("Payment link webhook DB error for order {$order->id}: " . $e->getMessage());
                return response()->json(['error' => 'Processing failed'], 500);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
