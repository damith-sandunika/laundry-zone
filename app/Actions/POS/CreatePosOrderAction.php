<?php

namespace App\Actions\POS;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\CustomerPackageService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\DB;

class CreatePosOrderAction
{
    public function execute(User $user, array $data): array
    {
        $branchId = $user->branch_id ?? \App\Models\Branch::first()->id;

        $packageId = $data['customer_package_id'] ?? null;
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

        foreach ($data['cart'] as $item) {
            $serviceTypeId = $item['service_type_id'] ?? $item['id'];
            $qty = (float) $item['qty'];
            $basePrice = (float) $item['base_price'];

            $turnaroundTypeId = !empty($item['turnaround_type_id']) ? $item['turnaround_type_id'] : null;
            $customTurnaroundPercent = null;
            if ($turnaroundTypeId === 'custom') {
                $turnaroundTypeId = null;
                $customTurnaroundPercent = isset($item['custom_turnaround_percentage']) ? (float) $item['custom_turnaround_percentage'] : null;
            }
            $selectedExtras = $item['selected_extras'] ?? [];

            $coveredQty = 0;
            $chargeableQty = $qty;

            if ($packageId) {
                if (empty($item['is_special']) && $packageServices->has($serviceTypeId)) {
                    $ps = $packageServices->get($serviceTypeId);
                    $remaining = max(0, $ps->total_quantity - $ps->used_quantity);

                    if ($qty > $remaining) {
                        throw new \Exception("Cannot process order. Requested quantity ({$qty}) for '{$item['name']}' exceeds the remaining package limit ({$remaining}).");
                    }

                    $coveredQty = $qty;
                    $chargeableQty = 0;

                    $packageDeductions[$ps->id] = $coveredQty;
                } else {
                    throw new \Exception("Cannot process order. '{$item['name']}' is not covered by the selected package.");
                }
            }

            $itemSubtotalBeforeTurnaround = ($chargeableQty * $basePrice);
            $turnaroundCharge = 0;
            
            if ($customTurnaroundPercent !== null) {
                $turnaroundCharge = $itemSubtotalBeforeTurnaround * ($customTurnaroundPercent / 100);
            } else if ($turnaroundTypeId) {
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
                'custom_turnaround_percentage' => $customTurnaroundPercent,
                'turnaround_extra_charge' => $turnaroundCharge,
                'selected_extras' => [],
            ];

            $lastIndex = count($processedCart) - 1;

            if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                foreach ($selectedExtras as $selEx) {
                    $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                    $extraQty = is_array($selEx) && isset($selEx['qty']) ? $selEx['qty'] : 1;
                    
                    $extra = \App\Models\Extra::find($extraId);
                    if ($extra) {
                        $extraSubtotal = $extra->price * $extraQty;
                        $subtotal += $extraSubtotal;

                        $processedCart[$lastIndex]['selected_extras'][] = [
                            'id' => $extra->id,
                            'qty' => $extraQty,
                            'price' => $extra->price,
                            'total' => $extraSubtotal
                        ];
                    }
                }
            }
        }

        $discountPercentage = max(0, min(100, (float) ($data['discount_percentage'] ?? 0)));
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $total = $subtotal - $discountAmount;
        $paymentMode = $data['payment_mode'] ?? 'pay_on_pickup';
        
        $paidAmount = 0;
        if ($paymentMode === 'pay_now') {
            if (isset($data['advance_amount']) && $data['advance_amount'] !== null) {
                $paidAmount = min($total, (float) $data['advance_amount']);
            } else {
                $paidAmount = $total;
            }
        }

        return DB::transaction(function () use ($data, $user, $branchId, $subtotal, $discountAmount, $discountPercentage, $total, $paidAmount, $paymentMode, $processedCart, $packageId, $activePackage, $packageDeductions) {
            $customerId = $data['customer_id'] ?? null;
            if (! $customerId) {
                $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                    ['phone' => '0000000000'],
                    ['name' => 'Walk-in Customer', 'branch_id' => $branchId]
                );
                $customerId = $customer->id;
            }

            if ($discountPercentage > 0 && $customerId) {
                Customer::where('id', $customerId)
                    ->update(['default_discount' => $discountPercentage]);
            }

            $branchProfit = 0;
            $workshopProfit = 0;
            $branch = \App\Models\Branch::find($branchId);
            if ($branch && $branch->branch_profit_percentage > 0) {
                $branchProfit = $subtotal * ($branch->branch_profit_percentage / 100);
                $workshopProfit = $subtotal - $branchProfit;
            }
            
            $order = Order::create([
                'order_number' => Order::generateOrderNumber($branchId),
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'created_by' => $user->id,
                'status' => 'received',
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'additional' => 0,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'payment_method' => $paymentMode === 'pay_now' ? ($data['payment_method'] ?? 'cash') : null,
                'due_date' => $data['due_date'] ? date('Y-m-d H:i:s', strtotime($data['due_date'])) : null,
                'branch_profit' => $branchProfit,
                'workshop_profit' => $workshopProfit,
            ]);

            if ($paidAmount > 0 && $paymentMode === 'pay_now') {
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'amount' => $paidAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'created_by' => $user->id,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'received',
                'changed_by' => $user->id,
            ]);

            if ($packageId && count($packageDeductions) > 0) {
                foreach ($packageDeductions as $cpsId => $qtyUsed) {
                    CustomerPackageService::where('id', $cpsId)
                        ->increment('used_quantity', $qtyUsed);
                }

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
                    'custom_turnaround_percentage' => $item['custom_turnaround_percentage'] ?? null,
                    'turnaround_extra_charge' => $item['turnaround_extra_charge'],
                ]);
                
                if (isset($item['selected_extras']) && count($item['selected_extras']) > 0) {
                    foreach ($item['selected_extras'] as $ex) {
                        \App\Models\OrderItemExtra::create([
                            'order_item_id' => $orderItem->id,
                            'extra_id' => $ex['id'],
                            'quantity' => $ex['qty'],
                            'unit_price' => $ex['price'],
                            'total_price' => $ex['total']
                        ]);
                    }
                }
            }

            $commService = new CommunicationService();
            $orderForComm = Order::with('customer')->find($order->id);
            
            if (! $orderForComm->is_delivery_order) {
                if ($branch && $branch->type === 'branch') {
                    $commService->sendCollectionOrderPlaced($orderForComm);
                } else {
                    $commService->sendPosWalkinPlaced($orderForComm);
                }
            }

            $isWorkshopAdmin = $user->role === 'workshop_admin' || ($user->role === 'staff' && $branch && $branch->type === 'workshop');
            if ($isWorkshopAdmin) {
                \App\Models\WorkshopDispatch::create([
                    'order_id'           => $order->id,
                    'branch_id'          => $branchId,
                    'workshop_branch_id' => $branchId,
                    'dispatched_by'      => $user->id,
                    'dispatched_at'      => now(),
                    'status'             => 'processing',
                    'received_by'        => $user->id,
                    'received_at_workshop_at' => now(),
                    'dispatch_notes'     => 'Walk-in POS Order',
                ]);
                $order->update(['status' => 'processing']);
            }

            if ($paidAmount > 0 && $paymentMode === 'pay_now') {
                try {
                    $commService->sendPaymentReceived($orderForComm);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('POS Payment notification failed: ' . $e->getMessage());
                }
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
    }
}
