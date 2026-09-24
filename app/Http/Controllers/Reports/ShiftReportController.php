<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackagePayment;
use App\Models\RegisterSession;
use Illuminate\Http\Request;

class ShiftReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $this->getActiveBranchId();
        $branch   = Branch::find($branchId);

        $session = RegisterSession::where('branch_id', $branchId)->whereNull('closed_at')->latest()->first()
            ?? RegisterSession::where('branch_id', $branchId)->latest()->first();

        $startDate       = $session ? $session->opened_at->toDateString() : now()->toDateString();
        $endDate         = now()->toDateString();
        $shiftPeriodStart = $session ? $session->opened_at : now()->startOfDay();
        $shiftPeriodEnd  = now();

        $shiftOrderQuery = Order::where('branch_id', $branchId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$shiftPeriodStart, $shiftPeriodEnd]);

        $totalSales          = (clone $shiftOrderQuery)->sum('total');
        $totalCashIn         = (clone $shiftOrderQuery)->sum('paid_amount');
        $totalDiscounts      = (clone $shiftOrderQuery)->sum('discount');
        $totalBranchProfit   = (clone $shiftOrderQuery)->sum('branch_profit');
        $totalWorkshopProfit = (clone $shiftOrderQuery)->sum('workshop_profit');
        $cashPaymentOnly     = (clone $shiftOrderQuery)->where('payment_method', 'cash')->sum('paid_amount');
        $cardPaymentOnly     = (clone $shiftOrderQuery)->where('payment_method', 'card')->sum('paid_amount');
        $bankPaymentOnly     = (clone $shiftOrderQuery)->where('payment_method', 'bank')->sum('paid_amount');

        $orders = $shiftOrderQuery->with('customer')->orderBy('created_at', 'desc')->paginate(15);

        $totalExpenses = Expense::where('branch_id', $branchId)->whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $expenses      = Expense::where('branch_id', $branchId)->whereBetween('expense_date', [$startDate, $endDate])->get();

        $packagePaymentsQ   = PackagePayment::whereHas('customerPackage', fn ($q) => $q->where('branch_id', $branchId))
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

        $inventoryItems = InventoryItem::where('branch_id', $branchId)->get();

        $serviceBreakdown = OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
            ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$shiftPeriodStart, $shiftPeriodEnd])
            ->groupBy('service_types.name')
            ->get();

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
            'startDate', 'endDate', 'inventoryItems', 'serviceBreakdown', 'inventoryConsumed',
            'totalPackageIncome', 'totalDiscounts', 'totalBranchProfit', 'totalWorkshopProfit'
        ));
    }
}
