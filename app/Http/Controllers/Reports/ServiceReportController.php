<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ServiceReportController extends Controller
{
    private function buildQuery(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user         = auth()->user();
        $isSuperAdmin = $user->canManageFinance();
        $branchId     = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate    = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate      = $request->input('end_date', now()->toDateString());

        $query = OrderItem::select(
                'main_categories.name as main_category_name',
                'service_categories.name as category_name',
                'service_types.name as service_name',
                \DB::raw('SUM(order_items.quantity) as total_qty'),
                \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue')
            )
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->leftJoin('service_categories', 'service_categories.id', '=', 'service_types.category_id')
            ->leftJoin('service_categories as main_categories', 'main_categories.id', '=', 'service_categories.parent_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
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
        $data     = $this->buildQuery($request);
        $services = $data['query']
            ->groupBy('main_categories.name', 'service_categories.name', 'service_types.name')
            ->having('total_qty', '>', 0)
            ->orderBy('total_revenue', 'desc')
            ->paginate(15)
            ->withQueryString();

        $totalsQuery = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$data['startDate'].' 00:00:00', $data['endDate'].' 23:59:59']);
        
        if ($data['branchId']) {
            $totalsQuery->where('orders.branch_id', $data['branchId']);
        }
        
        $totalsData = $totalsQuery->select(
            \DB::raw('SUM(order_items.quantity) as total_qty'),
            \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue')
        )->first();
        
        $totalQty     = $totalsData->total_qty ?? 0;
        $totalRevenue = $totalsData->total_revenue ?? 0;
        $branches     = $data['isSuperAdmin'] ? Branch::where('is_active', true)->get() : collect();
        $startDate    = $data['startDate'];
        $endDate      = $data['endDate'];
        $branchId     = $data['branchId'];
        $isSuperAdmin = $data['isSuperAdmin'];

        return view('reports.services', compact('services', 'startDate', 'endDate', 'branches', 'branchId', 'isSuperAdmin', 'totalQty', 'totalRevenue'));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        if ($start->diffInDays($end) > 31) {
            return back()->with('error', 'PDF generation is limited to a maximum of 31 days to ensure system stability. Please select a smaller date range.');
        }

        $data     = $this->buildQuery($request);
        
        $servicesCollection = collect();
        foreach ($data['query']->groupBy('main_categories.name', 'service_categories.name', 'service_types.name')
            ->having('total_qty', '>', 0)
            ->orderBy('total_revenue', 'desc')->cursor() as $row) {
            $servicesCollection->push($row);
        }
        $services = $servicesCollection;

        $totalQty     = $services->sum('total_qty');
        $totalRevenue = $services->sum('total_revenue');
        $branchId     = $data['branchId'];
        $isSuperAdmin = $data['isSuperAdmin'];
        $branch       = Branch::find($branchId ?? 1);

        \Illuminate\Support\Facades\Log::info("User ID " . auth()->id() . " generated Service PDF Report from {$startDate} to {$endDate}");

        return view('reports.services_pdf', compact('services', 'startDate', 'endDate', 'branch', 'totalQty', 'totalRevenue', 'isSuperAdmin', 'branchId'));
    }
}
