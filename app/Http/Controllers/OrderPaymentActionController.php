<?php

namespace App\Http\Controllers;

use App\Actions\Order\ApprovePaymentSlipAction;
use App\Actions\Order\CollectCreditPaymentAction;
use App\Actions\Order\ProcessDeliveryConversionAction;
use App\Actions\Order\RejectPaymentSlipAction;
use App\Actions\Order\RequestOverpaymentRefundAction;
use App\Http\Requests\Order\CollectCreditPaymentRequest;
use App\Http\Requests\Order\RejectPaymentRequest;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderPaymentActionController extends Controller
{
    public function requestPayment(Order $order, Request $request)
    {
        $amountType = $request->input('amount_type', 'full');
        $amount = match ($amountType) {
            'full' => $order->total,
            '50' => $order->total / 2,
            default => $request->input('custom_amount', $order->total),
        };

        $customer = $order->customer;
        if ($customer) {
            $paymentLink = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'customer.orders.pay',
                now()->addHours(48),
                ['order' => $order->order_number, 'amount' => $amount]
            );

            try {
                app(\App\Services\CommunicationService::class)->sendPaymentLinkNotification($customer, $order, $paymentLink, $amount);
                return redirect()->back()->with('success', 'Payment link sent to customer successfully.');
            } catch (\Exception $e) {
                Log::error('Payment link SMS error', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->with('error', 'Payment link generated but failed to send SMS. Link: ' . $paymentLink);
            }
        }

        return redirect()->back()->with('error', 'Customer not found.');
    }

    public function collectCreditPayment(CollectCreditPaymentRequest $request, $id, CollectCreditPaymentAction $action)
    {
        $order = Order::findOrFail($id);

        try {
            $action->execute($order, $request->validated(), auth()->id());
            return redirect()->back()->with('success', 'Credit payment collected successfully!');
        } catch (\Exception $e) {
            Log::error('Collect Credit Payment Failed', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred while collecting payment. Please try again.');
        }
    }

    public function approvePayment(Request $request, Order $order, PaymentTransaction $transaction, ApprovePaymentSlipAction $action)
    {
        try {
            $action->execute($order, $transaction);
            return redirect()->back()->with('success', 'Payment slip approved successfully.');
        } catch (\Exception $e) {
            Log::error('Approve Payment Failed', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while approving payment.'], 500);
        }
    }

    public function rejectPayment(RejectPaymentRequest $request, Order $order, PaymentTransaction $transaction, RejectPaymentSlipAction $action)
    {
        try {
            $action->execute($order, $transaction, $request->validated());
            return redirect()->back()->with('success', 'Payment slip rejected successfully.');
        } catch (\Exception $e) {
            Log::error('Reject Payment Failed', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while rejecting payment.'], 500);
        }
    }

    public function convertToDelivery(Request $request, Order $order, ProcessDeliveryConversionAction $action)
    {
        $request->validate([
            'delivery_address' => 'required|string',
            'delivery_fee' => 'nullable|numeric|min:0',
            'contact_phone' => 'nullable|string'
        ]);

        if ($order->is_delivery_order) {
            return response()->json(['success' => false, 'message' => 'Order is already a delivery order.']);
        }

        try {
            $result = $action->execute($order, $request->all(), auth()->user());
            return response()->json(['success' => true, 'message' => $result['message']]);
        } catch (\Exception $e) {
            Log::error('Convert to Delivery Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function refundOverpayment(Request $request, Order $order, RequestOverpaymentRefundAction $action)
    {
        $request->validate([
            'action' => 'required|in:refund,keep',
        ]);

        try {
            $result = $action->execute($order, $request->action, auth()->id());
            return response()->json(['success' => $result['success'], 'message' => $result['message']]);
        } catch (\Exception $e) {
            Log::error('Refund Overpayment Failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred while processing the request.'], 500);
        }
    }
}

