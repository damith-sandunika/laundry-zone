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

        // --- DB-level Aggregates (no data loaded into RAM for totals) ---
        $maxFunc = \DB::getDriverName() === 'sqlite' ? 'MAX(0, total - paid_amount)' : 'GREATEST(0, total - paid_amount)';
        $totalSales        = (clone $salesQuery)->sum('total');
        $totalCredit       = (clone $salesQuery)->where('payment_method', 'credit')->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;
        $totalPending      = (clone $salesQuery)->whereNotIn('payment_method', ['credit'])->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;

        // Delivery/Walkin revenue via DB
        $deliveryOrderIds  = DeliveryAppointment::whereNotNull('order_id')
            ->whereHas('order', function ($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                if ($branchId) $q->where('branch_id', $branchId);
            })->pluck('order_id')->toArray();
        $deliveryRevenue   = (clone $salesQuery)->whereIn('id', $deliveryOrderIds)->sum('total');
        $walkinRevenue     = (clone $salesQuery)->whereNotIn('id', $deliveryOrderIds)->sum('total');

        // Paginated orders list for the report table (15 per page)
        $orders = $salesQuery->with(['customer', 'items.serviceType'])->orderBy('created_at', 'desc')->paginate(15);

        // Collection helpers for view compatibility (from paginated page only)
        $deliveryOrders = $orders->getCollection()->whereIn('id', $deliveryOrderIds);
        $walkinOrders   = $orders->getCollection()->whereNotIn('id', $deliveryOrderIds);

        // Payments & package payments - using DB aggregates instead of RAM
        $paymentsQuery = PaymentTransaction::with(['order', 'order.branch'])->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId);
            });
        }

        $packagePaymentsQuery = PackagePayment::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $packagePaymentsSum = (clone $packagePaymentsQuery)->sum('amount');
        $packagePaymentsCash = (clone $packagePaymentsQuery)->where('payment_method', 'cash')->sum('amount');
        $packagePaymentsCard = (clone $packagePaymentsQuery)->where('payment_method', 'card')->sum('amount');
        $packagePaymentsBank = (clone $packagePaymentsQuery)->where('payment_method', 'bank')->sum('amount');

        $totalCollected = (clone $paymentsQuery)->sum('amount') + $packagePaymentsSum;
        $collectedCash  = (clone $paymentsQuery)->where('payment_method', 'cash')->sum('amount') + $packagePaymentsCash;
        $collectedCard  = (clone $paymentsQuery)->where('payment_method', 'card')->sum('amount') + $packagePaymentsCard;
        $collectedBank  = (clone $paymentsQuery)->where('payment_method', 'bank')->sum('amount') + $packagePaymentsBank;

        $actualTotalDeliveryFees = 0;
        $actualTotalBranchProfit = 0;
        $actualTotalWorkshopProfit = 0;
        
        $paymentsQuery->chunkById(500, function($paymentsChunk) use (&$actualTotalDeliveryFees, &$actualTotalBranchProfit, &$actualTotalWorkshopProfit) {
            $orderIds = $paymentsChunk->pluck('order_id')->filter()->unique()->toArray();
            if (empty($orderIds)) return;
            
            $allTransactions = PaymentTransaction::whereIn('order_id', $orderIds)
                ->select('id', 'order_id', 'amount')
                ->get()
                ->groupBy('order_id');

            foreach ($paymentsChunk as $payment) {
                if ($payment->order) {
                    $order = $payment->order;
                    $deliveryFee = $order->delivery_fee ?? 0;
                    
                    $transactionsForOrder = $allTransactions->get($order->id, collect());
                    $paidBefore = $transactionsForOrder->where('id', '<', $payment->id)->sum('amount');

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
        });
        
        // Add package payments to branch profit as they don't have delivery fees
        $actualTotalBranchProfit += $packagePaymentsSum;

        $totalBranchProfit   = $actualTotalBranchProfit;
        $totalWorkshopProfit = $actualTotalWorkshopProfit;
        $totalDeliveryFees   = $actualTotalDeliveryFees;

        // Credit Customers
        $creditOrdersQuery = Order::with('customer')
            ->whereColumn('paid_amount', '<', 'total')
            ->where('payment_method', 'credit');
        if ($branchId) {
            $creditOrdersQuery->where('branch_id', $branchId);
        }
        $creditOrders = collect(); // Optimized out, not used in view

        // 2. Service-wise breakdown (already DB-aggregated, no pagination needed)
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

        // 3. Expenses Report - paginated
        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $totalExpensesAmount = (clone $expenseQuery)->sum('amount'); // DB aggregate
        $expensesList = $expenseQuery->orderBy('expense_date', 'desc')->paginate(15);

        // 4. Inventory Transactions Log - paginated
        $inventoryQuery = InventoryTransaction::with(['inventoryItem', 'createdBy'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $inventoryQuery->whereHas('inventoryItem', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $inventoryLogs = $inventoryQuery->latest()->paginate(15);

        // 5. Packages Report - paginated
        $customerPackagesQuery = CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $customerPackagesQuery->where('branch_id', $branchId);
        }
        $customerPackages = $customerPackagesQuery->latest()->paginate(15);

        // Daily Revenue Trend
        $dailyRevenue = Order::select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(total) as revenue'))
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $dailyRevenue->where('branch_id', $branchId);
        }
        $dailyRevenue = $dailyRevenue->groupBy('date')->orderBy('date', 'asc')->get();

        // Top Customers
        $topCustomersQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $topCustomersQuery->where('branch_id', $branchId);
        $topCustomers = $topCustomersQuery->select('customer_id', \DB::raw('SUM(total) as total_spent'), \DB::raw('COUNT(id) as total_orders'))
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        // 7. Bank Deposits - paginated
        $depositsQuery = BankDeposit::with(['branch', 'user'])
            ->whereBetween('deposited_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $depositsQuery->where('branch_id', $branchId);
        }
        $bankDeposits = $depositsQuery->orderBy('deposited_at', 'desc')->paginate(15);

        // 8. Petty Cash Topups
        $topupsQuery = \DB::table('petty_cash_topups')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $topupsQuery->where('branch_id', $branchId);
        }
        $totalTopupsAmount = (clone $topupsQuery)->sum('amount');
        $pettyCashTopups = (clone $topupsQuery)->orderBy('created_at', 'desc')->paginate(15);

        // 9. Branch Petty Cash Summary (super admin only - all branches current balances)
        $branchPettyCashSummary = collect();
        if ($isSuperAdmin) {
            $branchPettyCashSummary = Branch::where('is_active', true)
                ->select('id', 'name', 'petty_cash_balance')
                ->get()
                ->map(function ($branch) use ($startDate, $endDate) {
                    $branch->total_expenses = Expense::where('branch_id', $branch->id)
                        ->whereBetween('expense_date', [$startDate, $endDate])
                        ->sum('amount');
                    $branch->total_topups = \DB::table('petty_cash_topups')
                        ->where('branch_id', $branch->id)
                        ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                        ->sum('amount');
                    return $branch;
                });
        }

        return view('reports.index', compact(
            'orders', 'totalSales', 'totalDeliveryFees', 'totalCollected', 'collectedCash', 'collectedCard', 'collectedBank', 'totalCredit', 'totalPending',
            'services', 'expensesList', 'totalExpensesAmount', 'inventoryLogs', 'startDate', 'endDate', 'creditOrders',
            'customerPackages', 'branches', 'branchId', 'isSuperAdmin',
            'deliveryRevenue', 'walkinRevenue', 'deliveryOrders', 'walkinOrders', 'dailyRevenue', 'topCustomers',
            'totalBranchProfit', 'totalWorkshopProfit', 'bankDeposits',
            'pettyCashTopups', 'totalTopupsAmount', 'branchPettyCashSummary'
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
        $totalSales = (clone $salesQuery)->sum('total');

        $paymentsQuery = PaymentTransaction::with(['order', 'order.branch'])->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId);
            });
        }

        $packagePaymentsQuery = PackagePayment::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        $packagePaymentsSum = (clone $packagePaymentsQuery)->sum('amount');
        $packagePaymentsCash = (clone $packagePaymentsQuery)->where('payment_method', 'cash')->sum('amount');
        $packagePaymentsCard = (clone $packagePaymentsQuery)->where('payment_method', 'card')->sum('amount');
        $packagePaymentsBank = (clone $packagePaymentsQuery)->where('payment_method', 'bank')->sum('amount');
        
        $totalCollected = (clone $paymentsQuery)->sum('amount') + $packagePaymentsSum;
        $collectedCash = (clone $paymentsQuery)->where('payment_method', 'cash')->sum('amount') + $packagePaymentsCash;
        $collectedCard = (clone $paymentsQuery)->where('payment_method', 'card')->sum('amount') + $packagePaymentsCard;
        $collectedBank = (clone $paymentsQuery)->where('payment_method', 'bank')->sum('amount') + $packagePaymentsBank;

        $actualTotalDeliveryFees = 0;
        $actualTotalBranchProfit = 0;
        $actualTotalWorkshopProfit = 0;
        
        $paymentsQuery->chunkById(500, function($paymentsChunk) use (&$actualTotalDeliveryFees, &$actualTotalBranchProfit, &$actualTotalWorkshopProfit) {
            $orderIds = $paymentsChunk->pluck('order_id')->filter()->unique()->toArray();
            if (empty($orderIds)) return;
            
            $allTransactions = PaymentTransaction::whereIn('order_id', $orderIds)
                ->select('id', 'order_id', 'amount')
                ->get()
                ->groupBy('order_id');

            foreach ($paymentsChunk as $payment) {
                if ($payment->order) {
                    $order = $payment->order;
                    $deliveryFee = $order->delivery_fee ?? 0;
                    
                    $transactionsForOrder = $allTransactions->get($order->id, collect());
                    $paidBefore = $transactionsForOrder->where('id', '<', $payment->id)->sum('amount');

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
        });
        
        // Add package payments to branch profit as they don't have delivery fees
        $actualTotalBranchProfit += $packagePaymentsSum;

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


        $totalCredit = (clone $salesQuery)->where('payment_method', 'credit')->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;
        $totalPending = (clone $salesQuery)->whereNotIn('payment_method', ['credit'])->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;

        $customerPackagesQuery = CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $customerPackagesQuery->where('branch_id', $branchId);
        }
        $customerPackages = collect(); // Optimized out for PDF

        $branch = Branch::find($branchId ?? \App\Models\Branch::first()->id);

        // Limit orders passed to PDF to keep it clean (latest 15)
        $recentOrders = collect(); // User requested to remove recent orders

        return view('reports.pdf', compact(
            'recentOrders', 'totalSales', 'totalDeliveryFees', 'services', 'salaries', 'totalSalaries',
            'expenses', 'totalExpenses', 'totalInventoryCost', 'startDate', 'endDate', 'branch',
            'totalCollected', 'collectedCash', 'collectedCard', 'collectedBank',
            'totalCredit', 'totalPending', 'customerPackages', 'totalBranchProfit', 'totalWorkshopProfit'
        ));
    }


    public function salesReport(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $branches = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        $query = Order::with(['customer', 'branch'])
            ->select('orders.*')
            ->withSum('items as total_qty', 'quantity')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.paid_amount', '>', 0)
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        $query->orderBy('orders.total', 'desc');

        $totalCash = (clone $query)->where('payment_method', 'cash')->sum('paid_amount');
        $totalCard = (clone $query)->where('payment_method', 'card')->sum('paid_amount');
        $totalBank = (clone $query)->where('payment_method', 'bank')->sum('paid_amount');
        $totalSalesAmount = $totalCash + $totalCard + $totalBank;

        // Efficient pagination keeping only 15 objects in RAM
        $orders = $query->paginate(15);

        return view('reports.sales', compact('orders', 'startDate', 'endDate', 'branches', 'branchId', 'isSuperAdmin', 'totalCash', 'totalCard', 'totalBank', 'totalSalesAmount'));
    }

    public function salesReportPdf(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $query = Order::with(['customer', 'branch'])
            ->select('orders.*')
            ->withSum('items as total_qty', 'quantity')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.paid_amount', '>', 0)
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

        if ($branchId) {
            $query->where('orders.branch_id', $branchId);
        }

        $query->orderBy('orders.total', 'desc');

        $totalCash = (clone $query)->where('payment_method', 'cash')->sum('paid_amount');
        $totalCard = (clone $query)->where('payment_method', 'card')->sum('paid_amount');
        $totalBank = (clone $query)->where('payment_method', 'bank')->sum('paid_amount');
        $totalSalesAmount = $totalCash + $totalCard + $totalBank;

        $orders = $query->paginate(500); // Optimized for PDF generation
        $branch = Branch::find($branchId ?? 1);

        return view('reports.sales_pdf', compact('orders', 'startDate', 'endDate', 'branch', 'isSuperAdmin', 'branchId', 'totalCash', 'totalCard', 'totalBank', 'totalSalesAmount'));
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
        $branchId = $this->getActiveBranchId();
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

        $shiftPeriodStart = $session ? $session->opened_at : now()->startOfDay();
        $shiftPeriodEnd   = now();

        $shiftOrderQuery = Order::where('branch_id', $branchId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$shiftPeriodStart, $shiftPeriodEnd]);

        // --- DB-level Aggregates for shift totals (no RAM overload) ---
        $totalSales          = (clone $shiftOrderQuery)->sum('total');
        $totalCashIn         = (clone $shiftOrderQuery)->sum('paid_amount');
        $totalDiscounts      = (clone $shiftOrderQuery)->sum('discount');
        $totalBranchProfit   = (clone $shiftOrderQuery)->sum('branch_profit');
        $totalWorkshopProfit = (clone $shiftOrderQuery)->sum('workshop_profit');
        $cashPaymentOnly     = (clone $shiftOrderQuery)->where('payment_method', 'cash')->sum('paid_amount');
        $cardPaymentOnly     = (clone $shiftOrderQuery)->where('payment_method', 'card')->sum('paid_amount');
        $bankPaymentOnly     = (clone $shiftOrderQuery)->where('payment_method', 'bank')->sum('paid_amount');

        // Paginated orders list for the shift view
        $orders = $shiftOrderQuery->with('customer')->orderBy('created_at', 'desc')->paginate(15);

        $totalExpenses = Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
        $expenses = Expense::where('branch_id', $branchId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        $packagePaymentsQ = PackagePayment::whereHas('customerPackage', fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$shiftPeriodStart, $shiftPeriodEnd]);
        $totalPackageIncome = (clone $packagePaymentsQ)->sum('amount');
        $cashPackageIncome  = (clone $packagePaymentsQ)->where('payment_method', 'cash')->sum('amount');
        $cardPackageIncome  = (clone $packagePaymentsQ)->where('payment_method', 'card')->sum('amount');
        $bankPackageIncome  = (clone $packagePaymentsQ)->where('payment_method', 'bank')->sum('amount');

        $totalCashIn     += $totalPackageIncome;
        $cashPaymentOnly += $cashPackageIncome;
        $cardPaymentOnly += $cardPackageIncome;
        $bankPaymentOnly += $bankPackageIncome;

        $openingBalance = $session ? ($session->opening_cash ?? 0) : 0;
        $closingBalance = $openingBalance + $totalCashIn - $totalExpenses;

        // Stock levels at end of day (inventory items are limited to branch, typically small set)
        $inventoryItems = InventoryItem::where('branch_id', $branchId)->get();

        // Service Breakdown (already DB-aggregated)
        $serviceBreakdown = OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$shiftPeriodStart, $shiftPeriodEnd])
            ->groupBy('service_types.name')
            ->get();

        // Inventory Consumed (already DB-aggregated)
        $inventoryConsumed = InventoryTransaction::select('inventory_items.name', 'inventory_items.unit', \DB::raw('SUM(inventory_transactions.quantity) as total_used'))
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
            ->where('inventory_items.branch_id', $branchId)
            ->where('inventory_transactions.type', 'out')
            ->whereBetween('inventory_transactions.created_at', [$shiftPeriodStart, $shiftPeriodEnd])
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

