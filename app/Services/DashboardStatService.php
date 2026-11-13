<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PackagePayment;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use App\Models\BankDeposit;

class DashboardStatService
{
    protected DashboardChartService $chartService;

    public function __construct(?DashboardChartService $chartService = null)
    {
        $this->chartService = $chartService ?? new DashboardChartService();
    }

    public function getShiftMetrics(RegisterSession $activeSession, int $branchId): array
    {
        $cashCollected = PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', $activeSession->opened_at)
            ->where('payment_method', 'cash')->sum('amount');

        $cardTx = PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', $activeSession->opened_at)->where('payment_method', 'card');
        $cardPaymentsCount = $cardTx->count();
        $cardPaymentsAmount = $cardTx->sum('amount');

        $bankTx = PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', $activeSession->opened_at)->where('payment_method', 'bank');
        $bankTransfersCount = $bankTx->count();
        $bankTransfersAmount = $bankTx->sum('amount');

        $cashCollected += Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $activeSession->opened_at)
            ->where('payment_method', 'cash')->whereDoesntHave('paymentTransactions')->sum('paid_amount');

        $bankTransfersAmount += Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $activeSession->opened_at)
            ->where('payment_method', 'bank')->whereDoesntHave('paymentTransactions')->sum('paid_amount');

        $expensesLogged = Expense::where('branch_id', $branchId)
            ->where('created_at', '>=', $activeSession->opened_at)->sum('amount');

        $pendingPayments = Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $activeSession->opened_at)
            ->selectRaw('SUM(total - paid_amount) as pending_total')->value('pending_total') ?? 0;

        $bankDepositsLogged = BankDeposit::where('branch_id', $branchId)
            ->where('created_at', '>=', $activeSession->opened_at)->sum('amount');

        $salesMetrics = $this->calculateSalesMetrics($activeSession->opened_at, $branchId);

        $currentBalance = $activeSession->opening_cash + $cashCollected + $cardPaymentsAmount + $bankTransfersAmount - $expensesLogged - $bankDepositsLogged;
        $cashBalance = $activeSession->opening_cash + $cashCollected - $expensesLogged - $bankDepositsLogged;

        $branchProfitPercent = $activeSession->branch->branch_profit_percentage ?? 0;
        $branchCommission = $salesMetrics['commissionableSales'] * ($branchProfitPercent / 100);
        $totalBranchRevenue = $branchCommission + $salesMetrics['totalDeliveryFees'];
        $netIncome = $totalBranchRevenue - $expensesLogged;
        $totalPaymentsCollected = $cashCollected + $cardPaymentsAmount + $bankTransfersAmount;

        return [
            'current_balance' => $currentBalance,
            'cash_balance' => $cashBalance,
            'cash_collected' => $cashCollected,
            'card_payments' => $cardPaymentsCount,
            'card_payments_amount' => $cardPaymentsAmount,
            'bank_transfers_count' => $bankTransfersCount,
            'bank_transfers' => $bankTransfersAmount,
            'total_payments_collected' => $totalPaymentsCollected,
            'gross_sales' => $salesMetrics['grossSales'],
            'commissionable_sales' => $salesMetrics['commissionableSales'],
            'total_delivery_fees' => $salesMetrics['totalDeliveryFees'],
            'branch_commission' => $branchCommission,
            'total_branch_revenue' => $totalBranchRevenue,
            'net_income' => $netIncome,
            'branch_profit_percentage' => $branchProfitPercent,
            'expenses_logged' => $expensesLogged,
            'pending_payments' => $pendingPayments,
        ];
    }

    public function getTodayMetrics(?int $branchId): array
    {
        $startOfDay = now()->startOfDay();

        $txQuery = PaymentTransaction::where('created_at', '>=', $startOfDay);
        if ($branchId) {
            $txQuery->whereHas('order', fn ($q) => $q->where('branch_id', $branchId));
        }

        $cashCollected = (clone $txQuery)->where('payment_method', 'cash')->sum('amount');
        $cardTx = (clone $txQuery)->where('payment_method', 'card');
        $cardPaymentsCount = $cardTx->count();
        $cardPaymentsAmount = $cardTx->sum('amount');

        $bankTx = (clone $txQuery)->where('payment_method', 'bank');
        $bankTransfersCount = $bankTx->count();
        $bankTransfersAmount = $bankTx->sum('amount');

        $directCashQuery = Order::where('created_at', '>=', $startOfDay)->where('payment_method', 'cash')->whereDoesntHave('paymentTransactions');
        if ($branchId) {
            $directCashQuery->where('branch_id', $branchId);
        }
        $cashCollected += $directCashQuery->sum('paid_amount');

        $directBankQuery = Order::where('created_at', '>=', $startOfDay)->where('payment_method', 'bank')->whereDoesntHave('paymentTransactions');
        if ($branchId) {
            $directBankQuery->where('branch_id', $branchId);
        }
        $bankTransfersAmount += $directBankQuery->sum('paid_amount');

        $expenseQuery = Expense::where('created_at', '>=', $startOfDay);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $expensesLogged = $expenseQuery->sum('amount');

        $pendingQuery = Order::where('created_at', '>=', $startOfDay);
        if ($branchId) {
            $pendingQuery->where('branch_id', $branchId);
        }
        $pendingPayments = $pendingQuery->selectRaw('SUM(total - paid_amount) as pending_total')->value('pending_total') ?? 0;

        $depositQuery = BankDeposit::where('created_at', '>=', $startOfDay);
        if ($branchId) {
            $depositQuery->where('branch_id', $branchId);
        }
        $bankDepositsLogged = $depositQuery->sum('amount');

        $salesMetrics = $this->calculateSalesMetrics($startOfDay, $branchId);
        $branch = $branchId ? Branch::find($branchId) : null;
        $branchProfitPercent = $branch->branch_profit_percentage ?? 0;
        $branchCommission = $salesMetrics['commissionableSales'] * ($branchProfitPercent / 100);
        $totalBranchRevenue = $branchCommission + $salesMetrics['totalDeliveryFees'];
        $netIncome = $totalBranchRevenue - $expensesLogged;
        $totalPaymentsCollected = $cashCollected + $cardPaymentsAmount + $bankTransfersAmount;

        return [
            'current_balance' => $cashCollected + $cardPaymentsAmount + $bankTransfersAmount - $expensesLogged - $bankDepositsLogged,
            'cash_balance' => $cashCollected - $expensesLogged - $bankDepositsLogged,
            'cash_collected' => $cashCollected,
            'card_payments' => $cardPaymentsCount,
            'card_payments_amount' => $cardPaymentsAmount,
            'bank_transfers_count' => $bankTransfersCount,
            'bank_transfers' => $bankTransfersAmount,
            'total_payments_collected' => $totalPaymentsCollected,
            'gross_sales' => $salesMetrics['grossSales'],
            'commissionable_sales' => $salesMetrics['commissionableSales'],
            'total_delivery_fees' => $salesMetrics['totalDeliveryFees'],
            'branch_commission' => $branchCommission,
            'total_branch_revenue' => $totalBranchRevenue,
            'net_income' => $netIncome,
            'branch_profit_percentage' => $branchProfitPercent,
            'expenses_logged' => $expensesLogged,
            'pending_payments' => $pendingPayments,
        ];
    }

    private function calculateSalesMetrics($since, ?int $branchId): array
    {
        $orderQuery = Order::where('created_at', '>=', $since);
        if ($branchId) {
            $orderQuery->where('branch_id', $branchId);
        }

        $orderMetrics = $orderQuery->selectRaw('
                SUM(COALESCE(total, 0)) as gross_sales,
                SUM(COALESCE(delivery_fee, 0)) as total_delivery_fees,
                SUM(CASE WHEN COALESCE(total, 0) > COALESCE(delivery_fee, 0) THEN COALESCE(total, 0) - COALESCE(delivery_fee, 0) ELSE 0 END) as commissionable_sales
            ')->first();

        $grossSales = (float) ($orderMetrics->gross_sales ?? 0);
        $totalDeliveryFees = (float) ($orderMetrics->total_delivery_fees ?? 0);
        $commissionableSales = (float) ($orderMetrics->commissionable_sales ?? 0);

        $packageQuery = PackagePayment::where('created_at', '>=', $since);
        if ($branchId) {
            $packageQuery->whereHas('customerPackage', fn ($q) => $q->where('branch_id', $branchId));
        }
        $totalPackageSales = $packageQuery->sum('amount');

        $grossSales += $totalPackageSales;
        $commissionableSales += $totalPackageSales;

        return [
            'grossSales' => $grossSales,
            'commissionableSales' => $commissionableSales,
            'totalDeliveryFees' => $totalDeliveryFees,
        ];
    }

    public function getChartData(?int $branchId): array
    {
        return $this->chartService->getChartData($branchId);
    }

    public function getEmptyMetrics(): array
    {
        return [
            'current_balance' => 0,
            'cash_balance' => 0,
            'cash_collected' => 0,
            'card_payments' => 0,
            'card_payments_amount' => 0,
            'bank_transfers_count' => 0,
            'bank_transfers' => 0,
            'total_payments_collected' => 0,
            'gross_sales' => 0,
            'commissionable_sales' => 0,
            'total_delivery_fees' => 0,
            'branch_commission' => 0,
            'total_branch_revenue' => 0,
            'net_income' => 0,
            'branch_profit_percentage' => 0,
            'expenses_logged' => 0,
            'pending_payments' => 0,
        ];
    }
}
