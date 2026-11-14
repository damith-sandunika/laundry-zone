<?php

namespace App\Http\Controllers;

use App\Models\SalaryAdvance;
use App\Models\SalaryAdjustment;
use App\Models\SalaryAuditLog;
use App\Models\User;
use App\Services\PayrollCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalaryAdvanceAndAdjustmentController extends Controller
{
    public function storeAdvance(Request $request, User $staff)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'date'   => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $month = Carbon::parse($request->date)->month;
        $year = Carbon::parse($request->date)->year;
        $payroll = PayrollCalculator::calculate($staff, $month, $year);

        if ($request->amount > $payroll->net_salary) {
            return back()->with('error', 'Advance amount cannot exceed payable balance (Rs. ' . number_format($payroll->net_salary, 2) . ').');
        }

        $advance = $staff->salaryAdvances()->create([
            'amount'     => $request->amount,
            'date'       => $request->date,
            'reason'     => $request->reason,
            'created_by' => auth()->id(),
        ]);

        SalaryAuditLog::create([
            'auditable_type' => SalaryAdvance::class,
            'auditable_id'   => $advance->id,
            'action'         => 'created',
            'new_amount'     => $request->amount,
            'reason'         => $request->reason ?: 'Salary advance requested',
            'user_id'        => auth()->id(),
        ]);

        return back()->with('success', 'Salary advance recorded.');
    }

    public function updateAdvance(Request $request, SalaryAdvance $advance)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'date'        => 'required|date',
            'reason'      => 'nullable|string',
            'edit_reason' => 'required|string|min:3',
        ]);

        $oldAmount = $advance->amount;
        $advance->update([
            'amount'          => $request->amount,
            'previous_amount' => $oldAmount,
            'date'            => $request->date,
            'reason'          => $request->filled('reason') ? $request->reason : $advance->reason,
            'edit_reason'     => $request->edit_reason,
            'updated_by'      => auth()->id(),
        ]);

        SalaryAuditLog::create([
            'auditable_type' => SalaryAdvance::class,
            'auditable_id'   => $advance->id,
            'action'         => 'edited',
            'old_amount'     => $oldAmount,
            'new_amount'     => $request->amount,
            'reason'         => $request->edit_reason,
            'user_id'        => auth()->id(),
        ]);

        return back()->with('success', 'Salary advance updated successfully.');
    }

    public function destroyAdvance(Request $request, SalaryAdvance $advance)
    {
        $request->validate(['delete_reason' => 'required|string|min:3']);

        $advance->update([
            'delete_reason' => $request->delete_reason,
            'deleted_by'    => auth()->id(),
        ]);
        $advance->delete();

        SalaryAuditLog::create([
            'auditable_type' => SalaryAdvance::class,
            'auditable_id'   => $advance->id,
            'action'         => 'deleted',
            'old_amount'     => $advance->amount,
            'reason'         => $request->delete_reason,
            'user_id'        => auth()->id(),
        ]);

        return back()->with('success', 'Salary advance deleted successfully.');
    }

    public function storeAdjustment(Request $request, User $staff)
    {
        $request->validate([
            'type'   => 'required|in:allowance,deduction',
            'amount' => 'required|numeric|min:0.01',
            'date'   => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $staff->salaryAdjustments()->create([
            'type'       => $request->type,
            'amount'     => $request->amount,
            'date'       => $request->date,
            'reason'     => $request->reason,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', ucfirst($request->type) . ' recorded successfully.');
    }

    public function destroyAdjustment(SalaryAdjustment $adjustment)
    {
        $adjustment->delete();
        return back()->with('success', 'Adjustment removed successfully.');
    }
}

