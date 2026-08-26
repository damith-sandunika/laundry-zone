<?php
$appt = App\Models\DeliveryAppointment::latest()->first();
echo "Appt:\n" . json_encode($appt, JSON_PRETTY_PRINT) . "\n";
echo "Tasks:\n" . json_encode($appt ? $appt->deliveryTasks : [], JSON_PRETTY_PRINT) . "\n";
echo "Order:\n" . json_encode($appt ? $appt->order : [], JSON_PRETTY_PRINT) . "\n";
