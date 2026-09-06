<?php
$customer = \App\Models\Customer::first();
$branch = \App\Models\Branch::first();
$user = \App\Models\User::first();
auth()->login($user);

// Test MarkAtBranchAction with Customer App style cart_json
$appt = \App\Models\DeliveryAppointment::create([
    'customer_id' => $customer->id,
    'branch_id' => $branch->id,
    'status' => 'pending',
    'delivery_preference' => 'deliver_to_address',
    'cart_json' => json_encode([[
        'id' => 1,
        'service_type_id' => 1,
        'name' => 'Trousers',
        'base_price' => 150,
        'qty' => 3,
        'turnaround_type_id' => null,
        'selected_extras' => [
            ['id' => 1, 'qty' => 1]
        ]
    ]])
]);

$order2 = \App\Models\Order::create([
    'order_number' => \App\Models\Order::generateOrderNumber($branch->id),
    'branch_id' => $branch->id,
    'customer_id' => $customer->id,
    'created_by' => $user->id,
    'status' => 'pending',
    'subtotal' => 0,
    'total' => 0,
    'delivery_appointment_id' => $appt->id
]);
$appt->update(['order_id' => $order2->id]);

$action2 = new \App\Actions\Delivery\MarkAtBranchAction();
$action2->execute($appt, []);
$order2->refresh();

echo "=== MarkAtBranch Test (Customer App cart_json) ===" . PHP_EOL;
echo "Order Subtotal: " . $order2->subtotal . PHP_EOL;
echo "Order Total: " . $order2->total . PHP_EOL;
echo "Order Items: " . PHP_EOL;
foreach ($order2->items as $item) {
    echo "  - " . $item->item_description . " x" . $item->quantity . " @ " . $item->unit_price . " = " . $item->total_price . PHP_EOL;
}
echo "Total Items Count: " . $order2->items()->count() . PHP_EOL;
