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
        if (strlen($q) < 2)
            return response()->json([]);

        $orders = Order::with('customer')
            ->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%"));
            })
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get(['id', 'order_number', 'status', 'total', 'customer_id', 'created_at']);

        return response()->json($orders->map(fn($o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'status' => $o->status,
            'status_label' => str_replace('_', ' ', ucfirst($o->status)),
            'total' => number_format($o->total, 2),
            'customer' => $o->customer?->name ?? 'Walk-in',
            'url' => route('orders.show', $o->order_number),
        ]));
    }

    public function edit(Order $order)
    {
        $order->load('items.serviceType', 'items.extras.extra', 'customer');
        $branchId = $this->getActiveBranchId();
        $isCentralized = \App\Models\Setting::get('centralized_management', '1') == '1';

        $services = \App\Models\ServiceType::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->get();

        $categories = \App\Models\ServiceCategory::with([
            'children' => function ($q) use ($branchId, $isCentralized) {
                $q->where('is_active', true);
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)
        ->where(function($q) use ($branchId, $isCentralized) {
            if (!$isCentralized && $branchId) {
                $q->where('branch_id', $branchId);
            }
        })->orderBy('order_index')->get();

        $cartItems = $order->items->map(function ($item) {
            return [
                'service_type_id' => $item->service_type_id,
                'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
                'base_price' => $item->unit_price,
                'qty' => $item->quantity,
                'turnaround_type_id' => (float)$item->custom_turnaround_percentage > 0 ? 'custom' : $item->turnaround_type_id,
                'custom_turnaround_percentage' => $item->custom_turnaround_percentage,
                'selected_extras' => $item->extras->map(function ($extra) {
                    return [
                        'id' => $extra->extra_id,
                        'name' => $extra->extra ? $extra->extra->name : 'Extra',
                        'price' => $extra->unit_price,
                        'qty' => $extra->quantity,
                        'total' => $extra->total_price,
                    ];
                })->toArray(),
            ];
        })->toArray();

        if (empty($cartItems) && $order->deliveryAppointment && $order->deliveryAppointment->cart_json) {
            $parsedCart = json_decode($order->deliveryAppointment->cart_json, true);
            if (is_array($parsedCart)) {
                $cartItems = array_map(function ($i) {
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
        $dispatch = (object) ['order' => $order, 'order_id' => $order->id];

        $turnaroundTypes = \App\Models\TurnaroundType::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->get();
        $systemExtras = \App\Models\Extra::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->get();

        return view('orders.edit', compact('order', 'dispatch', 'services', 'categories', 'cartItems', 'minimumDeliveryCost', 'turnaroundTypes', 'systemExtras'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Workshop admins don't use the branch orders page — redirect to their operations page
        if ($user->role === 'workshop_admin') {
            return redirect()->route('workshop.operations');
        }

        $branchId = $user->branch_id;
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
            $baseQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhere('workshop_id', $branchId);
            });
        } elseif (\Illuminate\Support\Facades\Gate::allows('is-super-admin') && $filterBranchId) {
            $baseQuery->where('branch_id', $filterBranchId);
        }

        $counts = [
            'received' => (clone $baseQuery)->whereIn('status', ['received', 'picked_up', 'dispatched_to_workshop'])->count(),
            'processing' => (clone $baseQuery)->whereIn('status', ['processing', 'completed_at_workshop'])->count(),
            'arriving' => (clone $baseQuery)->where('status', 'returned_from_workshop')->count(),
            'done' => (clone $baseQuery)->where('status', 'done')->count(),
            'delivered' => (clone $baseQuery)->whereIn('status', ['delivered', 'completed'])->count(),
        ];

        $query = Order::with(['customer', 'items.serviceType', 'cubby.cubby', 'workshop', 'deliveryAppointment', 'paymentTransactions'])
            ->orderBy('created_at', 'desc');

        if ($branchId && !\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            $query->where(function ($q) use ($branchId) {
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
                $query->whereIn('status', ['received', 'picked_up', 'dispatched_to_workshop']);
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
            $query->where(function ($q) {
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

        $orderIds = $orders->pluck('id')->toArray();
        $dispatches = \App\Models\WorkshopDispatch::whereIn('order_id', $orderIds)
            ->whereIn('status', ['returned_to_branch', 'received_at_branch'])
            ->orderByDesc('returned_at')
            ->get()
            ->groupBy('order_id');

        $orders->getCollection()->transform(function ($order) use ($dispatches) {
            // Attach the latest workshop dispatch ID so the branch can confirm receipt
            $dispatch = $dispatches->has($order->id) ? $dispatches->get($order->id)->first() : null;
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
            // Secure payment link with 48-hour expiry and signature to prevent tampering
            $paymentLink = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'customer.orders.pay',
                now()->addHours(48),
                ['order' => $order->order_number, 'amount' => $amount]
            );

            $commService = new \App\Services\CommunicationService();
            $commService->sendFinalizedBill($order, $amount, $paymentLink);

            return response()->json(['success' => true, 'message' => 'Payment request sent.']);
        }

        return response()->json(['success' => false, 'message' => 'Customer has no phone number.'], 400);
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
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->load(['customer', 'items.serviceType.category', 'items.turnaroundType', 'items.extras.extra', 'statusHistory.changedBy', 'cubbies.cubby', 'createdBy', 'deliveryAppointment', 'correctionRequests.requestedBy', 'correctionRequests.approvedBy']);
        $branchId = $this->getActiveBranchId();
        $cubbies = Cubby::where('branch_id', $branchId)->orderBy('name')->get();

        $pickmeCost = \App\Models\Expense::where('category', 'PickMe')
            ->where(function ($q) use ($order) {
                $q->where('description', 'like', '%Order #' . $order->order_number . '%');
                if ($order->delivery_appointment_id) {
                    $q->orWhere('description', 'like', '%pickup delivery #' . $order->delivery_appointment_id . '%')
                        ->orWhere('description', 'like', '%delivery dropoff #' . $order->delivery_appointment_id . '%');
                }
            })->sum('amount');

        $categories = \App\Models\ServiceCategory::with([
            'services' => function ($q) {
                $q->where('is_active', true);
            }
        ])->orderBy('name')->get();

        // Build a map of service_type_id => [name, category, subcategory] for use in correction request display
        $allServiceTypes = \App\Models\ServiceType::with(['category.parent'])->get();
        $serviceTypesMap = $allServiceTypes->mapWithKeys(function ($st) {
            $catName = null;
            $subCatName = null;
            if ($st->category) {
                if ($st->category->parent) {
                    // This category is a sub-category
                    $catName = $st->category->parent->name;
                    $subCatName = $st->category->name;
                } else {
                    $catName = $st->category->name;
                }
            }
            return [$st->id => [
                'name' => $st->name,
                'category' => $catName,
                'subcategory' => $subCatName,
            ]];
        })->toArray();

        return view('orders.show', compact('order', 'cubbies', 'pickmeCost', 'categories', 'serviceTypesMap'));
    }

    public function printBill(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra']);
        $branch = Branch::find($order->branch_id);
        $mainBranch = Branch::orderBy('id')->first();

        return view('orders.print', compact('order', 'branch', 'mainBranch'));
    }

    public function printChit(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra']);

        return view('orders.chit', compact('order'));
    }

    public function printLabel(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra', 'deliveryAppointment']);
        $branch = Branch::find($order->branch_id);
        $mainBranch = Branch::orderBy('id')->first();

        return view('orders.label', compact('order', 'branch', 'mainBranch'));
    }

    public function invoice(Order $order)
    {
        $order->load(['customer', 'items.serviceType.category.parent', 'items.turnaroundType', 'items.extras.extra', 'branch']);
        $mainBranch = Branch::orderBy('id')->first();

        return view('orders.invoice', compact('order', 'mainBranch'));
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

    public function reverseOrder(\App\Http\Requests\Order\ReverseOrderRequest $request, Order $order, \App\Actions\Order\ReverseOrderAction $action)
    {
        $authorized = \Illuminate\Support\Facades\Hash::check($request->password, auth()->user()->password);

        if (!$authorized) {
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
            \Illuminate\Support\Facades\Log::error('Collect Credit Payment Failed', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred while collecting payment. Please try again.');
        }
    }

    public function approvePayment(Request $request, Order $order, PaymentTransaction $transaction, \App\Actions\Order\ApprovePaymentSlipAction $action)
    {
        try {
            $action->execute($order, $transaction);
            return redirect()->back()->with('success', 'Payment slip approved successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Approve Payment Failed', [
                'order_id' => $order->id ?? null,
                'user_id'  => auth()->id(),
                'error'    => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while approving payment.'], 500);
        }
    }

    public function rejectPayment(\App\Http\Requests\Order\RejectPaymentRequest $request, Order $order, PaymentTransaction $transaction, \App\Actions\Order\RejectPaymentSlipAction $action)
    {
        try {
            $action->execute($order, $transaction, $request->validated());
            return redirect()->back()->with('success', 'Payment slip rejected successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reject Payment Failed', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while rejecting payment.'], 500);
        }
    }

    public function convertToDelivery(Request $request, Order $order)
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
            $fee = (float)$request->input('delivery_fee', 0);
            $user = auth()->user();
            $isAdmin = in_array($user->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']);

            if (!$isAdmin) {
                if ($order->correctionRequests()->where('status', 'pending')->exists()) {
                    return response()->json(['success' => false, 'message' => 'An active correction request already exists for this order.']);
                }

                $correction = \App\Models\OrderCorrectionRequest::create([
                    'order_id' => $order->id,
                    'requested_by' => auth()->id(),
                    'status' => 'pending',
                    'type' => 'convert_to_delivery',
                    'reason' => 'Requested to convert order to Delivery',
                    'old_data' => [
                        'is_delivery_order' => false,
                        'delivery_fee' => $order->delivery_fee
                    ],
                    'new_data' => [
                        'delivery_address' => $request->delivery_address,
                        'delivery_fee' => $fee,
                        'contact_phone' => $request->contact_phone,
                    ],
                ]);

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'changed_by' => auth()->id(),
                    'note' => "Correction requested (convert_to_delivery): Convert order to Delivery",
                ]);

                $admins = \App\Models\User::whereIn('role', ['admin', 'branch_admin', 'workshop_admin'])
                    ->where(function($q) use ($order) {
                        $q->where('branch_id', $order->branch_id)->orWhereNull('branch_id');
                    })->get();
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SystemAlertNotification("Correction Requested for Order #{$order->order_number}", route('orders.show', $order->order_number), 'warning'));

                return response()->json(['success' => true, 'message' => 'Conversion request submitted for admin approval.']);
            }

            \Illuminate\Support\Facades\DB::beginTransaction();
            
            $appointmentStatus = match($order->status) {
                'done' => 'ready',
                'completed_at_workshop', 'processing' => 'processing',
                'returned_from_workshop' => 'at_branch',
                default => 'order_created',
            };

            $appointment = \App\Models\DeliveryAppointment::updateOrCreate(
                ['order_id' => $order->id, 'order_number' => $order->order_number],
                [
                    'customer_id' => $order->customer_id,
                    'branch_id' => $order->branch_id,
                    'delivery_address' => $request->delivery_address,
                    'contact_phone' => $request->contact_phone,
                    'delivery_preference' => 'deliver_to_address',
                    'status' => $appointmentStatus,
                    'delivery_cost' => $fee,
                ]
            );

            $newTotal = $order->total + $fee;
            $paymentStatus = $order->paid_amount >= $newTotal ? 'paid' : ($order->paid_amount > 0 ? 'partial' : 'unpaid');

            $order->is_delivery_order = true;
            $order->delivery_fee = $order->delivery_fee + $fee;
            $order->total = $newTotal;
            $order->payment_status = $paymentStatus;
            $order->delivery_address = $request->delivery_address;
            $order->delivery_appointment_id = $appointment->id;
            $order->save();

            $userName = auth()->user()->name ?? 'System';
            $nowDate = now()->format('d M Y, h:i A');
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => 'Converted to Delivery Order. Delivery Fee: Rs. ' . number_format($fee, 2) . "\n[Requested by: {$userName} at {$nowDate} | Approved by: {$userName} at {$nowDate}]",
                'changed_by' => auth()->id(),
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['success' => true, 'message' => 'Converted to delivery successfully.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Convert to Delivery Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while converting the order.'], 500);
        }
    }

    public function convertToPickup(Request $request, Order $order)
    {
        if (!$order->is_delivery_order && !$order->delivery_appointment_id) {
            return response()->json(['success' => false, 'message' => 'This order is not a delivery order.']);
        }

        try {
            $deductAmount = (float)$request->input('deduct_amount', 0);
            $user = auth()->user();
            $isAdmin = in_array($user->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']);

            if (!$isAdmin) {
                if ($order->correctionRequests()->where('status', 'pending')->exists()) {
                    return response()->json(['success' => false, 'message' => 'An active correction request already exists for this order.']);
                }

                $correction = \App\Models\OrderCorrectionRequest::create([
                    'order_id' => $order->id,
                    'requested_by' => auth()->id(),
                    'status' => 'pending',
                    'type' => 'convert_to_pickup',
                    'reason' => 'Requested to convert order to Customer Pickup',
                    'old_data' => [
                        'is_delivery_order' => true,
                        'delivery_fee' => $order->delivery_fee,
                        'total' => $order->total
                    ],
                    'new_data' => [
                        'deduct_amount' => $deductAmount
                    ],
                ]);

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'changed_by' => auth()->id(),
                    'note' => "Correction requested (convert_to_pickup): Convert order to Customer Pickup",
                ]);

                $admins = \App\Models\User::whereIn('role', ['admin', 'branch_admin', 'workshop_admin'])
                    ->where(function($q) use ($order) {
                        $q->where('branch_id', $order->branch_id)->orWhereNull('branch_id');
                    })->get();
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SystemAlertNotification("Correction Requested for Order #{$order->order_number}", route('orders.show', $order->order_number), 'warning'));

                return response()->json(['success' => true, 'message' => 'Conversion request submitted for admin approval.']);
            }

            \Illuminate\Support\Facades\DB::beginTransaction();

            // Update delivery appointment
            if ($order->deliveryAppointment) {
                $order->deliveryAppointment->update([
                    'delivery_preference'   => 'pickup_from_branch',
                    'delivery_scheduled_at' => null,
                    'delivery_cost'         => 0,
                ]);
            }
            
            // Deduct from delivery fee and total
            if ($deductAmount > 0) {
                // Ensure we don't deduct more than the current delivery fee
                $deductAmount = min($deductAmount, $order->delivery_fee);
                $order->delivery_fee -= $deductAmount;
                $order->total -= $deductAmount;
            }

            $order->payment_status = $order->paid_amount >= $order->total ? 'paid' : ($order->paid_amount > 0 ? 'partial' : 'unpaid');

            // Convert order
            $order->is_delivery_order = false;
            $order->save();

            $userName = auth()->user()->name ?? 'System';
            $nowDate = now()->format('d M Y, h:i A');
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $order->status,
                'note'       => 'Converted to Customer Pickup. Customer will collect from branch. Delivery fee deducted: Rs. ' . number_format($deductAmount, 2) . "\n[Requested by: {$userName} at {$nowDate} | Approved by: {$userName} at {$nowDate}]",
                'changed_by' => auth()->id(),
            ]);

            \Illuminate\Support\Facades\DB::commit();

            // Check for overpayment after conversion
            $order->refresh();
            $overpayment = round($order->paid_amount - $order->total, 2);

            return response()->json([
                'success'     => true,
                'message'     => 'Order converted to customer pickup.',
                'overpayment' => $overpayment > 0 ? $overpayment : 0,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error('Convert to Pickup Failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function dispatchDelivery(\App\Http\Requests\Order\DispatchDeliveryRequest $request, Order $order, \App\Actions\Order\DispatchOrderDeliveryAction $action)
    {
        try {
            $result = $action->execute($order, $request->validated(), auth()->id());
            return response()->json(array_merge(['success' => true], $result));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Dispatch Delivery Failed', [
                'order_id' => $order->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred while dispatching delivery.'], 500);
        }
    }

    /**
     * Refund an overpayment on an order, or mark it as kept credit.
     * Creates a negative PaymentTransaction so cash drawer is decremented automatically.
     */
    public function refundOverpayment(Request $request, Order $order)
    {
        $request->validate([
            'action' => 'required|in:refund,keep',
        ]);

        $overpayment = round($order->paid_amount - $order->total, 2);

        if ($overpayment <= 0) {
            return response()->json(['success' => false, 'message' => 'No overpayment exists on this order.']);
        }

        try {
            $action = $request->action;
            $actionText = $action === 'refund' ? 'Refund' : 'Keep as Store Credit';
            
            // Check if there is already a pending overpayment request
            if ($order->correctionRequests()->where('status', 'pending')->where('type', 'overpayment_handling')->exists()) {
                return response()->json(['success' => false, 'message' => 'An overpayment request is already pending.']);
            }

            $user = auth()->user();
            $isAdmin = in_array($user->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']);

            \Illuminate\Support\Facades\DB::beginTransaction();

            $correction = \App\Models\OrderCorrectionRequest::create([
                'order_id' => $order->id,
                'requested_by' => $user->id,
                'status' => 'pending',
                'type' => 'overpayment_handling',
                'reason' => 'Overpayment refund/store credit requested',
                'old_data' => [],
                'new_data' => [
                    'action' => $action,
                    'amount' => $overpayment
                ],
            ]);

            \Illuminate\Support\Facades\DB::commit();

            if ($isAdmin) {
                // Auto-approve if requested by an admin
                app(\App\Http\Controllers\OrderCorrectionController::class)->approve($correction);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Overpayment handled successfully.'
                ]);
            }

            \App\Models\OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $order->status,
                'note'       => "Overpayment action requested: {$actionText} for Rs. " . number_format($overpayment, 2),
                'changed_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request submitted for admin approval.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Refund Overpayment Failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred while processing the request.'], 500);
        }
    }
}


