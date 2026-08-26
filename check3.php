<?php
$appt = App\Models\DeliveryAppointment::latest()->first();
echo "Appt Status: " . ($appt ? $appt->status : "NULL") . "\n";
echo "Appt ID: " . ($appt ? $appt->id : "NULL") . "\n";
