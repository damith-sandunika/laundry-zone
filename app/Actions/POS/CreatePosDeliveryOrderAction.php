<?php

namespace App\Actions\POS;

use App\Models\Customer;
use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePosDeliveryOrderAction
{
    public function execute(User $user, array $data): array
    {
        $branchId = $user->branch_id ?? 1;

        return DB::transaction(function () use ($data, $user, $branchId) {
            $pickupDate = date('Y-m-d H:i:s', strtotime($data['pickup_date']));
            $cart       = $data['cart'] ?? [];

            $appointment = DeliveryAppointment::create([
                'customer_id'          => $data['customer_id'],
                'branch_id'            => $branchId,
                'pickup_address'       => $data['pickup_address'],
                'delivery_address'     => $data['delivery_address'] ?? $data['pickup_address'],
                'pickup_scheduled_at'  => $pickupDate,
                'delivery_preference'  => 'deliver_to_address',
                'payment_method'       => ($data['payment_mode'] ?? null) === 'pay_now' ? 'cash' : 'cash',
                'status'               => 'confirmed', // auto-confirmed since staff initiated
                'notes'                => $data['notes'] ?? null,
                'contact_phone'        => Customer::find($data['customer_id'])?->phone,
            ]);

            $orderId     = null;
            $orderNumber = null;

            if (! empty($cart)) {
                $processedCart = [];
                $subtotal = 0;

                foreach ($cart as $item) {
                    $serviceTypeId = $item['service_type_id'] ?? $item['id'] ?? null;
                    $name = $item['name'] ?? 'Laundry Service';
                    $qty = (float) ($item['qty'] ?? 1);
                    $basePrice = (float) ($item['base_price'] ?? 0);

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

                    $processedCart[] = [
                        'service_type_id' => $serviceTypeId,
                        'name' => $name,
                        'qty' => $qty,
                        'base_price' => $basePrice,
                        'total_price' => $itemSubtotal,
                        'turnaround_type_id' => $turnaroundTypeId,
                        'turnaround_extra_charge' => $turnaroundCharge,
                        'selected_extras' => $selectedExtras,
                    ];

                    if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                        foreach ($selectedExtras as $selEx) {
                            $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                            $extraQty = is_array($selEx) && isset($selEx['qty']) ? $selEx['qty'] : 1;
                            
                            $extra = \App\Models\Extra::find($extraId);
                            if ($extra) {
                                $extraService = \App\Models\ServiceType::firstOrCreate(
                                    ['name' => 'Extra: ' . $extra->name],
                                    [
                                        'color' => 'slate', 
                                        'price_type' => 'per_item', 
                                        'base_price' => $extra->price, 
                                        'loyalty_points_earn' => 0, 
                                        'is_active' => 1, 
                                        'description' => 'Extra service'
                                    ]
                                );

                                $extraSubtotal = $extra->price * $extraQty;
                                $subtotal += $extraSubtotal;

                                $processedCart[] = [
                                    'service_type_id' => $extraService->id,
                                    'name' => 'Extra: ' . $extra->name,
                                    'qty' => $extraQty,
                                    'base_price' => $extra->price,
                                    'total_price' => $extraSubtotal,
                                    'turnaround_type_id' => null,
                                    'turnaround_extra_charge' => 0,
                                    'selected_extras' => [],
                                ];
                            }
                        }
                    }
                }

                $paymentMode = $data['payment_mode'] ?? 'pay_on_pickup';
                $paidAmount  = $paymentMode === 'pay_now' ? $subtotal : 0;

                $order = Order::create([
                    'order_number'            => Order::generateOrderNumber($branchId, true),
                    'branch_id'               => $branchId,
                    'customer_id'             => $data['customer_id'],
                    'created_by'              => $user->id,
                    'status'                  => 'received',
                    'subtotal'                => $subtotal,
                    'discount'                => 0,
                    'additional'              => 0,
                    'total'                   => $subtotal,
                    'paid_amount'             => $paidAmount,
                    'payment_method'          => $paymentMode === 'pay_now' ? ($data['payment_method'] ?? 'cash') : null,
                    'due_date'                => $pickupDate,
                    'is_delivery_order'       => true,
                    'delivery_appointment_id' => $appointment->id,
                    'delivery_address'        => $data['delivery_address'] ?? $data['pickup_address'],
                    'notes'                   => $data['notes'] ?? null,
                ]);

                foreach ($processedCart as $item) {
                    OrderItem::create([
                        'order_id'        => $order->id,
                        'service_type_id' => $item['service_type_id'],
                        'item_description' => $item['name'],
                        'quantity'        => $item['qty'],
                        'unit_price'      => $item['base_price'],
                        'total_price'     => $item['total_price'],
                        'turnaround_type_id' => $item['turnaround_type_id'],
                        'turnaround_extra_charge' => $item['turnaround_extra_charge'],
                    ]);
                }

                // Save cart_json matching the frontend structure for consistency
                $cartJson = array_map(function ($i) {
                    $stId = $i['service_type_id'] ?? $i['id'] ?? null;
                    return [
                        'id'                 => $stId,
                        'service_type_id'    => $stId,
                        'name'               => $i['name'] ?? 'Laundry Service',
                        'base_price'         => $i['base_price'] ?? 0,
                        'qty'                => $i['qty'] ?? 1,
                        'turnaround_type_id' => $i['turnaround_type_id'] ?? null,
                        'selected_extras'    => $i['selected_extras'] ?? [],
                    ];
                }, $cart);

                $appointment->update([
                    'order_id'  => $order->id,
                    'cart_json' => json_encode($cartJson),
                ]);

                $orderId     = $order->id;
                $orderNumber = $order->order_number;
            }

            return [
                'appointment_id' => $appointment->id,
                'order_id'       => $orderId,
                'order_number'   => $orderNumber,
                'has_order'      => $orderId !== null,
            ];
        });
    }
}
