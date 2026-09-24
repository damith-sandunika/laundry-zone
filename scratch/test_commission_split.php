<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\WorkshopDispatch;
use App\Models\Branch;
use App\Models\RegisterSession;
use App\Models\User;
use App\Services\DashboardStatService;
use Illuminate\Support\Facades\DB;

echo "========================================================\n";
echo "      COMMISSION & SALES SPLIT TEST (ACCRUAL BASIS)     \n";
echo "========================================================\n\n";

DB::beginTransaction();
try {
    $admin = User::first();

    // Create Branch (30% profit) and Workshop
    $branch = Branch::create(['name' => 'TEST-Branch', 'type' => 'branch', 'branch_profit_percentage' => 30]);
    $workshop = Branch::create(['name' => 'TEST-Workshop', 'type' => 'workshop']);

    // Open shifts today
    $branchShift = RegisterSession::create([
        'branch_id'    => $branch->id,
        'user_id'      => $admin->id,
        'status'       => 'open',
        'opening_cash' => 1000,
        'opened_at'    => now()->startOfDay(),
    ]);
    $workshopShift = RegisterSession::create([
        'branch_id'    => $workshop->id,
        'user_id'      => $admin->id,
        'status'       => 'open',
        'opening_cash' => 500,
        'opened_at'    => now()->startOfDay(),
    ]);

    // ── Step 1: Place order ──────────────────────────────────
    echo "--- 1. Order Placed at Branch (UNPAID) ---\n";
    $order = Order::create([
        'order_number'   => 'TST-' . time(),
        'branch_id'      => $branch->id,
        'customer_id'    => 1,
        'created_by'     => $admin->id,
        'status'         => 'received',
        'payment_status' => 'unpaid',
        'payment_method' => 'cash',
        'total'          => 5000,
        'delivery_fee'   => 500,
        'paid_amount'    => 0,
        'created_at'     => now()->addHours(1),
    ]);
    echo "  Order Total: Rs.5000  |  Delivery Fee: Rs.500  |  Commissionable: Rs.4500\n";

    // ── Step 2: Dispatch to workshop ─────────────────────────
    echo "\n--- 2. Dispatch to Workshop ---\n";
    WorkshopDispatch::create([
        'order_id'          => $order->id,
        'branch_id'         => $branch->id,
        'workshop_branch_id'=> $workshop->id,
        'dispatched_by'     => $admin->id,
        'status'            => 'dispatched_to_workshop',
        'created_at'        => now()->addHours(2),
    ]);

    // ── Branch Shift Report (before payment) ─────────────────
    $svc    = app(DashboardStatService::class);
    $stats1 = $svc->getShiftMetrics($branchShift->fresh()->load('branch'), $branch->id);

    echo "\n[BRANCH SHIFT - BEFORE PAYMENT]\n";
    printf("  Gross Sales          expected=5000    got=%s  %s\n",  $stats1['gross_sales'],          $stats1['gross_sales']         == 5000  ? '✅' : '❌');
    printf("  Commissionable Sales expected=4500    got=%s  %s\n",  $stats1['commissionable_sales'],  $stats1['commissionable_sales'] == 4500  ? '✅' : '❌');
    printf("  Branch Commission    expected=1350    got=%s  %s\n",  $stats1['branch_commission'],     $stats1['branch_commission']   == 1350  ? '✅' : '❌');
    printf("  Pending Payments     expected=5000    got=%s  %s\n",  $stats1['pending_payments'],      $stats1['pending_payments']    == 5000  ? '✅' : '❌');
    printf("  Cash Collected       expected=0       got=%s  %s\n",  $stats1['cash_collected'],        $stats1['cash_collected']      == 0     ? '✅' : '❌');
    printf("  Cash Balance         expected=1000    got=%s  %s\n",  $stats1['cash_balance'],          $stats1['cash_balance']        == 1000  ? '✅' : '❌');

    // ── Step 3: Customer pays Rs.2500 partial ────────────────
    echo "\n--- 3. Customer Pays Rs.2500 (Partial) ---\n";
    $order->update(['paid_amount' => 2500, 'payment_status' => 'partial']);
    PaymentTransaction::create([
        'order_id'       => $order->id,
        'payment_method' => 'cash',
        'amount'         => 2500,
        'collected_by'   => $admin->id,
        'created_at'     => now()->addHours(3),
    ]);

    $stats2 = $svc->getShiftMetrics($branchShift->fresh()->load('branch'), $branch->id);

    echo "\n[BRANCH SHIFT - AFTER PAYMENT]\n";
    printf("  Gross Sales          expected=5000    got=%s  %s\n",  $stats2['gross_sales'],           $stats2['gross_sales']          == 5000 ? '✅' : '❌');
    printf("  Branch Commission    expected=1350    got=%s  %s\n",  $stats2['branch_commission'],      $stats2['branch_commission']    == 1350 ? '✅' : '❌');
    printf("  Pending Payments     expected=2500    got=%s  %s\n",  $stats2['pending_payments'],       $stats2['pending_payments']     == 2500 ? '✅' : '❌');
    printf("  Cash Collected       expected=2500    got=%s  %s\n",  $stats2['cash_collected'],         $stats2['cash_collected']       == 2500 ? '✅' : '❌');
    printf("  Cash Balance         expected=3500    got=%s  %s\n",  $stats2['cash_balance'],           $stats2['cash_balance']         == 3500 ? '✅' : '❌');

    // ── Workshop Shift Report (replicate WorkshopController logic) ─
    $dispatches = WorkshopDispatch::with(['order', 'branch'])
        ->where('workshop_branch_id', $workshop->id)
        ->where('created_at', '>=', $workshopShift->opened_at)
        ->get();

    $wGross = 0; $branchCommsGiven = 0; $wPending = 0;
    foreach ($dispatches as $d) {
        $ord   = $d->order;
        $total = $ord->total;
        $dfee  = $ord->delivery_fee ?? 0;
        $paid  = $ord->paid_amount;
        $commissionable = max(0, $total - $dfee);
        $wGross          += $commissionable;
        $bComm            = $commissionable * ($d->branch->branch_profit_percentage / 100);
        $branchCommsGiven += $bComm;
        $wPending         += max(0, $total - $paid);
    }
    $wNet = $wGross - $branchCommsGiven;

    echo "\n[WORKSHOP SHIFT REPORT]\n";
    printf("  Gross Sales (commissionable)  expected=4500   got=%s  %s\n", $wGross,           $wGross           == 4500 ? '✅' : '❌');
    printf("  Branch Commissions Given      expected=1350   got=%s  %s\n", $branchCommsGiven, $branchCommsGiven == 1350 ? '✅' : '❌');
    printf("  Net Workshop Revenue          expected=3150   got=%s  %s\n", $wNet,             $wNet             == 3150 ? '✅' : '❌');
    printf("  Branch Pending Payments       expected=2500   got=%s  %s\n", $wPending,         $wPending         == 2500 ? '✅' : '❌');

    DB::rollBack();
    echo "\n✅  All done. DB rolled back — no real data changed.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌  Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
