<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkshopController extends Controller
{
    // ─── Branch Admin: Dispatch order to workshop ───
    public function dispatch(Request $request, Order $order)
    {
        $request->validate([
            'dispatch_notes' => 'nullable|string|max:1000',
            'workshop_branch_id' => 'nullable|exists:branches,id',
        ]);

        // Check if already dispatched
        $existing = WorkshopDispatch::where('order_id', $order->id)
            ->whereNotIn('status', ['received_at_branch'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Order is already dispatched to workshop.');
        }

        $dispatch = WorkshopDispatch::create([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'workshop_branch_id' => $request->workshop_branch_id,
            'dispatched_by' => auth()->id(),
            'status' => 'dispatched_to_workshop',
            'dispatched_at' => now(),
            'dispatch_notes' => $request->dispatch_notes,
        ]);

        $order->update([
            'status' => 'dispatched_to_workshop',
            'dispatched_to_workshop_at' => now(),
        ]);

        $workshopAdmins = \App\Models\User::where('role', 'workshop_admin')->where('branch_id', $request->workshop_branch_id)->get();
        \Illuminate\Support\Facades\Notification::send($workshopAdmins, new \App\Notifications\SystemAlertNotification('New Order Dispatched from Branch: #' . $order->order_number, route('workshop.dashboard'), 'branch_dispatch'));

        return redirect()->back()->with('success', 'Order dispatched to workshop successfully.');
    }

    public function bulkDispatch(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'workshop_branch_id' => 'required|exists:branches,id',
            'dispatch_notes' => 'nullable|string',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->whereNotIn('status', ['dispatched_to_workshop'])
            ->get();

        foreach ($orders as $order) {
            WorkshopDispatch::create([
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'workshop_branch_id' => $request->workshop_branch_id,
                'dispatched_by' => auth()->id(),
                'status' => 'dispatched_to_workshop',
                'dispatched_at' => now(),
                'dispatch_notes' => $request->dispatch_notes,
            ]);

            $order->update([
                'status' => 'dispatched_to_workshop',
                'dispatched_to_workshop_at' => now(),
                'workshop_id' => $request->workshop_branch_id,
            ]);
        }

        $workshopAdmins = \App\Models\User::where('role', 'workshop_admin')->where('branch_id', $request->workshop_branch_id)->get();
        \Illuminate\Support\Facades\Notification::send($workshopAdmins, new \App\Notifications\SystemAlertNotification(count($orders) . ' Orders Dispatched from Branch', route('workshop.dashboard'), 'branch_dispatch'));

        return redirect()->back()->with('success', count($orders).' orders dispatched to workshop.');
    }

    // ─── Workshop Admin: Dashboard ───
    public function dashboard()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Active register session details
        $activeSession = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $currentBalance = 0;
        $cashBalance = 0;
        $cardPayments = 0;
        $cardPaymentsAmount = 0;
        $totalSale = 0;

        $bankTransfers = 0;

        if ($activeSession) {
            $cashCollected = \App\Models\PaymentTransaction::whereHas('order', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                })
                ->where(function($q) use ($activeSession) {
                    $q->where('created_at', '>=', $activeSession->opened_at)
                      ->orWhere('collected_from_rider_at', '>=', $activeSession->opened_at);
                })
                ->where('payment_method', 'cash')
                ->sum('amount');

            $cardPaymentsAmount = \App\Models\PaymentTransaction::whereHas('order', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                })
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'card')
                ->sum('amount');
                
            $bankTransfers = \App\Models\PaymentTransaction::whereHas('order', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                })
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'bank')
                ->sum('amount');

            // Fallback for orders without payment transactions
            $directCashOrders = \App\Models\Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
            $cashCollected += $directCashOrders;

            $directBankOrders = \App\Models\Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'bank')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
            $bankTransfers += $directBankOrders;

            // Expenses logged by workshop admins
            $expensesLogged = \App\Models\Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            $cashBalance = $activeSession->opening_cash + $cashCollected - $expensesLogged;
            $currentBalance = $cashBalance;
            $totalSale = $cashCollected + $cardPaymentsAmount + $bankTransfers;
        }

        // Workshop Stats
        $receivedCount = WorkshopDispatch::where('workshop_branch_id', $branchId)->where('status', 'received_at_workshop')->count();
        $processingCount = WorkshopDispatch::where('workshop_branch_id', $branchId)->where('status', 'processing')->count();
        $doneCount = WorkshopDispatch::where('workshop_branch_id', $branchId)->where('status', 'completed_at_workshop')->count();
        
        $stats = [
            'active_orders' => $receivedCount,
            'pending_deliveries' => $processingCount,
            'completed_today' => $doneCount,
            'total_customers' => 0, // Not highly relevant for workshop
            'current_balance' => $currentBalance,
            'cash_balance' => $cashBalance,
            'cash_collected' => $cashCollected ?? 0,
            'card_payments' => $cardPayments,
            'card_payments_amount' => $cardPaymentsAmount,
            'bank_transfers' => $bankTransfers,
            'total_sale' => $totalSale,
            'expenses_logged' => $expensesLogged ?? 0,
        ];

        // Recent Dispatches (equivalent to active orders)
        $recentOrders = WorkshopDispatch::with(['order.customer', 'branch'])
            ->where('workshop_branch_id', $branchId)
            ->whereIn('status', ['dispatched_to_workshop', 'received_at_workshop', 'processing'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Low stock items
        $lowStockItems = \App\Models\InventoryItem::whereColumn('current_stock', '<=', 'min_stock')
            ->where('branch_id', $branchId)
            ->get();

        // All inventory items for popup
        $allInventory = \App\Models\InventoryItem::where('branch_id', $branchId)->get();

        // Fetch Laundry/Branch Name
        $branchName = \App\Models\Branch::find($branchId)->name ?? 'Workshop';

        // ── Chart Data ──
        $todayLabels = [];
        $todayPending = [];
        $todayDelivered = [];
        for ($h = 0; $h < 8; $h++) {
            $start = now()->startOfDay()->addHours($h * 3);
            $end = $start->copy()->addHours(3);
            $todayLabels[] = $start->format('H:i');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $todayPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $todayDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        $weeklyLabels = [];
        $weeklyPending = [];
        $weeklyDelivered = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $weeklyLabels[] = $day->format('D');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereDate('created_at', $day->toDateString());
            $weeklyPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $weeklyDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        $monthlyLabels = [];
        $monthlyPending = [];
        $monthlyDelivered = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end = $start->copy()->endOfWeek();
            $monthlyLabels[] = 'Wk '.$start->format('d/m');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $monthlyPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $monthlyDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        $chartData = [
            'today' => ['labels' => $todayLabels,   'pending' => $todayPending,   'delivered' => $todayDelivered],
            'weekly' => ['labels' => $weeklyLabels,  'pending' => $weeklyPending,  'delivered' => $weeklyDelivered],
            'monthly' => ['labels' => $monthlyLabels, 'pending' => $monthlyPending, 'delivered' => $monthlyDelivered],
        ];

        return view('workshop.dashboard', compact('stats', 'recentOrders', 'lowStockItems', 'allInventory', 'activeSession', 'branchName', 'chartData'));
    }

    // ─── Workshop Admin: Operations ───
    public function operations()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $dispatches = WorkshopDispatch::with(['order.customer', 'order.deliveryAppointment', 'order.paymentTransactions', 'branch', 'dispatchedBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->whereNotIn('status', ['received_at_branch', 'returned_to_branch'])
            ->orderByDesc('dispatched_at')
            ->get();

        $completedToday = WorkshopDispatch::whereIn('status', ['received_at_branch', 'returned_to_branch'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->where(function($q) {
                $q->whereDate('received_at_branch_at', today())
                  ->orWhereDate('returned_at', today());
            })
            ->count();

        $branches = Branch::where('type', 'branch')->get();
        $riders = \App\Models\User::where('role', 'rider')->get();

        return view('workshop.operations', compact('dispatches', 'completedToday', 'branches', 'riders'));
    }

    // ─── Workshop Admin: Mark received at workshop ───
    public function receive(WorkshopDispatch $dispatch)
    {
        $dispatch->update([
            'status' => 'received_at_workshop',
            'received_by' => auth()->id(),
            'received_at_workshop_at' => now(),
        ]);

        $dispatch->order->update(['status' => 'processing']);

        return response()->json(['success' => true, 'status' => 'received_at_workshop']);
    }

    // ─── Workshop Admin: Mark processing ───
    public function startProcessing(WorkshopDispatch $dispatch)
    {
        $order = $dispatch->order;

        // For delivery orders: only block if NO payment submitted at all (paid_amount = 0 and no transactions)
        if ($order->is_delivery_order && !$order->payment_approved) {
            $hasAnyPayment = $order->paid_amount > 0 || \App\Models\PaymentTransaction::where('order_id', $order->id)->exists();
            if (!$hasAnyPayment) {
                return response()->json(['success' => false, 'message' => 'Customer has not submitted any payment yet. Please wait for advance payment before starting processing.'], 403);
            }
        }

        $dispatch->update(['status' => 'processing']);
        $order->update(['status' => 'processing']);

        \App\Models\OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'processing',
            'changed_by' => auth()->id(),
            'note' => 'Processing started',
        ]);

        return response()->json(['success' => true, 'status' => 'processing']);
    }

    // ─── Workshop Admin: Mark completed at workshop ───
    public function complete(WorkshopDispatch $dispatch)
    {
        $dispatch->update([
            'status' => 'completed_at_workshop',
            'completed_at_workshop_at' => now(),
        ]);

        $dispatch->order->statusHistory()->create([
            'status' => 'completed_at_workshop',
            'note' => 'Completed at Workshop',
            'changed_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'status' => 'completed_at_workshop']);
    }

    public function requestPayment(WorkshopDispatch $dispatch, Request $request, \App\Services\WhatsAppService $waService)
    {
        $order = $dispatch->order;
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
        if ($customer && $customer->phone) {
            $paymentLink = url("/orders/{$order->id}/pay?amount={$amount}");
            $message = "💳 *Payment Request*\n\n"
                . "Order #{$order->order_number}\n"
                . "Amount Due: Rs. " . number_format($amount, 2) . "\n\n"
                . "Please pay via this link to start processing:\n{$paymentLink}";

            $waService->sendText($customer->phone, $message, $order->id, $customer->id);
            return response()->json(['success' => true, 'message' => 'Payment request sent via WhatsApp.']);
        }

        return response()->json(['success' => false, 'message' => 'Customer has no phone number.'], 400);
    }

    public function approvePayment(WorkshopDispatch $dispatch)
    {
        $order = $dispatch->order;
        
        // IMPORTANT: Do NOT add to paid_amount here.
        // The customer already paid via bank transfer (submitPayment added it).
        // We are just APPROVING the payment and starting processing.
        $order->update([
            'payment_approved' => true,
        ]);
        
        // Update payment_status based on current paid_amount vs total
        $freshOrder = $order->fresh();
        if ($freshOrder->paid_amount >= $freshOrder->total) {
            $freshOrder->update(['payment_status' => 'paid']);
        } elseif ($freshOrder->paid_amount > 0) {
            $freshOrder->update(['payment_status' => 'partial']);
        }

        if (in_array($dispatch->status, ['dispatched_to_workshop', 'received_at_workshop'])) {
            $dispatch->update(['status' => 'processing']);
            $freshOrder->update(['status' => 'processing']);
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'processing',
                'changed_by' => auth()->id(),
                'note' => 'Payment approved, processing started',
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Payment approved. Order is now processing.']);
    }

    public function finalizeBill(WorkshopDispatch $dispatch)
    {
        $dispatch->load('order.items.serviceType');
        $services = \App\Models\ServiceType::where('is_active', true)->get();
        
        $cartItems = $dispatch->order->items->map(function($item) {
            return [
                'service_type_id' => $item->service_type_id,
                'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
                'base_price' => $item->unit_price,
                'qty' => $item->quantity,
            ];
        });
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        return view('workshop.finalize_bill', compact('dispatch', 'services', 'cartItems', 'minimumDeliveryCost'));
    }

    // ─── Workshop Admin: Return to branch ───
    public function returnToBranch(WorkshopDispatch $dispatch)
    {
        $dispatch->update([
            'status' => 'returned_to_branch',
            'returned_by' => auth()->id(),
            'returned_at' => now(),
        ]);

        $dispatch->order->update(['status' => 'returned_from_workshop']);

        $branchAdmins = \App\Models\User::where('role', 'branch_admin')->where('branch_id', $dispatch->branch_id)->get();
        \Illuminate\Support\Facades\Notification::send($branchAdmins, new \App\Notifications\SystemAlertNotification('Order Returned from Workshop: #' . $dispatch->order->order_number, route('orders.show', $dispatch->order_id), 'workshop_return'));

        return response()->json(['success' => true, 'status' => 'returned_to_branch']);
    }

    // ─── Workshop Admin: Dispatch Delivery ───
    public function dispatchDelivery(Request $request, WorkshopDispatch $dispatch)
    {
        $request->validate([
            'delivery_method' => 'required|in:rider,pickme_flash',
            'rider_id' => 'required_if:delivery_method,rider|nullable|exists:users,id',
            'pickme_cost' => 'required_if:delivery_method,pickme_flash|nullable|numeric|min:0',
        ]);

        $order = $dispatch->order;
        $appointment = $order->deliveryAppointment;

        if ($request->delivery_method === 'rider') {
            \App\Models\DeliveryTask::create([
                'delivery_appointment_id' => $appointment ? $appointment->id : null,
                'order_id' => $order->id,
                'rider_id' => $request->rider_id,
                'branch_id' => $dispatch->workshop_branch_id,
                'task_type' => 'delivery',
                'status' => 'assigned',
            ]);

            if ($appointment) {
                $appointment->update(['status' => 'out_for_delivery']);
            }
            $order->update(['status' => 'out_for_delivery']);
            
            $dispatch->update([
                'status' => 'returned_to_branch', // Terminate from workshop view
                'returned_by' => auth()->id(),
                'returned_at' => now(),
            ]);

            $riderUser = \App\Models\User::find($request->rider_id);
            if ($riderUser) {
                \Illuminate\Support\Facades\Notification::send($riderUser, new \App\Notifications\SystemAlertNotification('New Delivery Task (from Workshop): Order #' . $order->order_number, route('rider.tasks')));
            }

            return response()->json(['success' => true, 'status' => 'dispatched_for_delivery']);

        } else {
            // PickMe
            if ($order->paid_amount < $order->total) {
                return response()->json(['success' => false, 'message' => 'PickMe cannot be used for unpaid or partially paid orders. Customer must pay in full first.'], 403);
            }

            \App\Models\Expense::create([
                'branch_id' => $dispatch->workshop_branch_id,
                'amount' => $request->pickme_cost,
                'category' => 'PickMe',
                'description' => 'PickMe Delivery for Order #' . $order->order_number,
                'expense_date' => now(),
                'created_by' => auth()->id(),
            ]);

            if ($appointment) {
                $appointment->update(['status' => 'delivered']);
            }
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'delivered',
                'changed_by' => auth()->id(),
                'note' => 'Delivered via PickMe',
            ]);
            
            $dispatch->update([
                'status' => 'returned_to_branch', // Terminate from workshop view
                'returned_by' => auth()->id(),
                'returned_at' => now(),
            ]);

            return response()->json(['success' => true, 'status' => 'completed']);
        }
    }

    // ─── Workshop Admin: Bulk Status Update ───
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'dispatch_ids' => 'required|array',
            'dispatch_ids.*' => 'exists:workshop_dispatches,id',
            'status' => 'required|string|in:processing,completed_at_workshop,returned_to_branch',
        ]);

        $status = $request->status;
        $dispatchIds = $request->dispatch_ids;

        DB::transaction(function () use ($dispatchIds, $status) {
            foreach ($dispatchIds as $id) {
                $dispatch = WorkshopDispatch::find($id);
                if (! $dispatch) {
                    continue;
                }

                if ($status === 'processing') {
                    $dispatch->update(['status' => 'processing']);
                    $dispatch->order->update(['status' => 'processing']);
                } elseif ($status === 'completed_at_workshop') {
                    $dispatch->update([
                        'status' => 'completed_at_workshop',
                        'completed_at_workshop_at' => now(),
                    ]);
                } elseif ($status === 'returned_to_branch') {
                    $dispatch->update([
                        'status' => 'returned_to_branch',
                        'returned_by' => auth()->id(),
                        'returned_at' => now(),
                    ]);
                    $dispatch->order->update(['status' => 'returned_from_workshop']);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    // ─── Branch Admin: Confirm order received back from workshop ───
    public function receivedAtBranch(WorkshopDispatch $dispatch, WhatsAppService $waService)
    {
        $dispatch->update([
            'status' => 'received_at_branch',
            'received_at_branch_at' => now(),
            'workshop_notes' => request('workshop_notes'),
        ]);

        $order = $dispatch->order;
        $order->update([
            'status' => 'done', // changed from returned_from_workshop to done
            'returned_from_workshop_at' => now(),
            'ready_at' => now(),
        ]);

        // Send WA "Ready" notification
        $waService->notifyOrderReady($order);

        return redirect()->back()->with('success', 'Order received back from workshop. Customer notified.');
    }


    // ─── Workshop Admin: All completed orders ───
    public function history()
    {
        $user = auth()->user();

        $dispatches = WorkshopDispatch::with(['order.customer', 'branch'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $user->branch_id))
            ->whereIn('status', ['received_at_branch', 'returned_to_branch'])
            ->orderByRaw('COALESCE(received_at_branch_at, returned_at) DESC')
            ->paginate(20);

        $cancelledBookings = \App\Models\DeliveryAppointment::with(['customer', 'cancelledBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->where('status', 'cancelled')
            ->orderByDesc('updated_at')
            ->get();

        return view('workshop.history', compact('dispatches', 'cancelledBookings'));
    }
}
