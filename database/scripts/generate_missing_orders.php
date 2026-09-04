<?php

$appointments = \App\Models\DeliveryAppointment::whereNull('order_id')->get();
foreach ($appointments as $appointment) {
    $admin = \App\Models\User::where('branch_id', $appointment->branch_id)->first() ?? \App\Models\User::first();
    
    $order = \App\Models\Order::create([
        'order_number' => \App\Models\Order::generateOrderNumber($appointment->branch_id, true),
        'customer_id' => $appointment->customer_id,
        'branch_id' => $appointment->branch_id,
        'status' => 'received',
        'subtotal' => 0,
        'total' => $appointment->delivery_cost ?? 0,
        'delivery_fee' => $appointment->delivery_cost ?? 0,
        'paid_amount' => 0,
        'payment_status' => 'pending',
        'payment_method' => 'cash',
        'due_date' => \Carbon\Carbon::parse($appointment->pickup_scheduled_at)->addDays(2),
        'created_by' => $admin ? $admin->id : 1,
        'is_delivery_order' => true,
        'delivery_appointment_id' => $appointment->id,
        'delivery_address' => $appointment->delivery_address ?? $appointment->pickup_address,
        'notes' => $appointment->notes,
    ]);

    $appointment->update(['order_id' => $order->id]);
    echo "Created Order " . $order->order_number . " for Appointment " . $appointment->id . "\n";
}
echo "Done.\n";
