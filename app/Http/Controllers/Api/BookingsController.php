<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    /**
     * GET /api/bookings  (PUBLIC — no auth required, share via link)
     *
     * Returns a flat JSON array of orders — exact format:
     * [
     *   {
     *     "id": 1,
     *     "order_number": "1001",
     *     "branch_id": "Colombo Branch",
     *     "service_id": "pickup-delivery",
     *     "status": "done",
     *     "payment_status": "paid",
     *     "total": 1500,
     *     "created_at": "2026-08-29T08:15:00+00:00"
     *   }
     * ]
     *
     * Query params:
     *   - branch_id  (int)    Filter by branch ID
     *   - status     (string) Filter by status
     *   - from       (date)   YYYY-MM-DD start date
     *   - to         (date)   YYYY-MM-DD end date
     *   - limit      (int)    Max records (default: 100, max: 1000)
     */
    public function index(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|integer',
            'status'    => 'nullable|string',
            'from'      => 'nullable|date',
            'to'        => 'nullable|date',
            'limit'     => 'nullable|integer|min:1|max:1000',
        ]);

        $query = Order::withoutGlobalScopes()
            ->with(['branch:id,name', 'items:id,order_id,service_type_id', 'items.serviceType:id,name'])
            ->select([
                'id', 'order_number', 'branch_id',
                'status', 'payment_status', 'total',
                'is_delivery_order', 'created_at',
            ]);

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

        $limit  = min((int) $request->get('limit', 100), 1000);
        $orders = $query->latest()->take($limit)->get();

        // Return exactly the flat array format requested
        $result = $orders->map(fn($order) => [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'branch_id'      => $order->branch->name ?? (string) $order->branch_id,
            'service_id'     => $this->resolveServiceId($order),
            'status'         => $order->status,
            'payment_status' => $order->payment_status,
            'total'          => (float) $order->total,
            'created_at'     => $order->created_at->toIso8601String(),
        ]);

        return response()->json($result);
    }

    /**
     * GET /api/bookings/{id}  (PUBLIC)
     * Returns a single booking by order ID.
     */
    public function show($id)
    {
        $order = Order::withoutGlobalScopes()
            ->with([
                'branch:id,name',
                'customer:id,name,phone,email',
                'items:id,order_id,item_description,quantity,unit_price,service_type_id',
                'items.serviceType:id,name',
                'deliveryAppointment:id,order_id,pickup_scheduled_at,delivery_scheduled_at,pickup_address,delivery_address,status',
                'statusHistory:id,order_id,status,created_at,notes',
            ])
            ->findOrFail($id);

        return response()->json([
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
        ]);
    }

    /**
     * GET /api/orders  (PROTECTED — requires X-API-Key)
     *
     * Returns paginated orders with customer contact details.
     * Supports type=delivery|walkin filter.
     *
     * Query params: type, branch_id, status, from, to, per_page, page
     */
    public function indexProtected(Request $request)
    {
        $request->validate([
            'type'      => 'nullable|in:delivery,walkin',
            'branch_id' => 'nullable|integer',
            'status'    => 'nullable|string',
            'from'      => 'nullable|date',
            'to'        => 'nullable|date',
            'per_page'  => 'nullable|integer|min:1|max:500',
        ]);

        $query = Order::withoutGlobalScopes()
            ->with([
                'branch:id,name',
                'customer:id,name,phone,email,title',
                'items:id,order_id,item_description,service_type_id',
                'items.serviceType:id,name',
            ])
            ->select([
                'id', 'order_number', 'branch_id', 'customer_id',
                'status', 'payment_status', 'total', 'discount',
                'is_delivery_order', 'is_online_booking', 'created_at',
            ]);

        if ($request->type === 'delivery') {
            $query->where('is_delivery_order', true);
        } elseif ($request->type === 'walkin') {
            $query->where('is_delivery_order', false);
        }

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

        $perPage   = min((int) $request->get('per_page', 50), 500);
        $paginated = $query->latest()->paginate($perPage);

        $data = $paginated->getCollection()->map(fn($order) => [
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
        ]);

        return response()->json([
            'data'            => $data,
            'pagination'      => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
            'filters_applied' => [
                'type'      => $request->type ?? 'all',
                'branch_id' => $request->branch_id,
                'status'    => $request->status,
                'from'      => $request->from,
                'to'        => $request->to,
            ],
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function resolveServiceId(Order $order): string
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
