<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountantDashboardController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::all();
        $selectedBranchId = $request->get('branch_id');

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Orders base query
        $ordersQuery = Order::query();
        $expensesQuery = Expense::query();
        $paymentsQuery = PaymentTransaction::query();
        $sessionsQuery = RegisterSession::with('branch', 'user');

        if ($selectedBranchId) {
            $ordersQuery->where('branch_id', $selectedBranchId);
            $expensesQuery->where('branch_id', $selectedBranchId);
            $paymentsQuery->whereHas('order', function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            });
            $sessionsQuery->where('branch_id', $selectedBranchId);
        }

        // Summary Calculations
        $todayRevenue = (clone $ordersQuery)->whereDate('created_at', $today)->sum('total');
        $monthRevenue = (clone $ordersQuery)->where('created_at', '>=', $startOfMonth)->sum('total');

        $todayExpenses = (clone $expensesQuery)->whereDate('expense_date', $today)->sum('amount');
        $monthExpenses = (clone $expensesQuery)->where('expense_date', '>=', $startOfMonth)->sum('amount');

        // Cash Collections Today
        $todayCashCollected = (clone $paymentsQuery)
            ->whereDate('created_at', $today)
            ->where('payment_method', 'cash')
            ->sum('amount');

        // Card & Online Transfers Today
        $todayDigitalCollected = (clone $paymentsQuery)
            ->whereDate('created_at', $today)
            ->whereIn('payment_method', ['card', 'bank'])
            ->sum('amount');

        // Pending credit/unpaid orders total
        $totalPendingReceivables = (clone $ordersQuery)
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['cancelled'])
            ->sum(DB::raw('total - paid_amount'));

        // Pending corrections count
        $pendingCorrectionsCount = OrderCorrectionRequest::where('status', 'pending')
            ->when($selectedBranchId, function ($q) use ($selectedBranchId) {
                $q->whereHas('order', function ($oq) use ($selectedBranchId) {
                    $oq->where('branch_id', $selectedBranchId);
                });
            })->count();

        // Recent Active Register Sessions
        $activeSessions = (clone $sessionsQuery)
            ->where('status', 'open')
            ->latest()
            ->get();

        // Recent Expenses (Petty Cash logs)
        $recentExpenses = (clone $expensesQuery)
            ->with(['branch', 'user'])
            ->latest('expense_date')
            ->take(8)
            ->get();

        // Recent Payments
        $recentPayments = (clone $paymentsQuery)
            ->with(['order.customer', 'order.branch'])
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('accountant.dashboard', compact(
            'branches',
            'selectedBranchId',
            'todayRevenue',
            'monthRevenue',
            'todayExpenses',
            'monthExpenses',
            'todayCashCollected',
            'todayDigitalCollected',
            'totalPendingReceivables',
            'pendingCorrectionsCount',
            'activeSessions',
            'recentExpenses',
            'recentPayments'
        ));
    }
}

