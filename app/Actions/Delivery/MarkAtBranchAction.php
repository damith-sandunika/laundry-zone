<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\OrderStatusHistory;
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
                    'order_number' => Order::generateOrderNumber($appointment->branch_id, true),
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
                try {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'received',
                        'changed_by' => auth()->id(),
                        'note' => 'Order created when garments arrived at branch.',
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('OrderStatusHistory insert failed (markAtBranch-new): ' . $e->getMessage());
                }
            } else {
                if ($appointment->order) {
                    $appointment->order->update(['status' => 'received']);
                    try {
                        OrderStatusHistory::create([
                            'order_id' => $appointment->order->id,
                            'status' => 'received',
                            'changed_by' => auth()->id(),
                            'note' => 'Garments arrived at branch.',
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('OrderStatusHistory insert failed (markAtBranch-existing): ' . $e->getMessage());
                    }
                }
            }

            $order = $appointment->order ?? Order::find($appointment->order_id);

            if ($order && $order->items()->count() === 0 && $appointment->cart_json) {
                $cart = json_decode($appointment->cart_json, true);
                if (is_array($cart)) {
                    $subtotal = 0;
                    foreach ($cart as $item) {
                        // Extract basic properties, supporting both old POS format and new Customer App format
                        $serviceTypeId = $item['service_type_id'] ?? $item['id'] ?? ($item['service']['id'] ?? null);
                        $name = $item['name'] ?? ($item['service']['name'] ?? 'Unknown');
                        $qty = (float) ($item['qty'] ?? $item['quantity'] ?? 1);
                        $basePrice = (float) ($item['base_price'] ?? ($item['service']['base_price'] ?? 0));
                        
                        if (!$serviceTypeId) continue;

                        $turnaroundTypeId = !empty($item['turnaround_type_id']) ? $item['turnaround_type_id'] : null;
                        $selectedExtras = $item['selected_extras'] ?? [];

                        $itemSubtotalBeforeTurnaround = ($qty * $basePrice);
                        $turnaroundCharge = 0;
                        if ($turnaroundTypeId) {
                            $tt = \App\Models\TurnaroundType::find($turnaroundTypeId);
                            if ($tt) {
                                if ($tt->markup_type === 'percentage') {
                                    $turnaroundCharge = $itemSubtotalBeforeTurnaround * ($tt->markup_value / 100);
                                } else {
                                    $turnaroundCharge = $tt->markup_value * $qty;
                                }
                            }
                        }

                        $itemSubtotal = $itemSubtotalBeforeTurnaround + $turnaroundCharge;
                        $subtotal += $itemSubtotal;

                        $orderItem = \App\Models\OrderItem::create([
                            'order_id' => $order->id,
                            'service_type_id' => $serviceTypeId,
                            'item_description' => $name,
                            'quantity' => $qty,
                            'unit_price' => $basePrice,
                            'total_price' => $itemSubtotal,
                            'turnaround_type_id' => $turnaroundTypeId,
                            'turnaround_extra_charge' => $turnaroundCharge,
                        ]);

                        if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                            foreach ($selectedExtras as $selEx) {
                                $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                                $extraQty = is_array($selEx) && isset($selEx['qty']) ? $selEx['qty'] : 1;
                                
                                $extra = \App\Models\Extra::find($extraId);
                                if ($extra) {
                                    $extraSubtotal = $extra->price * $extraQty;
                                    $subtotal += $extraSubtotal;

                                    \App\Models\OrderItemExtra::create([
                                        'order_item_id' => $orderItem->id,
                                        'extra_id' => $extraId,
                                        'quantity' => $extraQty,
                                        'unit_price' => $extra->price,
                                        'total_price' => $extraSubtotal
                                    ]);
                                }
                            }
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
            if ($order) {
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
