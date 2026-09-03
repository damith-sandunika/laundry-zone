<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackagePayment;
use App\Models\PaymentTransaction;
use App\Models\RegisterSession;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Redirect riders to their own dashboard
        if ($user->role === 'rider') {
            return redirect()->route('rider.dashboard');
        }

        // Redirect workshop admins to workshop dashboard
        if ($user->role === 'workshop_admin') {
            return redirect()->route('workshop.dashboard');
        }


        $branchId = $user->branch_id;
        $isSuperAdmin = $user->role === 'super_admin';

        $activeOrders = Order::whereNotIn('status', ['delivered', 'cancelled']);
        $pendingDeliveries = Order::where('status', 'done');
        $completedToday = Order::where('status', 'delivered')->whereDate('delivered_at', today());
        $totalCustomers = Customer::query();

        if (! $isSuperAdmin && $branchId) {
            $activeOrders->where('branch_id', $branchId);
            $pendingDeliveries->where('branch_id', $branchId);
            $completedToday->where('branch_id', $branchId);
        }

        // Active register session details
        $activeSession = RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $currentBalance = 0;
        $cashBalance = 0;
        $cardPayments = 0;
        $cardPaymentsAmount = 0;
        $bankTransfers = 0;
        $bankTransfersAmount = 0;
        if ($activeSession) {
            // Calculate total sales revenue paid during shift
            $salesCollected = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('paid_amount');

            // Calculate only cash payments (include credit orders paid in cash too)
            $cashCollected = PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->sum('amount');

            // Card payments via PaymentTransaction
            $cardTransactionsQuery = PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'card');
            $cardPayments = $cardTransactionsQuery->count();
            $cardPaymentsAmount = $cardTransactionsQuery->sum('amount');

            // Bank payments via PaymentTransaction
            $bankTransactionsQuery = PaymentTransaction::whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'bank');
            $bankTransfers = $bankTransactionsQuery->count();
            $bankTransfersAmount = $bankTransactionsQuery->sum('amount');

            // Also count orders paid by cash (not via credit route) for backward compat
            $directCashOrders = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
            $cashCollected += $directCashOrders;

            // Also count orders paid by bank (not via credit route)
            $directBankOrders = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'bank')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
            $bankTransfersAmount += $directBankOrders;

            $expensesLogged = Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            // Calculate pending payments for orders created today
            $pendingPayments = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->selectRaw('SUM(total - paid_amount) as pending_total')
                ->value('pending_total') ?? 0;

            // Calculate commissionable sales and delivery fees from ACTUAL PAYMENTS
            $totalDeliveryFees = 0;
            $commissionableSales = 0;
            $grossSales = 0;

            // 1. Process payment transactions in this shift
            $shiftPayments = PaymentTransaction::with('order')
                ->whereHas('order', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->get();

            foreach ($shiftPayments as $payment) {
                if ($payment->order) {
                    $order = $payment->order;
                    $deliveryFee = $order->delivery_fee ?? 0;
                    
                    // Sum payments made BEFORE this transaction
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
                    
                    $totalDeliveryFees += $paidToDelivery;
                    $commissionableSales += $commissionable;
                    $grossSales += $payment->amount;
                }
            }

            // 2. Process backward compat direct cash/bank orders without transactions
            $directOrders = Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->whereDoesntHave('paymentTransactions')
                ->whereIn('payment_method', ['cash', 'bank'])
                ->get();

            foreach ($directOrders as $order) {
                $deliveryFee = $order->delivery_fee ?? 0;
                $paid = $order->paid_amount;
                
                $paidToDelivery = min($paid, $deliveryFee);
                $commissionable = $paid - $paidToDelivery;
                
                $totalDeliveryFees += $paidToDelivery;
                $commissionableSales += $commissionable;
                $grossSales += $paid;
            }

            // 3. Package sales (fully commissionable, no delivery fee)
            $totalPackageSales = PackagePayment::whereHas('customerPackage', fn ($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            $grossSales += $totalPackageSales;
            $commissionableSales += $totalPackageSales;

            $currentBalance = $activeSession->opening_cash + $cashCollected + $cardPaymentsAmount + $bankTransfersAmount - $expensesLogged;
            $cashBalance = $activeSession->opening_cash + $cashCollected - $expensesLogged;
            $branchProfitPercent = $activeSession->branch->branch_profit_percentage ?? 0;
            
            $branchCommission = $commissionableSales * ($branchProfitPercent / 100);
            $totalBranchRevenue = $branchCommission + $totalDeliveryFees;
            $netIncome = $totalBranchRevenue - $expensesLogged;
            $totalPaymentsCollected = $cashCollected + $cardPaymentsAmount + $bankTransfersAmount;

        } else {
            $grossSales = 0;
            $commissionableSales = 0;
            $totalDeliveryFees = 0;
            $branchCommission = 0;
            $branchProfitPercent = 0;
            $totalBranchRevenue = 0;
            $netIncome = 0;
            $totalPaymentsCollected = 0;
            $pendingPayments = 0;
        }

        $stats = [
            'active_orders' => $activeOrders->count(),
            'pending_deliveries' => $pendingDeliveries->count(),
            'completed_today' => $completedToday->count(),
            'total_customers' => $totalCustomers->count(),
            'current_balance' => $currentBalance ?? 0,
            'cash_balance' => $cashBalance ?? 0,
            'cash_collected' => $cashCollected ?? 0,
            'card_payments' => $cardPayments ?? 0,
            'card_payments_amount' => $cardPaymentsAmount ?? 0,
            'bank_transfers_count' => $bankTransfers ?? 0,
            'bank_transfers' => $bankTransfersAmount ?? 0,
            'total_payments_collected' => $totalPaymentsCollected ?? 0,
            'gross_sales' => $grossSales ?? 0,
            'commissionable_sales' => $commissionableSales ?? 0,
            'total_delivery_fees' => $totalDeliveryFees ?? 0,
            'branch_commission' => $branchCommission,
            'total_branch_revenue' => $totalBranchRevenue ?? 0,
            'net_income' => $netIncome ?? 0,
            'branch_profit_percentage' => $branchProfitPercent,
            'expenses_logged' => $expensesLogged ?? 0,
            'pending_payments' => $pendingPayments ?? 0,
        ];

        // Active orders sorted by nearest due_date first
        $recentOrders = (clone $activeOrders)->with('customer')
            ->orderByRaw('due_date IS NULL') // Put null due dates at the end
            ->orderBy('due_date', 'asc')
            ->take(8)
            ->get();

        // Low stock items
        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold');
        if ($branchId) {
            $lowStockItems->where('branch_id', $branchId);
        }
        $lowStockItems = $lowStockItems->get();

        // All inventory items for popup
        $allInventoryQuery = InventoryItem::query();
        if ($branchId) {
            $allInventoryQuery->where('branch_id', $branchId);
        }
        $allInventory = $allInventoryQuery->get();

        // Fetch Laundry/Branch Name
        $branchName = Branch::find($branchId)->name ?? 'Smart Laundry';

        // ── Chart Data ──
        // Today: hourly breakdown (last 8 hours in 2h blocks)
        $todayLabels = [];
        $todayPending = [];
        $todayDelivered = [];
        for ($h = 0; $h < 8; $h++) {
            $start = now()->startOfDay()->addHours($h * 3);
            $end = $start->copy()->addHours(3);
            $todayLabels[] = $start->format('H:i');
            $q = Order::where('branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $todayPending[] = (clone $q)->where('status', 'received')->count();
            $todayDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        // Weekly: last 7 days
        $weeklyLabels = [];
        $weeklyPending = [];
        $weeklyDelivered = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $weeklyLabels[] = $day->format('D');
            $q = Order::where('branch_id', $branchId)->whereDate('created_at', $day->toDateString());
            $weeklyPending[] = (clone $q)->whereNotIn('status', ['delivered', 'cancelled'])->count();
            $weeklyDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        // Monthly: last 4 weeks
        $monthlyLabels = [];
        $monthlyPending = [];
        $monthlyDelivered = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end = $start->copy()->endOfWeek();
            $monthlyLabels[] = 'Wk '.$start->format('d/m');
            $q = Order::where('branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $monthlyPending[] = (clone $q)->whereNotIn('status', ['delivered', 'cancelled'])->count();
            $monthlyDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        $chartData = [
            'today' => ['labels' => $todayLabels,   'pending' => $todayPending,   'delivered' => $todayDelivered],
            'weekly' => ['labels' => $weeklyLabels,  'pending' => $weeklyPending,  'delivered' => $weeklyDelivered],
            'monthly' => ['labels' => $monthlyLabels, 'pending' => $monthlyPending, 'delivered' => $monthlyDelivered],
        ];

        // ── Super Admin Global Stats ──
        if ($isSuperAdmin) {
            $today = now()->format('Y-m-d');

            // Daily Global Revenue (Orders + Packages)
            $todayOrderSales = Order::whereDate('created_at', $today)->sum('total');
            $todayPackageSales = PackagePayment::whereDate('created_at', $today)->sum('amount');
            $todayRevenue = $todayOrderSales + $todayPackageSales;

            $superAdminStats = [
                'total_revenue_today' => $todayRevenue,
                'today_present_staff' => Attendance::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
                'opening_branches' => RegisterSession::where('status', 'open')->count(),
                'new_orders_today' => Order::whereDate('created_at', $today)->count(),
            ];

            // Chart 1: Sales by Branch (Today)
            $branches = Branch::all();
            $branchLabels = [];
            $branchSales = [];
            foreach ($branches as $b) {
                $branchLabels[] = $b->name;
                $branchSales[] = Order::where('branch_id', $b->id)->whereDate('created_at', $today)->sum('total')
                               + PackagePayment::whereHas('customerPackage', fn ($q) => $q->where('branch_id', $b->id))->whereDate('created_at', $today)->sum('amount');
            }
            $chartBranchSales = ['labels' => $branchLabels, 'data' => $branchSales];

            // Chart 2: Orders by Status (Today)
            $statuses = ['received', 'processing', 'washing', 'ironing', 'done', 'delivered'];
            $statusLabels = ['Received', 'Processing', 'Washing', 'Ironing', 'Ready', 'Delivered'];
            $statusCounts = [];
            foreach ($statuses as $s) {
                $statusCounts[] = Order::whereDate('created_at', $today)->where('status', $s)->count();
            }
            $chartOrderStatus = ['labels' => $statusLabels, 'data' => $statusCounts];

            // Get recent global activity
            $recentGlobalOrders = Order::with(['customer', 'branch'])
                ->latest()
                ->take(10)
                ->get();

            return view('dashboard_superadmin', compact('superAdminStats', 'recentGlobalOrders', 'branchName', 'chartBranchSales', 'chartOrderStatus'));
        }

        return view('dashboard', compact('stats', 'recentOrders', 'lowStockItems', 'allInventory', 'activeSession', 'branchName', 'chartData'));
    }

    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $branchId = auth()->user()->branch_id ?? \App\Models\Branch::first()->id;

        // Check if there is already an open session
        $existing = RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Branch is already open.');
        }

        $newSession = RegisterSession::create([
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'opening_cash' => $request->opening_cash,
            'opened_at' => now(),
            'status' => 'open',
        ]);



        return redirect()->back()->with('success', 'Branch shift opened successfully.');
    }

    public function closeShift(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? \App\Models\Branch::first()->id;
        $session = RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return redirect()->back()->with('error', 'No active branch shift found.');
        }

        // Calculate closing balance
        $salesCollected = Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('paid_amount');

        $expensesLogged = Expense::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $finalCash = $session->opening_cash + $salesCollected - $expensesLogged;

        $session->update([
            'closing_cash' => $finalCash,
            'closed_at' => now(),
            'status' => 'closed',
            'closed_by' => auth()->id(),
        ]);



        return redirect()->back()->with('success', 'Shift closed. Final Cash: Rs.'.number_format($finalCash, 2).'.');
    }
}
