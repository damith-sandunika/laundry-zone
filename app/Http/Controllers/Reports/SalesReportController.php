<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    private function buildActivities(Request $request, int $perPage)
    {
        $user         = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId     = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate    = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate      = $request->input('end_date', now()->toDateString());

        $paymentsBase = DB::table('payment_transactions')
            ->join('orders', 'payment_transactions.order_id', '=', 'orders.id')
            ->where('payment_transactions.status', 'completed')
            ->whereBetween('payment_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw("
                'payment' as type,
                payment_transactions.id as ref_id,
                payment_transactions.created_at as date,
                payment_transactions.amount as amount,
                payment_transactions.payment_method as payment_method,
                orders.id as order_id
            ");
        if ($branchId) {
            $paymentsBase->where('orders.branch_id', $branchId);
        }

        $creditsBase = DB::table('orders')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.payment_method', 'credit')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw("
                'credit_sale' as type,
                orders.id as ref_id,
                orders.created_at as date,
                orders.total as amount,
                'credit' as payment_method,
                orders.id as order_id
            ");
        if ($branchId) {
            $creditsBase->where('orders.branch_id', $branchId);
        }

        $unionQuery = $paymentsBase->unionAll($creditsBase);
        $unionSql = $unionQuery->toSql();

        $paginatedRecords = DB::table(DB::raw("({$unionSql}) as activities"))
            ->mergeBindings($unionQuery)
            ->orderBy('date', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $orderIds = collect($paginatedRecords->items())->pluck('order_id')->unique();
        $ordersMap = Order::with(['customer', 'branch', 'items'])->whereIn('id', $orderIds)->get()->keyBy('id');

        $paginatedRecords->getCollection()->transform(function ($activity) use ($ordersMap) {
            $activity->order = $ordersMap->get($activity->order_id);
            $activity->total_qty = $activity->order ? $activity->order->items->sum('quantity') : 0;
            return $activity;
        });

        // Totals
        $totalsQuery = DB::table('payment_transactions')
            ->join('orders', 'payment_transactions.order_id', '=', 'orders.id')
            ->where('payment_transactions.status', 'completed')
            ->whereBetween('payment_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $totalsQuery->where('orders.branch_id', $branchId);
        }

        $paymentTotals = (clone $totalsQuery)
            ->selectRaw("payment_transactions.payment_method, SUM(payment_transactions.amount) as total_amount")
            ->groupBy('payment_transactions.payment_method')
            ->pluck('total_amount', 'payment_transactions.payment_method');

        $totalCash = $paymentTotals['cash'] ?? 0;
        $totalCard = $paymentTotals['card'] ?? 0;
        $totalBank = $paymentTotals['bank'] ?? 0;

        $totalCreditQuery = DB::table('orders')
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', 'credit')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $totalCreditQuery->where('branch_id', $branchId);
        }
        $totalCredit = $totalCreditQuery->sum('total');

        return [
            'orders'       => $paginatedRecords,
            'branchId'     => $branchId,
            'isSuperAdmin' => $isSuperAdmin,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'totalCash'    => $totalCash,
            'totalCard'    => $totalCard,
            'totalBank'    => $totalBank,
            'totalCredit'  => $totalCredit,
            'totalSalesAmount' => $totalCash + $totalCard + $totalBank + $totalCredit,
        ];
    }

    public function index(Request $request)
    {
        $data = $this->buildActivities($request, 15);
        $data['branches'] = $data['isSuperAdmin'] ? Branch::where('is_active', true)->get() : collect();
        return view('reports.sales', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildActivities($request, 5000); // Higher limit for PDF
        $data['branch'] = Branch::find($data['branchId'] ?? 1);
        return view('reports.sales_pdf', $data);
    }
}
