<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BankDeposit;
use App\Models\Branch;
use App\Models\BranchReopenRequest;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkshopDispatch;

class WorkshopDashboardService
{
    public function getDashboardData(int $branchId): array
    {
        $activeSession = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $suggestedOpeningCash = 0;
        if (!$activeSession) {
            $lastSession = RegisterSession::withoutGlobalScope(BranchScope::class)
                ->where('branch_id', $branchId)
                ->where('status', 'closed')
                ->orderBy('closed_at', 'desc')
                ->first();
            $suggestedOpeningCash = $lastSession ? $lastSession->closing_cash : 0;
        }

        $stats = $this->calculateShiftStats($branchId, $activeSession);

        $recentOrders = WorkshopDispatch::with(['order.customer', 'branch'])
            ->join('orders', 'workshop_dispatches.order_id', '=', 'orders.id')
            ->select('workshop_dispatches.*')
            ->where('workshop_branch_id', $branchId)
            ->whereIn('workshop_dispatches.status', ['dispatched_to_workshop', 'received_at_workshop', 'processing'])
            ->orderByRaw('orders.due_date IS NULL')
            ->orderBy('orders.due_date', 'asc')
            ->get();

        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->where('branch_id', $branchId)->get();
        $allInventory = InventoryItem::where('branch_id', $branchId)->get();

        $currentBranch = Branch::find($branchId);
        $branchName = $currentBranch->name ?? 'Workshop';
        $branchStaff = User::where('branch_id', $branchId)->where('role', 'staff')->get();

        $activeAttendances = $activeSession
            ? Attendance::where('register_session_id', $activeSession->id)->get()->keyBy('user_id')
            : collect();

        $chartData = $this->generateChartData($branchId);
        $reopenData = $this->checkShiftReopenStatus($branchId, $activeSession);

        return array_merge([
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockItems' => $lowStockItems,
            'allInventory' => $allInventory,
            'activeSession' => $activeSession,
            'suggestedOpeningCash' => $suggestedOpeningCash,
            'branchName' => $branchName,
            'chartData' => $chartData,
            'currentBranch' => $currentBranch,
            'branchStaff' => $branchStaff,
            'activeAttendances' => $activeAttendances,
        ], $reopenData);
    }

    protected function calculateShiftStats(int $branchId, ?RegisterSession $activeSession): array
    {
        $cashCollected = 0;
        $cardPaymentsAmount = 0;
        $bankTransfers = 0;
        $cashBalance = 0;
        $currentBalance = 0;
        $totalPaymentsCollected = 0;
        $workshopDirectDeliveryFees = 0;
        $workshopDirectCommissionableSales = 0;
        $workshopGrossSales = 0;
        $branchDeliveryFees = 0;
        $commissionableSales = 0;
        $branchCommissionsGiven = 0;
        $branchBreakdown = [];
        $branchPendingPayments = 0;
        $ourPendingPayments = 0;
        $expensesLogged = 0;

        if ($activeSession) {
            $cashCollected = (float) PaymentTransaction::whereHas('order', fn($q) => $q->where('branch_id', $branchId))
                ->where(fn($q) => $q->where('created_at', '>=', $activeSession->opened_at)->orWhere('collected_from_rider_at', '>=', $activeSession->opened_at))
                ->where('payment_method', 'cash')->sum('amount');

            $cardPaymentsAmount = (float) PaymentTransaction::whereHas('order', fn($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'card')->sum('amount');

            $bankTransfers = (float) PaymentTransaction::whereHas('order', fn($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'bank')->sum('amount');

            $cashCollected += (float) Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')->whereDoesntHave('paymentTransactions')->sum('paid_amount');

            $bankTransfers += (float) Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'bank')->whereDoesntHave('paymentTransactions')->sum('paid_amount');

            $expensesLogged = (float) Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)->sum('amount');

            $bankDepositsLogged = (float) BankDeposit::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)->sum('amount');

            $cashBalance = $activeSession->opening_cash + $cashCollected - $bankDepositsLogged;
            $currentBalance = $cashBalance;
            $totalPaymentsCollected = $cashCollected + $cardPaymentsAmount + $bankTransfers;

            // Direct transactions processing
            $shiftPayments = PaymentTransaction::with('order')
                ->whereHas('order', fn($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)->get();

            $shiftOrderIds = $shiftPayments->pluck('order_id')->filter()->unique()->toArray();
            $allOrderTransactions = PaymentTransaction::whereIn('order_id', $shiftOrderIds)
                ->select('id', 'order_id', 'amount')->orderBy('id')->get()->groupBy('order_id');

            foreach ($shiftPayments as $payment) {
                if ($payment->order) {
                    $order = $payment->order;
                    $deliveryFee = $order->delivery_fee ?? 0;
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

            // Direct orders without transactions
            $directOrders = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->whereDoesntHave('paymentTransactions')->whereIn('payment_method', ['cash', 'bank'])->get();

            foreach ($directOrders as $order) {
                $deliveryFee = $order->delivery_fee ?? 0;
                $paid = $order->paid_amount;
                $paidToDelivery = min($paid, $deliveryFee);
                $workshopDirectDeliveryFees += $paidToDelivery;
                $workshopDirectCommissionableSales += ($paid - $paidToDelivery);
            }

            // Workshop dispatches breakdown
            $shiftDispatches = WorkshopDispatch::with(['order', 'branch'])
                ->where('workshop_branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)->get();

            $ourPendingPayments = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->selectRaw('SUM(total - paid_amount) as pending_total')->value('pending_total') ?? 0;

            foreach ($shiftDispatches as $dispatch) {
                if ($dispatch->order && $dispatch->branch) {
                    $bId = $dispatch->branch->id;
                    if ($bId == $branchId) continue;

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
                    $branchPendingPayments += max(0, $orderTotal - $order->paid_amount);

                    $commTotal = max(0, $orderTotal - $deliveryFee);
                    if ($commTotal > 0) {
                        $workshopGrossSales += $commTotal;
                        $branchDeliveryFees += $deliveryFee;
                        $commissionableSales += $commTotal;

                        $branchProfitPercent = $dispatch->branch->branch_profit_percentage ?? 0;
                        $branchComm = $commTotal * ($branchProfitPercent / 100);
                        $branchCommissionsGiven += $branchComm;

                        $branchBreakdown[$bId]['gross_sales'] += $commTotal;
                        $branchBreakdown[$bId]['branch_commission'] += $branchComm;
                        $branchBreakdown[$bId]['workshop_commission'] += ($commTotal - $branchComm);
                    }
                }
            }
        }

        $workshopCommissionFromBranches = $commissionableSales - $branchCommissionsGiven;
        $totalSalesRevenue = $workshopDirectCommissionableSales + $workshopCommissionFromBranches;
        $netIncome = $totalSalesRevenue + $workshopDirectDeliveryFees;

        $receivedCount = WorkshopDispatch::where('workshop_branch_id', $branchId)->where('status', 'received_at_workshop')->count();
        $processingCount = WorkshopDispatch::where('workshop_branch_id', $branchId)->where('status', 'processing')->count();
        $doneCount = WorkshopDispatch::where('workshop_branch_id', $branchId)->where('status', 'completed_at_workshop')->count();

        return [
            'active_orders' => $receivedCount,
            'pending_deliveries' => $processingCount,
            'completed_today' => $doneCount,
            'total_customers' => 0,
            'current_balance' => $currentBalance,
            'cash_balance' => $cashBalance,
            'cash_collected' => $cashCollected,
            'card_payments' => 0,
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
            'expenses_logged' => $expensesLogged,
            'our_pending_payments' => $ourPendingPayments,
            'branch_pending_payments' => $branchPendingPayments,
        ];
    }

    protected function generateChartData(int $branchId): array
    {
        $todayLabels = []; $todayPending = []; $todayDelivered = [];
        for ($h = 0; $h < 8; $h++) {
            $start = now()->startOfDay()->addHours($h * 3);
            $end = $start->copy()->addHours(3);
            $todayLabels[] = $start->format('H:i');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $todayPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $todayDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        $weeklyLabels = []; $weeklyPending = []; $weeklyDelivered = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $weeklyLabels[] = $day->format('D');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereDate('created_at', $day->toDateString());
            $weeklyPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $weeklyDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        $monthlyLabels = []; $monthlyPending = []; $monthlyDelivered = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end = $start->copy()->endOfWeek();
            $monthlyLabels[] = 'Wk '.$start->format('d/m');
            $q = WorkshopDispatch::where('workshop_branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $monthlyPending[] = (clone $q)->whereIn('status', ['received_at_workshop', 'processing'])->count();
            $monthlyDelivered[] = (clone $q)->where('status', 'completed_at_workshop')->count();
        }

        return [
            'today' => ['labels' => $todayLabels, 'pending' => $todayPending, 'delivered' => $todayDelivered],
            'weekly' => ['labels' => $weeklyLabels, 'pending' => $weeklyPending, 'delivered' => $weeklyDelivered],
            'monthly' => ['labels' => $monthlyLabels, 'pending' => $monthlyPending, 'delivered' => $monthlyDelivered],
        ];
    }

    protected function checkShiftReopenStatus(int $branchId, ?RegisterSession $activeSession): array
    {
        $today = now()->format('Y-m-d');
        $closedSessionsTodayCount = RegisterSession::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $branchId)->where('status', 'closed')->whereDate('closed_at', $today)->count();

        $requireApprovalSetting = Setting::get('require_superadmin_approval_to_reopen_shift', '1') == '1';
        $requiresApprovalToOpen = false;
        $pendingReopenRequest = null;
        $hasApprovedReopenRequest = false;

        if ($requireApprovalSetting && $closedSessionsTodayCount > 0 && !$activeSession) {
            $approvedRequest = BranchReopenRequest::where('branch_id', $branchId)
                ->where('request_date', $today)->where('status', 'approved')->latest()->first();

            if ($approvedRequest) {
                $sessionsAfterApproval = RegisterSession::withoutGlobalScope(BranchScope::class)
                    ->where('branch_id', $branchId)->where('opened_at', '>=', $approvedRequest->reviewed_at)->count();
                if ($sessionsAfterApproval === 0) {
                    $hasApprovedReopenRequest = true;
                } else {
                    $requiresApprovalToOpen = true;
                }
            } else {
                $requiresApprovalToOpen = true;
            }

            if ($requiresApprovalToOpen) {
                $pendingReopenRequest = BranchReopenRequest::where('branch_id', $branchId)
                    ->where('request_date', $today)->where('status', 'pending')->latest()->first();
            }
        }

        return [
            'requiresApprovalToOpen' => $requiresApprovalToOpen,
            'pendingReopenRequest' => $pendingReopenRequest,
            'hasApprovedReopenRequest' => $hasApprovedReopenRequest,
            'closedSessionsTodayCount' => $closedSessionsTodayCount,
        ];
    }
}
