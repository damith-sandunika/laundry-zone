<?php

namespace App\Http\Controllers;

use App\Models\DeliveryTask;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\ServiceType;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    // ─── Rider Dashboard ───
    public function dashboard()
    {
        $rider = auth()->user();

        $activeTasks = DeliveryTask::with(['appointment.customer', 'order'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'accepted', 'on_way', 'arrived'])
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

        $tasks = DeliveryTask::with(['appointment.customer', 'order', 'branch'])
            ->where('rider_id', $rider->id)
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

    // ─── Rider: Mark On The Way ───
    public function onWay(DeliveryTask $task, WhatsAppService $waService)
    {
        $this->authorizeRiderTask($task);
        $task->update(['status' => 'on_way']);

        if ($task->task_type === 'pickup') {
            $waService->notifyRiderArriving($task->appointment, auth()->user());
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

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $appointment = $task->appointment;

        if ($task->task_type === 'pickup') {
            // Rider picked up from customer, heading to branch
            $appointment->update(['status' => 'picked_up']);
        } elseif ($task->task_type === 'delivery') {
            // Rider delivered to customer
            $appointment->update(['status' => 'delivered']);
            $order = Order::find($task->order_id);
            if ($order) {
                // Process payment if requested
                if ($request->has('payment_amount') && $request->payment_amount > 0) {
                    $amount = $request->payment_amount;
                    $method = $request->payment_method ?? 'cash';

                    PaymentTransaction::create([
                        'order_id' => $order->id,
                        'amount' => $amount,
                        'payment_method' => $method,
                        'created_by' => auth()->id(),
                    ]);

                    $order->paid_amount += $amount;
                    $order->payment_status = ($order->paid_amount >= $order->total) ? 'paid' : 'partial';
                    $order->payment_method = $method;
                }

                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'status' => 'completed']);
    }

    // ─── Rider: Mobile Order Creation ───
    public function createOrder(DeliveryTask $task)
    {
        $this->authorizeRiderTask($task);
        if ($task->task_type !== 'pickup') {
            abort(403, 'Can only create orders for pickups.');
        }

        $services = ServiceType::where('is_active', true)->get();

        return view('rider.create-order', compact('task', 'services'));
    }

    public function storeOrder(Request $request, DeliveryTask $task, WhatsAppService $waService)
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

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'branch_id' => $branchId,
            'customer_id' => $appointment->customer_id,
            'created_by' => auth()->id(),
            'status' => 'received',
            'subtotal' => $subtotal,
            'total' => $subtotal, // Delivery fee added later if applicable
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
                'price' => $service->base_price,
                'subtotal' => $price,
            ]);
        }

        $appointment->update([
            'status' => 'order_created',
            'order_id' => $order->id,
            'picked_up_at' => now(),
        ]);

        // Complete the task as picked up
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
            'order_id' => $order->id,
        ]);

        // Notify customer
        $waService->notifyOrderCreatedByRider($order);

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
