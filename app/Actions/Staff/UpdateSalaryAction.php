<?php

namespace App\Actions\Staff;

use App\Models\Expense;
use App\Models\SalaryAuditLog;
use Illuminate\Support\Facades\DB;

class UpdateSalaryAction
{
    public function execute(Expense $expense, array $data, int $authId): Expense
    {
        return DB::transaction(function () use ($expense, $data, $authId) {
            $oldAmount = $expense->amount;

            $updatePayload = [
                'amount' => $data['amount'],
                'previous_amount' => $oldAmount,
                'edit_reason' => $data['edit_reason'],
                'updated_by' => $authId,
            ];

            if (!empty($data['expense_date'])) {
                $updatePayload['expense_date'] = $data['expense_date'];
            }
            if (!empty($data['description'])) {
                $updatePayload['description'] = $data['description'];
            }

            $expense->update($updatePayload);

            SalaryAuditLog::create([
                'auditable_type' => Expense::class,
                'auditable_id' => $expense->id,
                'action' => 'edited',
                'old_amount' => $oldAmount,
                'new_amount' => $data['amount'],
                'reason' => $data['edit_reason'],
                'user_id' => $authId,
            ]);

            return $expense;
        });
    }
}

