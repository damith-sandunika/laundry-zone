<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class POSController extends Controller
{
    public function index()
    {
        // For super admin without a branch_id, fetch all, otherwise fetch branch specific
        $branchId = auth()->user()->branch_id ?? 1;

        $activeSession = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->whereNull('closed_at')
            ->first();
            
        if (!$activeSession) {
            return redirect()->route('dashboard')->with('error', 'You must open a shift to use the POS system.');
        }
        
        $services = \App\Models\ServiceType::where('is_active', true)->get();
        
        $customersQuery = \App\Models\Customer::where('is_active', true);
        if ($branchId) {
            $customersQuery->where('branch_id', $branchId);
        }
        $customers = $customersQuery->get();

        $customerPackages = \App\Models\CustomerPackage::where('branch_id', $branchId)
            ->where('status', 'active')
            ->with(['package', 'services.serviceType'])
            ->get();
                        
        return view('pos.index', compact('services', 'customers', 'customerPackages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'       => 'nullable|exists:customers,id',
            'new_customer_name' => 'nullable|string|max:255',
            'new_customer_phone'=> 'nullable|string|max:255',
            'cart'              => 'required|array|min:1',
            'payment_mode'      => 'nullable|in:pay_now,pay_on_pickup',
            'due_date'          => 'required|string',
        ]);

        $branchId = auth()->user()->branch_id ?? 1;

        $activeSession = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->whereNull('closed_at')
            ->first();

        if (!$activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot place order. Shift is closed.'
            ], 403);
        }

        // Process cart with package deduction
        $packageId = $request->customer_package_id ?? null;
        $activePackage = null;
        $packageServices = collect();

        if ($packageId) {
            $activePackage = \App\Models\CustomerPackage::with('services')->find($packageId);
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
            $qty = (float)$item['qty'];
            $basePrice = (float)$item['base_price'];
            
            $coveredQty = 0;
            $chargeableQty = $qty;

            // Check if service is covered by package
            if ($packageId && empty($item['is_special']) && $packageServices->has($serviceTypeId)) {
                $ps = $packageServices->get($serviceTypeId);
                $remaining = max(0, $ps->total_quantity - $ps->used_quantity);
                
                if ($remaining > 0) {
                    $coveredQty = min($remaining, $qty);
                    $chargeableQty = $qty - $coveredQty;
                    
                    $packageDeductions[$ps->id] = $coveredQty;
                }
            }

            $itemSubtotal = $chargeableQty * $basePrice;
            $subtotal += $itemSubtotal;

            $processedCart[] = [
                'service_type_id' => $serviceTypeId,
                'name' => $item['name'],
                'qty' => $qty,
                'base_price' => $basePrice,
                'covered_qty' => $coveredQty,
                'chargeable_qty' => $chargeableQty,
                'total_price' => $itemSubtotal,
                'is_special' => $item['is_special'] ?? false
            ];
        }

        $discountPercentage = max(0, min(100, (float)($request->discount_percentage ?? 0)));
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $total = $subtotal - $discountAmount;
        $paymentMode = $request->payment_mode ?? 'pay_on_pickup';
        $paidAmount  = $paymentMode === 'pay_now' ? $total : 0;

        try {
            $result = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $branchId, $subtotal, $discountAmount, $total, $paidAmount, $paymentMode, $processedCart, $packageId, $activePackage, $packageDeductions) {
                // 1. Resolve Customer
                $customerId = $request->customer_id;
                if (!$customerId) {
                    $customer = \App\Models\Customer::firstOrCreate(
                        ['phone' => '0000000000', 'branch_id' => $branchId],
                        ['name' => 'Walk-in Customer']
                    );
                    $customerId = $customer->id;
                }

                // 2. Create Order
                $order = \App\Models\Order::create([
                    'order_number' => \App\Models\Order::generateOrderNumber(),
                    'branch_id'    => $branchId,
                    'customer_id'  => $customerId,
                    'created_by'   => auth()->id(),
                    'status'       => 'received',
                    'subtotal'     => $subtotal,
                    'discount'     => $discountAmount,
                    'additional'   => 0,
                    'total'        => $total,
                    'paid_amount'  => $paidAmount,
                    'payment_method' => $paymentMode === 'pay_now' ? ($request->payment_method ?? 'cash') : null,
                    'due_date'     => $request->due_date ? date('Y-m-d H:i:s', strtotime($request->due_date)) : null,
                ]);

                if ($paidAmount > 0 && $paymentMode === 'pay_now') {
                    \App\Models\PaymentTransaction::create([
                        'order_id' => $order->id,
                        'amount' => $paidAmount,
                        'payment_method' => $request->payment_method ?? 'cash',
                        'created_by' => auth()->id(),
                    ]);
                }

                // Status History
                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'received',
                    'changed_by' => auth()->id(),
                ]);

                // Deduct Package Quantities
                if ($packageId && count($packageDeductions) > 0) {
                    foreach ($packageDeductions as $cpsId => $qtyUsed) {
                        \App\Models\CustomerPackageService::where('id', $cpsId)
                            ->increment('used_quantity', $qtyUsed);
                    }
                    
                    // Check if package is exhausted
                    $allServices = \App\Models\CustomerPackageService::where('customer_package_id', $packageId)->get();
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

                    if (!empty($item['is_special'])) {
                        $specialService = \App\Models\ServiceType::firstOrCreate(
                            ['name' => 'Special Service'],
                            ['color' => 'purple', 'price_type' => 'per_item', 'base_price' => 0, 'loyalty_points_earn' => 0, 'is_active' => 1, 'description' => 'Custom one-time service']
                        );
                        $serviceTypeId = $specialService->id;
                    }

                    \App\Models\OrderItem::create([
                        'order_id'        => $order->id,
                        'service_type_id' => $serviceTypeId,
                        'item_description'=> $item['name'] . ($item['covered_qty'] > 0 ? " ({$item['covered_qty']} via Package)" : ''),
                        'quantity'        => $item['qty'],
                        'unit_price'      => $item['base_price'],
                        'total_price'     => $item['total_price'],
                    ]);


                }

                // 4. WhatsApp notification queue
                $customerObj = \App\Models\Customer::find($customerId);
                $msgContent = \App\Models\WhatsappTemplate::generateMessage('order_confirmation', $order, $customerObj);
                
                if (!empty($msgContent)) {
                    \App\Models\WhatsappLog::create([
                        'order_id' => $order->id,
                        'customer_id' => $customerId,
                        'phone' => $customerObj->phone,
                        'message_type' => 'order_received',
                        'message' => $msgContent,
                        'status' => 'pending',
                    ]);
                }

                return [
                    'order_id'      => $order->id,
                    'order_number'  => $order->order_number,
                    'customer_name' => $customerObj->name,
                    'due_date'      => $order->due_date ? $order->due_date->format('d M Y') : '-',
                    'cart'          => collect($processedCart)->map(fn($i) => [
                        'name' => $i['name'],
                        'qty'  => $i['qty'],
                    ])->values(),
                    'total'         => $total,
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
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }
}

