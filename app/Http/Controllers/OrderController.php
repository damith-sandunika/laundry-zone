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
        $q = $request->input('q', '');
        if (strlen($q) < 2)
            return response()->json([]);

        $user = auth()->user();

        $orders = Order::with('customer')
            ->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                      ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$q}%"));
            })
            ->when(
                // Non-super-admin: scope to their branch (collection or workshop)
                !$user->isSuperAdmin() && $user->branch_id,
                fn($q) => $q->where(function ($sub) use ($user) {
                    $sub->where('branch_id', $user->branch_id)    // orders collected at their branch
                        ->orWhere('workshop_id', $user->branch_id); // orders dispatched TO their workshop
                })
            )
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get(['id', 'order_number', 'status', 'total', 'customer_id', 'created_at', 'branch_id', 'workshop_id']);

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
                'note' => $item->note,
            ];
        })->toArray();

        // cart_json fallback removed as per audit: use order items directly.

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

        // Super admins use the unified history page to view and manage all orders across branches
        if ($user->isSuperAdmin()) {
            return redirect()->route('orders.history');
        }

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

        // Single grouped COUNT query — replaces 5 separate round-trips
        $statusCountsRaw = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $counts = [
            'received'   => ($statusCountsRaw['received'] ?? 0) + ($statusCountsRaw['picked_up'] ?? 0) + ($statusCountsRaw['dispatched_to_workshop'] ?? 0),
            'processing' => ($statusCountsRaw['processing'] ?? 0) + ($statusCountsRaw['completed_at_workshop'] ?? 0),
            'arriving'   => $statusCountsRaw['returned_from_workshop'] ?? 0,
            'done'       => $statusCountsRaw['done'] ?? 0,
            'delivered'  => ($statusCountsRaw['delivered'] ?? 0) + ($statusCountsRaw['completed'] ?? 0),
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
                   ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$q}%"));
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

    public function convertToDelivery(Request $request, Order $order, \App\Actions\Order\ProcessDeliveryConversionAction $action)
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
            \Illuminate\Support\Facades\Log::error('Convert to Delivery Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function convertToPickup(Request $request, Order $order, \App\Actions\Order\ProcessPickupConversionAction $action)
    {
        if (!$order->is_delivery_order && !$order->delivery_appointment_id) {
            return response()->json(['success' => false, 'message' => 'This order is not a delivery order.']);
        }

        try {
            $result = $action->execute($order, $request->all(), auth()->user());
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'overpayment' => $result['overpayment'] ?? 0
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Convert to Pickup Failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

        try {
            $action = app(\App\Actions\Order\RequestOverpaymentRefundAction::class);
            $result = $action->execute($order, $request->action, auth()->id());

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['message']]);
            }

            return response()->json(['success' => true, 'message' => $result['message']]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Refund Overpayment Failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'An error occurred while processing the request.'], 500);
        }
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $isSuperAdmin = $user->isSuperAdmin();
        $isWorkshop = $user->isWorkshopAdmin() || ($user->branch && $user->branch->type === 'workshop');

        $query = Order::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->withTrashed()
            ->with(['customer', 'branch', 'workshop', 'latestWorkshopDispatch.workshopBranch', 'deletedBy'])
            ->orderByDesc('id');
            
        $statsQuery = Order::withoutGlobalScope(\App\Models\Scopes\BranchScope::class);

        if (!$isSuperAdmin && $branchId) {
            if ($isWorkshop) {
                // Workshop Admin / Staff: Orders assigned or dispatched to their workshop, or created directly at their workshop
                $workshopCondition = function ($q) use ($branchId) {
                    $q->where('orders.workshop_id', $branchId)
                      ->orWhere('orders.branch_id', $branchId)
                      ->orWhereExists(function ($sub) use ($branchId) {
                          $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                              ->from('workshop_dispatches')
                              ->whereColumn('workshop_dispatches.order_id', 'orders.id')
                              ->where('workshop_dispatches.workshop_branch_id', $branchId);
                      });
                };
                $query->where($workshopCondition);
                $statsQuery->where($workshopCondition);
            } else {
                // Branch Admin / Staff: Only their own branch's orders (including any they dispatched to workshop)
                $query->where('orders.branch_id', $branchId);
                $statsQuery->where('orders.branch_id', $branchId);
            }
        }

        // Branch filter for Super Admin
        if ($isSuperAdmin && $request->filled('branch_id')) {
            $branchIdFilter = $request->branch_id;
            $query->where('orders.branch_id', $branchIdFilter);
            $statsQuery->where('orders.branch_id', $branchIdFilter);
        }

        // Workshop filter for Super Admin
        if ($isSuperAdmin && $request->filled('workshop_id')) {
            $workshopIdFilter = $request->workshop_id;
            $workshopSuperCondition = function ($q) use ($workshopIdFilter) {
                $q->where('orders.workshop_id', $workshopIdFilter)
                  ->orWhere('orders.branch_id', $workshopIdFilter)
                  ->orWhereExists(function ($sub) use ($workshopIdFilter) {
                      $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('workshop_dispatches')
                          ->whereColumn('workshop_dispatches.order_id', 'orders.id')
                          ->where('workshop_dispatches.workshop_branch_id', $workshopIdFilter);
                  });
            };
            $query->where($workshopSuperCondition);
            $statsQuery->where($workshopSuperCondition);
        }

        if ($request->filled('status')) {
            $statusFilter = $request->status;
            $query->where('status', $statusFilter);
            $statsQuery->where('status', $statusFilter);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Default to current month on initial page load if no date parameters were provided
        if (!$request->has('start_date') && !$request->has('end_date')) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->toDateString();
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
            $statsQuery->whereDate('created_at', '>=', $startDate)
                       ->whereDate('created_at', '<=', $endDate);
        }

        $ordersBase = clone $statsQuery;
        
        $totalOrderValue = (float) ((clone $ordersBase)->sum('total') ?? 0);
        $walkIn = (float) ((clone $ordersBase)->where('is_delivery_order', false)->sum('total') ?? 0);
        $delivery = (float) ((clone $ordersBase)->where('is_delivery_order', true)->sum('total') ?? 0);
        
        // Zero-RAM SQL aggregations for pending amounts
        $pendingDue = (float) ((clone $ordersBase)->selectRaw('SUM(GREATEST(0, total - COALESCE(paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);
        
        $regularPending = (float) ((clone $ordersBase)->where(function($q) {
            $q->where('payment_method', '!=', 'credit')->orWhereNull('payment_method');
        })->selectRaw('SUM(GREATEST(0, total - COALESCE(paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);

        $creditOrders = (float) ((clone $ordersBase)->where('payment_method', 'credit')->selectRaw('SUM(GREATEST(0, total - COALESCE(paid_amount, 0))) as aggregate')->value('aggregate') ?? 0);

        $collectedPayments = (float) ((clone $ordersBase)->sum('paid_amount') ?? 0);

        // SQL aggregations for payment totals using subquery
        $orderIdsSubquery = (clone $ordersBase)->select('orders.id');

        $directCash = (float) ((clone $ordersBase)->where('payment_method', 'cash')->whereDoesntHave('paymentTransactions')->sum('paid_amount') ?? 0);
        $directCard = (float) ((clone $ordersBase)->where('payment_method', 'card')->whereDoesntHave('paymentTransactions')->sum('paid_amount') ?? 0);
        $directBank = (float) ((clone $ordersBase)->where('payment_method', 'bank')->whereDoesntHave('paymentTransactions')->sum('paid_amount') ?? 0);

        $txSums = PaymentTransaction::whereIn('order_id', $orderIdsSubquery)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) as cash_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'card' THEN amount ELSE 0 END), 0) as card_sum,
                COALESCE(SUM(CASE WHEN payment_method = 'bank' THEN amount ELSE 0 END), 0) as bank_sum
            ")->first();

        $cashTotal = (float) (($txSums->cash_sum ?? 0) + $directCash);
        $cardTotal = (float) (($txSums->card_sum ?? 0) + $directCard);
        $bankTotal = (float) (($txSums->bank_sum ?? 0) + $directBank);

        $orderQty = (int) (clone $ordersBase)->count();
        $totalCustomerCount = (int) (clone $ordersBase)->distinct()->count('customer_id');
        $itemQty = (int) (\App\Models\OrderItem::whereIn('order_id', $orderIdsSubquery)->sum('quantity') ?? 0);

        $stats = [
            'total_order_value' => $totalOrderValue,
            'walk_in' => $walkIn,
            'delivery' => $delivery,
            'pending_due' => $pendingDue,
            'regular_pending' => $regularPending,
            'credit_orders' => $creditOrders,
            'collected_payments' => $collectedPayments,
            'cash' => $cashTotal,
            'card' => $cardTotal,
            'bank' => $bankTotal,
            'order_qty' => $orderQty,
            'total_customer_count' => $totalCustomerCount,
            'item_qty' => $itemQty,
        ];

        $completedOrders = $query->paginate(15);

        // Cancelled / Rejected Online Bookings Log
        $cancelledBookings = \App\Models\DeliveryAppointment::with(['customer', 'cancelledBy', 'branch'])
            ->when(!$user->isSuperAdmin(), fn($q) => $q->where('branch_id', $branchId))
            ->when($user->isSuperAdmin() && $request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($user->isSuperAdmin() && $request->filled('workshop_id'), fn($q) => $q->where('branch_id', $request->workshop_id))
            ->where('status', 'cancelled')
            ->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'cancelled_page');
        
        $allBranches = collect();
        $allWorkshops = collect();
        if ($user->isSuperAdmin()) {
            $allBranches = \App\Models\Branch::where('type', 'branch')->orWhereNull('type')->orderBy('name')->get();
            $allWorkshops = \App\Models\Branch::where('type', 'workshop')->orderBy('name')->get();
        }

        return view('orders.history', compact('completedOrders', 'cancelledBookings', 'stats', 'allBranches', 'allWorkshops', 'startDate', 'endDate', 'isWorkshop'));
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

