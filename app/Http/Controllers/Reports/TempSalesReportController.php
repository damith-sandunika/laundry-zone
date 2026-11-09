<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    private function getBaseInfo(Request $request)
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

        return [$branchId, $startDate, $endDate, $isSuperAdmin];
    }

    private function buildUnionQuery($branchId, $startDate, $endDate)
    {
        $paymentsBase = DB::table('payment_transactions')
            ->join('orders', 'payment_transactions.order_id', '=', 'orders.id')
            ->where('payment_transactions.status', 'completed')->whereNull('payment_transactions.deleted_at')
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

        return DB::table(DB::raw("({$unionSql}) as activities"))
            ->mergeBindings($unionQuery)
            ->orderBy('date', 'desc');
    }

    private function getTotals($branchId, $startDate, $endDate)
    {
        $totalsQuery = DB::table('payment_transactions')
            ->join('orders', 'payment_transactions.order_id', '=', 'orders.id')
            ->where('payment_transactions.status', 'completed')->whereNull('payment_transactions.deleted_at')
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
            'totalCash'    => $totalCash,
            'totalCard'    => $totalCard,
            'totalBank'    => $totalBank,
            'totalCredit'  => $totalCredit,
            'totalSalesAmount' => $totalCash + $totalCard + $totalBank + $totalCredit,
        ];
    }

    public function index(Request $request)
    {
        [$branchId, $startDate, $endDate, $isSuperAdmin] = $this->getBaseInfo($request);
        
        $query = $this->buildUnionQuery($branchId, $startDate, $endDate);
        
        $paginatedRecords = $query->paginate(15)->withQueryString();

        $orderIds = collect($paginatedRecords->items())->pluck('order_id')->unique();
        $ordersMap = Order::with(['customer', 'branch', 'items'])->whereIn('id', $orderIds)->get()->keyBy('id');

        $paginatedRecords->getCollection()->transform(function ($activity) use ($ordersMap) {
            $activity->order = $ordersMap->get($activity->order_id);
            $activity->total_qty = $activity->order ? $activity->order->items->sum('quantity') : 0;
            return $activity;
        });

        $totals = $this->getTotals($branchId, $startDate, $endDate);

        $data = array_merge([
            'orders'       => $paginatedRecords,
            'branchId'     => $branchId,
            'isSuperAdmin' => $isSuperAdmin,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'branches'     => $isSuperAdmin ? Branch::where('is_active', true)->get() : collect()
        ], $totals);

        return view('reports.sales', $data);
    }

    public function exportPdf(Request $request)
    {
        [$branchId, $startDate, $endDate, $isSuperAdmin] = $this->getBaseInfo($request);
        
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        if ($start->diffInDays($end) > 7) {
            return back()->with('error', 'PDF generation is limited to a maximum of 7 days to ensure system stability. Please select a smaller date range or use the CSV Export option.');
        }

        $query = $this->buildUnionQuery($branchId, $startDate, $endDate);
        $paginatedRecords = $query->paginate(2000); // 7 days max, 2000 is safe
        
        $orderIds = collect($paginatedRecords->items())->pluck('order_id')->unique();
        $ordersMap = Order::with(['customer', 'branch', 'items'])->whereIn('id', $orderIds)->get()->keyBy('id');

        $paginatedRecords->getCollection()->transform(function ($activity) use ($ordersMap) {
            $activity->order = $ordersMap->get($activity->order_id);
            $activity->total_qty = $activity->order ? $activity->order->items->sum('quantity') : 0;
            return $activity;
        });

        $totals = $this->getTotals($branchId, $startDate, $endDate);
        
        $data = array_merge([
            'orders'       => $paginatedRecords,
            'branchId'     => $branchId,
            'isSuperAdmin' => $isSuperAdmin,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'branch'       => Branch::find($branchId ?? 1)
        ], $totals);
        
        \Illuminate\Support\Facades\Log::info("User ID " . auth()->id() . " generated Sales PDF Report from {$startDate} to {$endDate}");
        
        return view('reports.sales_pdf', $data);
    }
    
    public function exportCsv(Request $request)
    {
        [$branchId, $startDate, $endDate, $isSuperAdmin] = $this->getBaseInfo($request);
        $query = $this->buildUnionQuery($branchId, $startDate, $endDate);
        
        $fileName = 'sales_report_' . $startDate . '_to_' . $endDate . '.csv';

        \Illuminate\Support\Facades\Log::info("User ID " . auth()->id() . " generated Sales CSV Report from {$startDate} to {$endDate}");

        return new StreamedResponse(function() use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Order No', 'Customer', 'Branch', 'Payment Method', 'Amount']);
            
            foreach ($query->cursor() as $activity) {
                // To avoid N+1 inside cursor, we query Order in chunks, but for streaming, 
                // simply fetching Order is fine if we cache it or fetch simply.
                // Actually, best is to chunk the cursor.
                $order = Order::with('customer', 'branch')->find($activity->order_id);
                
                fputcsv($handle, [
                    \Carbon\Carbon::parse($activity->date)->format('Y-m-d H:i'),
                    $activity->type === 'payment' ? 'Payment' : 'Credit Sale',
                    $order ? $order->order_number : '-',
                    $order && $order->customer ? $order->customer->name : '-',
                    $order && $order->branch ? $order->branch->name : '-',
                    ucfirst($activity->payment_method),
                    number_format((float)$activity->amount, 2, '.', '')
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
