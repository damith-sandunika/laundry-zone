<?php

namespace App\Actions\Customer;

use App\Models\Branch;
use App\Models\DeliveryAppointment;
use App\Models\PickupTimePeriod;
use App\Models\User;
use App\Models\Order;
use App\Models\DeliveryRange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;

class StoreCustomerBookingAction
{
    public function execute(array $data, \App\Models\Customer $customer): DeliveryAppointment
    {
        $pickupPeriod = PickupTimePeriod::find($data['pickup_time_period_id']);
        $pickupScheduledAt = Carbon::parse($data['pickup_date'] . ' ' . $pickupPeriod->start_time);

        $deliveryLat = $data['delivery_lat'] ?? null;
        $deliveryLng = $data['delivery_lng'] ?? null;
        $deliveryAddress = $data['delivery_address'] ?? null;

        if ($data['delivery_preference'] === 'deliver_to_address' && !empty($data['same_as_pickup'])) {
            $deliveryLat = $data['latitude'] ?? null;
            $deliveryLng = $data['longitude'] ?? null;
            $deliveryAddress = $data['pickup_address'];
        }

        $receiptPath = null; // Removed image upload logic to keep it simple, wasn't fully there anyway
        $deliveryCost = 0;

        $deliveryBranches = Branch::where('is_active', true)
            ->where(function($q) {
                $q->where('type', 'workshop')
                  ->orWhere('is_delivery_enabled', true);
            })
            ->get();
            
        $closestWorkshop = null;
        $shortestDistance = PHP_FLOAT_MAX;

        $calculateDistance = function($lat1, $lon1, $lat2, $lon2) {
            if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;
            $earthRadius = 6371;
            $latDelta = deg2rad($lat2 - $lat1);
            $lonDelta = deg2rad($lon2 - $lon1);
            $a = sin($latDelta / 2) * sin($latDelta / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) * sin($lonDelta / 2);
            return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
        };

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            foreach ($deliveryBranches as $ws) {
                if ($ws->latitude && $ws->longitude) {
                    $dist = $calculateDistance($ws->latitude, $ws->longitude, $data['latitude'], $data['longitude']);
                    if ($dist < $shortestDistance) {
                        $shortestDistance = $dist;
                        $closestWorkshop = $ws;
                    }
                }
            }
        }

        if (!$closestWorkshop) {
            $closestWorkshop = $deliveryBranches->first();
        }

        if ($closestWorkshop && $closestWorkshop->latitude && $closestWorkshop->longitude) {
            $distanceToPickup = $shortestDistance == PHP_FLOAT_MAX ? 0 : $shortestDistance;
            $distanceToDelivery = 0;

            if ($data['delivery_preference'] === 'deliver_to_address') {
                if (!empty($data['same_as_pickup'])) {
                    $distanceToDelivery = $distanceToPickup;
                } else if ($deliveryLat && $deliveryLng) {
                    $distanceToDelivery = $calculateDistance($closestWorkshop->latitude, $closestWorkshop->longitude, $deliveryLat, $deliveryLng);
                }
            }

            $getFeeForDistance = function($dist) use ($closestWorkshop) {
                if ($dist <= 0) return 0;
                $range = DeliveryRange::where(function($q) use ($closestWorkshop) {
                        $q->where('branch_id', $closestWorkshop->id)->orWhereNull('branch_id');
                    })
                    ->where('min_distance_km', '<=', $dist)
                    ->where('max_distance_km', '>=', $dist)
                    ->orderBy('cost', 'asc')
                    ->first();
                return $range ? $range->cost : 0;
            };

            $pickupFee = $getFeeForDistance($distanceToPickup);
            $deliveryFee = 0;
            if ($data['delivery_preference'] === 'deliver_to_address') {
                $deliveryFee = $getFeeForDistance($distanceToDelivery);
            }

            $deliveryCost = $pickupFee + $deliveryFee;
        }
        
        if ($deliveryCost == 0 && !empty($data['estimated_delivery_fee'])) {
            $deliveryCost = (float) $data['estimated_delivery_fee'];
        }

        $deliveryScheduledAt = null;
        if (!empty($data['delivery_date']) && !empty($data['delivery_time_period_id'])) {
            $deliveryPeriod = PickupTimePeriod::find($data['delivery_time_period_id']);
            if ($deliveryPeriod) {
                $deliveryScheduledAt = Carbon::parse($data['delivery_date'] . ' ' . $deliveryPeriod->start_time);
            }
        }

        $appointment = DB::transaction(function () use ($data, $customer, $deliveryLat, $deliveryLng, $deliveryAddress, $receiptPath, $deliveryCost, $pickupScheduledAt, $deliveryScheduledAt, $closestWorkshop) {
            
            $appointment = DeliveryAppointment::create([
                'customer_id' => $customer->id,
                'branch_id' => $closestWorkshop->id,
                'pickup_address' => $data['pickup_address'],
                'map_address' => $data['map_address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'pickup_scheduled_at' => $pickupScheduledAt,
                'delivery_scheduled_at' => $deliveryScheduledAt,
                'item_description' => $data['item_description'] ?? null,
                'cart_json' => $data['cart_json'] ?? null,
                'notes' => $data['notes'] ?? null,
                'contact_phone' => $customer->phone,
                'status' => 'pending',
                'payment_method' => 'pending',
                'payment_timing' => 'pending',
                'delivery_preference' => $data['delivery_preference'],
                'delivery_lat' => $deliveryLat,
                'delivery_lng' => $deliveryLng,
                'delivery_address' => $deliveryAddress,
                'receipt_path' => $receiptPath,
                'delivery_cost' => $deliveryCost,
            ]);

            $admin = User::where('branch_id', $closestWorkshop->id)->first() ?? User::first();
            
            $dueDate = $deliveryScheduledAt ? Carbon::parse($deliveryScheduledAt) : Carbon::parse($pickupScheduledAt)->addDays(2);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber($closestWorkshop->id, true),
                'customer_id' => $customer->id,
                'branch_id' => $closestWorkshop->id,
                'status' => 'pending',
                'subtotal' => 0,
                'total' => $deliveryCost,
                'delivery_fee' => $deliveryCost,
                'paid_amount' => 0,
                'payment_status' => 'pending',
                'payment_method' => 'cash',
                'due_date' => $dueDate,
                'created_by' => $admin ? $admin->id : 1,
                'is_delivery_order' => true,
                'delivery_appointment_id' => $appointment->id,
                'delivery_address' => $deliveryAddress,
                'notes' => $data['notes'] ?? null,
            ]);

            $appointment->update(['order_id' => $order->id, 'order_number' => $order->order_number]);

            return $appointment;
        });

        if ($closestWorkshop->type === 'workshop') {
            $admins = User::where('role', 'workshop_admin')->where('branch_id', $closestWorkshop->id)->get();
            Notification::send($admins, new SystemAlertNotification('New Booking Received: #' . $appointment->id, route('workshop.operations'), 'new_order'));
        } else {
            $admins = User::whereIn('role', ['branch_admin', 'admin'])->where('branch_id', $closestWorkshop->id)->get();
            Notification::send($admins, new SystemAlertNotification('New Delivery Booking: #' . $appointment->id, route('delivery.index'), 'new_order'));
        }

        return $appointment;
    }
}
