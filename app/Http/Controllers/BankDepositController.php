<?php

namespace App\Http\Controllers;

use App\Models\BankDeposit;
use App\Models\Branch;
use Illuminate\Http\Request;

class BankDepositController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
        ]);

        $branchId = auth()->user()->branch_id;
        if (!$branchId) {
            return redirect()->back()->with('error', 'You must belong to a branch to make a deposit.');
        }

        $branch = Branch::findOrFail($branchId);
        
        $activeSession = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($activeSession) {
            $cashCollected = \App\Models\PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->sum('amount');
            
            $directCashOrders = \App\Models\Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
            $cashCollected += $directCashOrders;

            $expensesLogged = \App\Models\Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');
                
            $bankDepositsLogged = BankDeposit::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            $availableCash = $activeSession->opening_cash + $cashCollected - $expensesLogged - $bankDepositsLogged;

            if ($request->amount > $availableCash) {
                return redirect()->back()->with('error', 'Deposit amount cannot exceed current drawer cash balance (Rs. ' . number_format($availableCash, 2) . ')');
            }
        } else {
            if ($request->amount > $branch->cash_balance) {
                return redirect()->back()->with('error', 'Deposit amount cannot exceed current branch cash balance (Rs. ' . number_format($branch->cash_balance, 2) . ')');
            }
        }

        BankDeposit::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'deposited_at' => now(),
        ]);

        \App\Models\BranchLog::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'action' => 'bank_deposit',
            'description' => 'Deposited Rs. ' . number_format($request->amount, 2) . ($request->reference_number ? ' (Ref: ' . $request->reference_number . ')' : ''),
        ]);

        return redirect()->back()->with('success', 'Bank deposit recorded successfully. Cash balance updated.');
    }
}

