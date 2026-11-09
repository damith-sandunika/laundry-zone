<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cubby;
use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\OrderCubby;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\CommunicationService;
use App\Models\WorkshopDispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function search(Request $request)
    {
        return app(OrderSearchController::class)->search($request);
    }




    public function edit(Order $order)
    {
        return app(OrderViewController::class)->edit($order);
    }

    public function index(Request $request)
    {
        return app(OrderViewController::class)->index($request);
    }
    public function requestPayment(Order $order, Request $request)
    {
        return app(OrderPaymentActionController::class)->requestPayment($order, $request);
    }

    public function collectCreditPayment(\App\Http\Requests\Order\CollectCreditPaymentRequest $request, $id, \App\Actions\Order\CollectCreditPaymentAction $action)
    {
        return app(OrderPaymentActionController::class)->collectCreditPayment($request, $id, $action);
    }

    public function approvePayment(Request $request, Order $order, PaymentTransaction $transaction, \App\Actions\Order\ApprovePaymentSlipAction $action)
    {
        return app(OrderPaymentActionController::class)->approvePayment($request, $order, $transaction, $action);
    }

    public function rejectPayment(\App\Http\Requests\Order\RejectPaymentRequest $request, Order $order, PaymentTransaction $transaction, \App\Actions\Order\RejectPaymentSlipAction $action)
    {
        return app(OrderPaymentActionController::class)->rejectPayment($request, $order, $transaction, $action);
    }

    public function convertToDelivery(Request $request, Order $order, \App\Actions\Order\ProcessDeliveryConversionAction $action)
    {
        return app(OrderPaymentActionController::class)->convertToDelivery($request, $order, $action);
    }

    public function refundOverpayment(Request $request, Order $order, \App\Actions\Order\RequestOverpaymentRefundAction $action)
    {
        return app(OrderPaymentActionController::class)->refundOverpayment($request, $order, $action);
    }

    public function updateOrder(Request $request, Order $order, \App\Actions\Order\UpdateOrderAction $action)
    {
        // Prevent cross-branch and unauthorized edits
        \Illuminate\Support\Facades\Gate::authorize('update', $order);

        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot edit an order that is already delivered or cancelled.']);
        }

        $cart = $request->cart;
        Log::channel('security')->info('Order Update Request', [
            'order_id' => $order->id, 
            'user_id' => auth()->id(), 
            'status' => $order->status
        ]);
        if (!$cart || !is_array($cart) || count($cart) === 0) {
            return response()->json(['success' => false, 'message' => 'Cart cannot be empty.']);
        }

        try {
            $action->execute($order, $request->all(), auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Order Update Error', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to update order.']);
        }
    }

    public function showByNumber($orderNumber)
    {
        return app(OrderViewController::class)->showByNumber($orderNumber);
    }

    public function printBill(Order $order)
    {
        return app(OrderPrintController::class)->printBill($order);
    }

    public function printChit(Order $order)
    {
        return app(OrderPrintController::class)->printChit($order);
    }

    public function printLabel(Order $order)
    {
        return app(OrderPrintController::class)->printLabel($order);
    }

    public function invoice(Order $order)
    {
        return app(OrderPrintController::class)->invoice($order);
    }

    public function updateStatus(\App\Http\Requests\Order\UpdateOrderStatusRequest $request, Order $order, \App\Actions\Order\UpdateOrderStatusAction $action)
    {
        try {
            $action->execute($order, $request->validated(), auth()->id());

            if ($request->expectsJson() || $request->ajax()) {
                $payload = ['success' => true, 'message' => 'Status updated successfully!'];
                if ($request->status === 'delivered') {
                    $payload['print_bill_id'] = $order->order_number;
                }
                return response()->json($payload);
            }

            if ($request->status === 'delivered') {
                return redirect()->back()
                    ->with('success', 'Order delivered successfully!')
                    ->with('print_bill_id', $order->order_number);
            }

            return redirect()->back()->with('success', 'Status updated successfully!');

        } catch (\Exception $e) {
            \Log::error('updateStatus failed', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Update failed. Please try again.'], 500);
            }

            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }



    public function bulkStatus(\App\Http\Requests\Order\BulkStatusUpdateRequest $request, \App\Actions\Order\BulkUpdateOrderStatusAction $action)
    {
        $action->execute($request->validated(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Bulk status updated successfully.',
        ]);
    }

    public function history(Request $request)
    {
        return app(OrderHistoryController::class)->index($request);
    }

    public function destroy(Order $order)
    {
        $user = auth()->user();
        if (!$user || !$user->canDeleteOrders()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action. Order deletion is restricted.'], 403);
        }

        // Cross-branch authorization guard
        if (!$user->isSuperAdmin() && (int)$order->branch_id !== (int)$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action: Cannot delete orders belonging to another branch.'], 403);
        }

        try {
            DB::transaction(function () use ($order, $user) {
                $order->deleted_by = $user->id;
                $order->saveQuietly();
                
                // Delete all payment transactions to reverse cash drawer and remove from revenue reports
                $order->paymentTransactions->each(function($tx) {
                    $tx->delete();
                });

                $order->delete();
                
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'deleted',
                    'changed_by' => $user->id,
                    'note' => "Order deleted by Admin.",
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Order deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Order Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete order.']);
        }
    }
}

