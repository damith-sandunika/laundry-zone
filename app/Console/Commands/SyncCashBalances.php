<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-cash-balances')]
#[Description('Command description')]
class SyncCashBalances extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branches = \App\Models\Branch::all();
        foreach ($branches as $branch) {
            $this->info("Processing Branch: {$branch->name}");
            
            // 1. PaymentTransactions (Cash)
            $cashPayments = \App\Models\PaymentTransaction::whereHas('order', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->where('payment_method', 'cash')->sum('amount');
            
            // 2. Direct Cash Orders (Backward compat)
            $directCashOrders = \App\Models\Order::where('branch_id', $branch->id)
                ->where('payment_method', 'cash')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
                
            // 3. Package Payments (Cash)
            $packagePayments = \App\Models\PackagePayment::whereHas('customerPackage', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->where('payment_method', 'cash')->sum('amount');
            
            // 4. Expenses (Cash)
            $expenses = \App\Models\Expense::where('branch_id', $branch->id)
                ->sum('amount'); // All expenses are basically cash deductions for now.
                
            $totalIn = $cashPayments + $directCashOrders + $packagePayments;
            $totalOut = $expenses;
            
            $cashBalance = $totalIn - $totalOut;
            
            $branch->update(['cash_balance' => $cashBalance]);
            $this->info("Balance set to: {$cashBalance}");
        }
        $this->info("Sync complete!");
    }
}
