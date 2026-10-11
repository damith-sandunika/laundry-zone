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

        $query = Order::where('created_at', '<', $cutoffDate)
            ->whereIn('status', ['delivered', 'cancelled']);

        $totalCount = $query->count();
        if ($totalCount === 0) {
            $this->info('No old orders found to archive.');
            return;
        }

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $archivedCount = 0;
        $now = Carbon::now();

        try {
            $query->chunkById(500, function ($orders) use ($bar, &$archivedCount, $now) {
                DB::transaction(function () use ($orders, $bar, &$archivedCount, $now) {
                    $archiveData = [];
                    $orderIdsToDelete = [];

                    foreach ($orders as $order) {
                        $archiveData[] = [
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
                            'archived_at' => $now,
                            'created_at' => $order->created_at,
                            'updated_at' => $order->updated_at,
                        ];
                        $orderIdsToDelete[] = $order->id;
                    }

                    if (!empty($archiveData)) {
                        DB::table('orders_archive')->insert($archiveData);
                        Order::whereIn('id', $orderIdsToDelete)->forceDelete();
                        $archivedCount += count($archiveData);
                        $bar->advance(count($archiveData));
                    }
                });
            });

            $bar->finish();
            $this->newLine();
            $this->info("Archiving completed successfully! Total archived: {$archivedCount} orders.");
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('Archiving failed: ' . $e->getMessage());
        }
    }
}
