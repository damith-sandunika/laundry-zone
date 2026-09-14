<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetTransactionalDataCommand extends Command
{
    protected $signature = 'system:reset-specific-data';

    protected $description = 'Clear customers, branch logs, petty cash, attendance, and salary advances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('Are you sure you want to delete ONLY Customers, Branch Logs, Petty Cash, Attendance, Allowances, and Salary Advances?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Clearing data...');

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tablesToTruncate = [
            // Customers
            \App\Models\Customer::class,
            \App\Models\CustomerPackage::class,
            \App\Models\CustomerPackageService::class,
            \App\Models\PackagePayment::class,
            \App\Models\LoyaltyTransaction::class,

            // HR (Attendance, Allowances, Deductions, Advance Payments)
            \App\Models\Attendance::class,
            \App\Models\SalaryAdjustment::class, // Allowances and Deductions
            \App\Models\SalaryAdvance::class,

            // Petty cash & Branch Logs
            \App\Models\Expense::class, // Petty Cash history
            \App\Models\BranchLog::class,
            \App\Models\RegisterSession::class,
        ];

        foreach ($tablesToTruncate as $model) {
            if (class_exists($model)) {
                $model::truncate();
            }
        }

        // Truncate non-model tables
        \Illuminate\Support\Facades\DB::table('petty_cash_topups')->truncate();

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Data successfully deleted! Services and other settings were NOT touched.');
    }
}
