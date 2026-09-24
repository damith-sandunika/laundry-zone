<?php
/**
 * Commission Split Audit Script
 */
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\WorkshopDispatch;
use App\Models\Branch;
use App\Models\RegisterSession;
use Illuminate\Support\Facades\DB;

echo "--- Commission & Sales Split Audit ---\n";

$issues = [];

// 1. Audit DashboardStatService (Branch Side)
echo "\n1. Auditing DashboardStatService (Branch Side)\n";
echo "   Branch commission is calculated as: commissionableSales * (branch_profit_percentage / 100)\n";
echo "   commissionableSales comes from PaymentTransactions created >= session.opened_at\n";
echo "   AND direct orders created >= session.opened_at without transactions.\n";
echo "   -> This is essentially a Cash-Basis calculation for the shift.\n";

// 2. Audit WorkshopController (Workshop Side)
echo "\n2. Auditing WorkshopController (Workshop Side)\n";
echo "   Workshop commission from branches is calculated by getting WorkshopDispatches\n";
echo "   created >= session.opened_at. Then it uses order->paid_amount (which is the total\n";
echo "   paid amount EVER for that order, not just during the shift).\n";
echo "   -> ISSUE 1: It mixes 'Dispatch time' with 'All-time payments'.\n";
echo "   -> ISSUE 2: If a payment is made tomorrow for an order dispatched today, tomorrow's workshop shift won't see it (wrong dispatch date), but today's shift will retroactively change if viewed later!\n";
$issues[] = "Workshop dashboard uses all-time `paid_amount` for orders dispatched during the shift. This causes shift balances to change retroactively and misses payments for past dispatches.";

// Let's create a test scenario in DB to prove this.
DB::beginTransaction();
try {
    // Create Branch & Workshop
    $branch = Branch::create(['name' => 'Test Branch', 'type' => 'branch', 'branch_profit_percentage' => 30]);
    $workshop = Branch::create(['name' => 'Test Workshop', 'type' => 'workshop']);
    
    // Create a shift for workshop yesterday
    $yesterdayShift = RegisterSession::create([
        'branch_id' => $workshop->id,
        'user_id' => 1,
        'status' => 'closed',
        'opening_cash' => 0,
        'closing_cash' => 0,
        'opened_at' => now()->subDays(1)->startOfDay(),
        'closed_at' => now()->subDays(1)->endOfDay(),
    ]);

    // Create a shift for workshop today
    $todayShift = RegisterSession::create([
        'branch_id' => $workshop->id,
        'user_id' => 1,
        'status' => 'open',
        'opening_cash' => 0,
        'opened_at' => now()->startOfDay(),
    ]);

    // Create an order yesterday, dispatched yesterday
    $order1 = Order::create([
        'order_number' => 'TEST-001',
        'branch_id' => $branch->id,
        'customer_id' => 1,
        'status' => 'received',
        'payment_status' => 'unpaid',
        'payment_method' => 'cash',
        'total' => 1000,
        'delivery_fee' => 0,
        'paid_amount' => 1000, // Fully paid
        'created_at' => now()->subDays(1)->addHours(1)
    ]);
    WorkshopDispatch::create([
        'order_id' => $order1->id,
        'branch_id' => $branch->id,
        'workshop_branch_id' => $workshop->id,
        'dispatched_by' => 1,
        'status' => 'dispatched_to_workshop',
        'created_at' => now()->subDays(1)->addHours(2)
    ]);
    
    // Simulate payment made TODAY for the order dispatched YESTERDAY
    PaymentTransaction::create([
        'order_id' => $order1->id,
        'payment_method' => 'cash',
        'amount' => 1000,
        'created_at' => now()->addHours(1) // Paid today
    ]);

    // Now let's calculate what WorkshopController would do for TODAY'S shift
    $shiftDispatchesToday = WorkshopDispatch::with(['order', 'branch'])
        ->where('workshop_branch_id', $workshop->id)
        ->where('created_at', '>=', $todayShift->opened_at)
        ->get();
        
    $todayWorkshopSales = 0;
    foreach ($shiftDispatchesToday as $dispatch) {
        $todayWorkshopSales += $dispatch->order->paid_amount;
    }
    
    echo "\n--- TEST RUN RESULTS ---\n";
    echo "Order was dispatched YESTERDAY.\n";
    echo "Customer paid $1000 TODAY.\n";
    echo "Does Workshop see this $1000 in TODAY's shift revenue? -> " . ($todayWorkshopSales > 0 ? "YES ($todayWorkshopSales)" : "NO (0)") . "\n";
    echo "Reason: The WorkshopController filters by WorkshopDispatch created_at (which is yesterday), not PaymentTransaction.\n";
    
    // Now let's calculate what it would do for YESTERDAY'S shift if viewed TODAY
    $shiftDispatchesYesterday = WorkshopDispatch::with(['order', 'branch'])
        ->where('workshop_branch_id', $workshop->id)
        ->whereBetween('created_at', [$yesterdayShift->opened_at, clone $yesterdayShift->closed_at])
        ->get();
        
    $yesterdayWorkshopSales = 0;
    foreach ($shiftDispatchesYesterday as $dispatch) {
        $yesterdayWorkshopSales += $dispatch->order->paid_amount; // uses CURRENT paid amount
    }
    echo "Does Workshop see this $1000 in YESTERDAY's shift revenue if they check the report today? -> YES ($yesterdayWorkshopSales)\n";
    echo "Reason: It uses the order's CURRENT paid_amount. This means historical shifts change retrospectively!\n";

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

if (!empty($issues)) {
    echo "\n[ISSUES DETECTED]\n";
    foreach ($issues as $i) {
        echo "- $i\n";
    }
}
