<?php

namespace App\Http\Controllers;

use App\Models\BankDeposit;
use App\Models\Branch;
use App\Models\BranchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            $bankDepositsLogged = BankDeposit::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            $availableCash = $activeSession->opening_cash + $cashCollected - $bankDepositsLogged;

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

        BranchLog::create([
            'branch_id'   => $branchId,
            'user_id'     => auth()->id(),
            'action'      => 'bank_deposit',
            'description' => 'Deposited Rs. ' . number_format($request->amount, 2) . ($request->reference_number ? ' (Ref: ' . $request->reference_number . ')' : ''),
        ]);

        return redirect()->back()->with('success', 'Bank deposit recorded successfully. Cash balance updated.');
    }

    public function update(Request $request, BankDeposit $bankDeposit)
    {
        $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:191',
            'notes'            => 'nullable|string',
            'edit_notes'       => 'required|string|max:500',
        ]);

        // Roles allowed to edit
        $user = auth()->user();
        if (!$user->hasAdminAccess() && !$user->isWorkshopAdmin()) {
            return redirect()->back()->with('error', 'You do not have permission to edit deposits.');
        }

        // Cross-branch IDOR protection: Non-superadmins can only edit deposits for their own branch
        if (!$user->isSuperAdmin() && (int)$bankDeposit->branch_id !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access: You cannot edit deposits belonging to another branch.');
        }

        $oldAmount = $bankDeposit->amount;
        $newAmount = $request->amount;
        $diff      = $newAmount - $oldAmount;

        DB::transaction(function () use ($bankDeposit, $request, $oldAmount, $newAmount, $diff) {
            // Adjust cash balance for the difference
            $cashService = app(\App\Services\CashBalanceService::class);
            if ($diff > 0) {
                // Increased deposit → further reduce cash
                $cashService->decrementCashBalance($bankDeposit->branch_id, $diff);
            } elseif ($diff < 0) {
                // Decreased deposit → return cash
                $cashService->incrementCashBalance($bankDeposit->branch_id, abs($diff));
            }

            $bankDeposit->update([
                'amount'           => $newAmount,
                'original_amount'  => $bankDeposit->original_amount ?? $oldAmount, // Only set once
                'reference_number' => $request->reference_number,
                'notes'            => $request->notes,
                'is_edited'        => true,
                'edited_at'        => now(),
                'edited_by'        => auth()->id(),
                'edit_notes'       => $request->edit_notes,
            ]);

            BranchLog::create([
                'branch_id'   => $bankDeposit->branch_id,
                'user_id'     => auth()->id(),
                'action'      => 'bank_deposit_edited',
                'description' => 'Edited deposit #' . $bankDeposit->id . ': Rs. ' . number_format($oldAmount, 2) . ' → Rs. ' . number_format($newAmount, 2) . ($request->edit_notes ? ' — Note: ' . $request->edit_notes : ''),
            ]);
        });

        return redirect()->back()->with('success', 'Bank deposit updated successfully.');
    }

    public function destroy(Request $request, BankDeposit $bankDeposit)
    {
        $request->validate([
            'delete_reason' => 'required|string|max:500',
        ]);

        // Roles allowed to delete
        $user = auth()->user();
        if (!$user->hasAdminAccess() && !$user->isWorkshopAdmin()) {
            return redirect()->back()->with('error', 'You do not have permission to delete deposits.');
        }

        // Cross-branch IDOR protection: Non-superadmins can only delete deposits for their own branch
        if (!$user->isSuperAdmin() && (int)$bankDeposit->branch_id !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access: You cannot delete deposits belonging to another branch.');
        }

        DB::transaction(function () use ($bankDeposit, $request) {
            // Store deletion metadata before soft-deleting
            $bankDeposit->deleted_by     = auth()->id();
            $bankDeposit->delete_reason  = $request->delete_reason;
            $bankDeposit->save();

            // Soft delete (triggers cash balance restore via model boot)
            $bankDeposit->delete();

            BranchLog::create([
                'branch_id'   => $bankDeposit->branch_id,
                'user_id'     => auth()->id(),
                'action'      => 'bank_deposit_deleted',
                'description' => 'Deleted deposit #' . $bankDeposit->id . ' (Rs. ' . number_format($bankDeposit->amount, 2) . ')' . ($request->delete_reason ? ' — Reason: ' . $request->delete_reason : '') . '. Cash balance restored.',
            ]);
        });

        return redirect()->back()->with('success', 'Bank deposit deleted. Cash balance has been restored.');
    }
}

