<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        
        $activeOrders = \App\Models\Order::whereNotIn('status', ['delivered', 'cancelled']);
        $pendingDeliveries = \App\Models\Order::where('status', 'done');
        $completedToday = \App\Models\Order::where('status', 'delivered')->whereDate('delivered_at', today());
        $totalCustomers = \App\Models\Customer::query();

        if (!$isSuperAdmin && $branchId) {
            $activeOrders->where('branch_id', $branchId);
            $pendingDeliveries->where('branch_id', $branchId);
            $completedToday->where('branch_id', $branchId);
            $totalCustomers->where('branch_id', $branchId);
        }

        // Active register session details
        $activeSession = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        $currentBalance = 0;
        $cashBalance = 0;
        $cardPayments = 0;
        $cardPaymentsAmount = 0;
        if ($activeSession) {
            // Calculate total sales revenue paid during shift
            $salesCollected = \App\Models\Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('paid_amount');

            // Calculate only cash payments (include credit orders paid in cash too)
            $cashCollected = \App\Models\PaymentTransaction::whereHas('order', fn($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->sum('amount');
                
            // Card payments via PaymentTransaction
            $cardTransactionsQuery = \App\Models\PaymentTransaction::whereHas('order', fn($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'card');
            $cardPayments = $cardTransactionsQuery->count();
            $cardPaymentsAmount = $cardTransactionsQuery->sum('amount');

            // Also count orders paid by cash (not via credit route) for backward compat
            $directCashOrders = \App\Models\Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->where('payment_method', 'cash')
                ->whereDoesntHave('paymentTransactions')
                ->sum('paid_amount');
            $cashCollected += $directCashOrders;

            // Calculate expenses logged during shift
            $expensesLogged = \App\Models\Expense::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');

            // Calculate total gross sales logged during shift (Orders + Packages)
            $totalOrderSales = \App\Models\Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('total');
                
            $totalPackageSales = \App\Models\PackagePayment::whereHas('customerPackage', fn($q) => $q->where('branch_id', $branchId))
                ->where('created_at', '>=', $activeSession->opened_at)
                ->sum('amount');
                
            $totalSale = $totalOrderSales + $totalPackageSales;

            $currentBalance = $activeSession->opening_cash + $cashCollected + $cardPaymentsAmount - $expensesLogged;
            $cashBalance = $activeSession->opening_cash + $cashCollected - $expensesLogged;
        } else {
            $totalSale = 0;
        }

        $stats = [
            'active_orders' => $activeOrders->count(),
            'pending_deliveries' => $pendingDeliveries->count(),
            'completed_today' => $completedToday->count(),
            'total_customers' => $totalCustomers->count(),
            'current_balance' => $currentBalance,
            'cash_balance' => $cashBalance,
            'card_payments' => $cardPayments,
            'card_payments_amount' => $cardPaymentsAmount,
            'total_sale' => $totalSale ?? 0,
        ];

        // Active orders sorted by nearest due_date first
        $recentOrders = (clone $activeOrders)->with('customer')
            ->orderByRaw('due_date IS NULL') // Put null due dates at the end
            ->orderBy('due_date', 'asc')
            ->take(8)
            ->get();

        // Low stock items
        $lowStockItems = \App\Models\InventoryItem::whereColumn('current_stock', '<=', 'min_stock');
        if ($branchId) {
            $lowStockItems->where('branch_id', $branchId);
        }
        $lowStockItems = $lowStockItems->get();

        // All inventory items for popup
        $allInventoryQuery = \App\Models\InventoryItem::query();
        if ($branchId) {
            $allInventoryQuery->where('branch_id', $branchId);
        }
        $allInventory = $allInventoryQuery->get();

        // Fetch Laundry/Branch Name
        $branchName = \App\Models\Branch::find($branchId)->name ?? 'Smart Laundry';

        // ── Chart Data ──
        // Today: hourly breakdown (last 8 hours in 2h blocks)
        $todayLabels = [];
        $todayPending = [];
        $todayDelivered = [];
        for ($h = 0; $h < 8; $h++) {
            $start = now()->startOfDay()->addHours($h * 3);
            $end   = $start->copy()->addHours(3);
            $todayLabels[]   = $start->format('H:i');
            $q = \App\Models\Order::where('branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $todayPending[]   = (clone $q)->where('status', 'received')->count();
            $todayDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        // Weekly: last 7 days
        $weeklyLabels = [];
        $weeklyPending = [];
        $weeklyDelivered = [];
        for ($d = 6; $d >= 0; $d--) {
            $day = now()->subDays($d);
            $weeklyLabels[]   = $day->format('D');
            $q = \App\Models\Order::where('branch_id', $branchId)->whereDate('created_at', $day->toDateString());
            $weeklyPending[]   = (clone $q)->whereNotIn('status', ['delivered', 'cancelled'])->count();
            $weeklyDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        // Monthly: last 4 weeks
        $monthlyLabels = [];
        $monthlyPending = [];
        $monthlyDelivered = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end   = $start->copy()->endOfWeek();
            $monthlyLabels[]   = 'Wk ' . $start->format('d/m');
            $q = \App\Models\Order::where('branch_id', $branchId)->whereBetween('created_at', [$start, $end]);
            $monthlyPending[]   = (clone $q)->whereNotIn('status', ['delivered', 'cancelled'])->count();
            $monthlyDelivered[] = (clone $q)->where('status', 'delivered')->count();
        }

        $chartData = [
            'today'   => ['labels' => $todayLabels,   'pending' => $todayPending,   'delivered' => $todayDelivered],
            'weekly'  => ['labels' => $weeklyLabels,  'pending' => $weeklyPending,  'delivered' => $weeklyDelivered],
            'monthly' => ['labels' => $monthlyLabels, 'pending' => $monthlyPending, 'delivered' => $monthlyDelivered],
        ];

        // ── Super Admin Global Stats ──
        if ($isSuperAdmin) {
            $today = now()->format('Y-m-d');
            
            // Daily Global Revenue (Orders + Packages)
            $todayOrderSales = \App\Models\Order::whereDate('created_at', $today)->sum('total');
            $todayPackageSales = \App\Models\PackagePayment::whereDate('created_at', $today)->sum('amount');
            $todayRevenue = $todayOrderSales + $todayPackageSales;

            $superAdminStats = [
                'total_revenue_today' => $todayRevenue,
                'new_orders_today' => \App\Models\Order::whereDate('created_at', $today)->count(),
                'pending_deliveries' => \App\Models\Order::where('status', 'done')->count(),
                'active_customers' => \App\Models\Customer::count(),
            ];
            
            // Chart 1: Sales by Branch (Today)
            $branches = \App\Models\Branch::all();
            $branchLabels = [];
            $branchSales = [];
            foreach($branches as $b) {
                $branchLabels[] = $b->name;
                $branchSales[] = \App\Models\Order::where('branch_id', $b->id)->whereDate('created_at', $today)->sum('total') 
                               + \App\Models\PackagePayment::whereHas('customerPackage', fn($q) => $q->where('branch_id', $b->id))->whereDate('created_at', $today)->sum('amount');
            }
            $chartBranchSales = ['labels' => $branchLabels, 'data' => $branchSales];

            // Chart 2: Orders by Status (Today)
            $statuses = ['received', 'processing', 'washing', 'ironing', 'done', 'delivered'];
            $statusLabels = ['Received', 'Processing', 'Washing', 'Ironing', 'Ready', 'Delivered'];
            $statusCounts = [];
            foreach($statuses as $s) {
                $statusCounts[] = \App\Models\Order::whereDate('created_at', $today)->where('status', $s)->count();
            }
            $chartOrderStatus = ['labels' => $statusLabels, 'data' => $statusCounts];
            
            // Get recent global activity
            $recentGlobalOrders = \App\Models\Order::with(['customer', 'branch'])
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

        $branchId = auth()->user()->branch_id ?? 1;

        // Check if there is already an open session
        $existing = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Branch is already open.');
        }

        $newSession = \App\Models\RegisterSession::create([
            'branch_id'    => $branchId,
            'user_id'      => auth()->id(),
            'opening_cash' => $request->opening_cash,
            'opened_at'    => now(),
            'status'       => 'open',
        ]);

        // ─── WhatsApp Shift Open Notification ───
        $branch = \App\Models\Branch::find($branchId);
        if ($branch && $branch->whatsapp_number) {
            $openedBy = auth()->user()->name ?? 'Staff';
            $openTime = now()->format('h:i A');
            $openDate = now()->format('d M Y');

            $template = \App\Models\WhatsappTemplate::where('type', 'shift_open')->first();
            $msg = $template ? $template->content : "*[BranchName] - Shift Opened* 🟢\n━━━━━━━━━━━━━━━━━━━━\n📅 Date: [Date]\n🕐 Time: [Time]\n👤 Opened By: [OpenedBy]\n━━━━━━━━━━━━━━━━━━━━\n💵 *Opening Balance: Rs.[OpeningBalance]*\n━━━━━━━━━━━━━━━━━━━━\n_Have a great day! - SmartLaundry System_";
            
            $msg = str_replace('[BranchName]', $branch->name ?? 'Smart Laundry', $msg);
            $msg = str_replace('[Date]', $openDate, $msg);
            $msg = str_replace('[Time]', $openTime, $msg);
            $msg = str_replace('[OpenedBy]', $openedBy, $msg);
            $msg = str_replace('[OpeningBalance]', number_format($request->opening_cash, 2), $msg);

            \App\Models\WhatsappLog::create([
                'order_id'     => null,
                'customer_id'  => null,
                'phone'        => $branch->whatsapp_number,
                'message_type' => 'shift_open',
                'message'      => $msg,
                'status'       => 'pending',
            ]);
        }

        return redirect()->back()->with('success', 'Branch shift opened successfully.');
    }

    public function closeShift(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $session = \App\Models\RegisterSession::where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'No active branch shift found.');
        }

        // Calculate closing balance
        $salesCollected = \App\Models\Order::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('paid_amount');

        $expensesLogged = \App\Models\Expense::where('branch_id', $branchId)
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $finalCash = $session->opening_cash + $salesCollected - $expensesLogged;

        $session->update([
            'closing_cash' => $finalCash,
            'closed_at' => now(),
            'status' => 'closed',
            'closed_by' => auth()->id(),
        ]);

        // ─── WhatsApp End-of-Day Summary ───
        $branch = \App\Models\Branch::find($branchId);
        if ($branch && $branch->whatsapp_number) {
            $orderCount = \App\Models\Order::where('branch_id', $branchId)
                ->where('created_at', '>=', $session->opened_at)->count();
            $deliveredCount = \App\Models\Order::where('branch_id', $branchId)
                ->where('status', 'delivered')
                ->where('created_at', '>=', $session->opened_at)->count();
            $pendingCount  = \App\Models\Order::where('branch_id', $branchId)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->where('created_at', '>=', $session->opened_at)->count();

            $openedAt    = \Carbon\Carbon::parse($session->opened_at);
            $shiftDate   = $openedAt->format('d M Y');
            $openedTime  = $openedAt->format('h:i A');
            $closedTime  = now()->format('h:i A');

            // ─── Stock Summary for WhatsApp ───
            $inventoryItems = \App\Models\InventoryItem::where('branch_id', $branchId)->get();
            $stockLines = '';
            foreach ($inventoryItems as $inv) {
                $stockLines .= "   • {$inv->name}: {$inv->current_stock} {$inv->unit} remaining\n";
            }

            // ─── Service Breakdown ───
            $serviceBreakdown = \App\Models\OrderItem::select('service_types.name', \DB::raw('SUM(order_items.quantity) as total_qty'), \DB::raw('SUM(order_items.total_price * (1 - IFNULL(orders.discount / NULLIF(orders.subtotal, 0), 0))) as total_revenue'))
                ->join('service_types', 'service_types.id', '=', 'order_items.service_type_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.branch_id', $branchId)
                ->where('orders.created_at', '>=', $session->opened_at)
                ->groupBy('service_types.name')
                ->get();
                
            $sbText = "";
            if ($serviceBreakdown->count() > 0) {
                $sbText .= "👕 *Services Sold:*\n";
                foreach ($serviceBreakdown as $sb) {
                    $sbText .= "   • {$sb->name}: {$sb->total_qty} (Rs." . number_format($sb->total_revenue, 2) . ")\n";
                }
                $sbText .= "━━━━━━━━━━━━━━━━━━━━";
            }

            // ─── Inventory Consumed ───
            $inventoryConsumed = \App\Models\InventoryTransaction::select('inventory_items.name', 'inventory_items.unit', \DB::raw('SUM(inventory_transactions.quantity) as total_used'))
                ->join('inventory_items', 'inventory_items.id', '=', 'inventory_transactions.inventory_item_id')
                ->where('inventory_items.branch_id', $branchId)
                ->where('inventory_transactions.type', 'out')
                ->where('inventory_transactions.created_at', '>=', $session->opened_at)
                ->groupBy('inventory_items.name', 'inventory_items.unit')
                ->get();

            $icText = "";
            if ($inventoryConsumed->count() > 0) {
                $icText .= "📦 *Inventory Consumed:*\n";
                foreach ($inventoryConsumed as $ic) {
                    $icText .= "   • {$ic->name}: {$ic->total_used} {$ic->unit}\n";
                }
                $icText .= "━━━━━━━━━━━━━━━━━━━━";
            }

            $srText = "";
            if ($stockLines) {
                $srText .= "📊 *Stock Remaining:*\n";
                $srText .= $stockLines;
            }

            $template = \App\Models\WhatsappTemplate::where('type', 'shift_close')->first();
            $summary = $template ? $template->content : "*[BranchName] - End of Day Report* 🔴\n━━━━━━━━━━━━━━━━━━━━\n📅 Date: [Date]\n🕐 Shift: [ShiftTime]\n━━━━━━━━━━━━━━━━━━━━\n📦 Total Orders: [TotalOrders]\n✅ Delivered: [DeliveredCount]\n⏳ Pending: [PendingCount]\n━━━━━━━━━━━━━━━━━━━━\n[ServiceBreakdown]\n💰 Sales Collected: Rs.[SalesCollected]\n📤 Expenses: Rs.[Expenses]\n🏦 Opening Balance: Rs.[OpeningBalance]\n💵 *Final Cash Count: Rs.[FinalCash]*\n[InventoryConsumed]\n[StockRemaining]\n━━━━━━━━━━━━━━━━━━━━\n_Powered by SmartLaundry System_";

            $summary = str_replace('[BranchName]', $branch->name ?? 'SmartLaundry', $summary);
            $summary = str_replace('[Date]', $shiftDate, $summary);
            $summary = str_replace('[ShiftTime]', "{$openedTime} → {$closedTime}", $summary);
            $summary = str_replace('[TotalOrders]', $orderCount, $summary);
            $summary = str_replace('[DeliveredCount]', $deliveredCount, $summary);
            $summary = str_replace('[PendingCount]', $pendingCount, $summary);
            $summary = str_replace('[ServiceBreakdown]', $sbText, $summary);
            $summary = str_replace('[SalesCollected]', number_format($salesCollected, 2), $summary);
            $summary = str_replace('[Expenses]', number_format($expensesLogged, 2), $summary);
            $summary = str_replace('[OpeningBalance]', number_format($session->opening_cash, 2), $summary);
            $summary = str_replace('[FinalCash]', number_format($finalCash, 2), $summary);
            $summary = str_replace('[InventoryConsumed]', $icText, $summary);
            $summary = str_replace('[StockRemaining]', $srText, $summary);
            
            $summary = preg_replace("/\n\s*\n/", "\n", $summary);

            \App\Models\WhatsappLog::create([
                'order_id'     => null,
                'customer_id'  => null,
                'phone'        => $branch->whatsapp_number,
                'message_type' => 'shift_close',
                'message'      => $summary,
                'status'       => 'pending',
            ]);
        }

        return redirect()->back()->with('success', 'Shift closed. Final Cash: Rs.' . number_format($finalCash, 2) . '. WhatsApp summary queued.');
    }
}
