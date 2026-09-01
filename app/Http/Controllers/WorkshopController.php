<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
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

        $commService = new CommunicationService();
        // $commService->sendOrderDispatchedToWorkshop($order);

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

        $commService = new CommunicationService();
        foreach ($orders as $order) {
            // $commService->sendOrderDispatchedToWorkshop($order);
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
            $totalPaymentsCollected = $cashCollected + $cardPaymentsAmount + $bankTransfers;

            $workshopDirectDeliveryFees = 0;
            $workshopDirectCommissionableSales = 0;
            
            // 1. Process payment transactions collected directly by the workshop
            $shiftPayments = \App\Models\PaymentTransaction::with('order')
                ->whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->get();

            foreach ($shiftPayments as $payment) {
                if ($payment->order) {
                    $order = $payment->order;
                    $deliveryFee = $order->delivery_fee ?? 0;
                    
                    $paidBefore = \App\Models\PaymentTransaction::where('order_id', $order->id)
                        ->where('id', '<', $payment->id)
                        ->sum('amount');

                    if ($paidBefore >= $deliveryFee) {
                        $paidToDelivery = 0;
                        $commissionable = $payment->amount;
                    } else {
                        $remainingDeliveryFee = $deliveryFee - $paidBefore;
                        $paidToDelivery = min($payment->amount, $remainingDeliveryFee);
                        $commissionable = $payment->amount - $paidToDelivery;
                    }
                    
                    $workshopDirectDeliveryFees += $paidToDelivery;
                    $workshopDirectCommissionableSales += $commissionable;
                }
            }

            // 2. Direct cash/bank orders without transactions
            $directOrders = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->whereDoesntHave('paymentTransactions')
                ->whereIn('payment_method', ['cash', 'bank'])
                ->get();

            foreach ($directOrders as $order) {
                $deliveryFee = $order->delivery_fee ?? 0;
                $paid = $order->paid_amount;
                
                $paidToDelivery = min($paid, $deliveryFee);
                $commissionable = $paid - $paidToDelivery;
                
                $workshopDirectDeliveryFees += $paidToDelivery;
                $workshopDirectCommissionableSales += $commissionable;
            }

            // Calculate total orders dispatched to this workshop during the shift
            $shiftDispatches = \App\Models\WorkshopDispatch::with(['order', 'branch'])
                ->where('workshop_branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->get();
            
            $workshopGrossSales = 0;
            $branchDeliveryFees = 0;
            $commissionableSales = 0;
            $branchCommissionsGiven = 0;
            $branchBreakdown = [];
            
            $branchPendingPayments = 0;
            $ourPendingPayments = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->selectRaw('SUM(total - paid_amount) as pending_total')
                ->value('pending_total') ?? 0;

            foreach ($shiftDispatches as $dispatch) {
                if ($dispatch->order && $dispatch->branch) {
                    $bId = $dispatch->branch->id;
                    
                    // Exclude the workshop itself from the branch breakdown
                    if ($bId == $branchId) {
                        continue;
                    }

                    if (!isset($branchBreakdown[$bId])) {
                        $branchBreakdown[$bId] = [
                            'name' => $dispatch->branch->name,
                            'profit_percentage' => $dispatch->branch->branch_profit_percentage ?? 0,
                            'gross_sales' => 0,
                            'branch_commission' => 0,
                            'workshop_commission' => 0,
                        ];
                    }

                    $order = $dispatch->order;
                    $orderTotal = $order->total;
                    $deliveryFee = $order->delivery_fee ?? 0;
                    $paidAmount = $order->paid_amount;
                    
                    $branchPendingPayments += ($orderTotal - $paidAmount);
                    
                    $paidToDelivery = min($paidAmount, $deliveryFee);
                    $commissionablePaid = $paidAmount - $paidToDelivery;
                    
                    if ($commissionablePaid > 0) {
                        $workshopGrossSales += $commissionablePaid;
                        $branchDeliveryFees += $paidToDelivery;
                        $commissionableSales += $commissionablePaid;
                        
                        $branchProfitPercent = $dispatch->branch->branch_profit_percentage ?? 0;
                        $branchComm = $commissionablePaid * ($branchProfitPercent / 100);
                        
                        $branchCommissionsGiven += $branchComm;

                        $branchBreakdown[$bId]['gross_sales'] += $commissionablePaid;
                        $branchBreakdown[$bId]['branch_commission'] += $branchComm;
                        $branchBreakdown[$bId]['workshop_commission'] += ($commissionablePaid - $branchComm);
                    }
                }
            }

            // The workshop keeps the commissionable sales minus the branch commissions given
            $workshopCommissionFromBranches = $commissionableSales - $branchCommissionsGiven;
            
            $totalSalesRevenue = $workshopDirectCommissionableSales + $workshopCommissionFromBranches;
            $netIncome = $totalSalesRevenue + $workshopDirectDeliveryFees - $expensesLogged;

        } else {
            $totalPaymentsCollected = 0;
            $workshopGrossSales = 0;
            $branchDeliveryFees = 0;
            $commissionableSales = 0;
            $branchCommissionsGiven = 0;
            $branchBreakdown = [];
            $branchPendingPayments = 0;
            $ourPendingPayments = 0;
            $workshopCommissionFromBranches = 0;
            $totalSalesRevenue = 0;
            $workshopDirectDeliveryFees = 0;
            $workshopDirectCommissionableSales = 0;
            $netIncome = 0;
            $cashCollected = 0;
            $cardPayments = 0;
            $cardPaymentsAmount = 0;
            $bankTransfers = 0;
            $expensesLogged = 0;
            $cashBalance = 0;
            $currentBalance = 0;
            $totalSale = 0;
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
            'total_payments_collected' => $totalPaymentsCollected,
            'workshop_direct_commissionable_sales' => $workshopDirectCommissionableSales,
            'workshop_direct_delivery_fees' => $workshopDirectDeliveryFees,
            'workshop_gross_sales' => $workshopGrossSales,
            'branch_delivery_fees' => $branchDeliveryFees,
            'commissionable_sales' => $commissionableSales,
            'branch_commissions_given' => $branchCommissionsGiven,
            'branch_breakdown' => $branchBreakdown,
            'workshop_commission_from_branches' => $workshopCommissionFromBranches,
            'total_sales_revenue' => $totalSalesRevenue,
            'net_income' => $netIncome,
            'expenses_logged' => $expensesLogged ?? 0,
            'our_pending_payments' => $ourPendingPayments,
            'branch_pending_payments' => $branchPendingPayments,
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

        $dispatches = WorkshopDispatch::with(['order.customer', 'order.deliveryAppointment', 'order.paymentTransactions', 'order.cubby.cubby', 'order.items.serviceType', 'branch', 'dispatchedBy'])
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
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId)->where('is_active', true)->get();
        $categories = \App\Models\ServiceCategory::with(['services' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }])->where('is_active', true)->where('branch_id', $branchId)->get();
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        return view('workshop.operations', compact('dispatches', 'completedToday', 'branches', 'riders', 'cubbies', 'categories', 'minimumDeliveryCost'));
    }

    // ─── Workshop Admin: Mark received at workshop ───
    public function receive(WorkshopDispatch $dispatch)
    {
        try {
            $dispatch->update([
                'status'                    => 'received_at_workshop',
                'received_by'               => auth()->id(),
                'received_at_workshop_at'   => now(),
            ]);

            $dispatch->order->update(['status' => 'processing']);

            try {
                $commService = new CommunicationService();
                // $commService->sendOrderProcessing($dispatch->order);
            } catch (\Exception $e) {
                \Log::error('Email notification failed (receive): ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'status' => 'received_at_workshop']);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::receive error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Workshop Admin: Mark processing ───
    public function startProcessing(WorkshopDispatch $dispatch)
    {
        try {
            $order = $dispatch->order;

            $dispatch->update(['status' => 'processing']);
            $order->update(['status' => 'processing']);

            try {
                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'processing',
                    'changed_by' => auth()->id(),
                    'note'       => 'Processing started',
                ]);
            } catch (\Exception $e) {
                \Log::warning('OrderStatusHistory insert failed (startProcessing): ' . $e->getMessage());
            }

            try {
                $commService = new CommunicationService();
                // $commService->sendOrderProcessing($order);
            } catch (\Exception $e) {
                \Log::error('Email notification failed (startProcessing): ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'status' => 'processing', 'order_id' => $order->id]);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::startProcessing error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Workshop Admin: Mark completed at workshop ───
    public function complete(Request $request, WorkshopDispatch $dispatch)
    {
        try {
            $request->validate([
                'cubby_id'       => 'nullable|exists:cubbies,id',
                'payment_method' => 'nullable|string',
                'amount_paid'    => 'nullable|numeric|min:0',
            ]);

            $dispatch->update([
                'status'                   => 'completed_at_workshop',
                'completed_at_workshop_at' => now(),
            ]);

            $order = $dispatch->order;

            if ($request->filled('cubby_id')) {
                \App\Models\OrderCubby::where('order_id', $order->id)->whereNull('released_at')
                    ->update(['released_at' => now()]);
                $cubby = \App\Models\Cubby::find($request->cubby_id);
                if ($cubby) {
                    \App\Models\OrderCubby::create([
                        'order_id'    => $order->id,
                        'cubby_id'    => $cubby->id,
                        'assigned_at' => now(),
                    ]);
                    $cubby->update(['status' => 'occupied']);
                }
            }

            if ($request->filled('amount_paid') && $request->amount_paid > 0) {
                \App\Models\PaymentTransaction::create([
                    'order_id'       => $order->id,
                    'amount'         => $request->amount_paid,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'created_by'     => auth()->id(),
                    'status'         => 'completed',
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                ]);
                $newPaid = $order->paid_amount + $request->amount_paid;
                $order->update([
                    'paid_amount'    => $newPaid,
                    'payment_method' => $request->payment_method ?? $order->payment_method,
                    'payment_status' => $newPaid >= $order->total ? 'paid' : 'partial',
                ]);
            }

            $order->update(['status' => 'completed_at_workshop']);

            try {
                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'completed_at_workshop',
                    'note'       => 'Completed at Workshop',
                    'changed_by' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('OrderStatusHistory insert failed (complete): ' . $e->getMessage());
            }

            // If this is a workshop walk-in (NOT a delivery order), send ready notification
            if ($dispatch->branch_id === $dispatch->workshop_branch_id && !$order->is_delivery_order) {
                try {
                    $commService = new CommunicationService();
                    $commService->sendPosWalkinReady($order);
                } catch (\Exception $e) {
                    \Log::error('Email notification failed (complete ready): ' . $e->getMessage());
                }
            }

            return response()->json(['success' => true, 'status' => 'completed_at_workshop']);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::complete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function requestPayment(WorkshopDispatch $dispatch, Request $request)
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

        if ($customer) {
            $paymentLink = url("/orders/{$order->id}/pay?amount={$amount}");
            
            $commService = new CommunicationService();
            // $commService->sendFinalizedBill($order);
            
            return response()->json(['success' => true, 'message' => 'Payment request sent.']);
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

    // ─── Workshop Admin: Return to branch (all order types) ───
    public function returnToBranch(WorkshopDispatch $dispatch)
    {
        try {
            $dispatch->update([
                'status'      => 'returned_to_branch',
                'returned_by' => auth()->id(),
                'returned_at' => now(),
            ]);

            $order = $dispatch->order;
            $order->update(['status' => 'returned_from_workshop']);

            try {
                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'returned_from_workshop',
                    'changed_by' => auth()->id(),
                    'note'       => 'Washing completed. Dispatched back to branch.',
                ]);
            } catch (\Exception $e) {
                \Log::warning('OrderStatusHistory insert failed (returnToBranch): ' . $e->getMessage());
            }

            try {
                $branchAdmins = \App\Models\User::whereIn('role', ['branch_admin', 'admin'])
                    ->where('branch_id', $dispatch->branch_id)->get();
                \Illuminate\Support\Facades\Notification::send(
                    $branchAdmins,
                    new \App\Notifications\SystemAlertNotification(
                        'Order Ready from Workshop: #' . $order->order_number,
                        route('orders.show', $order->order_number),
                        'workshop_return'
                    )
                );
            } catch (\Exception $e) {
                \Log::error('Notification failed (returnToBranch): ' . $e->getMessage());
            }

            try {
                $commService = new CommunicationService();
                // $commService->sendOrderReturnedFromWorkshop($order);
            } catch (\Exception $e) {
                \Log::error('Email notification failed (returnToBranch): ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'status' => 'returned_to_branch']);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::returnToBranch error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
                'status' => 'returned_to_branch',
                'returned_by' => auth()->id(),
                'returned_at' => now(),
            ]);

            // Send delivery completed notification
            try {
                $commService = new \App\Services\CommunicationService();
                $commService->sendDeliveryCompleted($order);
            } catch (\Exception $e) {
                \Log::error('Delivery notification failed (Workshop PickMe): ' . $e->getMessage());
            }

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

        $commService = new CommunicationService();
        foreach ($dispatchIds as $id) {
            $dispatch = WorkshopDispatch::find($id);
            if ($dispatch && $dispatch->order) {
                if ($status === 'processing') {
                    // $commService->sendOrderProcessing($dispatch->order);
                } elseif ($status === 'returned_to_branch') {
                    // $commService->sendOrderReturnedFromWorkshop($dispatch->order);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    // ─── Branch Admin: Confirm order received back from workshop ───
    public function receivedAtBranch(WorkshopDispatch $dispatch)
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

        // Send "Ready" notification
        $commService = new CommunicationService();
        $commService->sendCollectionOrderReady($order);

        return redirect()->back()->with('success', 'Order received back from workshop. Customer notified.');
    }

    // ─── Workshop Admin: Hand Over Walk-in Order to Customer ───
    public function handover(Request $request, WorkshopDispatch $dispatch)
    {
        try {
            $request->validate([
                'payment_method' => 'nullable|string',
                'amount_paid'    => 'nullable|numeric|min:0',
            ]);

            $order = $dispatch->order;

            DB::transaction(function () use ($request, $order, $dispatch) {
                // Collect payment if provided
                $amountPaid = (float) ($request->amount_paid ?? 0);
                if ($amountPaid > 0) {
                    \App\Models\PaymentTransaction::create([
                        'order_id'       => $order->id,
                        'amount'         => $amountPaid,
                        'payment_method' => $request->payment_method ?? 'cash',
                        'created_by'     => auth()->id(),
                        'status'         => 'completed',
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    ]);
                    $newPaid = $order->paid_amount + $amountPaid;
                    $order->update([
                        'paid_amount'    => $newPaid,
                        'payment_method' => $request->payment_method ?? $order->payment_method,
                        'payment_status' => $newPaid >= $order->total ? 'paid' : 'partial',
                    ]);
                    $order = $order->fresh();
                }

                // Require full payment before handover
                if ($order->paid_amount < $order->total) {
                    throw new \Exception('Balance due: Rs. ' . number_format($order->total - $order->paid_amount, 2) . '. Collect full payment before handover.');
                }

                $order->update([
                    'status'       => 'completed',
                    'delivered_at' => now(),
                ]);

                // Use direct model create to avoid relationship overwriting status field
                try {
                    \App\Models\OrderStatusHistory::create([
                        'order_id'   => $order->id,
                        'status'     => 'completed',
                        'note'       => 'Handed over to customer at workshop',
                        'changed_by' => auth()->id(),
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('OrderStatusHistory insert failed (handover): ' . $e->getMessage());
                }

                // Release cubby
                $cubbyIds = \App\Models\OrderCubby::where('order_id', $order->id)->pluck('cubby_id');
                \App\Models\OrderCubby::where('order_id', $order->id)->whereNull('released_at')
                    ->update(['released_at' => now()]);
                foreach ($cubbyIds as $cid) {
                    $cubby = \App\Models\Cubby::find($cid);
                    if ($cubby && !$cubby->currentOrder()->exists()) {
                        $cubby->update(['status' => 'available']);
                    }
                }

                $dispatch->update([
                    'status'         => 'returned_to_branch',
                    'returned_by'    => auth()->id(),
                    'returned_at'    => now(),
                    'workshop_notes' => 'Handed over directly to customer at workshop',
                ]);
            });

            // Send completion email AFTER transaction
            try {
                $order = $order->fresh();
                $commService = new CommunicationService();
                $commService->sendPosWalkinCompleted($order);
            } catch (\Exception $e) {
                \Log::error('Email notification failed (handover): ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'order_id' => $order->id]);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::handover error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
