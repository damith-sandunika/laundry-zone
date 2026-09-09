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
    public function dispatch(\App\Http\Requests\Workshop\DispatchToWorkshopRequest $request, Order $order, \App\Actions\Workshop\DispatchToWorkshopAction $action)
    {
        try {
            $action->execute($order, $request->validated(), auth()->id());
            return redirect()->back()->with('success', 'Order dispatched to workshop successfully.');
        } catch (\Exception $e) {
            \Log::error('WorkshopController::index error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred loading the workshop.');
        }
    }

    public function bulkDispatch(\App\Http\Requests\Workshop\BulkDispatchToWorkshopRequest $request, \App\Actions\Workshop\BulkDispatchToWorkshopAction $action)
    {
        try {
            $count = $action->execute($request->validated(), auth()->id());
            return redirect()->back()->with('success', $count . ' orders dispatched to workshop.');
        } catch (\Exception $e) {
            \Log::error('WorkshopController::bulkDispatch error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred during bulk dispatch.');
        }
    }

    // ─── Workshop Admin: Dashboard ───
    public function dashboard()
    {
        $user = auth()->user();
        $branchId = $this->getActiveBranchId();

        // Active register session details
        $activeSession = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $suggestedOpeningCash = 0;
        if (!$activeSession) {
            $lastSession = \App\Models\RegisterSession::where('branch_id', $branchId)
                ->where('status', 'closed')
                ->orderBy('closed_at', 'desc')
                ->first();
            $suggestedOpeningCash = $lastSession ? $lastSession->closing_cash : 0;
        }

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

            // Bank deposits made during the shift
            $bankDepositsLogged = \App\Models\BankDeposit::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            $cashBalance = $activeSession->opening_cash + $cashCollected - $bankDepositsLogged;
            $currentBalance = $cashBalance;
            $totalPaymentsCollected = $cashCollected + $cardPaymentsAmount + $bankTransfers;

            $workshopDirectDeliveryFees = 0;
            $workshopDirectCommissionableSales = 0;
            
            // 1. Process payment transactions collected directly by the workshop
            $shiftPayments = \App\Models\PaymentTransaction::with('order')
                ->whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->get();

            // Pre-group ALL transactions for these orders in ONE query (fixes N+1)
            $shiftOrderIds = $shiftPayments->pluck('order_id')->filter()->unique()->toArray();
            $allOrderTransactions = \App\Models\PaymentTransaction::whereIn('order_id', $shiftOrderIds)
                ->select('id', 'order_id', 'amount')
                ->orderBy('id')
                ->get()
                ->groupBy('order_id');

            foreach ($shiftPayments as $payment) {
                if ($payment->order) {
                    $order = $payment->order;
                    $deliveryFee = $order->delivery_fee ?? 0;

                    // In-memory lookup — no extra DB query per payment
                    $orderTxns = $allOrderTransactions->get($order->id, collect());
                    $paidBefore = $orderTxns->where('id', '<', $payment->id)->sum('amount');

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
            $netIncome = $totalSalesRevenue + $workshopDirectDeliveryFees;

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
            ->join('orders', 'workshop_dispatches.order_id', '=', 'orders.id')
            ->select('workshop_dispatches.*')
            ->where('workshop_branch_id', $branchId)
            ->whereIn('workshop_dispatches.status', ['dispatched_to_workshop', 'received_at_workshop', 'processing'])
            ->orderByRaw('orders.due_date IS NULL')
            ->orderBy('orders.due_date', 'asc')
            ->take(50)
            ->get();

        // Low stock items
        $lowStockItems = \App\Models\InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->where('branch_id', $branchId)
            ->get();

        // All inventory items for popup
        $allInventory = \App\Models\InventoryItem::where('branch_id', $branchId)->get();

        // Fetch Laundry/Branch Name
        $currentBranch = \App\Models\Branch::find($branchId);
        $branchName = $currentBranch->name ?? 'Workshop';

        // Fetch Branch Staff for assignment when opening shift
        $branchStaff = \App\Models\User::where('branch_id', $branchId)->where('role', 'staff')->get();
        
        $activeAttendances = collect();
        if ($activeSession) {
            $activeAttendances = \App\Models\Attendance::where('register_session_id', $activeSession->id)->get()->keyBy('user_id');
        }

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

        return view('workshop.dashboard', compact('stats', 'recentOrders', 'lowStockItems', 'allInventory', 'activeSession', 'suggestedOpeningCash', 'branchName', 'chartData', 'currentBranch', 'branchStaff', 'activeAttendances'));
    }

    // ─── Workshop Admin: Operations ───
    public function operations(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $branchId = $this->getActiveBranchId();

        $query = WorkshopDispatch::with(['order.customer', 'order.deliveryAppointment', 'order.paymentTransactions', 'order.cubby.cubby', 'order.items.serviceType', 'branch', 'dispatchedBy'])
            ->join('orders', 'workshop_dispatches.order_id', '=', 'orders.id')
            ->select('workshop_dispatches.*')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->whereNotIn('workshop_dispatches.status', ['received_at_branch'])
            ->orderByDesc('orders.id');

        $selectedBranch = $request->get('branch');
        if ($selectedBranch) {
            $query->where('workshop_dispatches.branch_id', $selectedBranch);
        }

        $countsQuery = clone $query;
        $counts = [
            'incoming' => (clone $countsQuery)->where('workshop_dispatches.status', 'dispatched_to_workshop')->count(),
            'to_wash' => (clone $countsQuery)->where('workshop_dispatches.status', 'received_at_workshop')->count(),
            'washing' => (clone $countsQuery)->where('workshop_dispatches.status', 'processing')->count(),
            'completed' => (clone $countsQuery)->where('workshop_dispatches.status', 'completed_at_workshop')->count(),
            'returned' => (clone $countsQuery)->whereIn('workshop_dispatches.status', ['returned_to_branch', 'received_at_branch'])->count(),
        ];

        $currentTab = $request->get('tab', 'incoming');
        if ($currentTab === 'incoming') {
            $query->where('workshop_dispatches.status', 'dispatched_to_workshop');
        } elseif ($currentTab === 'to_wash') {
            $query->where('workshop_dispatches.status', 'received_at_workshop');
        } elseif ($currentTab === 'washing') {
            $query->where('workshop_dispatches.status', 'processing');
        } elseif ($currentTab === 'completed') {
            $query->where('workshop_dispatches.status', 'completed_at_workshop');
        } elseif ($currentTab === 'returned') {
            $query->whereIn('workshop_dispatches.status', ['returned_to_branch', 'received_at_branch']);
        }

        $dispatches = $query->paginate(15);

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
        $categories = \App\Models\ServiceCategory::with('services')->where('is_active', true)->get();
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        return view('workshop.operations', compact('dispatches', 'completedToday', 'branches', 'riders', 'cubbies', 'categories', 'minimumDeliveryCost', 'currentTab', 'selectedBranch', 'counts'));
    }

    // ─── Workshop Admin: Mark received at workshop ───
    public function receive(WorkshopDispatch $dispatch, \App\Actions\Workshop\ReceiveWorkshopDispatchAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'status' => 'received_at_workshop']);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::receive error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    // ─── Workshop Admin: Mark processing ───
    public function startProcessing(WorkshopDispatch $dispatch, \App\Actions\Workshop\StartProcessingWorkshopAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'status' => 'processing', 'order_number' => $dispatch->order->order_number]);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::startProcessing error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    // ─── Workshop Admin: Mark completed at workshop ───
    public function complete(\App\Http\Requests\Workshop\CompleteWorkshopProcessingRequest $request, WorkshopDispatch $dispatch, \App\Actions\Workshop\CompleteWorkshopProcessingAction $action)
    {
        try {
            $action->execute($dispatch, $request->validated(), auth()->id());
            return response()->json(['success' => true, 'status' => 'completed_at_workshop']);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::complete error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
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

        $customer = $order->customer;
        if ($customer) {
            $commService = new CommunicationService();
            $commService->sendFinalizedBill($order, $amount);

            return response()->json(['success' => true, 'message' => 'Payment request sent.']);
        }

        return response()->json(['success' => false, 'message' => 'Customer has no phone number.'], 400);
    }

    public function approvePayment(WorkshopDispatch $dispatch, \App\Actions\Workshop\ApproveWorkshopPaymentAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'message' => 'Payment approved. Order is now processing.']);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::approvePayment error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function finalizeBill(string $order_number)
    {
        $order = \App\Models\Order::where('order_number', $order_number)
            ->with(['items.serviceType', 'items.extras', 'customer', 'deliveryAppointment'])
            ->firstOrFail();

        $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)
            ->whereNotIn('status', ['received_at_branch'])
            ->latest()
            ->firstOrFail();

        // Scope to the workshop's branch (fall back to order branch if dispatch has no branch)
        $branchId = $dispatch->workshop->branch_id
            ?? $dispatch->branch_id
            ?? $order->branch_id
            ?? null;

        $services = \App\Models\ServiceType::where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get();

        $categories = \App\Models\ServiceCategory::with(['children' => function($q) use ($branchId) {
            $q->where('is_active', true)->orderBy('order_index');
        }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('order_index')
            ->get();

        $turnaroundTypes = \App\Models\TurnaroundType::where(function($q) use ($branchId) {
            $q->whereNull('branch_id');
            if ($branchId) {
                $q->orWhere('branch_id', $branchId);
            }
        })->get();

        $systemExtras = \App\Models\Extra::where('is_active', true)
            ->where(function($q) use ($branchId) {
                $q->whereNull('branch_id');
                if ($branchId) {
                    $q->orWhere('branch_id', $branchId);
                }
            })
            ->get();
        
        $cartItems = $order->items->map(function($item) {
            $selectedExtras = $item->extras->map(function($ext) {
                return ['id' => $ext->extra_id, 'qty' => $ext->quantity];
            })->toArray();
            return [
                'service_type_id' => $item->service_type_id,
                'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
                'base_price' => $item->unit_price,
                'qty' => $item->quantity,
                'is_special' => $item->turnaround_type_id ? true : false,
                'turnaround_type_id' => $item->turnaround_type_id ? (int)$item->turnaround_type_id : null,
                'turnaround_extra_charge' => 0,
                'selected_extras' => $selectedExtras,
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
                        'is_special' => $i['is_special'] ?? false,
                        'turnaround_type_id' => !empty($i['turnaround_type_id']) ? (int)$i['turnaround_type_id'] : null,
                        'turnaround_extra_charge' => $i['turnaround_extra_charge'] ?? 0,
                        'selected_extras' => $i['selected_extras'] ?? [],
                    ];
                }, $parsedCart);
            }
        }
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        return view('workshop.finalize_bill', compact('dispatch', 'order', 'services', 'categories', 'turnaroundTypes', 'systemExtras', 'cartItems', 'minimumDeliveryCost'));
    }

    // ─── Workshop Admin: Return to branch (all order types) ───
    public function returnToBranch(WorkshopDispatch $dispatch, \App\Actions\Workshop\ReturnToBranchAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'status' => 'returned_to_branch']);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::returnToBranch error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    // ─── Workshop Admin: Dispatch Delivery ───
    public function dispatchDelivery(\App\Http\Requests\Workshop\WorkshopDispatchDeliveryRequest $request, WorkshopDispatch $dispatch, \App\Actions\Workshop\WorkshopDispatchDeliveryAction $action)
    {
        try {
            $result = $action->execute($dispatch, $request->validated(), auth()->id());
            return response()->json(array_merge(['success' => true], $result));
        } catch (\Exception $e) {
            \Log::error('WorkshopController::bulkUpdate error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    // ─── Workshop Admin: Bulk Status Update ───
    public function bulkStatus(\App\Http\Requests\Workshop\WorkshopBulkStatusUpdateRequest $request, \App\Actions\Workshop\WorkshopBulkStatusUpdateAction $action)
    {
        try {
            $action->execute($request->validated(), auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::bulkReturn error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    // ─── Branch Admin: Confirm order received back from workshop ───
    public function receivedAtBranch(WorkshopDispatch $dispatch, \Illuminate\Http\Request $request, \App\Actions\Workshop\MarkReceivedAtBranchAction $action)
    {
        try {
            $action->execute($dispatch, $request->all());
            return redirect()->back()->with('success', 'Order received back from workshop. Customer notified.');
        } catch (\Exception $e) {
            \Log::error('WorkshopController::dispatchDelivery error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred during dispatch.');
        }
    }

    // ─── Workshop Admin: Hand Over Walk-in Order to Customer ───
    public function handover(\App\Http\Requests\Workshop\WorkshopHandoverRequest $request, WorkshopDispatch $dispatch, \App\Actions\Workshop\WorkshopHandoverAction $action)
    {
        try {
            $action->execute($dispatch, $request->validated(), auth()->id());
            return response()->json(['success' => true, 'order_id' => $dispatch->order_id, 'order_number' => $dispatch->order->order_number]);
        } catch (\Exception $e) {
            \Log::error('WorkshopController::handover error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }


    // ─── Workshop Admin: All completed orders ───
    public function history()
    {
        $user = auth()->user();

        $dispatches = WorkshopDispatch::with(['order.customer', 'branch'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $this->getActiveBranchId()))
            ->whereIn('status', ['received_at_branch', 'returned_to_branch'])
            ->orderByRaw('COALESCE(received_at_branch_at, returned_at) DESC')
            ->paginate(15);

        $cancelledBookings = \App\Models\DeliveryAppointment::with(['customer', 'cancelledBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $this->getActiveBranchId()))
            ->where('status', 'cancelled')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('workshop.history', compact('dispatches', 'cancelledBookings'));
    }
}

