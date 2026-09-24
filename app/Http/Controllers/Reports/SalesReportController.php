<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    private function buildActivities(Request $request): array
    {
        $user         = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId     = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate    = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate      = $request->input('end_date', now()->toDateString());

        $paymentsQuery = PaymentTransaction::with(['order.customer', 'order.branch', 'order.items'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('status', 'completed');
        if ($branchId) {
            $paymentsQuery->whereHas('order', fn ($q) => $q->where('branch_id', $branchId));
        }

        $payments = $paymentsQuery->get()->map(function ($txn) {
            return (object)[
                'type'           => 'payment',
                'date'           => $txn->created_at,
                'order'          => $txn->order,
                'total_qty'      => $txn->order ? $txn->order->items->sum('quantity') : 0,
                'payment_method' => $txn->payment_method,
                'amount'         => $txn->amount,
                'is_settlement'  => $txn->order
                    ? $txn->created_at->startOfDay()->gt($txn->order->created_at->startOfDay())
                    : false,
            ];
        })->filter(fn ($item) => $item->order != null);

        $creditOrdersQuery = Order::with(['customer', 'branch', 'items'])
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', 'credit')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $creditOrdersQuery->where('branch_id', $branchId);

        $creditOrders = $creditOrdersQuery->get()->map(fn ($order) => (object)[
            'type'           => 'credit_sale',
            'date'           => $order->created_at,
            'order'          => $order,
            'total_qty'      => $order->items->sum('quantity'),
            'payment_method' => 'credit',
            'amount'         => $order->total,
            'is_settlement'  => false,
        ]);

        return [
            'payments'     => $payments,
            'creditOrders' => $creditOrders,
            'branchId'     => $branchId,
            'isSuperAdmin' => $isSuperAdmin,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
        ];
    }

    public function index(Request $request)
    {
        $data          = $this->buildActivities($request);
        $payments      = $data['payments'];
        $creditOrders  = $data['creditOrders'];
        $startDate     = $data['startDate'];
        $endDate       = $data['endDate'];
        $branchId      = $data['branchId'];
        $isSuperAdmin  = $data['isSuperAdmin'];
        $allActivities = $payments->concat($creditOrders)->sortByDesc('date')->values();

        $totalCash        = $payments->where('payment_method', 'cash')->sum('amount');
        $totalCard        = $payments->where('payment_method', 'card')->sum('amount');
        $totalBank        = $payments->where('payment_method', 'bank')->sum('amount');
        $totalCredit      = $creditOrders->sum('amount');
        $totalSalesAmount = $totalCash + $totalCard + $totalBank + $totalCredit;

        $perPage      = 15;
        $currentPage  = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $currentItems = $allActivities->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $orders       = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems, $allActivities->count(), $perPage, $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        $branches = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        return view('reports.sales', compact(
            'orders', 'startDate', 'endDate', 'branches', 'branchId', 'isSuperAdmin',
            'totalCash', 'totalCard', 'totalBank', 'totalCredit', 'totalSalesAmount'
        ));
    }


    public function exportPdf(Request $request)
    {
        $data         = $this->buildActivities($request);
        $payments     = $data['payments'];
        $creditOrders = $data['creditOrders'];
        $startDate    = $data['startDate'];
        $endDate      = $data['endDate'];
        $branchId     = $data['branchId'];
        $isSuperAdmin = $data['isSuperAdmin'];
        $allActivities = $payments->concat($creditOrders)->sortByDesc('date')->values();

        $totalCash        = $payments->where('payment_method', 'cash')->sum('amount');
        $totalCard        = $payments->where('payment_method', 'card')->sum('amount');
        $totalBank        = $payments->where('payment_method', 'bank')->sum('amount');
        $totalCredit      = $creditOrders->sum('amount');
        $totalSalesAmount = $totalCash + $totalCard + $totalBank + $totalCredit;

        $perPage      = 500;
        $currentPage  = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $currentItems = $allActivities->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $orders       = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems, $allActivities->count(), $perPage, $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        $branch = Branch::find($branchId ?? 1);

        return view('reports.sales_pdf', compact(
            'orders', 'startDate', 'endDate', 'branch', 'isSuperAdmin', 'branchId',
            'totalCash', 'totalCard', 'totalBank', 'totalCredit', 'totalSalesAmount'
        ));
    }
}
