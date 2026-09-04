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

            $salesCollected = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $session->opened_at)
                ->sum('paid_amount');

            $expensesLogged = Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $session->opened_at)
                ->sum('amount');

            $finalCash = $session->opening_cash + $salesCollected - $expensesLogged;

            $session->update([
                'closing_cash' => $finalCash,
                'closed_at' => now(),
                'status' => 'closed',
                'closed_by' => null, // Auto closed
            ]);



        $this->info(count($openSessions).' shifts auto-closed.');
    }
}

