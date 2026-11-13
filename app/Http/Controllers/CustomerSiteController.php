<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CustomerSiteService;
use App\Services\Tenancy\TenantManager;
use App\Http\Requests\Customer\CustomerStoreBookingRequest;
use App\Actions\Customer\StoreCustomerBookingAction;
use Illuminate\Support\Facades\Log;

class CustomerSiteController extends Controller
{
    protected CustomerSiteService $siteService;

    public function __construct(CustomerSiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    public function home()
    {
        if (!TenantManager::hasTenant()) {
            return redirect()->route('super_admin.login');
        }

        $redirectUrl = Setting::get('site_landing_page', '');
        if (!empty($redirectUrl)) {
            return redirect($redirectUrl);
        }

        // If the website folder is empty or not enabled, redirect to staff login
        if (!view()->exists('website.home')) {
            return redirect()->route('login');
        }

        $data = $this->siteService->getHomeData();
        return view('website.home', $data);
    }

    public function booking()
    {
        $data = $this->siteService->getBookingData();
        return view('customer.booking.index', $data);
    }

    public function getBranchServices(Branch $branch)
    {
        $data = $this->siteService->getBranchServicesData($branch);
        return response()->json($data);
    }

    public function store(CustomerStoreBookingRequest $request, StoreCustomerBookingAction $action)
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
            Log::error('Customer Booking Failed: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Booking failed: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Booking failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function success(Order $order)
    {
        $appointment = $order->deliveryAppointment;
        return view('booking.success', compact('appointment', 'order'));
    }
}
