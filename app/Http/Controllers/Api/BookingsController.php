<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DeliveryAppointment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    /**
     * GET /api/bookings
     * Returns all orders and delivery appointments in a unified format.
     * Supports optional query params: branch_id, status, from, to, limit
     */
    public function index(Request $request)
    {
        $query = Order::withoutGlobalScopes()
            ->with(['branch:id,name', 'items.serviceType:id,name'])
            ->select(['id', 'order_number', 'branch_id', 'customer_id', 'status', 'payment_status', 'total', 'is_delivery_order', 'created_at']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $limit = min((int) $request->get('limit', 100), 500);

        $orders = $query->latest()->take($limit)->get();

        $result = $orders->map(function ($order) {
            $serviceId = null;
            if ($order->is_delivery_order) {
                $serviceId = 'pickup-delivery';
            } elseif ($order->items->isNotEmpty()) {
                $serviceId = $order->items->first()->serviceType->name ?? 'laundry';
            } else {
                $serviceId = 'laundry';
            }

            return [
                'id'         => $order->id,
                'order_number' => $order->order_number,
                'branch_id'  => $order->branch->name ?? $order->branch_id,
                'service_id' => $serviceId,
                'status'     => $order->status,
                'payment_status' => $order->payment_status,
                'total'      => $order->total,
                'created_at' => $order->created_at->toIso8601String(),
            ];
        });

        return response()->json($result);
    }

    /**
     * GET /api/bookings/{id}
     * Returns a single booking by order ID.
     */
    public function show($id)
    {
        $order = Order::withoutGlobalScopes()
            ->with(['branch:id,name', 'items.serviceType:id,name', 'customer:id,name,phone'])
            ->findOrFail($id);

        $serviceId = null;
        if ($order->is_delivery_order) {
            $serviceId = 'pickup-delivery';
        } elseif ($order->items->isNotEmpty()) {
            $serviceId = $order->items->first()->serviceType->name ?? 'laundry';
        } else {
            $serviceId = 'laundry';
        }

        return response()->json([
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'branch_id'      => $order->branch->name ?? $order->branch_id,
            'service_id'     => $serviceId,
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'total'          => $order->total,
            'customer'       => $order->customer ? [
                'name'  => $order->customer->name,
                'phone' => $order->customer->phone,
            ] : null,
            'created_at'     => $order->created_at->toIso8601String(),
        ]);
    }
}

