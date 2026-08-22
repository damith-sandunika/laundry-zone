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
        $branches = Branch::regularBranches()->where('is_active', true)->get();
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();
        $categories = \App\Models\ServiceCategory::with('services')->where('is_active', true)->get();

        return view('site.booking', compact('branches', 'paymentMethods', 'categories'));
    }

    public function store(Request $request, WhatsAppService $waService)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:50',
                'password' => 'nullable|string|min:8',
                'branch_id' => 'required|exists:branches,id',
                'pickup_address' => 'required|string|max:255',
                'map_address' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'pickup_scheduled_at' => 'required|date|after:now',
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

        $appointment = DB::transaction(function () use ($request, $deliveryLat, $deliveryLng, $deliveryAddress, $receiptPath) {
            // Find or create customer based on phone globally
            $customer = Customer::where('phone', $request->phone)->first();
            if (! $customer) {
                $customer = Customer::create([
                    'phone' => $request->phone,
                    'name' => $request->name,
                    'address' => $request->pickup_address,
                    'branch_id' => $request->branch_id,
                    'password' => $request->password ? \Illuminate\Support\Facades\Hash::make($request->password) : null,
                ]);
            } elseif ($request->password && empty($customer->password)) {
                $customer->update([
                    'password' => \Illuminate\Support\Facades\Hash::make($request->password)
                ]);
            }

            // Auto login if not logged in and a password is provided (or just registered)
            if (!auth('customer')->check() && !empty($customer->password)) {
                auth('customer')->login($customer);
            }

            return DeliveryAppointment::create([
                'customer_id' => $customer->id,
                'branch_id' => $request->branch_id,
                'pickup_address' => $request->pickup_address,
                'map_address' => $request->map_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'pickup_scheduled_at' => $request->pickup_scheduled_at,
                'item_description' => $request->item_description,
                'notes' => $request->notes,
                'contact_phone' => $request->phone,
                'status' => 'pending',

                // New fields
                'payment_method' => $request->payment_method,
                'payment_timing' => $request->payment_timing,
                'delivery_preference' => $request->delivery_preference,
                'delivery_lat' => $deliveryLat,
                'delivery_lng' => $deliveryLng,
                'delivery_address' => $deliveryAddress,
                'receipt_path' => $receiptPath,
            ]);
        });

        // WhatsApp notification
        $waService->notifyBookingReceived($appointment);

        return redirect()->route('site.success', $appointment->id);
    }

    public function success(DeliveryAppointment $appointment)
    {
        return view('site.success', compact('appointment'));
    }
}
