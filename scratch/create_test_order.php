<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\User;
use App\Models\DeliveryAppointment;
use App\Models\PaymentTransaction;
use App\Models\OrderStatusHistory;

// Find existing data
$branch = Branch::first();
$customer = Customer::first();
$admin = User::whereIn('role', ['admin', 'branch_admin', 'super_admin'])->first();

echo "Branch: {$branch->name} (ID: {$branch->id})\n";
echo "Customer: {$customer->name} (ID: {$customer->id})\n";
echo "Admin: {$admin->name} (ID: {$admin->id})\n\n";

auth()->login($admin);

// Create a delivery order - already paid Rs. 1000 (includes Rs. 200 delivery fee)
// Service total = 800, Delivery fee = 200, Grand total = 1000
$orderNum = 'TEST-' . rand(1000, 9999);

$order = Order::create([
    'order_number'   => $orderNum,
    'customer_id'    => $customer->id,
    'branch_id'      => $branch->id,
    'status'         => 'done',
    'is_delivery_order' => true,
    'total'          => 1000.00,
    'delivery_fee'   => 200.00,
    'paid_amount'    => 1000.00,
    'payment_status' => 'paid',
    'payment_method' => 'cash',
    'created_by'     => $admin->id,
    'created_at'     => now()->subDay(), // yesterday
    'updated_at'     => now()->subDay(),
]);

// Create delivery appointment
$appointment = DeliveryAppointment::create([
    'order_id'            => $order->id,
    'order_number'        => $order->order_number,
    'customer_id'         => $customer->id,
    'branch_id'           => $branch->id,
    'delivery_address'    => '123 Test Street, Colombo',
    'contact_phone'       => $customer->phone ?? '0771234567',
    'delivery_preference' => 'deliver_to_address',
    'status'              => 'ready',
    'delivery_cost'       => 200.00,
]);

$order->delivery_appointment_id = $appointment->id;
$order->save();

// Create yesterday's payment transaction
$transaction = PaymentTransaction::create([
    'order_id'       => $order->id,
    'amount'         => 1000.00,
    'payment_method' => 'cash',
    'created_by'     => $admin->id,
    'status'         => 'approved',
    'created_at'     => now()->subDay(),
    'updated_at'     => now()->subDay(),
]);

// Add status history
OrderStatusHistory::create([
    'order_id'   => $order->id,
    'status'     => 'done',
    'note'       => 'Order marked as done at workshop.',
    'changed_by' => $admin->id,
    'created_at' => now()->subDay(),
]);

echo "✅ Test Order Created!\n";
echo "Order Number : {$order->order_number}\n";
echo "Order ID     : {$order->id}\n";
echo "Total        : Rs. {$order->total}\n";
echo "Delivery Fee : Rs. {$order->delivery_fee}\n";
echo "Paid         : Rs. {$order->paid_amount}\n";
echo "Status       : {$order->payment_status}\n\n";
echo "URL: http://127.0.0.1:8000/orders/view/{$order->order_number}\n";
