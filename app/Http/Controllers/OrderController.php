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
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $orders = Order::with('customer')
            ->where(function($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                      ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%"));
            })
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get(['id', 'order_number', 'status', 'total', 'customer_id', 'created_at']);

        return response()->json($orders->map(fn($o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'status'       => $o->status,
            'status_label' => str_replace('_', ' ', ucfirst($o->status)),
            'total'        => number_format($o->total, 2),
            'customer'     => $o->customer?->name ?? 'Walk-in',
            'url'          => route('orders.show', $o->order_number),
        ]));
    }

    public function edit(Order $order)
    {
        $order->load('items.serviceType', 'customer');
        $services = \App\Models\ServiceType::where('is_active', true)->get();
        
        $categories = \App\Models\ServiceCategory::with(['children' => function($q) {
            $q->where('is_active', true)->orderBy('order_index');
        }])->whereNull('parent_id')->where('is_active', true)->orderBy('order_index')->get();
        
        $cartItems = $order->items->map(function($item) {
            return [
                'service_type_id' => $item->service_type_id,
                'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
                'base_price' => $item->unit_price,
                'qty' => $item->quantity,
            ];
        })->toArray();
        
        if (empty($cartItems) && $order->deliveryAppointment && $order->deliveryAppointment->cart_json) {
            $parsedCart = json_decode($order->deliveryAppointment->cart_json, true);
            if (is_array($parsedCart)) {
                $cartItems = array_map(function($i) {
                    $service = $i['service'] ?? $i;
                    $sId = $service['id'] ?? $service['service_id'] ?? $service['service_type_id'] ?? null;
                    return [
                        'service_type_id' => $sId,
                        'name' => $service['name'] ?? $service['item_description'] ?? 'Item',
                        'base_price' => $service['base_price'] ?? $service['unit_price'] ?? 0,
                        'qty' => $i['quantity'] ?? $i['qty'] ?? 1,
                    ];
                }, $parsedCart);
            }
        }
        
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);
        $dispatch = (object)['order' => $order, 'order_id' => $order->id];
        
        $turnaroundTypes = \App\Models\TurnaroundType::all();
        $systemExtras = \App\Models\Extra::where('is_active', true)->get();

        return view('workshop.finalize_bill', compact('dispatch', 'services', 'categories', 'cartItems', 'minimumDeliveryCost', 'turnaroundTypes', 'systemExtras'));
    }

    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $q = $request->input('q');
        $status = $request->input('status');
        $isOnline = $request->input('is_online');
        $paymentReady = $request->input('payment_ready');

        $filterBranchId = $request->input('branch_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $tab = $request->input('tab', 'received');

        // Calculate counts for tabs
        $baseQuery = Order::query();
        if ($branchId && !\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            $baseQuery->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('workshop_id', $branchId);
            });
        } elseif (\Illuminate\Support\Facades\Gate::allows('is-super-admin') && $filterBranchId) {
            $baseQuery->where('branch_id', $filterBranchId);
        }

        $counts = [
            'received' => (clone $baseQuery)->whereIn('status', ['received', 'dispatched_to_workshop'])->count(),
            'processing' => (clone $baseQuery)->whereIn('status', ['processing', 'completed_at_workshop'])->count(),
            'arriving' => (clone $baseQuery)->where('status', 'returned_from_workshop')->count(),
            'done' => (clone $baseQuery)->where('status', 'done')->count(),
            'delivered' => (clone $baseQuery)->whereIn('status', ['delivered', 'completed'])->count(),
        ];

        $query = Order::with(['customer', 'items.serviceType', 'cubby.cubby', 'workshop', 'deliveryAppointment', 'paymentTransactions'])
            ->orderBy('created_at', 'desc');

        if ($branchId && !\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('workshop_id', $branchId);
            });
        } elseif (\Illuminate\Support\Facades\Gate::allows('is-super-admin') && $filterBranchId) {
            $query->where('branch_id', $filterBranchId);
        }

        if ($status) {
            $query->where('status', $status);
        } elseif (!$q) { // Only apply tab filter if not searching for a specific order
            if ($tab === 'received') {
                $query->whereIn('status', ['received', 'dispatched_to_workshop']);
            } elseif ($tab === 'processing') {
                $query->whereIn('status', ['processing', 'completed_at_workshop']);
            } elseif ($tab === 'arriving') {
                $query->where('status', 'returned_from_workshop');
            } elseif ($tab === 'done') {
                $query->where('status', 'done');
            } elseif ($tab === 'delivered') {
                $query->whereIn('status', ['delivered', 'completed']);
            }
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($isOnline) {
            $query->where('is_online_booking', true);
        }

        if ($paymentReady) {
            $query->where(function($q) {
                $q->where('payment_approved', true)
                  ->orWhereRaw('paid_amount >= requested_advance AND requested_advance > 0');
            });
        }

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
            });
        }

        $orders = $query->paginate(15)->withQueryString();
        
        $orders->getCollection()->transform(function($order) {
            // Attach the latest workshop dispatch ID so the branch can confirm receipt
            $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)
                ->whereIn('status', ['returned_to_branch', 'received_at_branch'])
                ->orderByDesc('returned_at')
                ->first();
            $order->workshop_dispatch_id = $dispatch ? $dispatch->id : null;
            return $order;
        });
        $cubbies = Cubby::where('branch_id', $this->getActiveBranchId())->orderBy('name')->get();
        $categories = \App\Models\ServiceCategory::with('services')->where('is_active', true)->get();
        $workshops = Branch::workshops()->get();
        $riders = \App\Models\User::where('role', 'rider')->where('is_active', true)->get();
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);
        $allBranches = \Illuminate\Support\Facades\Gate::allows('is-super-admin') ? Branch::all() : collect();

        return view('orders.index', compact('orders', 'cubbies', 'categories', 'workshops', 'riders', 'minimumDeliveryCost', 'allBranches', 'filterBranchId', 'tab', 'counts'));
    }
    public function requestPayment(Order $order, Request $request)
    {
        $amountType = $request->input('amount_type', 'full');
        $amount = 0;

        if ($amountType === 'full') {
            $amount = $order->total;
        } elseif ($amountType === '50') {
            $amount = $order->total / 2;
        } else {
            $amount = $request->input('custom_amount', $order->total);
        }

        $customer = $order->customer;
        if ($customer) {
            $paymentLink = url("/orders/{$order->id}/pay?amount={$amount}");
            
            // Sending manual payment request using generic method or standard approach?
            // Actually they wanted a "bill finalize" message!
            $commService = new \App\Services\CommunicationService();
            $commService->sendFinalizedBill($order);
            
            return response()->json(['success' => true, 'message' => 'Payment request sent.']);
        }

        return response()->json(['success' => false, 'message' => 'Customer has no phone number.'], 400);
    }

    public function updateOrder(Request $request, Order $order, \App\Actions\Order\UpdateOrderAction $action)
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot edit an order that is already delivered or cancelled.']);
        }

        $cart = $request->cart;
        Log::info('Order Update Request Payload: ', $request->all());
        if (! $cart || ! is_array($cart) || count($cart) === 0) {
            return response()->json(['success' => false, 'message' => 'Cart cannot be empty.']);
        }

        try {
            $action->execute($order, $request->all(), auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Order Update Error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update order.']);
        }
    }

    public function showByNumber($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->load(['customer', 'items.serviceType.category', 'items.turnaroundType', 'items.extras.extra', 'statusHistory.changedBy', 'cubbies.cubby', 'createdBy', 'deliveryAppointment', 'correctionRequests.requestedBy', 'correctionRequests.approvedBy']);
        $branchId = $this->getActiveBranchId();
        $cubbies = Cubby::where('branch_id', $branchId)->orderBy('name')->get();

        $pickmeCost = \App\Models\Expense::where('category', 'PickMe')
            ->where(function($q) use ($order) {
                $q->where('description', 'like', '%Order #' . $order->order_number . '%');
                if ($order->delivery_appointment_id) {
                    $q->orWhere('description', 'like', '%pickup delivery #' . $order->delivery_appointment_id . '%')
                      ->orWhere('description', 'like', '%delivery dropoff #' . $order->delivery_appointment_id . '%');
                }
            })->sum('amount');

        $categories = \App\Models\ServiceCategory::with(['services' => function($q) {
            $q->where('is_active', true);
        }])->orderBy('name')->get();

        return view('orders.show', compact('order', 'cubbies', 'pickmeCost', 'categories'));
    }

    public function printBill(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category']);
        $branch = Branch::find($order->branch_id);

        return view('orders.print', compact('order', 'branch'));
    }

    public function printChit(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category']);

        return view('orders.chit', compact('order'));
    }

    public function invoice(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category', 'branch']);

        return view('orders.invoice', compact('order'));
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
            \Log::error('updateStatus failed: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function reverseOrder(\App\Http\Requests\Order\ReverseOrderRequest $request, Order $order, \App\Actions\Order\ReverseOrderAction $action)
    {
        $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin', 'branch_admin', 'staff'])->get();
        $authorized = false;
        foreach ($admins as $admin) {
            if (\Hash::check($request->password, $admin->password)) {
                $authorized = true;
                break;
            }
        }

        if (! $authorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized password. Access denied.',
            ], 403);
        }

        $action->execute($order, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Order reversed successfully.',
        ]);
    }

    public function bulkStatus(\App\Http\Requests\Order\BulkStatusUpdateRequest $request, \App\Actions\Order\BulkUpdateOrderStatusAction $action)
    {
        $action->execute($request->validated(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Bulk status updated successfully.',
        ]);
    }

    public function collectCreditPayment(\App\Http\Requests\Order\CollectCreditPaymentRequest $request, $id, \App\Actions\Order\CollectCreditPaymentAction $action)
    {
        $order = Order::findOrFail($id);

        try {
            $action->execute($order, $request->validated(), auth()->id());
            return redirect()->back()->with('success', 'Credit payment collected successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approvePayment(Request $request, Order $order, PaymentTransaction $transaction, \App\Actions\Order\ApprovePaymentSlipAction $action)
    {
        try {
            $action->execute($order, $transaction);
            return redirect()->back()->with('success', 'Payment slip approved successfully.');
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    public function rejectPayment(\App\Http\Requests\Order\RejectPaymentRequest $request, Order $order, PaymentTransaction $transaction, \App\Actions\Order\RejectPaymentSlipAction $action)
    {
        try {
            $action->execute($order, $transaction, $request->validated());
            return redirect()->back()->with('success', 'Payment slip rejected successfully.');
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    public function dispatchDelivery(\App\Http\Requests\Order\DispatchDeliveryRequest $request, Order $order, \App\Actions\Order\DispatchOrderDeliveryAction $action)
    {
        try {
            $result = $action->execute($order, $request->validated(), auth()->id());
            return response()->json(array_merge(['success' => true], $result));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

