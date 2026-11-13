<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Services\Reports\SalesReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    protected SalesReportService $salesService;

    public function __construct(SalesReportService $salesService)
    {
        $this->salesService = $salesService;
    }

    private function getBaseInfo(Request $request): array
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $user         = auth()->user();
        $isSuperAdmin = $user->canManageFinance();
        $branchId     = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate    = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate      = $request->input('end_date', now()->toDateString());
        $customerType = $request->input('customer_type');

        return [$branchId, $startDate, $endDate, $isSuperAdmin, $customerType];
    }

    public function index(Request $request)
    {
        [$branchId, $startDate, $endDate, $isSuperAdmin, $customerType] = $this->getBaseInfo($request);
        $query = $this->salesService->buildUnionQuery($branchId, $startDate, $endDate, $customerType);
        $paginatedRecords = $query->paginate(15)->withQueryString();

        $orderIds = collect($paginatedRecords->items())->pluck('order_id')->unique();
        $ordersMap = Order::withTrashed()->with(['customer', 'branch', 'items'])->whereIn('id', $orderIds)->get()->keyBy('id');

        $paginatedRecords->getCollection()->transform(function ($activity) use ($ordersMap) {
            $activity->order = $ordersMap->get($activity->order_id);
            $activity->total_qty = $activity->order ? $activity->order->items->sum('quantity') : 0;
            return $activity;
        });

        $totals = $this->salesService->getTotals($branchId, $startDate, $endDate, $customerType);

        return view('reports.sales', array_merge([
            'orders'       => $paginatedRecords,
            'branchId'     => $branchId,
            'isSuperAdmin' => $isSuperAdmin,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'branches'     => $isSuperAdmin ? Branch::where('is_active', true)->get() : collect()
        ], $totals));
    }

    public function exportPdf(Request $request)
    {
        [$branchId, $startDate, $endDate, $isSuperAdmin, $customerType] = $this->getBaseInfo($request);
        if (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) > 7) {
            return back()->with('error', 'PDF generation is limited to a maximum of 7 days to ensure system stability. Please select a smaller date range or use CSV Export.');
        }

        $query = $this->salesService->buildUnionQuery($branchId, $startDate, $endDate, $customerType);
        $paginatedRecords = $query->paginate(2000);

        $orderIds = collect($paginatedRecords->items())->pluck('order_id')->unique();
        $ordersMap = Order::withTrashed()->with(['customer', 'branch', 'items'])->whereIn('id', $orderIds)->get()->keyBy('id');

        $paginatedRecords->getCollection()->transform(function ($activity) use ($ordersMap) {
            $activity->order = $ordersMap->get($activity->order_id);
            $activity->total_qty = $activity->order ? $activity->order->items->sum('quantity') : 0;
            return $activity;
        });

        $totals = $this->salesService->getTotals($branchId, $startDate, $endDate, $customerType);
        Log::info("User ID " . auth()->id() . " generated Sales PDF Report from {$startDate} to {$endDate}");

        return view('reports.sales_pdf', array_merge([
            'orders'       => $paginatedRecords,
            'branchId'     => $branchId,
            'isSuperAdmin' => $isSuperAdmin,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'branch'       => Branch::find($branchId ?? 1)
        ], $totals));
    }

    public function exportCsv(Request $request)
    {
        [$branchId, $startDate, $endDate, $isSuperAdmin, $customerType] = $this->getBaseInfo($request);
        $query = $this->salesService->buildUnionQuery($branchId, $startDate, $endDate, $customerType);
        $fileName = 'sales_report_' . $startDate . '_to_' . $endDate . '.csv';

        Log::info("Generated Sales CSV Report", ['user_id' => auth()->id(), 'branch_id' => $branchId]);

        return new StreamedResponse(function() use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Order No', 'Customer', 'Branch', 'Payment Method', 'Amount']);

            $query->chunk(500, function ($activities) use ($handle) {
                $orderIds = $activities->pluck('order_id')->filter()->unique();
                $ordersMap = Order::withTrashed()->with(['customer', 'branch'])->whereIn('id', $orderIds)->get()->keyBy('id');

                foreach ($activities as $activity) {
                    $order = $ordersMap->get($activity->order_id);
                    fputcsv($handle, [
                        Carbon::parse($activity->date)->format('Y-m-d H:i'),
                        $activity->type === 'payment' ? 'Payment' : 'Credit Sale',
                        $order ? $order->order_number : '-',
                        $order && $order->customer ? $order->customer->name : '-',
                        $order && $order->branch ? $order->branch->name : '-',
                        ucfirst($activity->payment_method ?? 'N/A'),
                        number_format((float)$activity->amount, 2, '.', '')
                    ]);
                }
            });
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
