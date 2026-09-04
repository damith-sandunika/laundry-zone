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

class POSController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // For super admin without a branch_id, fetch all, otherwise fetch branch specific
        $branchId = $user->branch_id ?? \App\Models\Branch::first()->id;

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

        $services = ServiceType::where('is_active', true)->orderBy('order_index')->get();
        $categories = ServiceCategory::with(['children' => function($q) {
            $q->where('is_active', true)->orderBy('order_index');
        }])->where('is_active', true)->orderBy('order_index')->get();

        $customers = Customer::withoutGlobalScopes()->where('is_active', true)->get();

        $customerPackages = CustomerPackage::where('status', 'active')
            ->with(['package', 'services.serviceType'])
            ->get();

        $timeSlots = \App\Models\PickupTimePeriod::where('is_active', true)->orderBy('start_time')->get();

        $extras = \App\Models\Extra::where('is_active', true)->where('branch_id', $branchId)->get();
        $turnaroundTypes = \App\Models\TurnaroundType::where('is_active', true)->where('branch_id', $branchId)->get();

        return view('pos.index', compact('services', 'categories', 'customers', 'customerPackages', 'timeSlots', 'extras', 'turnaroundTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer_name' => 'nullable|string|max:255',
            'new_customer_phone' => 'nullable|string|max:255',
            'cart' => 'required|array|min:1',
            'payment_mode' => 'nullable|in:pay_now,pay_on_pickup',
            'due_date' => 'required|string',
        ]);

        $user = auth()->user();
        $branchId = $user->branch_id ?? \App\Models\Branch::first()->id;

        // Workshop admins skip register session check
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

        // Process cart with package deduction
        $packageId = $request->customer_package_id ?? null;
        $activePackage = null;
        $packageServices = collect();

        if ($packageId) {
            $activePackage = CustomerPackage::with('services')->find($packageId);
            if ($activePackage && $activePackage->status === 'active') {
                $packageServices = $activePackage->services->keyBy('service_type_id');
            } else {
                $packageId = null;
            }
        }

        $processedCart = [];
        $subtotal = 0;
        $packageDeductions = [];

        foreach ($request->cart as $item) {
            $serviceTypeId = $item['service_type_id'] ?? $item['id'];
            $qty = (float) $item['qty'];
            $basePrice = (float) $item['base_price'];

            $turnaroundTypeId = !empty($item['turnaround_type_id']) ? $item['turnaround_type_id'] : null;
            $selectedExtras = $item['selected_extras'] ?? [];

            // Extras will be added as separate items below

            $coveredQty = 0;
            $chargeableQty = $qty;

            // Check if service is covered by package
            if ($packageId) {
                if (empty($item['is_special']) && $packageServices->has($serviceTypeId)) {
                    $ps = $packageServices->get($serviceTypeId);
                    $remaining = max(0, $ps->total_quantity - $ps->used_quantity);

                    if ($qty > $remaining) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot process order. Requested quantity ({$qty}) for '{$item['name']}' exceeds the remaining package limit ({$remaining}).",
                        ], 403);
                    }

                    $coveredQty = $qty;
                    $chargeableQty = 0;

                    $packageDeductions[$ps->id] = $coveredQty;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot process order. '{$item['name']}' is not covered by the selected package.",
                    ], 403);
                }
            }

            $itemSubtotalBeforeTurnaround = ($chargeableQty * $basePrice);
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
                'name' => $item['name'],
                'qty' => $qty,
                'base_price' => $basePrice,
                'covered_qty' => $coveredQty,
                'chargeable_qty' => $chargeableQty,
                'total_price' => $itemSubtotal,
                'is_special' => $item['is_special'] ?? false,
                'turnaround_type_id' => $turnaroundTypeId,
                'turnaround_extra_charge' => $turnaroundCharge,
                'selected_extras' => [], // Processed as separate items
            ];

            // Add Extras as separate items
            if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                foreach ($selectedExtras as $selEx) {
                    $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                    $extraQty = is_array($selEx) && isset($selEx['qty']) ? $selEx['qty'] : 1;
                    
                    $extra = \App\Models\Extra::find($extraId);
                    if ($extra) {
                        $extraService = \App\Models\ServiceType::firstOrCreate(
                            ['name' => 'Extra: ' . $extra->name, 'branch_id' => $branchId],
                            [
                                'color' => 'slate', 
                                'price_type' => 'per_item', 
                                'base_price' => $extra->price, 
                                'loyalty_points_earn' => 0, 
                                'is_active' => 1, 
                                'description' => 'Extra service'
                            ]
                        );

                        $extraSubtotalBeforeTurnaround = $extra->price * $extraQty;
                        
                        // Extras do NOT get turnaround markup, as requested by user.
                        $extraTurnaroundCharge = 0;
                        
                        $extraSubtotal = $extraSubtotalBeforeTurnaround + $extraTurnaroundCharge;
                        $subtotal += $extraSubtotal;

                        $processedCart[] = [
                            'service_type_id' => $extraService->id,
                            'name' => 'Extra: ' . $extra->name,
                            'qty' => $extraQty,
                            'base_price' => $extra->price,
                            'covered_qty' => 0,
                            'chargeable_qty' => $extraQty,
                            'total_price' => $extraSubtotal,
                            'is_special' => false,
                            'turnaround_type_id' => null, // No turnaround applied to extras
                            'turnaround_extra_charge' => 0,
                            'selected_extras' => [],
                        ];
                    }
                }
            }
        }

        $discountPercentage = max(0, min(100, (float) ($request->discount_percentage ?? 0)));
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $total = $subtotal - $discountAmount;
        $paymentMode = $request->payment_mode ?? 'pay_on_pickup';
        $paidAmount = $paymentMode === 'pay_now' ? $total : 0;

        try {
            $result = DB::transaction(function () use ($request, $user, $branchId, $subtotal, $discountAmount, $discountPercentage, $total, $paidAmount, $paymentMode, $processedCart, $packageId, $activePackage, $packageDeductions) {
                // 1. Resolve Customer
                $customerId = $request->customer_id;
                if (! $customerId) {
                    // Walk-in customer: find or create globally (not branch-scoped)
                    $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                        ['phone' => '0000000000'],
                        ['name' => 'Walk-in Customer', 'branch_id' => $branchId]
                    );
                    $customerId = $customer->id;
                }

                // Update customer default_discount if discount > 0
                if ($discountPercentage > 0 && $customerId) {
                    \App\Models\Customer::where('id', $customerId)
                        ->update(['default_discount' => $discountPercentage]);
                }

                // Calculate Profit Split
                $branchProfit = 0;
                $workshopProfit = 0;
                $branch = \App\Models\Branch::find($branchId);
                if ($branch && $branch->branch_profit_percentage > 0) {
                    $branchProfit = $subtotal * ($branch->branch_profit_percentage / 100);
                    $workshopProfit = $subtotal - $branchProfit;
                }
                
                // 2. Create Order
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber($branchId),
                    'branch_id' => $branchId,
                    'customer_id' => $customerId,
                    'created_by' => auth()->id(),
                    'status' => 'received',
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'additional' => 0,
                    'total' => $total,
                    'paid_amount' => $paidAmount,
                    'payment_method' => $paymentMode === 'pay_now' ? ($request->payment_method ?? 'cash') : null,
                    'due_date' => $request->due_date ? date('Y-m-d H:i:s', strtotime($request->due_date)) : null,
                    'branch_profit' => $branchProfit,
                    'workshop_profit' => $workshopProfit,
                ]);

                if ($paidAmount > 0 && $paymentMode === 'pay_now') {
                    PaymentTransaction::create([
                        'order_id' => $order->id,
                        'amount' => $paidAmount,
                        'payment_method' => $request->payment_method ?? 'cash',
                        'created_by' => auth()->id(),
                    ]);
                }

                // Status History
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'received',
                    'changed_by' => auth()->id(),
                ]);

                // Deduct Package Quantities
                if ($packageId && count($packageDeductions) > 0) {
                    foreach ($packageDeductions as $cpsId => $qtyUsed) {
                        CustomerPackageService::where('id', $cpsId)
                            ->increment('used_quantity', $qtyUsed);
                    }

                    // Check if package is exhausted
                    $allServices = CustomerPackageService::where('customer_package_id', $packageId)->get();
                    $isExhausted = true;
                    foreach ($allServices as $s) {
                        if ($s->used_quantity < $s->total_quantity) {
                            $isExhausted = false;
                            break;
                        }
                    }
                    if ($isExhausted) {
                        $activePackage->update(['status' => 'exhausted']);
                    }
                }

                // 3. Create Items & Deduct Inventory
                foreach ($processedCart as $item) {
                    $serviceTypeId = $item['service_type_id'];

                    if (! empty($item['is_special'])) {
                        $specialService = ServiceType::firstOrCreate(
                            ['name' => 'Special Service'],
                            ['color' => 'purple', 'price_type' => 'per_item', 'base_price' => 0, 'loyalty_points_earn' => 0, 'is_active' => 1, 'description' => 'Custom one-time service']
                        );
                        $serviceTypeId = $specialService->id;
                    }

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'service_type_id' => $serviceTypeId,
                        'item_description' => $item['name'].($item['covered_qty'] > 0 ? " ({$item['covered_qty']} via Package)" : ''),
                        'quantity' => $item['qty'],
                        'unit_price' => $item['base_price'],
                        'total_price' => $item['total_price'],
                        'turnaround_type_id' => $item['turnaround_type_id'],
                        'turnaround_extra_charge' => $item['turnaround_extra_charge'],
                    ]);

                    // Extras are now created as separate OrderItem entries above.

                }

                // 4. Communication notification queue
                $commService = new CommunicationService();
                $orderForComm = Order::with('customer')->find($order->id);
                
                if (! $orderForComm->is_delivery_order) {
                    $branch = \App\Models\Branch::find($branchId);
                    if ($branch && $branch->type === 'branch') {
                        $commService->sendCollectionOrderPlaced($orderForComm);
                    } else {
                        $commService->sendPosWalkinPlaced($orderForComm);
                    }
                }

                // 5. If workshop admin: auto-dispatch to operations as 'processing'
                $isWorkshopAdmin = $user->role === 'workshop_admin' || ($user->role === 'staff' && $branch && $branch->type === 'workshop');
                if ($isWorkshopAdmin) {
                    \App\Models\WorkshopDispatch::create([
                        'order_id'           => $order->id,
                        'branch_id'          => $branchId,
                        'workshop_branch_id' => $branchId,
                        'dispatched_by'      => auth()->id(),
                        'dispatched_at'      => now(),
                        'status'             => 'processing',
                        'received_by'        => auth()->id(),
                        'received_at_workshop_at' => now(),
                        'dispatch_notes'     => 'Walk-in POS Order',
                    ]);
                    $order->update(['status' => 'processing']);
                }

                return [
                    'success' => true,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $orderForComm->customer->name ?? 'Walk-in Customer',
                    'due_date' => $order->due_date ? $order->due_date->format('d M Y') : '-',
                    'cart' => collect($processedCart)->map(fn ($i) => [
                        'name' => $i['name'],
                        'qty' => $i['qty'],
                    ])->values(),
                    'total' => $total,
                ];
            });

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
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a Delivery Appointment (and optionally an Order) from the POS.
     * Called when a staff member takes a phone-in delivery request.
     */
    public function storeDelivery(Request $request)
    {
        $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'pickup_address'   => 'required|string|max:500',
            'delivery_address' => 'nullable|string|max:500',
            'pickup_date'      => 'required|string',
            'notes'            => 'nullable|string|max:500',
            'cart'             => 'nullable|array',
            'payment_mode'     => 'nullable|in:pay_now,pay_on_pickup',
        ]);

        $user     = auth()->user();
        $branchId = $user->branch_id ?? 1;

        try {
            $result = DB::transaction(function () use ($request, $user, $branchId) {
                $pickupDate = date('Y-m-d H:i:s', strtotime($request->pickup_date));
                $cart       = $request->cart ?? [];

                // 1. Create the DeliveryAppointment (auto-confirmed since staff created it)
                $appointment = \App\Models\DeliveryAppointment::create([
                    'customer_id'          => $request->customer_id,
                    'branch_id'            => $branchId,
                    'pickup_address'       => $request->pickup_address,
                    'delivery_address'     => $request->delivery_address ?? $request->pickup_address,
                    'pickup_scheduled_at'  => $pickupDate,
                    'delivery_preference'  => 'deliver_to_address',
                    'payment_method'       => $request->payment_mode === 'pay_now' ? 'cash' : 'cash',
                    'status'               => 'confirmed', // auto-confirmed since staff initiated
                    'notes'                => $request->notes,
                    'contact_phone'        => \App\Models\Customer::find($request->customer_id)?->phone,
                ]);

                $orderId     = null;
                $orderNumber = null;

                // 2. If cart items were provided, create the Order now
                if (! empty($cart)) {
                    $subtotal = 0;
                    foreach ($cart as $item) {
                        $subtotal += (float) $item['base_price'] * (float) $item['qty'];
                    }

                    $paymentMode = $request->payment_mode ?? 'pay_on_pickup';
                    $paidAmount  = $paymentMode === 'pay_now' ? $subtotal : 0;

                    $order = Order::create([
                        'order_number'            => Order::generateOrderNumber($branchId, true),
                        'branch_id'               => $branchId,
                        'customer_id'             => $request->customer_id,
                        'created_by'              => auth()->id(),
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
                        'delivery_address'        => $request->delivery_address ?? $request->pickup_address,
                        'notes'                   => $request->notes,
                    ]);

                    // Save cart items
                    foreach ($cart as $item) {
                        \App\Models\OrderItem::create([
                            'order_id'        => $order->id,
                            'service_type_id' => $item['service_type_id'] ?? $item['id'],
                            'item_description' => $item['name'],
                            'quantity'        => $item['qty'],
                            'unit_price'      => $item['base_price'],
                            'total_price'     => (float) $item['base_price'] * (float) $item['qty'],
                        ]);
                    }

                    // Save cart_json to appointment so rider can see it
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to create delivery request: '.$e->getMessage(),
            ], 500);
        }
    }
}

