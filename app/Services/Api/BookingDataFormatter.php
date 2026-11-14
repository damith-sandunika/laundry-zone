<?php

namespace App\Services\Api;

use App\Models\Order;

class BookingDataFormatter
{
    public function formatFlatOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'branch_id'      => $order->branch->name ?? (string) $order->branch_id,
            'service_id'     => $this->resolveServiceId($order),
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'total'          => (float) $order->total,
            'customer_name'  => $order->customer->name ?? null,
            'customer_phone' => $order->customer->phone ?? null,
            'created_at'     => $order->created_at->toIso8601String(),
        ];
    }

    public function formatDetailOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'branch_id'      => $order->branch->name ?? (string) $order->branch_id,
            'service_id'     => $this->resolveServiceId($order),
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'total'          => (float) $order->total,
            'created_at'     => $order->created_at->toIso8601String(),
            'customer'       => $order->customer ? [
                'name'  => $order->customer->name,
                'phone' => $order->customer->phone,
                'email' => $order->customer->email,
            ] : null,
            'items'          => $order->items->map(fn($item) => [
                'description' => $item->item_description,
                'quantity'    => $item->quantity ?? 1,
                'unit_price'  => (float) ($item->unit_price ?? 0),
                'service'     => $item->serviceType->name ?? null,
            ])->values(),
            'delivery'       => $order->deliveryAppointment ? [
                'pickup_scheduled_at'   => optional($order->deliveryAppointment->pickup_scheduled_at)->toIso8601String(),
                'delivery_scheduled_at' => optional($order->deliveryAppointment->delivery_scheduled_at)->toIso8601String(),
                'pickup_address'        => $order->deliveryAppointment->pickup_address,
                'delivery_address'      => $order->deliveryAppointment->delivery_address,
                'status'                => $order->deliveryAppointment->status,
            ] : null,
            'status_history' => $order->statusHistory->map(fn($h) => [
                'status'     => $h->status,
                'notes'      => $h->notes,
                'changed_at' => $h->created_at->toIso8601String(),
            ])->values(),
        ];
    }

    public function formatProtectedOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'order_type'     => $order->is_delivery_order ? 'delivery' : 'walkin',
            'branch'         => $order->branch->name ?? $order->branch_id,
            'service_id'     => $this->resolveServiceId($order),
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'total'          => (float) $order->total,
            'discount'       => (float) ($order->discount ?? 0),
            'is_online'      => (bool) $order->is_online_booking,
            'customer'       => $order->customer ? [
                'id'    => $order->customer->id,
                'name'  => $order->customer->name,
                'phone' => $order->customer->phone,
                'email' => $order->customer->email,
                'title' => $order->customer->title,
            ] : null,
            'created_at'     => $order->created_at->toIso8601String(),
        ];
    }

    public function resolveServiceId(Order $order): string
    {
        if ($order->is_delivery_order) {
            return 'pickup-delivery';
        }
        if ($order->items->isNotEmpty()) {
            return $order->items->first()->serviceType->name ?? 'laundry';
        }
        return 'laundry';
    }
}

