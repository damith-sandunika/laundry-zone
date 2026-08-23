<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::latest()->first();
echo "Latest Order ID: {$order->id}, Number: {$order->order_number}\n";
echo "is_delivery_order: {$order->is_delivery_order}\n";
echo "delivery_appointment_id on order: {$order->delivery_appointment_id}\n";

$appointment = App\Models\DeliveryAppointment::where('order_id', $order->id)->first();
if ($appointment) {
    echo "Found Delivery Appointment: {$appointment->id} for order {$order->id}\n";
} else {
    echo "NO Delivery Appointment found for order {$order->id}!\n";
    $app2 = App\Models\DeliveryAppointment::latest()->first();
    echo "Latest Appointment ID: {$app2->id}, order_id: {$app2->order_id}\n";
}
