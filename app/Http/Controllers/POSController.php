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
                        
        return view('pos.index', compact('services', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'      => 'nullable|exists:customers,id',
            'new_customer_name'=> 'nullable|string|max:255',
            'new_customer_phone'=> 'nullable|string|max:255',
            'cart'             => 'required|array|min:1',
            'discount'         => 'nullable|numeric|min:0',
            'additional'       => 'nullable|numeric|min:0',
            'payment_mode'     => 'nullable|in:pay_now,pay_on_pickup',
            'due_date'         => 'required|string',
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

        // Calculate Totals
        $subtotal   = collect($request->cart)->sum(fn($item) => $item['base_price'] * $item['qty']);
        $discount   = $request->discount ?? 0;
        $additional = $request->additional ?? 0;
        $total      = max(0, $subtotal - $discount + $additional);
        $paymentMode = $request->payment_mode ?? 'pay_on_pickup';
        $paidAmount  = $paymentMode === 'pay_now' ? $total : 0;

        try {
            $result = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $branchId, $subtotal, $discount, $additional, $total, $paidAmount, $paymentMode) {
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
                    'discount'     => $discount,
                    'additional'   => $additional,
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

                // 3. Create Items & Deduct Inventory
                foreach ($request->cart as $item) {
                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'service_type_id' => $item['id'],
                        'item_description' => $item['name'],
                        'quantity' => $item['qty'],
                        'unit_price' => $item['base_price'],
                        'total_price' => $item['base_price'] * $item['qty'],
                    ]);

                    $service = \App\Models\ServiceType::with('inventoryItems')->find($item['id']);
                    foreach ($service->inventoryItems as $invItem) {
                        if ($invItem->pivot->deduct_at_stage === 'washing' || $invItem->pivot->deduct_at_stage === 'received' || $invItem->pivot->deduct_at_stage === 'done') {
                            $qtyToDeduct = $invItem->pivot->qty_per_use * $item['qty'];
                            
                            $oldStock = $invItem->current_stock;
                            $invItem->decrement('current_stock', $qtyToDeduct);
                            $newStock = $oldStock - $qtyToDeduct;

                            \App\Models\InventoryTransaction::create([
                                'inventory_item_id' => $invItem->id,
                                'type' => 'out',
                                'quantity' => $qtyToDeduct,
                                'order_id' => $order->id,
                                'reference' => 'Order #' . $order->order_number . ' - Auto deduct',
                                'created_by' => auth()->id(),
                            ]);

                            // Send Low Stock Alert to Admin if it crosses threshold
                            if ($oldStock > $invItem->low_stock_threshold && $newStock <= $invItem->low_stock_threshold) {
                                $branch = \App\Models\Branch::find($branchId);
                                if ($branch && $branch->whatsapp_number) {
                                    $template = \App\Models\WhatsappTemplate::where('type', 'low_stock_alert')->first();
                                    $alertMsg = $template
                                        ? str_replace(
                                            ['[ItemName]', '[Location]', '[CurrentStock]', '[Unit]', '[Threshold]'],
                                            [$invItem->name, $invItem->location ?? 'N/A', $newStock, $invItem->unit, $invItem->low_stock_threshold],
                                            $template->content
                                          )
                                        : "⚠️ LOW STOCK: {$invItem->name} - {$newStock} {$invItem->unit} remaining. Location: " . ($invItem->location ?? 'N/A');

                                    \App\Models\WhatsappLog::create([
                                        'order_id'     => null,
                                        'customer_id'  => null,
                                        'phone'        => $branch->whatsapp_number,
                                        'message_type' => 'system_alert',
                                        'message'      => $alertMsg,
                                        'status'       => 'pending',
                                    ]);
                                }
                            }
                        }
                    }
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
                    'cart'          => collect($request->cart)->map(fn($i) => [
                        'name' => $i['name'],
                        'qty'  => $i['qty'],
                    ])->values(),
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
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }
}

