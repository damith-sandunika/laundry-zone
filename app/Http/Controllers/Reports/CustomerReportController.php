<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerReportController extends Controller
{
    private function buildQuery(Request $request)
    {
        $user         = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId     = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate    = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate      = $request->input('end_date', now()->toDateString());

        $query = Order::select(
                'customers.name as customer_name',
                \DB::raw('COUNT(orders.id) as order_count'),
                \DB::raw('SUM(orders.total) as total_revenue')
            )
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        } elseif ($isSuperAdmin) {
            $query->addSelect('branches.name as branch_name')
                  ->join('branches', 'branches.id', '=', 'orders.branch_id')
                  ->groupBy('branches.name');
        }

        return [
            'query'       => $query,
            'branchId'    => $branchId,
            'isSuperAdmin'=> $isSuperAdmin,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
        ];
    }

    public function index(Request $request)
    {
        $data      = $this->buildQuery($request);
        $customers = $data['query']->groupBy('customers.id', 'customers.name')->orderBy('total_revenue', 'desc')->get();

        $totalOrders  = $customers->sum('order_count');
        $totalRevenue = $customers->sum('total_revenue');
        $branches     = $data['isSuperAdmin'] ? Branch::where('is_active', true)->get() : collect();
        $startDate    = $data['startDate'];
        $endDate      = $data['endDate'];
        $branchId     = $data['branchId'];
        $isSuperAdmin = $data['isSuperAdmin'];

        return view('reports.customers', compact('customers', 'startDate', 'endDate', 'branches', 'branchId', 'isSuperAdmin', 'totalOrders', 'totalRevenue'));
    }

    public function exportPdf(Request $request)
    {
        $data      = $this->buildQuery($request);
        $customers = $data['query']->groupBy('customers.id', 'customers.name')->orderBy('total_revenue', 'desc')->get();

        $totalOrders  = $customers->sum('order_count');
        $totalRevenue = $customers->sum('total_revenue');
        $startDate    = $data['startDate'];
        $endDate      = $data['endDate'];
        $branchId     = $data['branchId'];
        $isSuperAdmin = $data['isSuperAdmin'];
        $branch       = Branch::find($branchId ?? 1);

        return view('reports.customers_pdf', compact('customers', 'startDate', 'endDate', 'branch', 'totalOrders', 'totalRevenue', 'isSuperAdmin', 'branchId'));
    }
}
