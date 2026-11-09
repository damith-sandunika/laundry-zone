<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Order;
use App\Models\PackagePayment;
use App\Models\RegisterSession;
use App\Models\Scopes\BranchScope;

class SuperAdminDashboardService
{
    public function getGlobalDashboardData(string $branchName): array
    {
        $today = now()->format('Y-m-d');

        $todayOrderSales = Order::whereDate('created_at', $today)->sum('total');
        $todayPackageSales = PackagePayment::whereDate('created_at', $today)->sum('amount');
        $todayRevenue = $todayOrderSales + $todayPackageSales;

        $superAdminStats = [
            'total_revenue_today' => $todayRevenue,
            'today_present_staff' => Attendance::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
            'opening_branches' => RegisterSession::withoutGlobalScope(BranchScope::class)->where('status', 'open')->distinct('branch_id')->count('branch_id'),
            'new_orders_today' => Order::whereDate('created_at', $today)->count(),
        ];

        $branches = Branch::all();
        
        $branchSalesMap = Order::whereDate('created_at', $today)
            ->selectRaw('branch_id, SUM(total) as total_sales')
            ->groupBy('branch_id')
            ->pluck('total_sales', 'branch_id');
            
        $packageSalesMap = PackagePayment::whereDate('package_payments.created_at', $today)
            ->join('customer_packages', 'package_payments.customer_package_id', '=', 'customer_packages.id')
            ->selectRaw('customer_packages.branch_id, SUM(package_payments.amount) as total_amount')
            ->groupBy('customer_packages.branch_id')
            ->pluck('total_amount', 'branch_id');

        $branchLabels = [];
        $branchSales = [];
        $balanceLabels = [];
        $balanceData = [];

        foreach ($branches as $b) {
            $branchLabels[] = $b->name;
            $orderSales = $branchSalesMap->get($b->id, 0);
            $pkgSales = $packageSalesMap->get($b->id, 0);
            $branchSales[] = $orderSales + $pkgSales;

            $balanceLabels[] = $b->name;
            $balanceData[] = $b->cash_balance;
        }

        $chartBranchSales = ['labels' => $branchLabels, 'data' => $branchSales];
        $chartBranchBalances = ['labels' => $balanceLabels, 'data' => $balanceData];

        $recentGlobalOrders = Order::with(['customer', 'branch'])
            ->latest()
            ->take(10)
            ->get();

        return compact('superAdminStats', 'recentGlobalOrders', 'branchName', 'chartBranchSales', 'chartBranchBalances');
    }
}

