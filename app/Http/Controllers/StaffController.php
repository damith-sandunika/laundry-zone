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

        return view('staff.index', compact('staff', 'salaries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'role' => 'required|in:admin,staff',
            'password' => 'required|string|min:8',
        ]);

        $branchId = auth()->user()->branch_id ?? 1;

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'branch_id' => $branchId,
            'password' => bcrypt($request->password),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Staff member added successfully.');
    }

    public function show(\App\Models\User $staff)
    {
        $salaries = \App\Models\Expense::where('category', 'salary')
            ->where('notes', 'like', "%{$staff->name}%")
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
            'phone'     => 'nullable|string|max:50',
            'role'      => 'required|in:admin,staff,super_admin',
            'is_active' => 'required|boolean',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        $request->validate($rules);

        $data = $request->only(['name', 'phone', 'role', 'is_active']);
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $staff->update($data);
        return redirect()->route('staff.index')->with('success', 'Staff updated.');
    }

    public function destroy(\App\Models\User $staff)
    {
        $staff->update(['is_active' => false]);
        return redirect()->route('staff.index')->with('success', 'Staff deactivated.');
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
            'notes' => "Salary payment for {$user->name} ({$request->month})",
            'expense_date' => now(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Salary payment recorded successfully.');
    }
}
