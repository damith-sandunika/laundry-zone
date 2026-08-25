<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryAppointment;
use App\Models\Package;
use App\Models\PaymentSetting;
use App\Models\ServiceCategory;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerSiteController extends Controller
{
    public function home()
    {
        $branches = Branch::regularBranches()->where('is_active', true)->get();
        $categories = ServiceCategory::with('services')->where('is_active', true)->get();
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
        $categories = \App\Models\ServiceCategory::with('services')->where('is_active', true)->get();
        $pickupTimePeriods = \App\Models\PickupTimePeriod::where('is_active', true)->orderBy('start_time')->get();

        return view('customer.booking.index', compact('paymentMethods', 'categories', 'pickupTimePeriods'));
    }

    public function store(Request $request, WhatsAppService $waService)
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
                'payment_method' => 'required|string',
                'payment_timing' => 'required|in:at_pickup,at_delivery',
                'delivery_preference' => 'required|in:deliver_to_address,pickup_from_branch',
                'same_as_pickup' => 'nullable|boolean',
                'delivery_lat' => 'nullable|numeric',
                'delivery_lng' => 'nullable|numeric',
                'delivery_address' => 'nullable|string|max:255',
                'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
        }

        $deliveryCost = 0;
        $workshops = Branch::workshops()->where('is_active', true)->get();
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

        // Find the closest workshop based on pickup coordinates
        if ($request->latitude && $request->longitude) {
            foreach ($workshops as $ws) {
                if ($ws->latitude && $ws->longitude) {
                    $dist = $calculateDistance($ws->latitude, $ws->longitude, $request->latitude, $request->longitude);
                    if ($dist < $shortestDistance) {
                        $shortestDistance = $dist;
                        $closestWorkshop = $ws;
                    }
                }
            }
        }

        // Fallback to first workshop if none matched coordinates
        if (!$closestWorkshop) {
            $closestWorkshop = $workshops->first();
        }

        if ($closestWorkshop && $closestWorkshop->latitude && $closestWorkshop->longitude) {
            $totalDistance += $shortestDistance == PHP_FLOAT_MAX ? 0 : $shortestDistance;

            // Calculate distance for delivery if different from pickup
            if ($request->delivery_preference === 'deliver_to_address') {
                if ($request->has('same_as_pickup')) {
                    if ($request->latitude && $request->longitude) {
                        $totalDistance += $calculateDistance($closestWorkshop->latitude, $closestWorkshop->longitude, $request->latitude, $request->longitude);
                    }
                } else if ($deliveryLat && $deliveryLng) {
                    $totalDistance += $calculateDistance($closestWorkshop->latitude, $closestWorkshop->longitude, $deliveryLat, $deliveryLng);
                }
            }

            // Find applicable delivery range for the closest workshop
            $deliveryRange = \App\Models\DeliveryRange::where(function($q) use ($closestWorkshop) {
                    $q->where('branch_id', $closestWorkshop->id)->orWhereNull('branch_id');
                })
                ->where('min_distance_km', '<=', $totalDistance)
                ->where('max_distance_km', '>=', $totalDistance)
                ->orderBy('cost', 'asc')
                ->first();
                
            if ($deliveryRange) {
                $deliveryCost = $deliveryRange->cost;
            }
        }

        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);
        $deliveryCost = max($deliveryCost, $minimumDeliveryCost);

        $appointment = DB::transaction(function () use ($request, $deliveryLat, $deliveryLng, $deliveryAddress, $receiptPath, $deliveryCost, $pickupScheduledAt, $closestWorkshop) {
            
            $customer = auth('customer')->user();

            return DeliveryAppointment::create([
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
                'payment_method' => $request->payment_method,
                'payment_timing' => $request->payment_timing,
                'delivery_preference' => $request->delivery_preference,
                'delivery_lat' => $deliveryLat,
                'delivery_lng' => $deliveryLng,
                'delivery_address' => $deliveryAddress,
                'receipt_path' => $receiptPath,
                'delivery_cost' => $deliveryCost,
            ]);
        });

        // WhatsApp notification
        $waService->notifyBookingReceived($appointment);

        return redirect()->route('customer.booking.success', $appointment->id);
    }

    public function success(DeliveryAppointment $appointment)
    {
        return view('booking.success', compact('appointment'));
    }
}
