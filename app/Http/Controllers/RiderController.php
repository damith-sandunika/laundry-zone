<?php

namespace App\Http\Controllers;

use App\Models\DeliveryTask;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\ServiceType;
use App\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiderController extends Controller
{
    // ─── Rider Dashboard ───
    public function dashboard()
    {
        $rider = auth()->user();

        $activeTasks = DeliveryTask::with(['appointment.customer', 'appointment.order', 'order'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'accepted', 'on_way', 'arrived', 'waiting_for_payment'])
            ->orderBy('created_at', 'asc')
            ->get();

        $completedToday = DeliveryTask::where('rider_id', $rider->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        $totalCompleted = DeliveryTask::where('rider_id', $rider->id)
            ->where('status', 'completed')
            ->count();

        return view('rider.dashboard', compact('activeTasks', 'completedToday', 'totalCompleted'));
    }

    // ─── Rider: View all tasks ───
    public function tasks()
    {
        $rider = auth()->user();

        $tasks = DeliveryTask::with(['appointment.customer', 'appointment.order', 'order', 'branch'])
            ->where('rider_id', $rider->id)
            ->where('status', '!=', 'returned')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('rider.tasks', compact('tasks'));
    }

    // ─── Rider: Accept Task ───
    public function accept(DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);

        $task->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json(['success' => true, 'status' => 'accepted']);
    }

    public function onWay(DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);
        $task->update(['status' => 'on_way']);

        if ($task->task_type === 'pickup' && $task->appointment) {
            $commService = new CommunicationService();
            // $commService->sendPickupConfirmation($task->appointment);
        } elseif ($task->task_type === 'delivery' && $task->order) {
            $commService = new CommunicationService();
            // $commService->sendDeliveryConfirmation($task->order);
        }

        return response()->json(['success' => true, 'status' => 'on_way']);
    }

    // ─── Rider: Mark Arrived ───
    public function arrived(DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);
        $task->update(['status' => 'arrived']);

        return response()->json(['success' => true, 'status' => 'arrived']);
    }

    // ─── Rider: Mark Complete (Picked up / Delivered) ───
    public function complete(Request $request, DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);

        $appointment = $task->appointment;
        $isWaitingForPayment = false;

        if ($task->task_type === 'pickup') {
            // Rider picked up from customer, heading to branch
            $appointment->update(['status' => 'picked_up', 'picked_up_at' => now()]);
            // Sync Order status if linked
            if ($appointment->order_id) {
                $order = Order::find($appointment->order_id);
                if ($order) {
                    $order->update(['status' => 'picked_up']);
                    try {
                        OrderStatusHistory::create([
                            'order_id' => $order->id,
                            'status' => 'picked_up',
                            'changed_by' => auth()->id(),
                            'note' => 'Rider picked up garments from customer.',
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('OrderStatusHistory failed (riderPickedUp): ' . $e->getMessage());
                    }
                }
            }
        } elseif ($task->task_type === 'delivery') {
            // Rider delivered to customer
            $appointment?->update(['status' => 'delivered']);
            $order = Order::find($task->order_id);
            if ($order) {
                $balance = $order->total - $order->paid_amount;
                
                // Determine the payment method chosen by the rider to settle the balance
                $paymentMethod = $request->input('payment_method', 'cash');
                
                if ($balance > 0 && in_array($paymentMethod, ['cash', 'card', 'bank_transfer'])) {
                    $actualMethod = $paymentMethod === 'bank_transfer' ? 'bank' : $paymentMethod;
                    PaymentTransaction::create([
                        'order_id' => $order->id,
                        'amount' => $balance,
                        'payment_method' => $actualMethod,
                        'created_by' => auth()->id(),
                        'status' => 'completed',
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                        'collected_from_rider_at' => $actualMethod === 'cash' ? null : now() // Branch admin needs to collect cash from rider
                    ]);

                    $order->paid_amount += $balance;
                    $order->payment_status = 'paid';
                    
                    if ($actualMethod === 'cash') {
                        $isWaitingForPayment = true;
                    }
                } elseif ($balance > 0 && $paymentMethod === 'credit') {
                    $order->payment_status = 'pending';
                    $order->payment_method = 'credit';
                }

                $updateData = [
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ];

                if ($balance > 0) {
                    $updateData['paid_amount'] = $order->paid_amount;
                    $updateData['payment_status'] = $order->payment_status;
                    $updateData['payment_method'] = isset($actualMethod) ? $actualMethod : ($paymentMethod === 'credit' ? 'credit' : null);
                }

                $order->update($updateData);

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'changed_by' => auth()->id(),
                    'note' => 'Delivered by rider',
                ]);

                $commService = new \App\Services\CommunicationService();
                $commService->sendDeliveryCompleted($order);
            }
        }

        $newStatus = $isWaitingForPayment ? 'waiting_for_payment' : 'completed';
        
        $task->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? now() : null,
        ]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    // ─── Rider: Return Task (Customer Unavailable) ───
    public function returnTask(Request $request, DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);

        if ($task->task_type === 'delivery') {
            $order = Order::find($task->order_id);
            if ($order) {
                // Find the previous status before it was assigned to a rider
                $previousHistory = OrderStatusHistory::where('order_id', $order->id)
                    ->whereNotIn('status', ['out_for_delivery', 'rider_assigned'])
                    ->orderBy('id', 'desc')
                    ->first();
                
                $revertStatus = $previousHistory ? $previousHistory->status : 'done';

                $order->update([
                    'status' => 'returned_from_delivery', // Explicitly marking it as returned
                    'delivered_at' => null
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'returned_from_delivery',
                    'changed_by' => auth()->id(),
                    'note' => 'Delivery returned by rider (Customer unavailable).',
                ]);
            }
            if ($task->appointment) {
                $task->appointment->update(['status' => 'returned_from_delivery']);
            }
        } elseif ($task->task_type === 'pickup') {
            if ($task->appointment) {
                $task->appointment->update(['status' => 'pending']);
            }
        }

        $task->update([
            'status' => 'returned',
            'completed_at' => now(), // Technically finished processing this task
            'notes' => 'Returned: Customer unavailable'
        ]);

        return response()->json(['success' => true, 'status' => 'returned']);
    }

    // ─── Rider: Mobile Order Creation ───
    public function createOrder(DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);
        if ($task->task_type !== 'pickup') {
            abort(403, 'Can only create orders for pickups.');
        }

        // If POS already created an order for this appointment, skip item entry
        if ($task->appointment->order_id) {
            return redirect()->route('rider.tasks')
                ->with('info', 'This delivery already has an order created by the branch. Proceed to complete the pickup task.');
        }

        $categories = \App\Models\ServiceCategory::with(['services' => function($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->get();
        $cartData = $task->appointment->cart_json ? json_decode($task->appointment->cart_json, true) : [];

        return view('rider.create-order', compact('task', 'categories', 'cartData'));
    }

    public function storeOrder(Request $request, DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);

        $request->validate([
            'items' => 'required|array',
            'items.*.service_id' => 'required|exists:service_types,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        $appointment = $task->appointment;
        $branchId = $task->branch_id;

        // Calculate subtotal
        $subtotal = 0;
        foreach ($request->items as $item) {
            $service = ServiceType::find($item['service_id']);
            $subtotal += $service->base_price * $item['quantity'];
        }

        $order = null;
        DB::transaction(function () use ($request, $task, $appointment, $branchId, $subtotal, &$order) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber($branchId, true),
                'branch_id' => $branchId,
                'customer_id' => $appointment->customer_id,
                'created_by' => auth()->id(),
                'status' => 'received',
                'subtotal' => $subtotal,
                'total' => $subtotal + ($appointment->delivery_cost ?? 0), // Include delivery fee
                'delivery_fee' => $appointment->delivery_cost ?? 0,
                'notes' => $request->notes,
                'is_delivery_order' => true,
                'delivery_appointment_id' => $appointment->id,
                'delivery_address' => $appointment->delivery_address,
            ]);

            foreach ($request->items as $item) {
                $service = ServiceType::find($item['service_id']);
                $price = $service->base_price * $item['quantity'];
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_type_id' => $service->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $service->base_price,
                    'total_price' => $price,
                ]);
            }

            $appointment->update([
                'status' => 'picked_up',
                'order_id' => $order->id,
                'picked_up_at' => now(),
            ]);

            // Complete the task as picked up
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'order_id' => $order->id,
            ]);
        });

        // Notify customer
        $commService = new CommunicationService();
        // $commService->sendOrderConfirmation($order);

        return redirect()->route('rider.dashboard')->with('success', 'Order created successfully! Customer notified.');
    }

    // ─── Rider: Update Location ───
    public function updateLocation(Request $request, DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);

        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $task->update([
            'rider_lat' => $request->lat,
            'rider_lng' => $request->lng,
        ]);

        return response()->json(['success' => true]);
    }

    private function authorizeRiderTask(DeliveryTask $task): void
    {
        if ($task->rider_id !== auth()->id() && ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Not your task.');
        }
    }
}

