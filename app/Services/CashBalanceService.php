<?php

namespace App\Services;

use App\Models\Branch;

class CashBalanceService
{
    /**
     * Increment the cash balance for a specific branch.
     *
     * @param int $branchId
     * @param float $amount
     * @return void
     */
    public function incrementCashBalance($branchId, $amount)
    {
        $branch = Branch::find($branchId);
        if ($branch && $amount > 0) {
            $branch->cash_balance += $amount;
            $branch->save();
        }
    }

    /**
     * Decrement the cash balance for a specific branch.
     *
     * @param int $branchId
     * @param float $amount
     * @return void
     */
    public function decrementCashBalance($branchId, $amount)
    {
        $branch = Branch::find($branchId);
        if ($branch && $amount > 0) {
            $branch->cash_balance -= $amount;
            $branch->save();
        }
    }
}
