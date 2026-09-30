<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TopCustomersController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $branchId = $request->input('branch_id');
        if (!Gate::allows('is-super-admin')) {
            $branchId = $user->branch_id;
        }

        $minSpent = $request->input('min_spent', 0);
        $minOrders = $request->input('min_orders', 0);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $sortBy = $request->input('sort_by', 'total_spent'); // total_spent, order_count

        // We use addSelect with subqueries to efficiently calculate total spent and order counts dynamically.
        $query = Customer::query()
            ->select('customers.*')
            ->addSelect(['total_spent' => Order::selectRaw('COALESCE(SUM(total), 0)')
                ->whereColumn('customer_id', 'customers.id')
                ->whereIn('status', ['delivered', 'done', 'completed', 'received', 'processing']) // Valid revenue orders
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ])
            ->addSelect(['order_count' => Order::selectRaw('COUNT(*)')
                ->whereColumn('customer_id', 'customers.id')
                ->whereIn('status', ['delivered', 'done', 'completed', 'received', 'processing'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ]);

        // Having clauses for dynamic filtering
        if ($minSpent > 0) {
            $query->having('total_spent', '>=', $minSpent);
        }
        if ($minOrders > 0) {
            $query->having('order_count', '>=', $minOrders);
        }

        // Always sort descending to get the 'Top' customers
        if ($sortBy === 'order_count') {
            $query->orderByDesc('order_count')->orderByDesc('total_spent');
        } else {
            $query->orderByDesc('total_spent')->orderByDesc('order_count');
        }

        $topCustomers = $query->paginate(15)->withQueryString();

        $allBranches = Gate::allows('is-super-admin') ? Branch::all() : collect();

        return view('customers.top', compact('topCustomers', 'allBranches', 'branchId', 'minSpent', 'minOrders', 'startDate', 'endDate', 'sortBy'));
    }
}
