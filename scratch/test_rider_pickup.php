<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$appointment = App\Models\DeliveryAppointment::first();
if (!$appointment) {
    echo "No appointment found\n";
    exit;
}
echo "Found appointment: {$appointment->id} with status {$appointment->status}\n";

$customer = $appointment->customer;
echo "Customer: {$customer->name}\n";

// Let's see what DashboardController would return for this customer NOW
$activeOrders = $customer->orders()
    ->with(['branch', 'items.serviceType'])
    ->whereNotIn('status', ['delivered', 'cancelled'])
    ->latest()
    ->get();
    
$appointments = $customer->deliveryAppointments()
    ->with('branch')
    ->whereNotIn('status', ['completed', 'cancelled', 'order_created', 'delivered'])
    ->whereNull('order_id')
    ->latest()
    ->get();
    
echo "Active Orders Count: " . $activeOrders->count() . "\n";
echo "Active Appointments Count: " . $appointments->count() . "\n";

