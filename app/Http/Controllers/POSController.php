<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\CustomerPackageService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class POSController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // For super admin without a branch_id, fetch all, otherwise fetch branch specific
        $branchId = $this->getActiveBranchId();

        // Workshop admins bypass shift requirement (they don't have a register/cash till)
        $isWorkshopAdmin = $user->role === 'workshop_admin' || ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop');
        if (!$isWorkshopAdmin) {
            $activeSession = RegisterSession::where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->first();

            if (! $activeSession) {
                return redirect()->route('dashboard')->with('error', 'You must open a shift to use the POS system.');
            }
        }

        $services = Cache::rememberForever('pos_services', function () {
            return ServiceType::where('is_active', true)->orderBy('order_index')->get();
        });

        $categories = Cache::rememberForever('pos_categories', function () {
            return ServiceCategory::with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('order_index');
            }])->where('is_active', true)->orderBy('order_index')->get();
        });

        $customers = collect();
        $customerPackages = collect();

        $timeSlots = Cache::rememberForever('pos_timeslots', function () {
            return \App\Models\PickupTimePeriod::where('is_active', true)->orderBy('start_time')->get();
        });

        $extras = Cache::rememberForever('pos_extras', function () {
            return \App\Models\Extra::where('is_active', true)->get();
        });

        $turnaroundTypes = Cache::rememberForever('pos_turnarounds', function () {
            return \App\Models\TurnaroundType::where('is_active', true)->get();
        });

        return view('pos.index', compact('services', 'categories', 'customers', 'customerPackages', 'timeSlots', 'extras', 'turnaroundTypes'));
    }

    public function store(\App\Http\Requests\POS\StorePosOrderRequest $request, \App\Actions\POS\CreatePosOrderAction $action)
    {
        $user = auth()->user();
        $branchId = $this->getActiveBranchId();

        $isWorkshopAdmin = $user->role === 'workshop_admin' || ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop');
        if (!$isWorkshopAdmin) {
            $activeSession = RegisterSession::where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->first();

            if (! $activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot place order. Shift is closed.',
                ], 403);
            }
        }

        try {
            $result = $action->execute($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $result['order_id'],
                'order_number' => $result['order_number'],
                'customer_name' => $result['customer_name'],
                'due_date' => $result['due_date'],
                'cart' => $result['cart'],
                'total' => $result['total'],
            ]);

        } catch (\Exception $e) {
            \Log::error('POSController::placeOrder error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while placing the order. Please try again.',
            ], 500);
        }
    }

    /**
     * Create a Delivery Appointment (and optionally an Order) from the POS.
     * Called when a staff member takes a phone-in delivery request.
     */
    public function storeDelivery(\App\Http\Requests\POS\StorePosDeliveryOrderRequest $request, \App\Actions\POS\CreatePosDeliveryOrderAction $action)
    {
        try {
            $result = $action->execute(auth()->user(), $request->validated());

            return response()->json([
                'success'        => true,
                'message'        => $result['has_order']
                    ? 'Delivery request created with order #'.$result['order_number'].'! Go to Delivery page to assign a rider.'
                    : 'Delivery request created! Go to Delivery page to assign a rider.',
                'appointment_id' => $result['appointment_id'],
                'order_id'       => $result['order_id'],
                'order_number'   => $result['order_number'],
                'has_order'      => $result['has_order'],
                'redirect_url'   => route('delivery.index'),
            ]);
        } catch (\Exception $e) {
            \Log::error('POSController::placeDeliveryOrder error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while placing the delivery order. Please try again.',
            ], 500);
        }
    }
}

