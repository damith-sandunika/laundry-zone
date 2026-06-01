<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        
        $query = \App\Models\Expense::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $expenses = $query->orderBy('expense_date', 'desc')->get();

        return view('expenses.index', compact('expenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        $branchId = auth()->user()->branch_id ?? 1;

        \App\Models\Expense::create([
            'branch_id' => $branchId,
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Expense recorded successfully.');
    }

    public function edit(\App\Models\Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, \App\Models\Expense $expense)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        $expense->update($request->only(['category', 'description', 'amount', 'expense_date']));

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(\App\Models\Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
