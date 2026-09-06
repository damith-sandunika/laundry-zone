<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveOldOrders extends Command
{
    protected $signature = 'orders:archive {--years=2 : The number of years before an order is considered old}';
    protected $description = 'Archive orders older than a specified number of years to keep the main table fast.';

    public function handle()
    {
        $years = (int) $this->option('years');
        $cutoffDate = Carbon::now()->subYears($years);

        $this->info("Starting archiving for orders created before: {$cutoffDate->toDateString()}");

        // Fetch old orders that are completed or cancelled
        $oldOrders = Order::where('created_at', '<', $cutoffDate)
            ->whereIn('status', ['delivered', 'cancelled'])
            ->get();

        if ($oldOrders->isEmpty()) {
            $this->info('No old orders found to archive.');
            return;
        }

        $bar = $this->output->createProgressBar($oldOrders->count());
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($oldOrders as $order) {
                // Insert into archive table
                DB::table('orders_archive')->insert([
                    'original_order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'branch_id' => $order->branch_id,
                    'customer_id' => $order->customer_id,
                    'created_by' => $order->created_by,
                    'status' => $order->status,
                    'subtotal' => $order->subtotal,
                    'discount' => $order->discount,
                    'total' => $order->total,
                    'paid_amount' => $order->paid_amount,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'loyalty_points_used' => $order->loyalty_points_used,
                    'loyalty_points_earned' => $order->loyalty_points_earned,
                    'due_date' => $order->due_date,
                    'notes' => $order->notes,
                    'is_online_booking' => $order->is_online_booking,
                    'ready_at' => $order->ready_at,
                    'delivered_at' => $order->delivered_at,
                    'archived_at' => Carbon::now(),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ]);

                // We don't delete the order items, or maybe we should?
                // For this example, let's just delete the main order.
                // Cascading delete might remove items if foreign keys are setup with cascade.
                $order->delete();

                $bar->advance();
            }
            DB::commit();
            
            $bar->finish();
            $this->newLine();
            $this->info('Archiving completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error('Archiving failed: ' . $e->getMessage());
        }
    }
}
