<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Api\BookingDataFormatter;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    protected BookingDataFormatter $formatter;

    public function __construct(BookingDataFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

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
            ->with(['branch:id,name', 'customer:id,name,phone', 'items:id,order_id,service_type_id', 'items.serviceType:id,name'])
            ->select([
                'id', 'order_number', 'branch_id', 'customer_id',
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

        $result = $orders->map(fn($order) => $this->formatter->formatFlatOrder($order));

        return response()->json($result);
    }

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

        return response()->json($this->formatter->formatDetailOrder($order));
    }

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

        $data = $paginated->getCollection()->map(fn($order) => $this->formatter->formatProtectedOrder($order));

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
}
