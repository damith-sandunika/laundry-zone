<?php
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
App\Models\Order::truncate();
App\Models\OrderItem::truncate();
App\Models\DeliveryAppointment::truncate();
App\Models\DeliveryTask::truncate();
App\Models\WorkshopDispatch::truncate();
App\Models\PaymentTransaction::truncate();
App\Models\Expense::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo "All orders and related tables truncated successfully!\n";
