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

        if ($activeSession) {
            // Cash collected by workshop admins during this shift (from riders returning to workshop)
            $cashCollected = \App\Models\PaymentTransaction::whereHas('order', function($q) use ($branchId) {
                    $q->where('workshop_id', $branchId);
                })
                ->where('collected_from_rider_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->sum('amount');

            // Expenses logged by workshop admins
            $expensesLogged = \App\Models\Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            $cashBalance = $activeSession->opening_cash + $cashCollected - $expensesLogged;
            $currentBalance = $cashBalance;
            $totalSale = $cashCollected; // Workshops mostly just collect cash
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
            'card_payments' => $cardPayments,
            'card_payments_amount' => $cardPaymentsAmount,
            'total_sale' => $totalSale,
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

        $dispatches = WorkshopDispatch::with(['order.customer', 'branch', 'dispatchedBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->whereNotIn('status', ['received_at_branch'])
            ->orderByDesc('dispatched_at')
            ->get();

        $completedToday = WorkshopDispatch::where('status', 'received_at_branch')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->whereDate('received_at_branch_at', today())
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

        if ($order->is_delivery_order && !$order->payment_approved) {
            return response()->json(['success' => false, 'message' => 'Payment must be approved before starting processing for delivery orders.'], 403);
        }

        $dispatch->update(['status' => 'processing']);
        $order->update(['status' => 'processing']);

        return response()->json(['success' => true, 'status' => 'processing']);
    }

    // ─── Workshop Admin: Mark completed at workshop ───
    public function complete(WorkshopDispatch $dispatch)
    {
        $dispatch->update([
            'status' => 'completed_at_workshop',
            'completed_at_workshop_at' => now(),
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
        $order->update(['payment_approved' => true]);

        return response()->json(['success' => true, 'message' => 'Payment approved. You can now start processing.']);
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

        return view('workshop.finalize_bill', compact('dispatch', 'services', 'cartItems'));
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

        return response()->json(['success' => true, 'status' => 'returned_to_branch']);
    }

    // ─── Workshop Admin: Dispatch Delivery ───
    public function dispatchDelivery(Request $request, WorkshopDispatch $dispatch)
    {
        $request->validate([
            'delivery_method' => 'required|in:rider,pickme_flash',
            'rider_id' => 'required_if:delivery_method,rider|exists:users,id',
            'pickme_cost' => 'required_if:delivery_method,pickme_flash|numeric|min:0',
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
        } else {
            // PickMe Flash
            \App\Models\Expense::create([
                'branch_id' => $dispatch->workshop_branch_id,
                'amount' => $request->pickme_cost,
                'category' => 'delivery',
                'description' => 'PickMe Flash Delivery for Order #' . $order->order_number,
                'date' => now(),
                'added_by' => auth()->id(),
            ]);
        }

        if ($appointment) {
            $appointment->update(['status' => 'out_for_delivery']);
        }
        
        $order->update(['status' => 'out_for_delivery']);
        
        $dispatch->update([
            'status' => 'returned_to_branch', // Terminate from workshop view
            'returned_by' => auth()->id(),
            'returned_at' => now(),
        ]);

        return response()->json(['success' => true, 'status' => 'dispatched_for_delivery']);
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

    // ─── View order tracking steps ───
    public function tracking($orderId)
    {
        $order = Order::withoutGlobalScopes()->findOrFail($orderId);
        
        $dispatch = WorkshopDispatch::with(['dispatchedBy', 'receivedBy', 'returnedBy'])
            ->where('order_id', $order->id)
            ->latest()
            ->first();

        return view('workshop.tracking', compact('order', 'dispatch'));
    }

    // ─── Workshop Admin: All completed orders ───
    public function history()
    {
        $user = auth()->user();

        $dispatches = WorkshopDispatch::with(['order.customer', 'branch'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $user->branch_id))
            ->where('status', 'received_at_branch')
            ->orderByDesc('received_at_branch_at')
            ->paginate(20);

        return view('workshop.history', compact('dispatches'));
    }
}
