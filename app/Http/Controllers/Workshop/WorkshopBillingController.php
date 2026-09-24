<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Illuminate\Http\Request;

class WorkshopBillingController extends Controller
{
    public function requestPayment(WorkshopDispatch $dispatch, Request $request)
    {
        $order = $dispatch->order;
        $amountType = $request->input('amount_type', 'full');
        $amount = 0;

        if ($amountType === 'full') {
            $amount = $order->total;
        } elseif ($amountType === '50') {
            $amount = $order->total / 2;
        } else {
            $amount = $request->input('custom_amount', $order->total);
        }

        $customer = $order->customer;
        if ($customer) {
            $commService = new CommunicationService();
            $commService->sendFinalizedBill($order, $amount);

            return response()->json(['success' => true, 'message' => 'Payment request sent.']);
        }

        return response()->json(['success' => false, 'message' => 'Customer has no phone number.'], 400);
    }

    public function approvePayment(WorkshopDispatch $dispatch, \App\Actions\Workshop\ApproveWorkshopPaymentAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'message' => 'Payment approved. Order is now processing.']);
        } catch (\Exception $e) {
            \Log::error('WorkshopBillingController::approvePayment error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function finalizeBill(string $order_number)
    {
        $order = \App\Models\Order::where('order_number', $order_number)
            ->with(['items.serviceType', 'items.extras', 'customer', 'deliveryAppointment'])
            ->firstOrFail();

        $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)
            ->whereNotIn('status', ['received_at_branch'])
            ->latest()
            ->firstOrFail();

        // Scope to the workshop's branch (fall back to order branch if dispatch has no branch)
        $branchId = $dispatch->workshop->branch_id
            ?? $dispatch->branch_id
            ?? $order->branch_id
            ?? null;

        $isCentralized = \App\Models\Setting::get('centralized_management', '1') == '1';

        $services = \App\Models\ServiceType::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->get();

        $categories = \App\Models\ServiceCategory::with(['children' => function($q) use ($branchId, $isCentralized) {
            $q->where('is_active', true);
            if (!$isCentralized && $branchId) {
                $q->where('branch_id', $branchId);
            }
            $q->orderBy('order_index');
        }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->orderBy('order_index')
            ->get();

        $turnaroundTypes = \App\Models\TurnaroundType::where(function($q) use ($branchId, $isCentralized) {
            if (!$isCentralized && $branchId) {
                $q->where('branch_id', $branchId);
            }
        })->get();

        $systemExtras = \App\Models\Extra::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->get();
        
        $cartItems = $order->items->map(function($item) {
            $selectedExtras = $item->extras->map(function($ext) {
                return ['id' => $ext->extra_id, 'qty' => $ext->quantity];
            })->toArray();
            return [
                'service_type_id' => $item->service_type_id,
                'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
                'base_price' => $item->unit_price,
                'qty' => $item->quantity,
                'is_special' => $item->turnaround_type_id || ((float)$item->custom_turnaround_percentage > 0) ? true : false,
                'turnaround_type_id' => (float)$item->custom_turnaround_percentage > 0 ? 'custom' : ($item->turnaround_type_id ? (int)$item->turnaround_type_id : null),
                'custom_turnaround_percentage' => $item->custom_turnaround_percentage,
                'turnaround_extra_charge' => 0,
                'selected_extras' => $selectedExtras,
            ];
        })->toArray();
        
        if (empty($cartItems) && $order->deliveryAppointment && $order->deliveryAppointment->cart_json) {
            $parsedCart = json_decode($order->deliveryAppointment->cart_json, true);
            if (is_array($parsedCart)) {
                $cartItems = array_map(function($i) {
                    $service = $i['service'] ?? $i;
                    $sId = $service['id'] ?? $service['service_id'] ?? $service['service_type_id'] ?? null;
                    return [
                        'service_type_id' => $sId,
                        'name' => $service['name'] ?? $service['item_description'] ?? 'Item',
                        'base_price' => $service['base_price'] ?? $service['unit_price'] ?? 0,
                        'qty' => $i['quantity'] ?? $i['qty'] ?? 1,
                        'is_special' => $i['is_special'] ?? false,
                        'turnaround_type_id' => !empty($i['custom_turnaround_percentage']) ? 'custom' : (!empty($i['turnaround_type_id']) ? (int)$i['turnaround_type_id'] : null),
                        'custom_turnaround_percentage' => $i['custom_turnaround_percentage'] ?? null,
                        'turnaround_extra_charge' => $i['turnaround_extra_charge'] ?? 0,
                        'selected_extras' => $i['selected_extras'] ?? [],
                    ];
                }, $parsedCart);
            }
        }
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        return view('workshop.finalize_bill', compact('dispatch', 'order', 'services', 'categories', 'turnaroundTypes', 'systemExtras', 'cartItems', 'minimumDeliveryCost'));
    }
}
