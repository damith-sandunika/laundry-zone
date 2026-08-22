<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\RegisterSession;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $activeSession = RegisterSession::where('branch_id', $branchId ?? 1)
            ->where('status', 'open')
            ->first();

        $query = Expense::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $period = $request->input('period', 'shift');
        $category = $request->input('category', '');
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');

        if ($category) {
            $query->where('category', $category);
        }

        if ($period === 'shift') {
            if ($activeSession) {
                $query->where('created_at', '>=', $activeSession->opened_at);
            } else {
                $query->where('id', -1); // Show no expenses if shift is closed
            }
        } elseif ($period === 'today') {
            $query->whereDate('expense_date', now()->toDateString());
        } elseif ($period === 'this_week') {
            $query->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'custom') {
            if ($startDate && $endDate) {
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('expense_date', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('expense_date', '<=', $endDate);
            }
        } // 'all_time' doesn't add any date restrictions

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        return view('expenses.index', compact('expenses', 'activeSession', 'period', 'category', 'startDate', 'endDate'));
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

        Expense::create([
            'branch_id' => $branchId,
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
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

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
