<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
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

class MainReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());
        $branches  = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        $salesQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $salesQuery->where('branch_id', $branchId);

        $maxFunc = \DB::getDriverName() === 'sqlite'
            ? 'MAX(0, total - paid_amount)'
            : 'GREATEST(0, total - paid_amount)';

        $totalSales   = (clone $salesQuery)->sum('total');
        $totalCredit  = (clone $salesQuery)->where('payment_method', 'credit')->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;
        $totalPending = (clone $salesQuery)->whereNotIn('payment_method', ['credit'])->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;

        $deliveryOrderIds = DeliveryAppointment::whereNotNull('order_id')
            ->whereHas('order', function ($q) use ($startDate, $endDate, $branchId) {
                $q->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                if ($branchId) $q->where('branch_id', $branchId);
            })->pluck('order_id')->toArray();

        $deliveryRevenue = (clone $salesQuery)->whereIn('id', $deliveryOrderIds)->sum('total');
        $walkinRevenue   = (clone $salesQuery)->whereNotIn('id', $deliveryOrderIds)->sum('total');
        $orders = $salesQuery->with(['customer', 'items.serviceType'])->orderBy('created_at', 'desc')->paginate(15);
        $deliveryOrders  = $orders->getCollection()->whereIn('id', $deliveryOrderIds);
        $walkinOrders    = $orders->getCollection()->whereNotIn('id', $deliveryOrderIds);

        $paymentsQuery = PaymentTransaction::with(['order', 'order.branch'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', fn ($q) => $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId));
        }

        $packagePaymentsQuery = PackagePayment::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', fn ($q) => $q->where('branch_id', $branchId));
        }
        $packagePaymentsSum  = (clone $packagePaymentsQuery)->sum('amount');
        $packagePaymentsCash = (clone $packagePaymentsQuery)->where('payment_method', 'cash')->sum('amount');
        $packagePaymentsCard = (clone $packagePaymentsQuery)->where('payment_method', 'card')->sum('amount');
        $packagePaymentsBank = (clone $packagePaymentsQuery)->where('payment_method', 'bank')->sum('amount');

        $totalCollected = (clone $paymentsQuery)->sum('amount') + $packagePaymentsSum;
        $collectedCash  = (clone $paymentsQuery)->where('payment_method', 'cash')->sum('amount') + $packagePaymentsCash;
        $collectedCard  = (clone $paymentsQuery)->where('payment_method', 'card')->sum('amount') + $packagePaymentsCard;
        $collectedBank  = (clone $paymentsQuery)->where('payment_method', 'bank')->sum('amount') + $packagePaymentsBank;

        $actualTotalDeliveryFees   = 0;
        $actualTotalBranchProfit   = 0;
        $actualTotalWorkshopProfit = 0;
        $paymentsQuery->chunkById(500, function ($chunk) use (&$actualTotalDeliveryFees, &$actualTotalBranchProfit, &$actualTotalWorkshopProfit) {
            $orderIds = $chunk->pluck('order_id')->filter()->unique()->toArray();
            if (empty($orderIds)) return;
            $allTransactions = PaymentTransaction::whereIn('order_id', $orderIds)->select('id', 'order_id', 'amount')->get()->groupBy('order_id');
            foreach ($chunk as $payment) {
                if (!$payment->order) continue;
                $order       = $payment->order;
                $deliveryFee = $order->delivery_fee ?? 0;
                $paidBefore  = $allTransactions->get($order->id, collect())->where('id', '<', $payment->id)->sum('amount');
                if ($paidBefore >= $deliveryFee) {
                    $paidToDelivery = 0; $commissionable = $payment->amount;
                } else {
                    $paidToDelivery = min($payment->amount, $deliveryFee - $paidBefore);
                    $commissionable = $payment->amount - $paidToDelivery;
                }
                $actualTotalDeliveryFees += $paidToDelivery;
                if ($commissionable > 0) {
                    $branchProfitPercent = $order->branch->branch_profit_percentage ?? 0;
                    if ($order->branch_id == $order->workshop_id || !$order->workshop_id) {
                        $actualTotalWorkshopProfit += $commissionable;
                    } else {
                        $actualTotalBranchProfit   += $commissionable * ($branchProfitPercent / 100);
                        $actualTotalWorkshopProfit += $commissionable * (1 - $branchProfitPercent / 100);
                    }
                }
            }
        });
        $actualTotalBranchProfit += $packagePaymentsSum;
        $totalBranchProfit   = $actualTotalBranchProfit;
        $totalWorkshopProfit = $actualTotalWorkshopProfit;
        $totalDeliveryFees   = $actualTotalDeliveryFees;

        $creditOrdersQuery = Order::with(['customer', 'branch'])
            ->whereColumn('paid_amount', '<', 'total')
            ->where('payment_method', 'credit');
        if ($branchId) $creditOrdersQuery->where('branch_id', $branchId);
        $creditOrders = $creditOrdersQuery->orderBy('created_at', 'desc')->paginate(15, ['*'], 'credit_page');

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
        if ($branchId) $serviceBreakdown->where('orders.branch_id', $branchId);
        $services = $serviceBreakdown->groupBy('service_types.name')->get();

        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) $expenseQuery->where('branch_id', $branchId);
        $totalExpensesAmount = (clone $expenseQuery)->sum('amount');
        $expensesList = $expenseQuery->orderBy('expense_date', 'desc')->paginate(15);

        $inventoryQuery = InventoryTransaction::with(['inventoryItem', 'createdBy'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $inventoryQuery->whereHas('inventoryItem', fn ($q) => $q->where('branch_id', $branchId));
        $inventoryLogs = $inventoryQuery->latest()->paginate(15);

        $customerPackagesQuery = CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $customerPackagesQuery->where('branch_id', $branchId);
        $customerPackages = $customerPackagesQuery->latest()->paginate(15);

        $dailyRevenue = Order::select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(total) as revenue'))
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $dailyRevenue->where('branch_id', $branchId);
        $dailyRevenue = $dailyRevenue->groupBy('date')->orderBy('date', 'asc')->get();

        $topCustomersQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $topCustomersQuery->where('branch_id', $branchId);
        $topCustomers = $topCustomersQuery->select('customer_id', \DB::raw('SUM(total) as total_spent'), \DB::raw('COUNT(id) as total_orders'))
            ->with('customer')->groupBy('customer_id')->orderByDesc('total_spent')->limit(5)->get();

        $depositsQuery = BankDeposit::with(['branch', 'user'])
            ->whereBetween('deposited_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $depositsQuery->where('branch_id', $branchId);
        $bankDeposits = $depositsQuery->orderBy('deposited_at', 'desc')->paginate(15);

        $topupsQuery = \DB::table('petty_cash_topups')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $topupsQuery->where('branch_id', $branchId);
        $totalTopupsAmount = (clone $topupsQuery)->sum('amount');
        $pettyCashTopups   = (clone $topupsQuery)->orderBy('created_at', 'desc')->paginate(15);

        $branchPettyCashSummary = collect();
        if ($isSuperAdmin) {
            $branchPettyCashSummary = Branch::where('is_active', true)->select('id', 'name', 'petty_cash_balance')->get()
                ->map(function ($branch) use ($startDate, $endDate) {
                    $branch->total_expenses = Expense::where('branch_id', $branch->id)->whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
                    $branch->total_topups   = \DB::table('petty_cash_topups')->where('branch_id', $branch->id)->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])->sum('amount');
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
        $endDate   = $request->input('end_date', now()->toDateString());

        $salesQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $salesQuery->where('branch_id', $branchId);
        $totalSales = (clone $salesQuery)->sum('total');

        $maxFunc = \DB::getDriverName() === 'sqlite'
            ? 'MAX(0, total - paid_amount)'
            : 'GREATEST(0, total - paid_amount)';

        $paymentsQuery = PaymentTransaction::with(['order', 'order.branch'])
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $paymentsQuery->whereHas('order', fn ($q) => $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId));
        }
        $packagePaymentsQuery = PackagePayment::whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) {
            $packagePaymentsQuery->whereHas('customerPackage', fn ($q) => $q->where('branch_id', $branchId));
        }
        $packagePaymentsSum  = (clone $packagePaymentsQuery)->sum('amount');
        $packagePaymentsCash = (clone $packagePaymentsQuery)->where('payment_method', 'cash')->sum('amount');
        $packagePaymentsCard = (clone $packagePaymentsQuery)->where('payment_method', 'card')->sum('amount');
        $packagePaymentsBank = (clone $packagePaymentsQuery)->where('payment_method', 'bank')->sum('amount');

        $totalCollected = (clone $paymentsQuery)->sum('amount') + $packagePaymentsSum;
        $collectedCash  = (clone $paymentsQuery)->where('payment_method', 'cash')->sum('amount') + $packagePaymentsCash;
        $collectedCard  = (clone $paymentsQuery)->where('payment_method', 'card')->sum('amount') + $packagePaymentsCard;
        $collectedBank  = (clone $paymentsQuery)->where('payment_method', 'bank')->sum('amount') + $packagePaymentsBank;

        $actualTotalDeliveryFees = 0; $actualTotalBranchProfit = 0; $actualTotalWorkshopProfit = 0;
        $paymentsQuery->chunkById(500, function ($chunk) use (&$actualTotalDeliveryFees, &$actualTotalBranchProfit, &$actualTotalWorkshopProfit) {
            $orderIds = $chunk->pluck('order_id')->filter()->unique()->toArray();
            if (empty($orderIds)) return;
            $allTransactions = PaymentTransaction::whereIn('order_id', $orderIds)->select('id', 'order_id', 'amount')->get()->groupBy('order_id');
            foreach ($chunk as $payment) {
                if (!$payment->order) continue;
                $order = $payment->order; $deliveryFee = $order->delivery_fee ?? 0;
                $paidBefore = $allTransactions->get($order->id, collect())->where('id', '<', $payment->id)->sum('amount');
                $paidToDelivery = $paidBefore >= $deliveryFee ? 0 : min($payment->amount, $deliveryFee - $paidBefore);
                $commissionable = $payment->amount - $paidToDelivery;
                $actualTotalDeliveryFees += $paidToDelivery;
                if ($commissionable > 0) {
                    $pct = $order->branch->branch_profit_percentage ?? 0;
                    if ($order->branch_id == $order->workshop_id || !$order->workshop_id) {
                        $actualTotalWorkshopProfit += $commissionable;
                    } else {
                        $actualTotalBranchProfit   += $commissionable * ($pct / 100);
                        $actualTotalWorkshopProfit += $commissionable * (1 - $pct / 100);
                    }
                }
            }
        });
        $actualTotalBranchProfit += $packagePaymentsSum;
        $totalBranchProfit   = $actualTotalBranchProfit;
        $totalWorkshopProfit = $actualTotalWorkshopProfit;
        $totalDeliveryFees   = $actualTotalDeliveryFees;

        $serviceBreakdown = OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $serviceBreakdown->where('orders.branch_id', $branchId);
        $services = $serviceBreakdown->groupBy('service_types.name')->get();

        $salaryQuery = Expense::where('category', 'salary')->whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) $salaryQuery->where('branch_id', $branchId);
        $salaries      = $salaryQuery->orderBy('expense_date', 'desc')->get();
        $totalSalaries = $salaries->sum('amount');

        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) $expenseQuery->where('branch_id', $branchId);
        $expenses      = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        $inventoryCostQuery = InventoryTransaction::where('inventory_transactions.type', 'out')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
            ->whereBetween('inventory_transactions.created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        if ($branchId) $inventoryCostQuery->where('inventory_items.branch_id', $branchId);
        $totalInventoryCost = $inventoryCostQuery->sum(\DB::raw('inventory_transactions.quantity * inventory_items.cost_per_unit'));

        $totalCredit   = (clone $salesQuery)->where('payment_method', 'credit')->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;
        $totalPending  = (clone $salesQuery)->whereNotIn('payment_method', ['credit'])->selectRaw("SUM({$maxFunc})")->value(\DB::raw("SUM({$maxFunc})")) ?? 0;
        $customerPackages = collect();
        $recentOrders     = collect();
        $branch = Branch::find($branchId ?? Branch::first()->id);

        return view('reports.pdf', compact(
            'recentOrders', 'totalSales', 'totalDeliveryFees', 'services', 'salaries', 'totalSalaries',
            'expenses', 'totalExpenses', 'totalInventoryCost', 'startDate', 'endDate', 'branch',
            'totalCollected', 'collectedCash', 'collectedCard', 'collectedBank',
            'totalCredit', 'totalPending', 'customerPackages', 'totalBranchProfit', 'totalWorkshopProfit'
        ));
    }

    public function approvePackagePayment(Request $request, CustomerPackage $customerPackage)
    {
        $request->validate(['payment_method' => 'required|in:cash,card,bank']);

        if ($customerPackage->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Package is already paid.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($customerPackage, $request) {
            PackagePayment::create([
                'customer_package_id' => $customerPackage->id,
                'amount'              => $customerPackage->package->price,
                'payment_method'      => $request->payment_method,
                'created_by'          => auth()->id(),
            ]);
            $customerPackage->update(['payment_status' => 'paid']);
        });

        return redirect()->back()->with('success', 'Package payment approved successfully!');
    }
}
