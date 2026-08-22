<?php

namespace Database\Seeders;

use App\Models\Cubby;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderCubby;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TemporaryDataSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = 1; // Colombo Branch
        $admin = User::where('role', 'admin')->first();
        $services = ServiceType::all();
        $cubbies = Cubby::where('branch_id', $branchId)->get();

        // Create 10 dummy customers
        $customers = [];
        for ($i = 1; $i <= 10; $i++) {
            $customers[] = Customer::create([
                'branch_id' => $branchId,
                'name' => "Demo Customer $i",
                'phone' => '07700000'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'address' => "No. $i, Demo Street, Colombo",
                'created_at' => Carbon::now()->subDays(rand(1, 10)),
            ]);
        }

        // Status pool
        $statuses = ['received', 'washing', 'drying', 'ready', 'delivered'];

        // Create 15 orders (mix of old and new)
        for ($i = 1; $i <= 15; $i++) {
            $customer = $customers[array_rand($customers)];
            $status = $statuses[array_rand($statuses)];
            $createdAt = Carbon::now()->subDays(rand(0, 5))->subHours(rand(1, 12));

            // Random total calculation
            $itemCount = rand(1, 3);
            $total = 0;
            $orderItems = [];

            // Create Order
            $order = Order::create([
                'order_number' => 'LDY'.$createdAt->format('ymd').str_pad($i, 3, '0', STR_PAD_LEFT),
                'branch_id' => $branchId,
                'customer_id' => $customer->id,
                'created_by' => $admin->id,
                'status' => $status,
                'subtotal' => 0,
                'total' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            for ($j = 0; $j < $itemCount; $j++) {
                $service = $services->random();
                $qty = rand(1, 5);
                $itemTotal = $service->base_price * $qty;
                $total += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'service_type_id' => $service->id,
                    'item_description' => $service->name,
                    'quantity' => $qty,
                    'unit_price' => $service->base_price,
                    'total_price' => $itemTotal,
                ]);
            }

            // Update order total
            $order->update(['subtotal' => $total, 'total' => $total]);

            // History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $status,
                'changed_by' => $admin->id,
                'created_at' => $createdAt,
            ]);

            // If ready, assign to a cubby
            if ($status === 'ready') {
                // Find an empty cubby
                $emptyCubby = Cubby::where('branch_id', $branchId)->where('status', 'empty')->first();
                if ($emptyCubby) {
                    OrderCubby::create([
                        'order_id' => $order->id,
                        'cubby_id' => $emptyCubby->id,
                        'assigned_at' => $createdAt,
                    ]);
                    $emptyCubby->update(['status' => 'occupied']);
                }
            }

            // If delivered, mark cubby as empty if it had one
            if ($status === 'delivered') {
                $order->update(['delivered_at' => $createdAt->addDays(1)]);
            }
        }
    }
}
