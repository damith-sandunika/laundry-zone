<?php

namespace App\Actions\POS;

use App\Models\CustomerPackageService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\ServiceType;
use App\Models\WorkshopDispatch;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\Log;

class PersistPosOrderAction
{
    public function execute(User $user, int $branchId, array $orderParams, array $cartData, array $data): array
    {
        $subtotal = $orderParams['subtotal'];
        $discountAmount = $orderParams['discountAmount'];
        $total = $orderParams['total'];
        $paidAmount = $orderParams['paidAmount'];
        $paymentMode = $orderParams['paymentMode'];
        $branchProfit = $orderParams['branchProfit'];
        $workshopProfit = $orderParams['workshopProfit'];
        $customerId = $orderParams['customerId'];

        $processedCart = $cartData['processedCart'];
        $packageId = $cartData['packageId'];
        $activePackage = $cartData['activePackage'];
        $packageDeductions = $cartData['packageDeductions'];

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
            'due_date' => !empty($data['due_date']) ? date('Y-m-d H:i:s', strtotime($data['due_date'])) : null,
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
                CustomerPackageService::where('id', $cpsId)->increment('used_quantity', $qtyUsed);
            }
            $allServices = CustomerPackageService::where('customer_package_id', $packageId)->get();
            $isExhausted = $allServices->every(fn($s) => $s->used_quantity >= $s->total_quantity);
            if ($isExhausted && $activePackage) {
                $activePackage->update(['status' => 'exhausted']);
            }
        }

        foreach ($processedCart as $item) {
            $serviceTypeId = $item['service_type_id'];

            if (!empty($item['is_special'])) {
                $specialService = ServiceType::firstOrCreate(
                    ['name' => 'Special Service'],
                    ['color' => 'purple', 'price_type' => 'per_item', 'base_price' => 0, 'loyalty_points_earn' => 0, 'is_active' => 1, 'description' => 'Custom one-time service']
                );
                $serviceTypeId = $specialService->id;
            }

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'service_type_id' => $serviceTypeId,
                'item_description' => $item['name'] . ($item['covered_qty'] > 0 ? " ({$item['covered_qty']} via Package)" : ''),
                'quantity' => $item['qty'],
                'unit_price' => $item['base_price'],
                'total_price' => $item['total_price'],
                'turnaround_type_id' => $item['turnaround_type_id'],
                'custom_turnaround_percentage' => $item['custom_turnaround_percentage'] ?? null,
                'turnaround_extra_charge' => $item['turnaround_extra_charge'],
                'note' => $item['note'],
            ]);

            if (!empty($item['selected_extras'])) {
                foreach ($item['selected_extras'] as $ex) {
                    OrderItemExtra::create([
                        'order_item_id' => $orderItem->id,
                        'extra_id' => $ex['id'],
                        'quantity' => $ex['qty'],
                        'unit_price' => $ex['price'],
                        'total_price' => $ex['total']
                    ]);
                }
            }
        }

        $branch = \App\Models\Branch::find($branchId);
        $commService = new CommunicationService();
        $orderForComm = Order::with('customer')->find($order->id);

        if (!$orderForComm->is_delivery_order) {
            if ($branch && $branch->type === 'branch') {
                $commService->sendCollectionOrderPlaced($orderForComm);
            } else {
                $commService->sendPosWalkinPlaced($orderForComm);
            }
        }

        $isWorkshopAdmin = $user->role === 'workshop_admin' || ($user->role === 'staff' && $branch && $branch->type === 'workshop');
        if ($isWorkshopAdmin) {
            WorkshopDispatch::create([
                'order_id' => $order->id,
                'branch_id' => $branchId,
                'workshop_branch_id' => $branchId,
                'dispatched_by' => $user->id,
                'dispatched_at' => now(),
                'status' => 'received_at_workshop',
                'received_by' => $user->id,
                'received_at_workshop_at' => now(),
                'dispatch_notes' => 'Walk-in POS Order',
            ]);
            $order->update(['status' => 'received']);
        }

        if ($paidAmount > 0 && $paymentMode === 'pay_now') {
            try {
                $commService->sendPaymentReceived($orderForComm);
            } catch (\Exception $e) {
                Log::error('POS Payment notification failed: ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $orderForComm->customer->name ?? 'Walk-in Customer',
            'due_date' => $order->due_date ? $order->due_date->format('d M Y') : '-',
            'cart' => collect($processedCart)->map(fn($i) => [
                'name' => $i['name'],
                'qty' => $i['qty'],
            ])->values(),
            'total' => $total,
        ];
    }
}

