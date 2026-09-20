<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RegisterSession;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCloseShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shifts:autoclose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close any open branch shifts at the end of the day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now()->format('H:i');

        $openSessions = RegisterSession::where('status', 'open')
            ->whereHas('branch', function ($query) use ($now) {
                $query->where('auto_close_time', 'like', $now.'%');
            })
            ->get();

        if ($openSessions->isEmpty()) {
            return;
        }

        foreach ($openSessions as $session) {
            $branchId = $session->branch_id;

            $cashCollected = \App\Models\PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $session->opened_at)
                ->where('payment_method', 'cash')
                ->sum('amount');
                
            $directCashOrders = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $session->opened_at)
                ->where('payment_method', 'cash')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
                
            $cashCollected += $directCashOrders;

            $bankDepositsLogged = \App\Models\BankDeposit::where('branch_id', $branchId)
                ->where('created_at', '>=', $session->opened_at)
                ->sum('amount');

            $finalCash = $session->opening_cash + $cashCollected - $bankDepositsLogged;

            $session->update([
                'closing_cash' => $finalCash,
                'closed_at' => now(),
                'status' => 'closed',
                'closed_by' => null, // Auto closed
            ]);

            \App\Models\BranchLog::create([
                'branch_id' => $branchId,
                'user_id' => null,
                'action' => 'shift_closed',
                'description' => 'Shift auto-closed by system. Final Cash: Rs. ' . number_format($finalCash, 2),
            ]);
        }
        $this->info(count($openSessions).' shifts auto-closed.');
    }
}

