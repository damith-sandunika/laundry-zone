<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\User;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\PaymentTransaction;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use App\Services\CashBalanceService;
use App\Actions\Order\CreateOrderAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

function echo_header($title) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "  $title\n";
    echo str_repeat('=', 60) . "\n";
}

function assert_true($condition, $message) {
    if ($condition) {
        echo "[PASS] $message\n";
    } else {
        echo "[FAIL] $message\n";
    }
}

try {
    DB::beginTransaction();

    $branch = Branch::first();
    $admin = User::where('role', 'super_admin')->first();
    $customer = Customer::first() ?? Customer::factory()->create();
    
    Auth::login($admin);
    echo_header("Setup Complete: Logged in as Admin ({$admin->name})");

    // =========================================================================
    // Test Case 1: Convert to Delivery & Overpayment Refund Approval Workflow
    // =========================================================================
    echo_header("Test 1: Convert to Delivery & Refund Workflow");
    
    // 1. Create a dummy order
    $order1 = Order::create([
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'order_number' => 'TEST-R-' . time(),
        'status' => 'pending',
        'payment_status' => 'paid',
        'is_delivery_order' => false,
        'delivery_fee' => 0,
        'subtotal' => 1000,
        'discount' => 0,
        'total' => 1000,
        'paid_amount' => 1000,
        'created_by' => $admin->id
    ]);
    
    PaymentTransaction::create([
        'order_id' => $order1->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'status' => 'completed',
        'created_by' => $admin->id
    ]);

    // 2. Convert to Delivery (adds fee) -> Should create pending correction
    // Actually, in OrderCorrectionController, 'convert_to_delivery' is a type of correction.
    $correction1 = OrderCorrectionRequest::create([
        'order_id' => $order1->id,
        'type' => 'convert_to_delivery',
        'requested_by' => $admin->id,
        'status' => 'pending',
        'reason' => 'Customer requested delivery',
        'old_data' => ['is_delivery_order' => false, 'delivery_fee' => 0],
        'new_data' => [
            'is_delivery_order' => true, 
            'delivery_fee' => 250, 
            'address' => '123 Test St',
            'delivery_appointment' => [
                'type' => 'delivery',
                'preference' => 'home_delivery',
                'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i'),
                'cost' => 250
            ]
        ],
    ]);

    app(\App\Http\Controllers\OrderCorrectionController::class)->approve($correction1);
    
    $order1->refresh();
    assert_true($order1->is_delivery_order == true, "Order is now a delivery order");
    assert_true($order1->delivery_fee == 250, "Delivery fee is 250 (Total: {$order1->total})");
    assert_true($order1->total == 1250, "Total updated to 1250");
    assert_true($order1->payment_status == 'partial', "Payment status is partial (Paid: {$order1->paid_amount})");

    // 3. Customer decides to cancel delivery -> Convert back to pickup (deducts fee)
    $correction2 = OrderCorrectionRequest::create([
        'order_id' => $order1->id,
        'type' => 'convert_to_pickup',
        'requested_by' => $admin->id,
        'status' => 'pending',
        'reason' => 'Customer changed mind to pickup',
        'old_data' => ['is_delivery_order' => true, 'delivery_fee' => 250],
        'new_data' => ['is_delivery_order' => false, 'deduct_amount' => 250],
    ]);
    
    app(\App\Http\Controllers\OrderCorrectionController::class)->approve($correction2);
    
    $order1->refresh();
    assert_true($order1->is_delivery_order == false, "Order converted back to pickup");
    assert_true($order1->delivery_fee == 0, "Delivery fee reset to 0");
    assert_true($order1->total == 1000, "Total back to 1000");
    assert_true($order1->payment_status == 'paid', "Payment status is paid again");

    // 4. Admin decides to give a discount of 200 via items_change auto-approval
    $request = new \Illuminate\Http\Request([
        'type' => 'items_change',
        'reason' => 'Manager discount applied',
        'old_data' => ['total' => 1000],
        'new_data' => ['total' => 800, 'discount' => 200]
    ]);
    
    // Simulate manual update
    $order1->total = 800;
    $order1->discount = 200;
    $order1->save();
    
    OrderStatusHistory::create([
        'order_id' => $order1->id,
        'status' => $order1->status,
        'changed_by' => $admin->id,
        'note' => "Correction auto-applied by Admin (items_change): Manager discount applied",
    ]);
    
    $order1->refresh();
    $overpayment = $order1->paid_amount - $order1->total;
    assert_true($overpayment == 200, "Overpayment detected: $overpayment");

    // 5. Refund Overpayment Workflow
    $request = new \Illuminate\Http\Request([
        'action' => 'refund'
    ]);
    app(\App\Http\Controllers\OrderController::class)->refundOverpayment($request, $order1);
    
    $pendingRefund = OrderCorrectionRequest::where('order_id', $order1->id)->where('type', 'overpayment_handling')->where('status', 'pending')->first();
    assert_true($pendingRefund != null, "Pending overpayment refund request created");
    
    // Approve Refund
    app(\App\Http\Controllers\OrderCorrectionController::class)->approve($pendingRefund);
    
    $order1->refresh();
    assert_true($order1->paid_amount == 800, "Paid amount updated to 800 after refund");
    assert_true($order1->payment_status == 'paid', "Payment status is paid");
    
    $refundTx = PaymentTransaction::where('order_id', $order1->id)->where('amount', -200)->where('status', 'completed')->first();
    assert_true($refundTx != null, "Negative payment transaction (Refund) created");

    // Verify Logs
    $logs = OrderStatusHistory::where('order_id', $order1->id)->orderBy('id', 'asc')->get();
    echo "\nLogs for Order 1:\n";
    foreach($logs as $log) {
        if ($log->note) echo "- " . str_replace("\n", " ", $log->note) . "\n";
    }

    // =========================================================================
    // Test Case 2: Store Credit overpayment
    // =========================================================================
    echo_header("Test 2: Store Credit Overpayment Workflow");
    
    $order2 = Order::create([
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'order_number' => 'TEST-SC-' . time(),
        'status' => 'pending',
        'total' => 500,
        'paid_amount' => 700, // Overpaid by 200
        'created_by' => $admin->id
    ]);
    
    $request2 = new \Illuminate\Http\Request([
        'action' => 'keep'
    ]);
    app(\App\Http\Controllers\OrderController::class)->refundOverpayment($request2, $order2);
    
    $pendingCredit = OrderCorrectionRequest::where('order_id', $order2->id)->where('type', 'overpayment_handling')->where('status', 'pending')->first();
    app(\App\Http\Controllers\OrderCorrectionController::class)->approve($pendingCredit);
    
    $order2->refresh();
    assert_true($order2->paid_amount == 700, "Paid amount remains 700 (kept as credit)");
    
    $logs2 = OrderStatusHistory::where('order_id', $order2->id)->orderBy('id', 'desc')->first();
    echo "Latest Log: " . str_replace("\n", " ", $logs2->note) . "\n";
    assert_true(str_contains($logs2->note, 'kept as store credit'), "Log mentions store credit");
    assert_true(str_contains($logs2->note, '[Requested by:'), "Log mentions Requested by");

    DB::rollBack();
    echo_header("All tests completed. Database rolled back.");

} catch (\Exception $e) {
    DB::rollBack();
    echo_header("TEST FAILED WITH EXCEPTION");
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
