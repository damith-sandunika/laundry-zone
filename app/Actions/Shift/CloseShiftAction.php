<?php

namespace App\Actions\Shift;

use App\Models\BankDeposit;
use App\Models\Branch;
use App\Models\BranchLog;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use Illuminate\Support\Facades\DB;

class CloseShiftAction
{
    public function execute(RegisterSession $session, int $branchId, int $userId): float
    {
        $cashCollected = (float) PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
            ->where('created_at', '>=', $session->opened_at)
            ->where('payment_method', 'cash')
            ->sum('amount');

        $directCashOrders = (float) Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->where('payment_method', 'cash')
            ->whereDoesntHave('paymentTransactions')
            ->sum('paid_amount');

        $cashCollected += $directCashOrders;

        $bankDepositsLogged = (float) BankDeposit::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $finalCash = $session->opening_cash + $cashCollected - $bankDepositsLogged;

        DB::transaction(function () use ($session, $finalCash, $branchId, $userId) {
            $session->update([
                'closing_cash' => $finalCash,
                'closed_at'    => now(),
                'status'       => 'closed',
                'closed_by'    => $userId,
            ]);

            Branch::where('id', $branchId)->update(['cash_balance' => $finalCash]);

            BranchLog::create([
                'branch_id'   => $branchId,
                'user_id'     => $userId,
                'action'      => 'shift_closed',
                'description' => 'Shift closed. Final Cash: Rs. ' . number_format($finalCash, 2),
            ]);

            session()->forget("branch_shift_open_{$branchId}");
        });

        return $finalCash;
    }
}
