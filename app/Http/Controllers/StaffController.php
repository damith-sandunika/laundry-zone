<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        
        $query = \App\Models\User::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $staff = $query->orderBy('name', 'asc')->get();

        // Get expenses related to staff salary
        $salaryExpensesQuery = \App\Models\Expense::where('category', 'salary');
        if ($branchId) {
            $salaryExpensesQuery->where('branch_id', $branchId);
        }
        $salaries = $salaryExpensesQuery->latest()->get();

        $branches = [];
        if (auth()->user()->role === 'super_admin') {
            $branches = \App\Models\Branch::all();
        }

        return view('staff.index', compact('staff', 'salaries', 'branches'));
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
        ];
        if (auth()->user()->role === 'super_admin') {
            $rules['branch_id'] = 'required|exists:branches,id';
        }
        $request->validate($rules);

        $branchId = auth()->user()->role === 'super_admin' ? $request->branch_id : (auth()->user()->branch_id ?? 1);
        $currentUserRole = auth()->user()->role;
        
        $role = $request->role;
        // Only super_admin can create admin users. Others (like rider/staff/workshop) are allowed.
        if ($role === 'admin' && $currentUserRole !== 'super_admin') {
            $role = 'staff';
        }

        \App\Models\User::create([
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
        ]);

        return redirect()->back()->with('success', 'Staff member added successfully.');
    }

    public function show(\App\Models\User $staff)
    {
        $salaries = \App\Models\Expense::where('category', 'salary')
            ->where('description', 'like', "%{$staff->name}%")
            ->latest()->get();
        return view('staff.show', compact('staff', 'salaries'));
    }

    public function edit(\App\Models\User $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, \App\Models\User $staff)
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email,' . $staff->id,
            'phone'     => 'nullable|string|max:50',
            'role'      => 'required|in:admin,branch_admin,staff,super_admin,rider,workshop_admin',
            'is_active' => 'required|boolean',
            'nic' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255',
            'basic_salary' => 'nullable|numeric|min:0',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        if (auth()->user()->role === 'super_admin') {
            $rules['branch_id'] = 'required|exists:branches,id';
        }
        $request->validate($rules);

        $data = $request->only(['name', 'email', 'phone', 'role', 'is_active', 'branch_id', 'nic', 'dob', 'address', 'emergency_contact', 'basic_salary']);
        
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

    public function destroy(\App\Models\User $staff)
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

        $user = \App\Models\User::find($request->user_id);
        $branchId = auth()->user()->branch_id ?? 1;

        \App\Models\Expense::create([
            'branch_id' => $branchId,
            'category' => 'salary',
            'amount' => $request->amount,
            'description' => "Salary payment for {$user->name} ({$request->month})",
            'expense_date' => now(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Salary payment recorded successfully.');
    }
}
