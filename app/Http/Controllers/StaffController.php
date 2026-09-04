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
        $branchId = auth()->user()->role === 'super_admin' ? null : auth()->user()->branch_id;

        $query = User::query();
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
        if (auth()->user()->role === 'super_admin') {
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
            'basic_salary' => 'nullable|numeric|min:0',
            'casual_leaves' => 'nullable|integer|min:0',
            'standard_working_hours' => 'nullable|numeric|min:0',
            'ot_rate_per_hour' => 'nullable|numeric|min:0',
        ];
        if (auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin') {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }
        $request->validate($rules);

        $branchId = (auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin') ? $request->branch_id : (auth()->user()->branch_id ?? 1);
        $currentUserRole = auth()->user()->role;

        $role = $request->role;
        // Only super_admin can create admin users. Others (like rider/staff/workshop) are allowed.
        if ($role === 'admin' && $currentUserRole !== 'super_admin') {
            $role = 'staff';
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $role,
            'branch_id' => $branchId,
            'password' => bcrypt($request->password),
            'is_active' => true,
            'nic' => $request->nic,
            'dob' => $request->dob,
            'address' => $request->address,
            'emergency_contact' => $request->emergency_contact,
            'basic_salary' => $request->basic_salary,
            'casual_leaves' => $request->casual_leaves ?? 0,
            'standard_working_hours' => $request->standard_working_hours ?? 8.0,
            'ot_rate_per_hour' => $request->ot_rate_per_hour ?? 0,
        ]);

        return redirect()->back()->with('success', 'Staff member added successfully.');
    }

    public function show(User $staff)
    {
        $salaries = Expense::where('category', 'salary')
            ->where('description', 'like', "%{$staff->name}%")
            ->latest()->get();

        $month = request('month', now()->month);
        $year = request('year', now()->year);
        
        // Calculate OT for the requested month
        $attendancesThisMonth = \App\Models\Attendance::where('user_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();
            
        $totalOtMinutes = $attendancesThisMonth->sum('ot_minutes');
        $otAmount = $totalOtMinutes * ($staff->ot_rate_per_hour / 60);
        
        // Calculate Advances for the requested month
        $advancesThisMonth = $staff->salaryAdvances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
            
        // Calculate Net Salary
        $basicSalary = $staff->basic_salary ?? 0;
        $netSalary = $basicSalary + $otAmount - $advancesThisMonth;
        
        $payroll = (object)[
            'month' => $month,
            'year' => $year,
            'ot_minutes' => $totalOtMinutes,
            'ot_amount' => $otAmount,
            'advances' => $advancesThisMonth,
            'net_salary' => $netSalary,
        ];

        return view('staff.show', compact('staff', 'salaries', 'payroll', 'attendancesThisMonth'));
    }

    public function edit(User $staff)
    {
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
            'basic_salary' => 'nullable|numeric|min:0',
            'casual_leaves' => 'nullable|integer|min:0',
            'standard_working_hours' => 'nullable|numeric|min:0',
            'ot_rate_per_hour' => 'nullable|numeric|min:0',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        if (auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin') {
            $rules['branch_id'] = 'nullable|exists:branches,id';
        }
        $request->validate($rules);

        $data = $request->only(['name', 'email', 'phone', 'role', 'is_active', 'branch_id', 'nic', 'dob', 'address', 'emergency_contact', 'basic_salary', 'casual_leaves', 'standard_working_hours', 'ot_rate_per_hour']);

        // Only super_admin can set role to admin
        if (isset($data['role']) && $data['role'] === 'admin' && auth()->user()->role !== 'super_admin') {
            unset($data['role']); // Keep existing role
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $staff->update($data);

        return redirect()->route('staff.index')->with('success', 'Staff updated.');
    }

    public function destroy(User $staff)
    {
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
        $branchId = auth()->user()->branch_id ?? \App\Models\Branch::first()->id;

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
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        
        $attendancesThisMonth = \App\Models\Attendance::where('user_id', $staff->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();
            
        $totalOtMinutes = $attendancesThisMonth->sum('ot_minutes');
        $otAmount = $totalOtMinutes * ($staff->ot_rate_per_hour / 60);
        
        $advancesThisMonth = $staff->salaryAdvances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');
            
        $basicSalary = $staff->basic_salary ?? 0;
        $netSalary = $basicSalary + $otAmount - $advancesThisMonth;
        
        $payroll = (object)[
            'month' => $month,
            'year' => $year,
            'ot_minutes' => $totalOtMinutes,
            'ot_amount' => $otAmount,
            'advances' => $advancesThisMonth,
            'net_salary' => $netSalary,
        ];

        return view('staff.payslip', compact('staff', 'payroll'));
    }
}

