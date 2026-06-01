<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // 1. Sales Report
        $salesQuery = \App\Models\Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        $orders = $salesQuery->with('customer')->orderBy('created_at', 'desc')->get();
        $totalSales = $orders->sum('total');

        // 2. Service-wise breakdown
        $serviceBreakdown = \App\Models\OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $serviceBreakdown->where('orders.branch_id', $branchId);
        }
        $services = $serviceBreakdown->groupBy('service_types.name')->get();

        // 3. Staff Salary Report
        $salaryQuery = \App\Models\Expense::where('category', 'salary')
            ->whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) {
            $salaryQuery->where('branch_id', $branchId);
        }
        $salaries = $salaryQuery->orderBy('expense_date', 'desc')->get();
        $totalSalaries = $salaries->sum('amount');

        // 4. Inventory Transactions Log
        $inventoryQuery = \App\Models\InventoryTransaction::with(['inventoryItem', 'createdBy'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $inventoryQuery->whereHas('inventoryItem', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $inventoryLogs = $inventoryQuery->latest()->get();

        return view('reports.index', compact(
            'orders', 'totalSales', 'services', 'salaries', 'totalSalaries', 'inventoryLogs', 'startDate', 'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Reuse same logic, render print-friendly view
        $branchId  = auth()->user()->branch_id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        $salesQuery = \App\Models\Order::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $salesQuery->where('branch_id', $branchId);
        $orders     = $salesQuery->with('customer')->orderBy('created_at', 'desc')->get();
        $totalSales = $orders->sum('total');

        $serviceBreakdown = \App\Models\OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $serviceBreakdown->where('orders.branch_id', $branchId);
        $services = $serviceBreakdown->groupBy('service_types.name')->get();

        $salaryQuery = \App\Models\Expense::where('category', 'salary')->whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) $salaryQuery->where('branch_id', $branchId);
        $salaries      = $salaryQuery->orderBy('expense_date', 'desc')->get();
        $totalSalaries = $salaries->sum('amount');

        $expenseQuery = \App\Models\Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) $expenseQuery->where('branch_id', $branchId);
        $expenses      = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        $branch = \App\Models\Branch::find($branchId ?? 1);

        return view('reports.pdf', compact(
            'orders', 'totalSales', 'services', 'salaries', 'totalSalaries',
            'expenses', 'totalExpenses', 'startDate', 'endDate', 'branch'
        ));
    }

    public function closeShiftReport(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $branch   = \App\Models\Branch::find($branchId);

        // Latest active shift
        $session = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->whereNull('closed_at')
            ->latest()
            ->first();

        if (!$session) {
            $session = \App\Models\RegisterSession::where('branch_id', $branchId)
                ->latest()
                ->first();
        }

        $startDate = $session ? $session->opened_at->toDateString() : now()->toDateString();
        $endDate   = now()->toDateString();

        $orders = \App\Models\Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->with('customer')
            ->get();
        $totalSales    = $orders->sum('total');
        $totalCashIn   = $orders->sum('paid_amount');

        $expenses = \App\Models\Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();
        $totalExpenses = $expenses->sum('amount');

        $openingBalance = $session ? ($session->opening_cash ?? 0) : 0;
        $closingBalance = $openingBalance + $totalCashIn - $totalExpenses;

        // Stock levels at end of day
        $inventoryItems = \App\Models\InventoryItem::where('branch_id', $branchId)->get();

        return view('reports.close_shift', compact(
            'branch', 'session', 'orders', 'totalSales', 'totalCashIn',
            'expenses', 'totalExpenses', 'openingBalance', 'closingBalance',
            'startDate', 'endDate', 'inventoryItems'
        ));
    }
}
