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
use App\Services\ReportService;
use Illuminate\Http\Request;

class MainReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $branches = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        try {
            $salesData = $this->reportService->getSalesData($branchId, $startDate, $endDate);
            $salesQuery = $salesData['salesQuery'];
            $totalSales = $salesData['totalSales'];
            $totalCredit = $salesData['totalCredit'];
            $totalPending = $salesData['totalPending'];
            $deliveryRevenue = $salesData['deliveryRevenue'];
            $walkinRevenue = $salesData['walkinRevenue'];
            $maxFunc = $salesData['maxFunc'];
            $totalCollectedFromOrders = $salesData['totalCollectedFromOrders'];

            $orders = $salesQuery->with(['customer', 'items.serviceType'])->orderBy('created_at', 'desc')->paginate(15);
            
            $paginatedOrderIds = $orders->getCollection()->pluck('id')->toArray();
            $paginatedDeliveryOrderIds = DeliveryAppointment::whereIn('order_id', $paginatedOrderIds)
                ->pluck('order_id')->toArray();

            $deliveryOrders  = $orders->getCollection()->whereIn('id', $paginatedDeliveryOrderIds);
            $walkinOrders    = $orders->getCollection()->whereNotIn('id', $paginatedDeliveryOrderIds);

            $paymentData = $this->reportService->getPaymentsData($branchId, $startDate, $endDate);
            $packagePaymentsSum = $paymentData['packagePaymentsSum'];
            $totalCollected = $paymentData['totalCollected'];
            $collectedCash = $paymentData['collectedCash'];
            $collectedCard = $paymentData['collectedCard'];
            $collectedBank = $paymentData['collectedBank'];

            $profitData = $this->reportService->getProfitData($branchId, $startDate, $endDate, $packagePaymentsSum);
            $totalDeliveryFees = $profitData['totalDeliveryFees'];
            $totalBranchProfit = $profitData['totalBranchProfit'];
            $totalWorkshopProfit = $profitData['totalWorkshopProfit'];

            $services = $this->reportService->getServiceBreakdown($branchId, $startDate, $endDate);

        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId)
            $expenseQuery->where('branch_id', $branchId);
        $totalExpensesAmount = (clone $expenseQuery)->sum('amount');
        $expensesList = $expenseQuery->orderBy('expense_date', 'desc')->paginate(15);

        $inventoryQuery = InventoryTransaction::with(['inventoryItem', 'createdBy'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId)
            $inventoryQuery->whereHas('inventoryItem', fn($q) => $q->where('branch_id', $branchId));
        $inventoryLogs = $inventoryQuery->latest()->paginate(15);

        $customerPackagesQuery = CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId)
            $customerPackagesQuery->where('branch_id', $branchId);
        $customerPackages = $customerPackagesQuery->latest()->paginate(15);

        $dailyRevenue = Order::select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(total) as revenue'))
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId)
            $dailyRevenue->where('branch_id', $branchId);
        $dailyRevenue = $dailyRevenue->groupBy('date')->orderBy('date', 'asc')->get();

        $topCustomersQuery = Order::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId)
            $topCustomersQuery->where('branch_id', $branchId);
        $topCustomers = $topCustomersQuery->select('customer_id', \DB::raw('SUM(total) as total_spent'), \DB::raw('COUNT(id) as total_orders'))
            ->with('customer')->groupBy('customer_id')->orderByDesc('total_spent')->limit(5)->get();

        $depositsQuery = BankDeposit::withTrashed()->with(['branch', 'user', 'deletedByUser', 'editedByUser'])
            ->whereBetween('deposited_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId)
            $depositsQuery->where('branch_id', $branchId);
        $bankDeposits = $depositsQuery->orderBy('deposited_at', 'desc')->paginate(15);

        $topupsQuery = \DB::table('petty_cash_topups')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId)
            $topupsQuery->where('branch_id', $branchId);
        $totalTopupsAmount = (clone $topupsQuery)->sum('amount');
        $pettyCashTopups = (clone $topupsQuery)->orderBy('created_at', 'desc')->paginate(15);

        $branchPettyCashSummary = collect();
        if ($isSuperAdmin) {
            $activeBranches = Branch::where('is_active', true)->select('id', 'name', 'petty_cash_balance')->get();
            $branchIds = $activeBranches->pluck('id');

            // Batch fetch expenses per branch — single query instead of N queries
            $expensesByBranch = Expense::selectRaw('branch_id, SUM(amount) as total_expenses')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->whereIn('branch_id', $branchIds)
                ->groupBy('branch_id')
                ->pluck('total_expenses', 'branch_id');

            // Batch fetch petty cash topups per branch — single query instead of N queries
            $topupsByBranch = \DB::table('petty_cash_topups')
                ->selectRaw('branch_id, SUM(amount) as total_topups')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->whereIn('branch_id', $branchIds)
                ->groupBy('branch_id')
                ->pluck('total_topups', 'branch_id');

            $branchPettyCashSummary = $activeBranches->map(function ($branch) use ($expensesByBranch, $topupsByBranch) {
                $branch->total_expenses = $expensesByBranch[$branch->id] ?? 0;
                $branch->total_topups = $topupsByBranch[$branch->id] ?? 0;
                return $branch;
            });
        }


            return view('reports.index', compact(
                'orders',
                'totalSales',
                'totalDeliveryFees',
                'totalCollected',
                'collectedCash',
                'collectedCard',
                'collectedBank',
                'totalCredit',
                'totalPending',
                'services',
                'expensesList',
                'totalExpensesAmount',
                'inventoryLogs',
                'startDate',
                'endDate',
                'customerPackages',
                'branches',
                'branchId',
                'isSuperAdmin',
                'deliveryRevenue',
                'walkinRevenue',
                'deliveryOrders',
                'walkinOrders',
                'dailyRevenue',
                'topCustomers',
                'totalBranchProfit',
                'totalWorkshopProfit',
                'bankDeposits',
                'pettyCashTopups',
                'totalTopupsAmount',
                'branchPettyCashSummary',
                'totalCollectedFromOrders'
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MainReportController index error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = $isSuperAdmin ? $request->input('branch_id') : $user->branch_id;
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        if ($start->diffInDays($end) > 31) {
            return back()->with('error', 'PDF generation is limited to a maximum of 31 days to ensure system stability. Please select a smaller date range.');
        }

        try {
            $salesData = $this->reportService->getSalesData($branchId, $startDate, $endDate);
            $salesQuery = $salesData['salesQuery'];
            $totalSales = $salesData['totalSales'];
            $totalCredit = $salesData['totalCredit'];
            $totalPending = $salesData['totalPending'];

            $paymentData = $this->reportService->getPaymentsData($branchId, $startDate, $endDate);
            $packagePaymentsSum = $paymentData['packagePaymentsSum'];
            $totalCollected = $paymentData['totalCollected'];
            $collectedCash = $paymentData['collectedCash'];
            $collectedCard = $paymentData['collectedCard'];
            $collectedBank = $paymentData['collectedBank'];

            $profitData = $this->reportService->getProfitData($branchId, $startDate, $endDate, $packagePaymentsSum);
            $totalDeliveryFees = $profitData['totalDeliveryFees'];
            $totalBranchProfit = $profitData['totalBranchProfit'];
            $totalWorkshopProfit = $profitData['totalWorkshopProfit'];

            $services = $this->reportService->getServiceBreakdown($branchId, $startDate, $endDate);

        $salaryQuery = Expense::where('category', 'salary')->whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId)
            $salaryQuery->where('branch_id', $branchId);
        $salaries = $salaryQuery->orderBy('expense_date', 'desc')->get();
        $totalSalaries = $salaries->sum('amount');

        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId)
            $expenseQuery->where('branch_id', $branchId);
        $expenses = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        $inventoryCostQuery = InventoryTransaction::where('inventory_transactions.type', 'out')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
            ->whereBetween('inventory_transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if ($branchId)
            $inventoryCostQuery->where('inventory_items.branch_id', $branchId);
        $totalInventoryCost = $inventoryCostQuery->sum(\DB::raw('inventory_transactions.quantity * inventory_items.cost_per_unit'));

            $customerPackages = collect();
            $recentOrders = collect();
            $branch = Branch::find($branchId ?? Branch::first()->id);

            return view('reports.pdf', compact(
                'recentOrders',
                'totalSales',
                'totalDeliveryFees',
                'services',
                'salaries',
                'totalSalaries',
                'expenses',
                'totalExpenses',
                'totalInventoryCost',
                'startDate',
                'endDate',
                'branch',
                'totalCollected',
                'collectedCash',
                'collectedCard',
                'collectedBank',
                'totalCredit',
                'totalPending',
                'customerPackages',
                'packagePaymentsSum',
                'totalBranchProfit',
                'totalWorkshopProfit'
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MainReportController exportPdf error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while generating the PDF. Please try again.');
        }
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
                'amount' => $customerPackage->package->price,
                'payment_method' => $request->payment_method,
                'created_by' => auth()->id(),
            ]);
            $customerPackage->update(['payment_status' => 'paid']);
        });

        return redirect()->back()->with('success', 'Package payment approved successfully!');
    }
}
