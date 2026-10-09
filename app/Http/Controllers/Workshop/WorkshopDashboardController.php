<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\WorkshopDispatch;
use Illuminate\Http\Request;

class WorkshopDashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $branchId = $this->getActiveBranchId();

        // Active register session details
        $activeSession = \App\Models\RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $suggestedOpeningCash = 0;
        if (!$activeSession) {
            $lastSession = \App\Models\RegisterSession::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->where('branch_id', $branchId)
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
                    
                    $branchPendingPayments += max(0, $orderTotal - $paidAmount);
                    
                    // Commission is based on the FULL order value, regardless of payment status
                    $commissionableTotal = max(0, $orderTotal - $deliveryFee);
                    
                    if ($commissionableTotal > 0) {
                        $workshopGrossSales += $commissionableTotal;
                        $branchDeliveryFees += $deliveryFee;
                        $commissionableSales += $commissionableTotal;
                        
                        $branchProfitPercent = $dispatch->branch->branch_profit_percentage ?? 0;
                        $branchComm = $commissionableTotal * ($branchProfitPercent / 100);
                        
                        $branchCommissionsGiven += $branchComm;

                        $branchBreakdown[$bId]['gross_sales'] += $commissionableTotal;
                        $branchBreakdown[$bId]['branch_commission'] += $branchComm;
                        $branchBreakdown[$bId]['workshop_commission'] += ($commissionableTotal - $branchComm);
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

    public function history(Request $request)
    {
        $user = auth()->user();
        $branchId = $this->getActiveBranchId();

        $query = WorkshopDispatch::with(['order' => fn($q) => $q->with(['customer', 'deletedBy']), 'branch'])
            ->whereHas('order')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId));

        $statsQuery = WorkshopDispatch::join('orders', 'workshop_dispatches.order_id', '=', 'orders.id')
            ->whereNull('orders.deleted_at')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_dispatches.workshop_branch_id', $branchId));

        if ($user->isSuperAdmin() && $request->filled('branch_id')) {
            $branchIdFilter = $request->branch_id;
            $query->where('workshop_branch_id', $branchIdFilter);
            $statsQuery->where('workshop_dispatches.workshop_branch_id', $branchIdFilter);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $query->whereHas('order', function($q) use ($startDate, $endDate) {
                $q->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
            });
            $statsQuery->whereDate('orders.created_at', '>=', $startDate)
                       ->whereDate('orders.created_at', '<=', $endDate);
        }

        $ordersBase = $statsQuery->select('orders.*')->get();

        $totalOrderValue = $ordersBase->sum('total');
        $walkIn = $ordersBase->where('delivery_method', 'pickup')->sum('total');
        $delivery = $ordersBase->where('delivery_method', 'delivery')->sum('total');
        
        $pendingDue = $ordersBase->sum(fn($o) => max(0, $o->total - $o->paid_amount));
        $regularPending = $ordersBase->where('payment_method', '!=', 'credit')->sum(fn($o) => max(0, $o->total - $o->paid_amount));
        $creditOrders = $ordersBase->where('payment_method', 'credit')->sum(fn($o) => max(0, $o->total - $o->paid_amount));
        
        $collectedPayments = $ordersBase->sum('paid_amount');

        $orderIds = $ordersBase->pluck('id')->toArray();
        $transactions = \App\Models\PaymentTransaction::whereIn('order_id', $orderIds)->get();
        
        $cashTotal = $transactions->where('payment_method', 'cash')->sum('amount');
        $cardTotal = $transactions->where('payment_method', 'card')->sum('amount');
        $bankTotal = $transactions->where('payment_method', 'bank')->sum('amount');
        
        $directCash = $ordersBase->where('payment_method', 'cash')->filter(fn($o) => !$transactions->where('order_id', $o->id)->count())->sum('paid_amount');
        $cashTotal += $directCash;
        
        $directCard = $ordersBase->where('payment_method', 'card')->filter(fn($o) => !$transactions->where('order_id', $o->id)->count())->sum('paid_amount');
        $cardTotal += $directCard;
        
        $directBank = $ordersBase->where('payment_method', 'bank')->filter(fn($o) => !$transactions->where('order_id', $o->id)->count())->sum('paid_amount');
        $bankTotal += $directBank;

        $orderQty = $ordersBase->count();
        $totalCustomerCount = $ordersBase->pluck('customer_id')->unique()->count();
        $itemQty = \App\Models\OrderItem::whereIn('order_id', $orderIds)->sum('quantity');

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

        $dispatches = $query->orderByDesc('order_id')->paginate(15, ['*'], 'dispatch_page');

        $cancelledBookings = \App\Models\DeliveryAppointment::with(['customer', 'cancelledBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'cancelled')
            ->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'cancelled_page');
            
        $allBranches = collect();
        if ($user->isSuperAdmin()) {
            $allBranches = \App\Models\Branch::where('type', 'workshop')->get();
        }

        return view('workshop.history', compact('dispatches', 'cancelledBookings', 'stats', 'allBranches'));
    }
}
