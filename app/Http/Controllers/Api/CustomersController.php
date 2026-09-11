<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * GET /api/customers
     *
     * Returns paginated customer list with contact details.
     *
     * Query params:
     *   - search    (string) Search by name or phone
     *   - branch_id (int)    Filter by branch
     *   - per_page  (int)    Items per page (default: 50, max: 500)
     *   - page      (int)    Page number
     */
    public function index(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|integer',
            'search'    => 'nullable|string|max:100',
            'per_page'  => 'nullable|integer|min:1|max:500',
        ]);

        $query = Customer::withoutGlobalScopes()
            ->select([
                'id', 'name', 'phone', 'email', 'title',
                'branch_id', 'loyalty_points', 'created_at',
            ]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage  = min((int) $request->get('per_page', 50), 500);
        $paginated = $query->latest()->paginate($perPage);

        $data = $paginated->getCollection()->map(fn($c) => [
            'id'             => $c->id,
            'name'           => $c->name,
            'title'          => $c->title,
            'phone'          => $c->phone,
            'email'          => $c->email,
            'branch_id'      => $c->branch_id,
            'loyalty_points' => (int) ($c->loyalty_points ?? 0),
            'created_at'     => $c->created_at->toIso8601String(),
        ]);

        return response()->json([
            'data'       => $data,
            'pagination' => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
        ]);
    }

    /**
     * GET /api/customers/{id}
     * Returns a single customer with full detail and order summary.
     */
    public function show($id)
    {
        $customer = Customer::withoutGlobalScopes()
            ->withCount('orders')
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id'             => $customer->id,
                'name'           => $customer->name,
                'title'          => $customer->title,
                'phone'          => $customer->phone,
                'email'          => $customer->email,
                'address'        => $customer->address,  // auto-decrypted via model accessor
                'branch_id'      => $customer->branch_id,
                'loyalty_points' => (int) ($customer->loyalty_points ?? 0),
                'orders_count'   => $customer->orders_count,
                'created_at'     => $customer->created_at->toIso8601String(),
            ],
        ]);
    }
}
