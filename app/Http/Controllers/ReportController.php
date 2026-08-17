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
        $orders = $salesQuery->with(['customer', 'items.serviceType'])->orderBy('created_at', 'desc')->get();
        $totalSales = $orders->sum('total');
        
        $paymentsQuery = \App\Models\PaymentTransaction::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $payments = $paymentsQuery->get();
        
        $packagePaymentsQuery = \App\Models\PackagePayment::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $packagePayments = $packagePaymentsQuery->get();
        
        $totalCollected = $payments->sum('amount') + $packagePayments->sum('amount');
        $collectedCash = $payments->where('payment_method', 'cash')->sum('amount') + $packagePayments->where('payment_method', 'cash')->sum('amount');
        $collectedCard = $payments->where('payment_method', 'card')->sum('amount') + $packagePayments->where('payment_method', 'card')->sum('amount');
        
        $totalCredit = $orders->where('payment_method', 'credit')->sum(function($q) { return max(0, $q->total - $q->paid_amount); });
        $totalPending = $orders->where('payment_method', '!=', 'credit')->sum(function($q) { return max(0, $q->total - $q->paid_amount); });

        // Credit Customers
        $creditOrdersQuery = \App\Models\Order::with('customer')
            ->whereColumn('paid_amount', '<', 'total')
            ->where('payment_method', 'credit');
        if ($branchId) {
            $creditOrdersQuery->where('branch_id', $branchId);
        }
        $creditOrders = $creditOrdersQuery->orderBy('created_at', 'asc')->get();

        // 2. Service-wise breakdown
        $serviceBreakdown = \App\Models\OrderItem::select(
            'service_types.name', 
            \DB::raw('SUM(order_items.quantity) as total_qty'), 
            \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'),
            \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) as done_payment'),
            \DB::raw('SUM(CASE WHEN orders.payment_method = "credit" THEN order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * (1 - IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) ELSE 0 END) as credit_payment'),
            \DB::raw('SUM(CASE WHEN (orders.payment_method IS NULL OR orders.payment_method != "credit") THEN order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * (1 - IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) ELSE 0 END) as pending_payment')
        )
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

        // 5. Packages Report
        $customerPackagesQuery = \App\Models\CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $customerPackagesQuery->where('branch_id', $branchId);
        }
        $customerPackages = $customerPackagesQuery->latest()->get();

        return view('reports.index', compact(
            'orders', 'totalSales', 'totalCollected', 'collectedCash', 'collectedCard', 'totalCredit', 'totalPending', 'services', 'salaries', 'totalSalaries', 'inventoryLogs', 'startDate', 'endDate', 'creditOrders', 'customerPackages', 'packagePayments'
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

        $serviceBreakdown = \App\Models\OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
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

        $paymentsQuery = \App\Models\PaymentTransaction::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $payments = $paymentsQuery->get();

        $packagePaymentsQuery = \App\Models\PackagePayment::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $packagePayments = $packagePaymentsQuery->get();
        $totalPackagePayments = $packagePayments->sum('amount');

        $totalCollected = $payments->sum('amount') + $totalPackagePayments;
        $collectedCash = $payments->where('payment_method', 'cash')->sum('amount') + $packagePayments->where('payment_method', 'cash')->sum('amount');
        $collectedCard = $payments->where('payment_method', 'card')->sum('amount') + $packagePayments->where('payment_method', 'card')->sum('amount');

        $totalCredit = $orders->where('payment_method', 'credit')->sum(function($q) { return max(0, $q->total - $q->paid_amount); });
        $totalPending = $orders->where('payment_method', '!=', 'credit')->sum(function($q) { return max(0, $q->total - $q->paid_amount); });

        $customerPackagesQuery = \App\Models\CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId) {
            $customerPackagesQuery->where('branch_id', $branchId);
        }
        $customerPackages = $customerPackagesQuery->latest()->get();

        $branch = \App\Models\Branch::find($branchId ?? 1);

        // Limit orders passed to PDF to keep it clean (latest 15)
        $recentOrders = $orders->take(15);

        return view('reports.pdf', compact(
            'recentOrders', 'totalSales', 'services', 'salaries', 'totalSalaries',
            'expenses', 'totalExpenses', 'startDate', 'endDate', 'branch', 
            'totalPackagePayments', 'totalCollected', 'collectedCash', 'collectedCard',
            'totalCredit', 'totalPending', 'customerPackages'
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
        $totalDiscounts = $orders->sum('discount');
        
        $cashPaymentOnly = $orders->where('payment_method', 'cash')->sum('paid_amount');

        $expenses = \App\Models\Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();
        $totalExpenses = $expenses->sum('amount');

        $packagePayments = \App\Models\PackagePayment::whereHas('customerPackage', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->whereBetween('created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->get();
        $totalPackageIncome = $packagePayments->sum('amount');
        $cashPackageIncome = $packagePayments->where('payment_method', 'cash')->sum('amount');

        $totalCashIn += $totalPackageIncome;
        $cashPaymentOnly += $cashPackageIncome;

        $openingBalance = $session ? ($session->opening_cash ?? 0) : 0;
        $closingBalance = $openingBalance + $totalCashIn - $totalExpenses;

        // Stock levels at end of day
        $inventoryItems = \App\Models\InventoryItem::where('branch_id', $branchId)->get();

        // Service Breakdown
        $serviceBreakdown = \App\Models\OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->groupBy('service_types.name')
            ->get();

        // Inventory Consumed
        $inventoryConsumed = \App\Models\InventoryTransaction::select('inventory_items.name', 'inventory_items.unit', \DB::raw('SUM(inventory_transactions.quantity) as total_used'))
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
            ->where('inventory_items.branch_id', $branchId)
            ->where('inventory_transactions.type', 'out')
            ->whereBetween('inventory_transactions.created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->groupBy('inventory_items.name', 'inventory_items.unit')
            ->get();

        return view('reports.close_shift', compact(
            'branch', 'session', 'orders', 'totalSales', 'totalCashIn', 'cashPaymentOnly',
            'expenses', 'totalExpenses', 'openingBalance', 'closingBalance',
            'startDate', 'endDate', 'inventoryItems', 'serviceBreakdown', 'inventoryConsumed', 'totalPackageIncome', 'totalDiscounts'
        ));
    }

    public function approvePackagePayment(Request $request, \App\Models\CustomerPackage $customerPackage)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card'
        ]);

        if ($customerPackage->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Package is already paid.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($customerPackage, $request) {
            \App\Models\PackagePayment::create([
                'customer_package_id' => $customerPackage->id,
                'amount' => $customerPackage->package->price,
                'payment_method' => $request->payment_method,
                'created_by' => auth()->id()
            ]);

            $customerPackage->update(['payment_status' => 'paid']);
        });

        return redirect()->back()->with('success', 'Package payment approved successfully!');
    }
}
