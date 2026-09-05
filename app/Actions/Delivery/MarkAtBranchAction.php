<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\WorkshopDispatch;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MarkAtBranchAction
{
    public function execute(DeliveryAppointment $appointment, array $data): WorkshopDispatch
    {
        $appointment->update(['status' => 'at_branch']);

        if (!empty($data['pickme_flash_cost'])) {
            Expense::create([
                'branch_id' => $appointment->branch_id,
                'category' => 'PickMe',
                'description' => 'PickMe payment for pickup delivery Order #' . ($appointment->order ? $appointment->order->id : $appointment->order_id),
                'amount' => $data['pickme_flash_cost'],
                'expense_date' => now(),
                'created_by' => auth()->id(),
            ]);
        }

        $dispatch = null;
        DB::transaction(function () use ($data, $appointment, &$dispatch) {
            // Create the official Order stub for the Workshop if not exists
            if (! $appointment->order_id) {
                $order = Order::create([
                    'customer_id' => $appointment->customer_id,
                    'branch_id' => $appointment->branch_id,
                    'status' => 'received',
                    'subtotal' => 0,
                    'total' => $appointment->delivery_cost ?? 0,
                    'delivery_fee' => $appointment->delivery_cost ?? 0,
                    'paid_amount' => 0,
                    'payment_status' => 'pending',
                    'payment_method' => $appointment->payment_method ?? 'cash',
                    'due_date' => \Carbon\Carbon::now()->addDays(2), // Default 2 days
                    'created_by' => auth()->id(),
                    'is_delivery_order' => true,
                    'delivery_appointment_id' => $appointment->id,
                ]);

                $appointment->update(['order_id' => $order->id]);

                if ($appointment->cart_json) {
                    $cart = json_decode($appointment->cart_json, true);
                    if (is_array($cart)) {
                        $subtotal = 0;
                        foreach ($cart as $item) {
                            if (isset($item['service']['id']) && isset($item['quantity'])) {
                                $qty = $item['quantity'];
                                $price = $item['service']['base_price'] ?? 0;
                                
                                \App\Models\OrderItem::create([
                                    'order_id' => $order->id,
                                    'service_type_id' => $item['service']['id'],
                                    'item_description' => $item['service']['name'] ?? '',
                                    'quantity' => $qty,
                                    'unit_price' => $price,
                                    'total_price' => $qty * $price,
                                ]);
                                $subtotal += $qty * $price;
                            }
                        }
                        $order->update([
                            'subtotal' => $subtotal,
                            'total' => $subtotal + ($appointment->delivery_cost ?? 0),
                            'delivery_fee' => $appointment->delivery_cost ?? 0,
                        ]);
                    }
                }
                
                // Calculate Profit Share
                $branch = Branch::find($appointment->branch_id);
                if ($branch && $branch->branch_profit_percentage > 0) {
                    $subtotal = $order->subtotal ?? 0;
                    $branchProfit = $subtotal * ($branch->branch_profit_percentage / 100);
                    $workshopProfit = $subtotal - $branchProfit;
                    $order->update([
                        'branch_profit' => $branchProfit,
                        'workshop_profit' => $workshopProfit,
                    ]);
                }
            }

            $dispatch = WorkshopDispatch::create([
                'order_id' => $appointment->order_id,
                'branch_id' => $appointment->branch_id,
                'workshop_branch_id' => $appointment->branch_id,
                'dispatched_by' => auth()->id(),
                'dispatched_at' => now(),
                'status' => 'received_at_workshop',
                'received_by' => auth()->id(),
                'received_at_workshop_at' => now(),
            ]);
        });

        $workshopAdmins = User::whereIn('role', ['workshop_admin', 'staff'])->where('branch_id', $appointment->branch_id)->get();
        $isWorkshopAdmin = \Illuminate\Support\Facades\Gate::allows('is-workshop-admin');
        $targetRouteName = $isWorkshopAdmin ? 'workshop.operations' : 'orders.index';
        
        Notification::send($workshopAdmins, new SystemAlertNotification('New Delivery Order Arrived: #' . $dispatch->order->id, route($targetRouteName), 'delivery_arrived'));

        return $dispatch;
    }
}
