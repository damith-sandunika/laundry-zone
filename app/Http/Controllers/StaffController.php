<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $branchId = \Illuminate\Support\Facades\Gate::allows('is-super-admin') ? null : $this->getActiveBranchId();

        $query = User::with('branch');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $staff = $query->orderBy('name', 'asc')->get();

        // Get expenses related to staff salary
        $salaryExpensesQuery = Expense::where('category', 'salary');
        if ($branchId) {
            $salaryExpensesQuery->where('branch_id', $branchId);
        }
        $salaries = $salaryExpensesQuery->latest()->get();

        $branches = [];
        if (\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            $branches = Branch::all();
        }

        return view('staff.index', compact('staff', 'salaries', 'branches'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        return view('staff.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'role' => 'required|in:admin,branch_admin,staff,rider,workshop_admin',
            'password' => 'required|string|min:8',
            'nic' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'casual_leaves' => 'nullable|integer|min:0',
            'standard_working_hours' => 'nullable|numeric|min:0',
            'ot_rate_per_hour' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
        ];
        $this->authorize('create', User::class);
        if (\Illuminate\Support\Facades\Gate::allows('manage-users')) {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }
        $request->validate($rules);

        $branchId = \Illuminate\Support\Facades\Gate::allows('manage-users') ? $request->branch_id : $this->getActiveBranchId();
        $currentUserRole = auth()->user()->role;

        $role = $request->role;
        // Only super_admin can create admin users. Others (like rider/staff/workshop) are allowed.
        if ($role === 'admin' && $currentUserRole !== 'super_admin') {
            $role = 'staff';
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $role,
            'branch_id' => $branchId,
            'password' => bcrypt($request->password),
            'is_active' => true,
        ]);

        $user->profile()->create([
            'nic' => $request->nic,
            'dob' => $request->dob,
            'address' => $request->address,
            'emergency_contact' => $request->emergency_contact,
            'vehicle_number' => $request->vehicle_number,
            'casual_leaves' => $request->casual_leaves ?? 0,
            'standard_working_hours' => $request->standard_working_hours ?? 8.0,
            'ot_rate_per_hour' => $request->ot_rate_per_hour ?? 0,
            'hourly_rate' => $request->hourly_rate ?? 0,
        ]);

        return redirect()->back()->with('success', 'Staff member added successfully.');
    }

    public function show(User $staff)
    {
        $salaries = Expense::where('category', 'salary')
            ->where('description', 'like', "%{$staff->name}%")
            ->latest()->get();

        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        
        $attendancesThisMonth = \App\Models\Attendance::where('user_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();
            
        // Fetch branch monthly plan for this month
        $defaultBranch = \App\Models\Branch::first()->id ?? null;
        $branchPlan = \App\Models\BranchMonthlyPlan::where('branch_id', $staff->branch_id ?? $defaultBranch)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
            
        $payroll = \App\Services\PayrollCalculator::calculate($staff, $month, $year);

        return view('staff.show', compact('staff', 'salaries', 'payroll', 'attendancesThisMonth', 'branchPlan'));
    }

    public function edit(User $staff)
    {
        $this->authorize('update', $staff);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$staff->id,
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:admin,branch_admin,staff,super_admin,rider,workshop_admin',
            'is_active' => 'required|boolean',
            'nic' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'casual_leaves' => 'nullable|integer|min:0',
            'standard_working_hours' => 'nullable|numeric|min:0',
            'ot_rate_per_hour' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        $this->authorize('update', $staff);
        if (\Illuminate\Support\Facades\Gate::allows('manage-users')) {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }
        $request->validate($rules);

        $data = $request->only(['name', 'email', 'phone', 'role', 'is_active', 'branch_id']);
        $profileData = $request->only(['nic', 'dob', 'address', 'emergency_contact', 'vehicle_number', 'casual_leaves', 'standard_working_hours', 'ot_rate_per_hour', 'hourly_rate']);
        
        $profileData['casual_leaves'] = $profileData['casual_leaves'] ?? 0;
        $profileData['standard_working_hours'] = $profileData['standard_working_hours'] ?? 8.0;
        $profileData['ot_rate_per_hour'] = $profileData['ot_rate_per_hour'] ?? 0;
        $profileData['hourly_rate'] = $profileData['hourly_rate'] ?? 0;

        // Only super_admin can set role to admin
        if (isset($data['role']) && $data['role'] === 'admin' && !\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            unset($data['role']); // Keep existing role
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $staff->update($data);
        
        if ($staff->profile) {
            $staff->profile->update($profileData);
        } else {
            $staff->profile()->create($profileData);
        }

        return redirect()->route('staff.index')->with('success', 'Staff updated.');
    }

    public function destroy(User $staff)
    {
        $this->authorize('delete', $staff);
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff member deleted.');
    }

    // Pay Salary (registers an expense)
    public function paySalary(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $branchId = $this->getActiveBranchId();

        Expense::create([
            'branch_id' => $branchId,
            'category' => 'salary',
            'amount' => $request->amount,
            'description' => "Salary payment for {$user->name} ({$request->month})",
            'expense_date' => now(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Salary payment recorded successfully.');
    }

    public function downloadPayslip(User $staff, Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        
        $payroll = \App\Services\PayrollCalculator::calculate($staff, $month, $year);
        $monthName = date('F Y', mktime(0, 0, 0, $month, 10, $year));

        $salaryPayment = Expense::with('createdBy')
            ->where('category', 'salary')
            ->where('description', 'like', "%{$staff->name}%")
            ->where('description', 'like', "%{$monthName}%")
            ->latest()
            ->first();

        $attendances = \App\Models\Attendance::where('user_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        return view('staff.payslip', compact('staff', 'payroll', 'salaryPayment', 'attendances'));
    }
}

