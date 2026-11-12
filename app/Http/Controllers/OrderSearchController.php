<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            return response()->json([
                'items' => [],
                'total' => 0,
                'has_more' => false,
            ]);
        }

        $limit = max(1, min(50, (int) $request->input('limit', 5)));
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $limit;

        $user = auth()->user();

        $baseQuery = Order::with('customer')
            ->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                      ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
            })
            ->when(
                !$user->isSuperAdmin() && $user->branch_id,
                fn($q) => $q->where(function ($sub) use ($user) {
                    $sub->where('branch_id', $user->branch_id)
                        ->orWhere('workshop_id', $user->branch_id);
                })
            );

        $total = (clone $baseQuery)->count();

        $orders = $baseQuery
            ->orderByRaw("
                CASE 
                    WHEN order_number = ? THEN 1
                    WHEN order_number LIKE ? THEN 2
                    WHEN order_number LIKE ? THEN 3
                    ELSE 4
                END ASC",
                [$q, "{$q}%", "%{$q}%"]
            )
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get(['id', 'order_number', 'status', 'total', 'customer_id', 'created_at', 'branch_id', 'workshop_id']);

        $items = $orders->map(fn($o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'status'       => $o->status,
            'status_label' => str_replace('_', ' ', ucfirst($o->status)),
            'total'        => number_format($o->total, 2),
            'customer'     => ($o->customer ? $o->customer->name . ($o->customer->phone ? ' (' . $o->customer->phone . ')' : '') : 'Walk-in'),
            'url'          => route('orders.show', $o->order_number),
        ]);

        return response()->json([
            'items'    => $items,
            'total'    => $total,
            'has_more' => ($offset + $limit) < $total,
        ]);
    }
}
