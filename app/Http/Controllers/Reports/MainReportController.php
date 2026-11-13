<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\BankDeposit;
use App\Models\Branch;
use App\Models\CustomerPackage;
use App\Models\DeliveryAppointment;
use App\Models\Expense;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\PackagePayment;
use App\Services\ReportService;
use App\Services\Reports\BranchPettyCashService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainReportController extends Controller
{
    protected ReportService $reportService;
    protected BranchPettyCashService $pettyCashService;

    public function __construct(ReportService $reportService, BranchPettyCashService $pettyCashService)
    {
        $this->reportService = $reportService;
        $this->pettyCashService = $pettyCashService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->canManageFinance();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $customerType = $request->input('customer_type');
        $branches = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        try {
            $salesData = $this->reportService->getSalesData($branchId, $startDate, $endDate, $customerType);
            $orders = $salesData['salesQuery']->with(['customer', 'items.serviceType'])->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

            $paginatedOrderIds = $orders->getCollection()->pluck('id')->toArray();
            $paginatedDeliveryOrderIds = DeliveryAppointment::whereIn('order_id', $paginatedOrderIds)->pluck('order_id')->toArray();
            $deliveryOrders = $orders->getCollection()->whereIn('id', $paginatedDeliveryOrderIds);
            $walkinOrders = $orders->getCollection()->whereNotIn('id', $paginatedDeliveryOrderIds);

            $paymentData = $this->reportService->getPaymentsData($branchId, $startDate, $endDate, $customerType);
            $profitData = $this->reportService->getProfitData($branchId, $startDate, $endDate, $paymentData['packagePaymentsSum']);
            $services = $this->reportService->getServiceBreakdown($branchId, $startDate, $endDate, $customerType);

            $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
            if ($branchId) $expenseQuery->where('branch_id', $branchId);
            $totalExpensesAmount = (clone $expenseQuery)->sum('amount');
            $expensesList = $expenseQuery->orderBy('expense_date', 'desc')->paginate(15);

            $inventoryQuery = InventoryTransaction::with(['inventoryItem', 'createdBy'])->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($branchId) $inventoryQuery->whereHas('inventoryItem', fn($q) => $q->where('branch_id', $branchId));
            $inventoryLogs = $inventoryQuery->latest()->paginate(15);

            $customerPackagesQuery = CustomerPackage::with(['customer', 'package', 'services.serviceType'])->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($branchId) $customerPackagesQuery->where('branch_id', $branchId);
            $customerPackages = $customerPackagesQuery->latest()->paginate(15);

            $dailyRevenue = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($branchId) $dailyRevenue->where('branch_id', $branchId);
            $dailyRevenue = $dailyRevenue->groupBy('date')->orderBy('date', 'asc')->get();

            $topCustomersQuery = Order::where('status', '!=', 'cancelled')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($branchId) $topCustomersQuery->where('branch_id', $branchId);
            $topCustomers = $topCustomersQuery->select('customer_id', DB::raw('SUM(total) as total_spent'), DB::raw('COUNT(id) as total_orders'))->with('customer')->groupBy('customer_id')->orderByDesc('total_spent')->limit(5)->get();

            $depositsQuery = BankDeposit::withTrashed()->with(['branch', 'user', 'deletedByUser', 'editedByUser'])->whereBetween('deposited_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($branchId) $depositsQuery->where('branch_id', $branchId);
            $bankDeposits = $depositsQuery->orderBy('deposited_at', 'desc')->paginate(15);

            $topupsQuery = DB::table('petty_cash_topups')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($branchId) $topupsQuery->where('branch_id', $branchId);
            $totalTopupsAmount = (clone $topupsQuery)->sum('amount');
            $pettyCashTopups = (clone $topupsQuery)->orderBy('created_at', 'desc')->paginate(15);

            $branchPettyCashSummary = $isSuperAdmin ? $this->pettyCashService->getSummary(Branch::where('is_active', true)->select('id', 'name', 'petty_cash_balance')->get(), $startDate, $endDate) : collect();

            return view('reports.index', array_merge([
                'orders' => $orders, 'deliveryOrders' => $deliveryOrders, 'walkinOrders' => $walkinOrders,
                'services' => $services, 'expensesList' => $expensesList, 'totalExpensesAmount' => $totalExpensesAmount,
                'inventoryLogs' => $inventoryLogs, 'customerPackages' => $customerPackages, 'branches' => $branches,
                'branchId' => $branchId, 'isSuperAdmin' => $isSuperAdmin, 'startDate' => $startDate, 'endDate' => $endDate,
                'dailyRevenue' => $dailyRevenue, 'topCustomers' => $topCustomers, 'bankDeposits' => $bankDeposits,
                'pettyCashTopups' => $pettyCashTopups, 'totalTopupsAmount' => $totalTopupsAmount, 'branchPettyCashSummary' => $branchPettyCashSummary,
            ], $salesData, $paymentData, $profitData));
        } catch (\Exception $e) {
            Log::error('MainReportController index error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while generating the report.');
        }
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->canManageFinance();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) > 31) {
            return back()->with('error', 'PDF generation is limited to a maximum of 31 days.');
        }

        try {
            $salesData = $this->reportService->getSalesData($branchId, $startDate, $endDate);
            $paymentData = $this->reportService->getPaymentsData($branchId, $startDate, $endDate);
            $profitData = $this->reportService->getProfitData($branchId, $startDate, $endDate, $paymentData['packagePaymentsSum']);
            $services = $this->reportService->getServiceBreakdown($branchId, $startDate, $endDate);

            $salaryQuery = Expense::where('category', 'salary')->whereBetween('expense_date', [$startDate, $endDate]);
            if ($branchId) $salaryQuery->where('branch_id', $branchId);
            $salaries = $salaryQuery->orderBy('expense_date', 'desc')->get();

            $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
            if ($branchId) $expenseQuery->where('branch_id', $branchId);
            $expenses = $expenseQuery->get();

            $inventoryCostQuery = InventoryTransaction::where('inventory_transactions.type', 'out')
                ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
                ->whereBetween('inventory_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            if ($branchId) $inventoryCostQuery->where('inventory_items.branch_id', $branchId);
            $totalInventoryCost = $inventoryCostQuery->sum(DB::raw('inventory_transactions.quantity * inventory_items.cost_per_unit'));

            return view('reports.pdf', array_merge([
                'recentOrders' => collect(), 'services' => $services, 'salaries' => $salaries,
                'totalSalaries' => $salaries->sum('amount'), 'expenses' => $expenses, 'totalExpenses' => $expenses->sum('amount'),
                'totalInventoryCost' => $totalInventoryCost, 'startDate' => $startDate, 'endDate' => $endDate,
                'branch' => Branch::find($branchId ?? Branch::first()->id), 'customerPackages' => collect(),
            ], $salesData, $paymentData, $profitData));
        } catch (\Exception $e) {
            Log::error('MainReportController exportPdf error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while generating PDF.');
        }
    }

    public function approvePackagePayment(Request $request, CustomerPackage $customerPackage)
    {
        $request->validate(['payment_method' => 'required|in:cash,card,bank']);
        if ($customerPackage->payment_status === 'paid') return redirect()->back()->with('error', 'Package already paid.');

        DB::transaction(function () use ($customerPackage, $request) {
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
