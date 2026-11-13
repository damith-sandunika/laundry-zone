<?php

namespace App\Actions\Staff;

use App\Models\Expense;
use App\Models\SalaryAuditLog;
use Illuminate\Support\Facades\DB;

class DeleteSalaryAction
{
    public function execute(Expense $expense, string $deleteReason, int $authId): void
    {
        DB::transaction(function () use ($expense, $deleteReason, $authId) {
            $expense->update([
                'delete_reason' => $deleteReason,
                'deleted_by' => $authId,
            ]);
            $expense->delete();

            SalaryAuditLog::create([
                'auditable_type' => Expense::class,
                'auditable_id' => $expense->id,
                'action' => 'deleted',
                'old_amount' => $expense->amount,
                'reason' => $deleteReason,
                'user_id' => $authId,
            ]);
        });
    }
}

