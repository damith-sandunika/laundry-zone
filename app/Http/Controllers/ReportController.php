<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CustomerPackage;
use App\Models\DeliveryAppointment;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackagePayment;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use App\Models\BankDeposit;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $branches = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        // 1. Sales Report
        $salesQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        $orders = $salesQuery->with(['customer', 'items.serviceType'])->orderBy('created_at', 'desc')->get();
        $totalSales = $orders->sum('total');
        $paymentsQuery = PaymentTransaction::with(['order', 'order.branch'])->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId);
            });
        }
        $payments = $paymentsQuery->get();

        $packagePaymentsQuery = PackagePayment::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $packagePayments = $packagePaymentsQuery->get();

        $totalCollected = $payments->sum('amount') + $packagePayments->sum('amount');
        $collectedCash = $payments->where('payment_method', 'cash')->sum('amount') + $packagePayments->where('payment_method', 'cash')->sum('amount');
        $collectedCard = $payments->where('payment_method', 'card')->sum('amount') + $packagePayments->where('payment_method', 'card')->sum('amount');
        $collectedBank = $payments->where('payment_method', 'bank')->sum('amount') + $packagePayments->where('payment_method', 'bank')->sum('amount');

        $actualTotalDeliveryFees = 0;
        $actualTotalBranchProfit = 0;
        $actualTotalWorkshopProfit = 0;
        
        foreach ($payments as $payment) {
            if ($payment->order) {
                $order = $payment->order;
                $deliveryFee = $order->delivery_fee ?? 0;
                
                $paidBefore = PaymentTransaction::where('order_id', $order->id)
                    ->where('id', '<', $payment->id)
                    ->sum('amount');

                if ($paidBefore >= $deliveryFee) {
                    $paidToDelivery = 0;
                    $commissionable = $payment->amount;
                } else {
                    $remainingDeliveryFee = $deliveryFee - $paidBefore;
                    $paidToDelivery = min($payment->amount, $remainingDeliveryFee);
                    $commissionable = $payment->amount - $paidToDelivery;
                }
                
                $actualTotalDeliveryFees += $paidToDelivery;
                
                if ($commissionable > 0) {
                    $branchProfitPercent = $order->branch->branch_profit_percentage ?? 0;
                    
                    if ($order->branch_id == $order->workshop_id || !$order->workshop_id) {
                        $actualTotalWorkshopProfit += $commissionable;
                    } else {
                        $branchComm = $commissionable * ($branchProfitPercent / 100);
                        $workshopComm = $commissionable - $branchComm;
                        
                        $actualTotalBranchProfit += $branchComm;
                        $actualTotalWorkshopProfit += $workshopComm;
                    }
                }
            }
        }
        
        // Add package payments to branch profit as they don't have delivery fees
        $actualTotalBranchProfit += $packagePayments->sum('amount');

        $totalBranchProfit = $actualTotalBranchProfit;
        $totalWorkshopProfit = $actualTotalWorkshopProfit;
        $totalDeliveryFees = $actualTotalDeliveryFees;

        $totalCredit = $orders->where('payment_method', 'credit')->sum(function ($q) {
            return max(0, $q->total - $q->paid_amount);
        });
        $totalPending = $orders->whereNotIn('payment_method', ['credit'])->sum(function ($q) {
            return max(0, $q->total - $q->paid_amount);
        });

        // Credit Customers
        $creditOrdersQuery = Order::with('customer')
            ->whereColumn('paid_amount', '<', 'total')
            ->where('payment_method', 'credit');
        if ($branchId) {
            $creditOrdersQuery->where('branch_id', $branchId);
        }
        $creditOrders = $creditOrdersQuery->orderBy('created_at', 'asc')->get();

        // 2. Service-wise breakdown
        $serviceBreakdown = OrderItem::select(
            'service_types.name',
            \DB::raw('SUM(order_items.quantity) as total_qty'),
            \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'),
            \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) as done_payment'),
            \DB::raw('SUM(CASE WHEN orders.payment_method = "credit" THEN order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * (1 - IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) ELSE 0 END) as credit_payment'),
            \DB::raw('SUM(CASE WHEN (orders.payment_method IS NULL OR orders.payment_method != "credit") THEN order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0)) * (1 - IFNULL(orders.paid_amount / NULLIF(orders.total, 0), 0)) ELSE 0 END) as pending_payment')
        )
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $serviceBreakdown->where('orders.branch_id', $branchId);
        }
        $services = $serviceBreakdown->groupBy('service_types.name')->get();

        // 3. Expenses Report
        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $expensesList = $expenseQuery->orderBy('expense_date', 'desc')->get();
        $totalExpensesAmount = $expensesList->sum('amount');

        // 4. Inventory Transactions Log
        $inventoryQuery = InventoryTransaction::with(['inventoryItem', 'createdBy'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $inventoryQuery->whereHas('inventoryItem', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $inventoryLogs = $inventoryQuery->latest()->get();

        // 5. Packages Report
        $customerPackagesQuery = CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $customerPackagesQuery->where('branch_id', $branchId);
        }
        $customerPackages = $customerPackagesQuery->latest()->get();

        // 6. Advanced Analytics
        // Walk-in vs Delivery Split
        $deliveryAppointments = DeliveryAppointment::whereNotNull('order_id')
            ->whereHas('order', function ($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->get();

        $deliveryOrderIds = $deliveryAppointments->pluck('order_id')->toArray();

        $deliveryOrders = $orders->whereIn('id', $deliveryOrderIds);
        $walkinOrders = $orders->whereNotIn('id', $deliveryOrderIds);

        $deliveryRevenue = $deliveryOrders->sum('total');
        $walkinRevenue = $walkinOrders->sum('total');

        // Daily Revenue Trend
        $dailyRevenue = Order::select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(total) as revenue'))
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $dailyRevenue->where('branch_id', $branchId);
        }
        $dailyRevenue = $dailyRevenue->groupBy('date')->orderBy('date', 'asc')->get();

        // Top Customers
        $topCustomersQuery = clone $salesQuery;
        $topCustomers = $topCustomersQuery->select('customer_id', \DB::raw('SUM(total) as total_spent'), \DB::raw('COUNT(id) as total_orders'))
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        // 7. Bank Deposits
        $depositsQuery = BankDeposit::with(['branch', 'user'])
            ->whereBetween('deposited_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $depositsQuery->where('branch_id', $branchId);
        }
        $bankDeposits = $depositsQuery->orderBy('deposited_at', 'desc')->get();

        return view('reports.index', compact(
            'orders', 'totalSales', 'totalDeliveryFees', 'totalCollected', 'collectedCash', 'collectedCard', 'collectedBank', 'totalCredit', 'totalPending',
            'services', 'expensesList', 'totalExpensesAmount', 'inventoryLogs', 'startDate', 'endDate', 'creditOrders',
            'customerPackages', 'packagePayments', 'branches', 'branchId', 'isSuperAdmin',
            'deliveryRevenue', 'walkinRevenue', 'deliveryOrders', 'walkinOrders', 'dailyRevenue', 'topCustomers',
            'totalBranchProfit', 'totalWorkshopProfit', 'bankDeposits'
        ));
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $salesQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        $orders = $salesQuery->with('customer')->orderBy('created_at', 'desc')->get();
        $totalSales = $orders->sum('total');

        $paymentsQuery = PaymentTransaction::with(['order', 'order.branch'])->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId);
            });
        }
        $payments = $paymentsQuery->get();

        $packagePaymentsQuery = PackagePayment::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $packagePayments = $packagePaymentsQuery->get();
        $totalPackagePayments = $packagePayments->sum('amount');
        
        $totalCollected = $payments->sum('amount') + $totalPackagePayments;
        $collectedCash = $payments->where('payment_method', 'cash')->sum('amount') + $packagePayments->where('payment_method', 'cash')->sum('amount');
        $collectedCard = $payments->where('payment_method', 'card')->sum('amount') + $packagePayments->where('payment_method', 'card')->sum('amount');
        $collectedBank = $payments->where('payment_method', 'bank')->sum('amount') + $packagePayments->where('payment_method', 'bank')->sum('amount');

        $actualTotalDeliveryFees = 0;
        $actualTotalBranchProfit = 0;
        $actualTotalWorkshopProfit = 0;
        
        foreach ($payments as $payment) {
            if ($payment->order) {
                $order = $payment->order;
                $deliveryFee = $order->delivery_fee ?? 0;
                
                $paidBefore = PaymentTransaction::where('order_id', $order->id)
                    ->where('id', '<', $payment->id)
                    ->sum('amount');

                if ($paidBefore >= $deliveryFee) {
                    $paidToDelivery = 0;
                    $commissionable = $payment->amount;
                } else {
                    $remainingDeliveryFee = $deliveryFee - $paidBefore;
                    $paidToDelivery = min($payment->amount, $remainingDeliveryFee);
                    $commissionable = $payment->amount - $paidToDelivery;
                }
                
                $actualTotalDeliveryFees += $paidToDelivery;
                
                if ($commissionable > 0) {
                    $branchProfitPercent = $order->branch->branch_profit_percentage ?? 0;
                    
                    if ($order->branch_id == $order->workshop_id || !$order->workshop_id) {
                        $actualTotalWorkshopProfit += $commissionable;
                    } else {
                        $branchComm = $commissionable * ($branchProfitPercent / 100);
                        $workshopComm = $commissionable - $branchComm;
                        
                        $actualTotalBranchProfit += $branchComm;
                        $actualTotalWorkshopProfit += $workshopComm;
                    }
                }
            }
        }
        
        // Add package payments to branch profit as they don't have delivery fees
        $actualTotalBranchProfit += $packagePayments->sum('amount');

        $totalBranchProfit = $actualTotalBranchProfit;
        $totalWorkshopProfit = $actualTotalWorkshopProfit;
        $totalDeliveryFees = $actualTotalDeliveryFees;

        $serviceBreakdown = OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $serviceBreakdown->where('orders.branch_id', $branchId);
        }
        $services = $serviceBreakdown->groupBy('service_types.name')->get();

        $salaryQuery = Expense::where('category', 'salary')->whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) {
            $salaryQuery->where('branch_id', $branchId);
        }
        $salaries = $salaryQuery->orderBy('expense_date', 'desc')->get();
        $totalSalaries = $salaries->sum('amount');

        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $expenses = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        // Inventory Cost
        $inventoryCostQuery = InventoryTransaction::where('inventory_transactions.type', 'out')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
            ->whereBetween('inventory_transactions.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $inventoryCostQuery->where('inventory_items.branch_id', $branchId);
        }
        $totalInventoryCost = $inventoryCostQuery->sum(\DB::raw('inventory_transactions.quantity * inventory_items.cost_per_unit'));


        $totalCredit = $orders->where('payment_method', 'credit')->sum(function ($q) {
            return max(0, $q->total - $q->paid_amount);
        });
        $totalPending = $orders->whereNotIn('payment_method', ['credit'])->sum(function ($q) {
            return max(0, $q->total - $q->paid_amount);
        });

        $customerPackagesQuery = CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $customerPackagesQuery->where('branch_id', $branchId);
        }
        $customerPackages = $customerPackagesQuery->latest()->get();

        $branch = Branch::find($branchId ?? \App\Models\Branch::first()->id);

        // Limit orders passed to PDF to keep it clean (latest 15)
        $recentOrders = collect(); // User requested to remove recent orders

        return view('reports.pdf', compact(
            'recentOrders', 'totalSales', 'totalDeliveryFees', 'services', 'salaries', 'totalSalaries',
            'expenses', 'totalExpenses', 'totalInventoryCost', 'startDate', 'endDate', 'branch',
            'totalPackagePayments', 'totalCollected', 'collectedCash', 'collectedCard', 'collectedBank',
            'totalCredit', 'totalPending', 'customerPackages', 'totalBranchProfit', 'totalWorkshopProfit'
        ));
    }

    public function serviceReport(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $branches = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        $query = OrderItem::select(
                'service_categories.name as category_name',
                'service_types.name as service_name',
                \DB::raw('SUM(order_items.quantity) as total_qty'),
                \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue')
            )
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->leftJoin('service_categories', 'service_categories.id', '=', 'service_types.category_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        } else if ($isSuperAdmin) {
            // Include branch name if viewing all branches
            $query->addSelect('branches.name as branch_name')
                  ->join('branches', 'branches.id', '=', 'orders.branch_id')
                  ->groupBy('branches.name');
        }

        $services = $query->groupBy('service_categories.name', 'service_types.name')
            ->having('total_qty', '>', 0)
            ->orderBy('total_revenue', 'desc')
            ->get();

        $totalQty = $services->sum('total_qty');
        $totalRevenue = $services->sum('total_revenue');

        return view('reports.services', compact('services', 'startDate', 'endDate', 'branches', 'branchId', 'isSuperAdmin', 'totalQty', 'totalRevenue'));
    }

    public function serviceReportPdf(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $query = OrderItem::select(
                'service_categories.name as category_name',
                'service_types.name as service_name',
                \DB::raw('SUM(order_items.quantity) as total_qty'),
                \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue')
            )
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->leftJoin('service_categories', 'service_categories.id', '=', 'service_types.category_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        } else if ($isSuperAdmin) {
            $query->addSelect('branches.name as branch_name')
                  ->join('branches', 'branches.id', '=', 'orders.branch_id')
                  ->groupBy('branches.name');
        }

        $services = $query->groupBy('service_categories.name', 'service_types.name')
            ->having('total_qty', '>', 0)
            ->orderBy('total_revenue', 'desc')
            ->get();

        $totalQty = $services->sum('total_qty');
        $totalRevenue = $services->sum('total_revenue');
        $branch = Branch::find($branchId ?? 1);

        return view('reports.services_pdf', compact('services', 'startDate', 'endDate', 'branch', 'totalQty', 'totalRevenue', 'isSuperAdmin', 'branchId'));
    }

    public function customerReport(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $branches = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

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
        } else if ($isSuperAdmin) {
            $query->addSelect('branches.name as branch_name')
                  ->join('branches', 'branches.id', '=', 'orders.branch_id')
                  ->groupBy('branches.name');
        }

        $customers = $query->groupBy('customers.id', 'customers.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $totalOrders = $customers->sum('order_count');
        $totalRevenue = $customers->sum('total_revenue');

        return view('reports.customers', compact('customers', 'startDate', 'endDate', 'branches', 'branchId', 'isSuperAdmin', 'totalOrders', 'totalRevenue'));
    }

    public function customerReportPdf(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

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
        } else if ($isSuperAdmin) {
            $query->addSelect('branches.name as branch_name')
                  ->join('branches', 'branches.id', '=', 'orders.branch_id')
                  ->groupBy('branches.name');
        }

        $customers = $query->groupBy('customers.id', 'customers.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $totalOrders = $customers->sum('order_count');
        $totalRevenue = $customers->sum('total_revenue');
        $branch = Branch::find($branchId ?? 1);

        return view('reports.customers_pdf', compact('customers', 'startDate', 'endDate', 'branch', 'totalOrders', 'totalRevenue', 'isSuperAdmin', 'branchId'));
    }

    public function closeShiftReport(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? \App\Models\Branch::first()->id;
        $branch = Branch::find($branchId);

        // Latest active shift
        $session = RegisterSession::where('branch_id', $branchId)
            ->whereNull('closed_at')
            ->latest()
            ->first();

        if (! $session) {
            $session = RegisterSession::where('branch_id', $branchId)
                ->latest()
                ->first();
        }

        $startDate = $session ? $session->opened_at->toDateString() : now()->toDateString();
        $endDate = now()->toDateString();

        $orders = Order::where('branch_id', $branchId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->with('customer')
            ->get();
        $totalSales = $orders->sum('total');
        $totalCashIn = $orders->sum('paid_amount');
        $totalDiscounts = $orders->sum('discount');
        
        $totalBranchProfit = $orders->sum('branch_profit');
        $totalWorkshopProfit = $orders->sum('workshop_profit');

        $cashPaymentOnly = $orders->where('payment_method', 'cash')->sum('paid_amount');
        $cardPaymentOnly = $orders->where('payment_method', 'card')->sum('paid_amount');
        $bankPaymentOnly = $orders->where('payment_method', 'bank')->sum('paid_amount');

        $expenses = Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();
        $totalExpenses = $expenses->sum('amount');

        $packagePayments = PackagePayment::whereHas('customerPackage', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
            ->whereBetween('created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->get();
        $totalPackageIncome = $packagePayments->sum('amount');
        $cashPackageIncome = $packagePayments->where('payment_method', 'cash')->sum('amount');
        $cardPackageIncome = $packagePayments->where('payment_method', 'card')->sum('amount');
        $bankPackageIncome = $packagePayments->where('payment_method', 'bank')->sum('amount');

        $totalCashIn += $totalPackageIncome;
        $cashPaymentOnly += $cashPackageIncome;
        $cardPaymentOnly += $cardPackageIncome;
        $bankPaymentOnly += $bankPackageIncome;

        $openingBalance = $session ? ($session->opening_cash ?? 0) : 0;
        $closingBalance = $openingBalance + $totalCashIn - $totalExpenses;

        // Stock levels at end of day
        $inventoryItems = InventoryItem::where('branch_id', $branchId)->get();

        // Service Breakdown
        $serviceBreakdown = OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->groupBy('service_types.name')
            ->get();

        // Inventory Consumed
        $inventoryConsumed = InventoryTransaction::select('inventory_items.name', 'inventory_items.unit', \DB::raw('SUM(inventory_transactions.quantity) as total_used'))
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
            ->where('inventory_items.branch_id', $branchId)
            ->where('inventory_transactions.type', 'out')
            ->whereBetween('inventory_transactions.created_at', [$session ? $session->opened_at : now()->startOfDay(), now()])
            ->groupBy('inventory_items.name', 'inventory_items.unit')
            ->get();

        return view('reports.close_shift', compact(
            'branch', 'session', 'orders', 'totalSales', 'totalCashIn', 'cashPaymentOnly', 'cardPaymentOnly', 'bankPaymentOnly',
            'expenses', 'totalExpenses', 'openingBalance', 'closingBalance',
            'startDate', 'endDate', 'inventoryItems', 'serviceBreakdown', 'inventoryConsumed', 'totalPackageIncome', 'totalDiscounts',
            'totalBranchProfit', 'totalWorkshopProfit'
        ));
    }

    public function approvePackagePayment(Request $request, CustomerPackage $customerPackage)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,bank',
        ]);

        if ($customerPackage->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Package is already paid.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($customerPackage, $request) {
            PackagePayment::create([
                'customer_package_id' => $customerPackage->id,
                'amount' => $customerPackage->package->price,
                'payment_method' => $request->payment_method,
                'created_by' => auth()->id(),
            ]);

            $customerPackage->update(['payment_status' => 'paid']);
        });

        return redirect()->back()->with('success', 'Package payment approved successfully!');
    }
}
