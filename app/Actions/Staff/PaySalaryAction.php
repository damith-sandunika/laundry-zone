<?php

namespace App\Actions\Staff;

use App\Models\Expense;
use App\Models\SalaryAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaySalaryAction
{
    public function execute(int $userId, float $amount, string $month, ?int $branchId, int $authId): Expense
    {
        return DB::transaction(function () use ($userId, $amount, $month, $branchId, $authId) {
            $user = User::findOrFail($userId);

            $expense = Expense::create([
                'branch_id' => $branchId,
                'category' => 'salary',
                'amount' => $amount,
                'description' => "Salary payment for {$user->name} ({$month})",
                'expense_date' => now(),
                'created_by' => $authId,
            ]);

            SalaryAuditLog::create([
                'auditable_type' => Expense::class,
                'auditable_id' => $expense->id,
                'action' => 'created',
                'new_amount' => $amount,
                'reason' => "Salary payment for {$month}",
                'user_id' => $authId,
            ]);

            return $expense;
        });
    }
}

