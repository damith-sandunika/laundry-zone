<?php

namespace App\Http\Controllers;

use App\Actions\Staff\CreateStaffAction;
use App\Actions\Staff\DeleteSalaryAction;
use App\Actions\Staff\PaySalaryAction;
use App\Actions\Staff\UpdateSalaryAction;
use App\Actions\Staff\UpdateStaffAction;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\BranchMonthlyPlan;
use App\Models\Expense;
use App\Models\Leave;
use App\Models\User;
use App\Services\PayrollCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StaffController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $user = auth()->user();
        $isGlobalView = $user->role === 'super_admin' || $user->isHrManager();
        $branchId = $isGlobalView ? null : $this->getActiveBranchId();

        $query = User::with('branch');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $staff = $query->orderBy('name', 'asc')->get();

        $salaryExpensesQuery = Expense::where('category', 'salary');
        if ($branchId) {
            $salaryExpensesQuery->where('branch_id', $branchId);
        }
        $salaries = $salaryExpensesQuery->latest()->get();

        $branches = $isGlobalView ? Branch::all() : [];

        return view('staff.index', compact('staff', 'salaries', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('staff.create', compact('branches'));
    }

    public function store(Request $request, CreateStaffAction $action)
    {
        $this->authorize('create', User::class);
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'role' => 'required|in:branch_admin,staff,rider,workshop_admin,super_admin,hr_manager,accountant,accountant_and_hr_manager',
            'password' => 'required|string|min:8',
            'nic' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'casual_leaves' => 'nullable|integer|min:0',
            'standard_working_hours' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'basic_salary' => 'nullable|numeric|min:0',
        ];
        if (Gate::allows('manage-users')) {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }
        $request->validate($rules);

        $action->execute($request->all(), $this->getActiveBranchId());

        return redirect()->back()->with('success', 'Staff member added successfully.');
    }

    public function show(User $staff)
    {
        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        $monthName = date('F Y', mktime(0, 0, 0, $month, 10, $year));

        $salaries = Expense::withTrashed()
            ->with(['createdBy', 'updatedBy', 'deletedBy', 'auditLogs.user'])
            ->where('category', 'salary')
            ->where('description', 'like', "%{$staff->name}%")
            ->where('description', 'like', "%{$monthName}%")
            ->latest()->get();

        $attendancesThisMonth = Attendance::where('user_id', $staff->id)
            ->whereMonth('date', $month)->whereYear('date', $year)->get();

        $defaultBranch = Branch::first()->id ?? null;
        $branchPlan = BranchMonthlyPlan::where('branch_id', $staff->branch_id ?? $defaultBranch)
            ->where('year', $year)->where('month', $month)->first();

        $leavesThisMonth = Leave::where('user_id', $staff->id)
            ->where(function ($q) use ($month, $year) {
                $q->whereMonth('start_date', $month)->whereYear('start_date', $year)
                  ->orWhere(fn($q2) => $q2->whereMonth('end_date', $month)->whereYear('end_date', $year));
            })->latest('start_date')->get();

        $payroll = PayrollCalculator::calculate($staff, $month, $year);

        return view('staff.show', compact('staff', 'salaries', 'payroll', 'attendancesThisMonth', 'branchPlan', 'leavesThisMonth'));
    }

    public function edit(User $staff)
    {
        $this->authorize('update', $staff);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff, UpdateStaffAction $action)
    {
        $this->authorize('update', $staff);
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$staff->id,
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:branch_admin,staff,super_admin,rider,workshop_admin,hr_manager,accountant,accountant_and_hr_manager',
            'is_active' => 'required|boolean',
            'nic' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'casual_leaves' => 'nullable|integer|min:0',
            'standard_working_hours' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'basic_salary' => 'nullable|numeric|min:0',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        if (Gate::allows('manage-users')) {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }
        $request->validate($rules);

        $action->execute($staff, $request->all());

        return redirect()->route('staff.index')->with('success', 'Staff updated.');
    }

    public function destroy(User $staff)
    {
        $this->authorize('delete', $staff);
        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'Staff member deleted.');
    }

    public function paySalary(Request $request, PaySalaryAction $action)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|string',
        ]);

        $action->execute($request->user_id, (float) $request->amount, $request->month, $this->getActiveBranchId(), auth()->id());
        return redirect()->back()->with('success', 'Salary payment recorded successfully.');
    }

    public function updateSalary(Request $request, Expense $expense, UpdateSalaryAction $action)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'nullable|date',
            'description' => 'nullable|string',
            'edit_reason' => 'required|string|min:3',
        ]);

        $action->execute($expense, $request->all(), auth()->id());
        return redirect()->back()->with('success', 'Salary payment record updated successfully.');
    }

    public function destroySalary(Request $request, Expense $expense, DeleteSalaryAction $action)
    {
        $request->validate(['delete_reason' => 'required|string|min:3']);
        $action->execute($expense, $request->delete_reason, auth()->id());
        return redirect()->back()->with('success', 'Salary payment record deleted successfully.');
    }

    public function downloadPayslip(User $staff, Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $payroll = PayrollCalculator::calculate($staff, $month, $year);
        $monthName = date('F Y', mktime(0, 0, 0, $month, 10, $year));

        $salaryPayments = Expense::with('createdBy')
            ->where('category', 'salary')
            ->where('description', 'like', "%{$staff->name}%")
            ->where('description', 'like', "%{$monthName}%")
            ->latest()->get();

        $salaryPayment = $salaryPayments->first();
        $attendances = Attendance::where('user_id', $staff->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->orderBy('date', 'asc')->get();

        return view('staff.payslip', compact('staff', 'payroll', 'salaryPayment', 'salaryPayments', 'attendances'));
    }
}
