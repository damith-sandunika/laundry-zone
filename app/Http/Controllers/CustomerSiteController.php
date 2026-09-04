<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryAppointment;
use App\Models\Package;
use App\Models\PaymentSetting;
use App\Models\ServiceCategory;
use App\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerSiteController extends Controller
{
    public function home()
    {
        // Include both workshop branches and delivery-enabled branches on the map
        $branches = Branch::where('is_active', true)
            ->where(function($q) {
                $q->where('type', 'workshop')
                  ->orWhere('is_delivery_enabled', true);
            })
            ->get();
        $categories = ServiceCategory::with(['services' => function($q) {
            $q->where('is_visible_on_website', true)->orderBy('order_index');
        }])->where('is_active', true)->orderBy('order_index')->get();
        $packages = Package::with('services.serviceType')->where('is_active', true)->get();

        return view('site.home', compact('branches', 'categories', 'packages'));
    }

    public function booking()
    {
        $paymentMethods = collect();
        if (\App\Models\Setting::get('payment_bank_transfer_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        }
        if (\App\Models\Setting::get('payment_online_active', '0') == '1') {
            $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        }
        if (\App\Models\Setting::get('payment_cod_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'cash_on_delivery', 'name' => 'Cash on Delivery', 'type' => 'cash_on_delivery']);
        }
        $categories = \App\Models\ServiceCategory::with([
            'children.services' => function($q) {
                $q->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'services' => function($q) {
                $q->where('is_visible_on_website', true)->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)->orderBy('order_index')->get();

        $pickupTimePeriods = \App\Models\PickupTimePeriod::where('is_active', true)->orderBy('start_time')->get();
        $minimumOrderLimit = \App\Models\Setting::get('minimum_delivery_cost', 0);
        
        $turnaroundTypes = \App\Models\TurnaroundType::all();
        $extras = \App\Models\Extra::where('is_active', true)->get();
        $shopLat = \App\Models\Setting::get('shop_latitude', '6.9271');
        $shopLng = \App\Models\Setting::get('shop_longitude', '79.8612');
        
        // Fetch delivery ranges for distance cost calculation
        $deliveryRanges = \App\Models\DeliveryRange::orderBy('min_distance_km')->get();
        
        // Fetch all active workshops AND delivery-enabled branches for map markers & distance calculation
        $workshops = \App\Models\Branch::where('is_active', true)
            ->where(function($q) {
                $q->where('type', 'workshop')
                  ->orWhere('is_delivery_enabled', true);
            })
            ->get();
        // Fallback to first branch if none exist
        if ($workshops->isEmpty()) {
            $fallback = \App\Models\Branch::where('is_active', true)->first();
            if ($fallback) $workshops = collect([$fallback]);
        }
        // For backward compat: single workshop variable pointing to first
        $workshop = $workshops->first();

        return view('customer.booking.index', compact('paymentMethods', 'categories', 'pickupTimePeriods', 'minimumOrderLimit', 'deliveryRanges', 'workshop', 'workshops', 'turnaroundTypes', 'extras', 'shopLat', 'shopLng'));
    }

    public function getBranchServices(Branch $branch)
    {
        $categories = \App\Models\ServiceCategory::with([
            'children.services' => function($q) {
                $q->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'services' => function($q) {
                $q->where('is_visible_on_website', true)->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)->orderBy('order_index')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('customer.booking')->withErrors(['error' => 'Please login or register to complete your booking.']);
        }

        try {
            $validated = $request->validate([
                'pickup_address' => 'required|string|max:255',
                'map_address' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'pickup_date' => 'required|date|after_or_equal:today',
                'pickup_time_period_id' => 'required|exists:pickup_time_periods,id',
                'item_description' => 'nullable|string',
                'notes' => 'nullable|string',

                // New fields
                'delivery_preference' => 'required|in:deliver_to_address,pickup_from_branch',
                'same_as_pickup' => 'nullable|boolean',
                'delivery_lat' => 'nullable|numeric',
                'delivery_lng' => 'nullable|numeric',
                'delivery_address' => 'nullable|string|max:255',
                'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'estimated_delivery_fee' => 'nullable|numeric',
            ]);
        } catch (ValidationException $e) {
            \Log::error('Booking Validation Failed', $e->errors());
            throw $e;
        }

        $pickupPeriod = \App\Models\PickupTimePeriod::find($request->pickup_time_period_id);
        $pickupScheduledAt = \Carbon\Carbon::parse($request->pickup_date . ' ' . $pickupPeriod->start_time);

        $deliveryLat = $request->delivery_lat;
        $deliveryLng = $request->delivery_lng;
        $deliveryAddress = $request->delivery_address;

        if ($request->delivery_preference === 'deliver_to_address' && $request->has('same_as_pickup')) {
            $deliveryLat = $request->latitude;
            $deliveryLng = $request->longitude;
            $deliveryAddress = $request->pickup_address;
        }

        $receiptPath = null;
        $deliveryCost = 0;

        // Find all delivery-capable branches: workshops OR delivery-enabled branches
        $deliveryBranches = Branch::where('is_active', true)
            ->where(function($q) {
                $q->where('type', 'workshop')
                  ->orWhere('is_delivery_enabled', true);
            })
            ->get();
        $closestWorkshop = null;
        $shortestDistance = PHP_FLOAT_MAX;
        $totalDistance = 0;

        // Haversine formula function
        $calculateDistance = function($lat1, $lon1, $lat2, $lon2) {
            if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;
            $earthRadius = 6371; // km
            $latDelta = deg2rad($lat2 - $lat1);
            $lonDelta = deg2rad($lon2 - $lon1);
            $a = sin($latDelta / 2) * sin($latDelta / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) * sin($lonDelta / 2);
            return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
        };

        // Find the closest delivery-capable branch based on pickup coordinates
        if ($request->latitude && $request->longitude) {
            foreach ($deliveryBranches as $ws) {
                if ($ws->latitude && $ws->longitude) {
                    $dist = $calculateDistance($ws->latitude, $ws->longitude, $request->latitude, $request->longitude);
                    if ($dist < $shortestDistance) {
                        $shortestDistance = $dist;
                        $closestWorkshop = $ws;
                    }
                }
            }
        }

        // Fallback to first delivery-capable branch if none matched coordinates
        if (!$closestWorkshop) {
            $closestWorkshop = $deliveryBranches->first();
        }

        if ($closestWorkshop && $closestWorkshop->latitude && $closestWorkshop->longitude) {
            $distanceToPickup = $shortestDistance == PHP_FLOAT_MAX ? 0 : $shortestDistance;
            $distanceToDelivery = 0;

            if ($request->delivery_preference === 'deliver_to_address') {
                if ($request->has('same_as_pickup')) {
                    $distanceToDelivery = $distanceToPickup;
                } else if ($deliveryLat && $deliveryLng) {
                    $distanceToDelivery = $calculateDistance($closestWorkshop->latitude, $closestWorkshop->longitude, $deliveryLat, $deliveryLng);
                }
            }

            $getFeeForDistance = function($dist) use ($closestWorkshop) {
                if ($dist <= 0) return 0;
                $range = \App\Models\DeliveryRange::where(function($q) use ($closestWorkshop) {
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
            if ($request->delivery_preference === 'deliver_to_address') {
                $deliveryFee = $getFeeForDistance($distanceToDelivery);
            }

            $deliveryCost = $pickupFee + $deliveryFee;
        }
        
        // If delivery range cost is not set, use the frontend estimate
        if ($deliveryCost == 0 && $request->filled('estimated_delivery_fee')) {
            $deliveryCost = (float) $request->estimated_delivery_fee;
        }

        $appointment = DB::transaction(function () use ($request, $deliveryLat, $deliveryLng, $deliveryAddress, $receiptPath, $deliveryCost, $pickupScheduledAt, $closestWorkshop) {
            
            $customer = auth('customer')->user();

            $appointment = DeliveryAppointment::create([
                'customer_id' => $customer->id,
                'branch_id' => $closestWorkshop->id,
                'pickup_address' => $request->pickup_address,
                'map_address' => $request->map_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'pickup_scheduled_at' => $pickupScheduledAt,
                'item_description' => $request->item_description,
                'cart_json' => $request->cart_json,
                'notes' => $request->notes,
                'contact_phone' => $customer->phone,
                'status' => 'pending',

                // New fields
                'payment_method' => 'pending',
                'payment_timing' => 'pending',
                'delivery_preference' => $request->delivery_preference,
                'delivery_lat' => $deliveryLat,
                'delivery_lng' => $deliveryLng,
                'delivery_address' => $deliveryAddress,
                'receipt_path' => $receiptPath,
                'delivery_cost' => $deliveryCost,
            ]);

            $admin = \App\Models\User::where('branch_id', $closestWorkshop->id)->first() ?? \App\Models\User::first();
            
            $order = \App\Models\Order::create([
                'order_number' => \App\Models\Order::generateOrderNumber($closestWorkshop->id, true),
                'customer_id' => $customer->id,
                'branch_id' => $closestWorkshop->id,
                'status' => 'received',
                'subtotal' => 0,
                'total' => $deliveryCost,
                'delivery_fee' => $deliveryCost,
                'paid_amount' => 0,
                'payment_status' => 'pending',
                'payment_method' => 'cash',
                'due_date' => \Carbon\Carbon::parse($pickupScheduledAt)->addDays(2),
                'created_by' => $admin ? $admin->id : 1,
                'is_delivery_order' => true,
                'delivery_appointment_id' => $appointment->id,
                'delivery_address' => $deliveryAddress,
                'notes' => $request->notes,
            ]);

            $appointment->update(['order_id' => $order->id]);

            return $appointment;
        });



        // System Alert: notify the right admin based on branch type
        if ($closestWorkshop->type === 'workshop') {
            // Workshop branch → notify workshop admins
            $admins = \App\Models\User::where('role', 'workshop_admin')->where('branch_id', $closestWorkshop->id)->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SystemAlertNotification('New Booking Received: #' . $appointment->id, route('workshop.operations'), 'new_order'));
        } else {
            // Delivery-enabled regular branch → notify branch admins
            $admins = \App\Models\User::whereIn('role', ['branch_admin', 'admin'])->where('branch_id', $closestWorkshop->id)->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\SystemAlertNotification('New Delivery Booking: #' . $appointment->id, route('delivery.index'), 'new_order'));
        }

        return redirect()->route('customer.booking.success', $appointment->order_number ?? $appointment->id);
    }

    public function success(DeliveryAppointment $appointment)
    {
        return view('booking.success', compact('appointment'));
    }
}

