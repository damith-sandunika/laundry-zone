<?php

namespace App\Services\Reports;

use App\Models\Branch;
use App\Models\Expense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BranchPettyCashService
{
    public function getSummary(Collection $activeBranches, string $startDate, string $endDate): Collection
    {
        $branchIds = $activeBranches->pluck('id');

        $expensesByBranch = Expense::selectRaw('branch_id, SUM(amount) as total_expenses')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('branch_id', $branchIds)
            ->groupBy('branch_id')
            ->pluck('total_expenses', 'branch_id');

        $topupsByBranch = DB::table('petty_cash_topups')
            ->selectRaw('branch_id, SUM(amount) as total_topups')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('branch_id', $branchIds)
            ->groupBy('branch_id')
            ->pluck('total_topups', 'branch_id');

        return $activeBranches->map(function ($branch) use ($expensesByBranch, $topupsByBranch) {
            $branch->total_expenses = $expensesByBranch[$branch->id] ?? 0;
            $branch->total_topups = $topupsByBranch[$branch->id] ?? 0;
            return $branch;
        });
    }
}

