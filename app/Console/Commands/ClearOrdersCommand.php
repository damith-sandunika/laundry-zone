<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:clear')]
#[Description('Clear all orders and related data from the database')]
class ClearOrdersCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('Are you sure you want to delete ALL orders? This cannot be undone!')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Clearing all orders...');

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        \App\Models\Order::truncate();
        \App\Models\OrderItem::truncate();
        \App\Models\OrderItemExtra::truncate();
        \App\Models\OrderStatusHistory::truncate();
        \App\Models\PaymentTransaction::truncate();
        \App\Models\WorkshopDispatch::truncate();
        \App\Models\OrderCorrectionRequest::truncate();
        
        if (class_exists(\App\Models\DeliveryAppointment::class)) {
            \App\Models\DeliveryAppointment::truncate();
        }
        if (class_exists(\App\Models\DeliveryTask::class)) {
            \App\Models\DeliveryTask::truncate();
        }

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('All orders and related data have been successfully deleted!');
    }
}
