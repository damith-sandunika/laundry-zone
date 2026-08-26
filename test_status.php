<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = new stdClass();
$order->is_delivery_order = true;
$order->status = 'completed';
$order->payment_approved = true;
$order->payment_status = 'paid';
$order->requested_advance = 2000;

            if ($order->is_delivery_order) {
                $statuses = [
                    'received'         => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-list', 'color' => 'yellow'],
                    'picked_up'        => ['label' => 'Picked Up', 'icon' => 'fa-truck-pickup', 'color' => 'blue'],
                    'processing'       => ['label' => 'Processing', 'icon' => 'fa-spinner', 'color' => 'blue'],
                    'ready'            => ['label' => 'Ready to Deliver', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'fa-motorcycle', 'color' => 'blue'],
                    'delivered'        => ['label' => 'Delivered', 'icon' => 'fa-truck-fast', 'color' => 'purple'],
                    'completed'        => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            } else {
                $statuses = [
                    'received'   => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-list', 'color' => 'yellow'],
                    'processing' => ['label' => 'Processing', 'icon' => 'fa-spinner', 'color' => 'blue'],
                    'ready'      => ['label' => 'Job Done', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'completed'  => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            }
            
            $actualStatus = $order->status;
            
            if ($order->is_delivery_order) {
                if (in_array($actualStatus, ['pending', 'received', 'dispatched_to_workshop', 'received_at_workshop'])) {
                    $actualStatus = 'picked_up';
                }
                if (in_array($actualStatus, ['washing', 'drying', 'ironing']) || ($order->payment_approved && in_array($order->status, ['dispatched_to_workshop', 'received_at_workshop']))) {
                    $actualStatus = 'processing';
                }
                if (in_array($actualStatus, ['returned_from_workshop', 'done'])) {
                    $actualStatus = 'ready';
                }
            } else {
                if (in_array($actualStatus, ['pending', 'dispatched_to_workshop', 'received_at_workshop'])) {
                    $actualStatus = 'received';
                }
                if (in_array($actualStatus, ['washing', 'drying', 'ironing']) || ($order->payment_approved && in_array($order->status, ['dispatched_to_workshop', 'received_at_workshop']))) {
                    $actualStatus = 'processing';
                }
            }
            
            if ($actualStatus === 'delivered' && $order->payment_status === 'paid') {
                $actualStatus = 'completed';
            } elseif ($actualStatus === 'delivered' && $order->payment_status !== 'paid' && !$order->is_delivery_order) {
                $actualStatus = 'ready'; 
            }

            if ($order->status === 'cancelled') {
                $statuses = ['cancelled' => ['label' => 'Order Cancelled', 'icon' => 'fa-xmark', 'color' => 'red']];
                $actualStatus = 'cancelled';
            }
            
            $currentStatusIndex = array_search($actualStatus, array_keys($statuses));
            if ($currentStatusIndex === false) $currentStatusIndex = 0;
            
echo "actualStatus: $actualStatus, index: $currentStatusIndex\n";
