<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\RegisterSession;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        // Super admin can filter by any branch via query param; others only see their own branch
        if ($isSuperAdmin) {
            $branchId = $request->input('branch_id') ?: null;
        } else {
            $branchId = $user->branch_id;
        }

        $resolvedBranchId = $branchId ?? \App\Models\Branch::first()?->id;

        $activeSession = RegisterSession::where('branch_id', $resolvedBranchId)
            ->where('status', 'open')
            ->first();

        $branches = $isSuperAdmin ? \App\Models\Branch::where('is_active', true)->get() : collect();

        $query = Expense::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } elseif (!$isSuperAdmin) {
            $query->where('branch_id', $resolvedBranchId);
        }

        $period = $request->input('period', $isSuperAdmin ? 'this_month' : 'shift');
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
                $query->where('id', -1);
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
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(15);

        $topupsQuery = \DB::table('petty_cash_topups');
        if ($branchId) {
            $topupsQuery->where('branch_id', $branchId);
        } elseif (!$isSuperAdmin) {
            $topupsQuery->where('branch_id', $resolvedBranchId);
        }
        $topups = $topupsQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'topup_page');

        // Petty cash balance for current branch (only relevant for non-super-admin or filtered branch)
        $currentBranch = $branchId ? \App\Models\Branch::find($branchId) : ($isSuperAdmin ? null : \App\Models\Branch::find($resolvedBranchId));

        return view('expenses.index', compact(
            'expenses', 'topups', 'activeSession', 'period', 'category',
            'startDate', 'endDate', 'branches', 'branchId', 'isSuperAdmin', 'currentBranch'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);

        $branchId = $this->getActiveBranchId();

        Expense::create([
            'branch_id' => $branchId,
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Petty Cash expense recorded successfully.');
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

        return redirect()->route('expenses.index')->with('success', 'Petty Cash expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Petty Cash expense deleted successfully.');
    }

    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reference_note' => 'required|string|max:255',
        ]);

        $branchId = $this->getActiveBranchId();
        
        \DB::table('petty_cash_topups')->insert([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'reference_note' => $request->reference_note,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $branch = \App\Models\Branch::find($branchId);
        if ($branch) {
            $branch->petty_cash_balance += $request->amount;
            $branch->save();
        }

        return redirect()->back()->with('success', 'Petty Cash topped up successfully.');
    }
}

