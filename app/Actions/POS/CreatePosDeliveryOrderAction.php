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
                $subtotal = 0;
                foreach ($cart as $item) {
                    $subtotal += (float) $item['base_price'] * (float) $item['qty'];
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
                    'payment_method'          => $paymentMode === 'pay_now' ? 'cash' : null,
                    'due_date'                => $pickupDate,
                    'is_delivery_order'       => true,
                    'delivery_appointment_id' => $appointment->id,
                    'delivery_address'        => $data['delivery_address'] ?? $data['pickup_address'],
                    'notes'                   => $data['notes'] ?? null,
                ]);

                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id'        => $order->id,
                        'service_type_id' => $item['service_type_id'] ?? $item['id'],
                        'item_description' => $item['name'],
                        'quantity'        => $item['qty'],
                        'unit_price'      => $item['base_price'],
                        'total_price'     => (float) $item['base_price'] * (float) $item['qty'],
                    ]);
                }

                $cartJson = array_map(fn($i) => [
                    'service' => [
                        'id'         => $i['service_type_id'] ?? $i['id'],
                        'name'       => $i['name'],
                        'base_price' => $i['base_price'],
                    ],
                    'quantity' => $i['qty'],
                ], $cart);

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
