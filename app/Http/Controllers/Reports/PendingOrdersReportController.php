<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Models\Branch;

class PendingOrdersReportController extends Controller
{
    private function getQuery(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super_admin';
        $isWorkshop = $request->input('workshop');

        // Filters
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $branchId = $request->input('branch_id', $isSuperAdmin ? null : $user->branch_id);
        $deliveryType = $request->input('delivery_type');

        if ($isWorkshop) {
            $query = WorkshopDispatch::with(['order.customer', 'branch'])
                ->join('orders', 'workshop_dispatches.order_id', '=', 'orders.id')
                ->select('workshop_dispatches.*')
                ->whereIn('workshop_dispatches.status', ['dispatched_to_workshop', 'received_at_workshop', 'processing']);

            if ($branchId) {
                $query->where('workshop_dispatches.workshop_branch_id', $branchId);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('orders.due_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            if ($deliveryType === 'delivery') {
                $query->where('orders.is_delivery_order', 1);
            } elseif ($deliveryType === 'pickup') {
                $query->where('orders.is_delivery_order', 0);
            }
        } else {
            $query = Order::with('customer')
                ->whereNotIn('status', ['delivered', 'cancelled']);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('due_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            if ($deliveryType === 'delivery') {
                $query->where('is_delivery_order', 1);
            } elseif ($deliveryType === 'pickup') {
                $query->where('is_delivery_order', 0);
            }
        }

        if ($isWorkshop) {
            $query->orderByRaw('orders.due_date IS NULL')->orderBy('orders.due_date', 'asc');
        } else {
            $query->orderByRaw('due_date IS NULL')->orderBy('due_date', 'asc');
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super_admin';
        $isWorkshop = $request->input('workshop');
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $branchId = $request->input('branch_id', $isSuperAdmin ? null : $user->branch_id);
        $deliveryType = $request->input('delivery_type');

        $query = $this->getQuery($request);

        $orders = $query->paginate(15)->withQueryString();

        $branches = $isSuperAdmin ? Branch::all() : [];

        return view('reports.pending_orders', compact('orders', 'isWorkshop', 'isSuperAdmin', 'branches', 'startDate', 'endDate', 'branchId', 'deliveryType'));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        if ($start->diffInDays($end) > 31) {
            return back()->with('error', 'PDF generation is limited to a maximum of 31 days to ensure system stability. Please select a smaller date range.');
        }

        $query = $this->getQuery($request);
        $orders = $query->lazy(500); // Process in chunks of 500 to save memory
        $isWorkshop = $request->input('workshop');

        \Illuminate\Support\Facades\Log::info("User ID " . auth()->id() . " generated Pending Orders PDF Report from {$startDate} to {$endDate}");

        $label = 'All Pending Orders';
        if ($request->input('start_date') && $request->input('end_date')) {
            $label = 'Pending Orders: ' . $request->input('start_date') . ' to ' . $request->input('end_date');
        } elseif ($request->input('start_date')) {
            $label = 'Pending Orders from ' . $request->input('start_date');
        }

        return view('reports.active-orders-pdf', compact('orders', 'isWorkshop', 'label'));
    }
}
