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
        $categories = ServiceCategory::with([
            'services' => function($q) {
                $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'children' => function($q) {
                $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'children.services' => function($q) {
                $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index')->get();
        $packages = Package::with('services.serviceType')->where('is_active', true)->get();

        $turnaroundTypes = \App\Models\TurnaroundType::all();
        $extras = \App\Models\Extra::where('is_active', true)->get();

        $pickupTimePeriods = \App\Models\PickupTimePeriod::where('is_active', true)->orderBy('start_time')->get();
        $minimumOrderLimit = \App\Models\Setting::get('minimum_delivery_cost', 0);
        $shopLat = \App\Models\Setting::get('shop_latitude', '6.9271');
        $shopLng = \App\Models\Setting::get('shop_longitude', '79.8612');
        $deliveryRanges = \App\Models\DeliveryRange::orderBy('min_distance_km')->get();

        $workshops = \App\Models\Branch::where('is_active', true)
            ->where(function($q) {
                $q->where('type', 'workshop')
                  ->orWhere('is_delivery_enabled', true);
            })
            ->get();
        if ($workshops->isEmpty()) {
            $fallback = \App\Models\Branch::where('is_active', true)->first();
            if ($fallback) $workshops = collect([$fallback]);
        }

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

        return view('site.home', compact('branches', 'categories', 'packages', 'turnaroundTypes', 'extras', 'pickupTimePeriods', 'minimumOrderLimit', 'shopLat', 'shopLng', 'deliveryRanges', 'workshops', 'paymentMethods'));
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
            'children' => function($q) {
                $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'children.services' => function($q) {
                $q->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'services' => function($q) {
                $q->where('is_visible_on_website', true)->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index')->get();

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
        $branchId = $branch->id;

        $categories = \App\Models\ServiceCategory::with([
            'children' => function($q) use ($branchId) {
                $q->where('is_active', true)
                  ->where('is_visible_on_website', true)
                  ->where(function($sq) use ($branchId) {
                      $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                  })
                  ->orderBy('order_index')
                  ->with(['services' => function($sq) use ($branchId) {
                      $sq->where('is_visible_on_website', true)
                         ->where(function($ssq) use ($branchId) {
                             $ssq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                         })
                         ->orderBy('order_index');
                  }]);
            },
            'services' => function($q) use ($branchId) {
                $q->where('is_visible_on_website', true)
                  ->where(function($sq) use ($branchId) {
                      $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                  })
                  ->orderBy('order_index');
            }
        ])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('is_visible_on_website', true)
            ->where(function($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            })
            ->orderBy('order_index')
            ->get();

        $pickupTimePeriods = \App\Models\PickupTimePeriod::where('is_active', true)
            ->where(function($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            })
            ->orderBy('start_time')
            ->get();

        $turnaroundTypes = \App\Models\TurnaroundType::where(function($q) use ($branchId) {
            $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->get();

        $extras = \App\Models\Extra::where('is_active', true)
            ->where(function($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            })
            ->get();

        return response()->json([
            'categories'        => $categories,
            'pickupTimePeriods' => $pickupTimePeriods,
            'turnaroundTypes'   => $turnaroundTypes,
            'extras'            => $extras,
        ]);
    }

    public function store(\App\Http\Requests\Customer\CustomerStoreBookingRequest $request, \App\Actions\Customer\StoreCustomerBookingAction $action)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('customer.booking')->withErrors(['error' => 'Please login or register to complete your booking.']);
        }

        try {
            $appointment = $action->execute($request->validated(), auth('customer')->user());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'redirect' => route('customer.booking.success', $appointment->order_number)
                ]);
            }
            return redirect()->route('customer.booking.success', $appointment->order_number);
        } catch (\Exception $e) {
            \Log::error('Customer Booking Failed: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Booking failed: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Booking failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function success(\App\Models\Order $order)
    {
        $appointment = $order->deliveryAppointment;
        return view('booking.success', compact('appointment', 'order'));
    }
}

