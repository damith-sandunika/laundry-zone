<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\WorkshopDispatch;
use App\Actions\Order\UpdateOrderStatusAction;
use App\Actions\Workshop\DispatchToWorkshopAction;
use App\Actions\Workshop\ReceiveWorkshopDispatchAction;
use App\Actions\Workshop\StartProcessingWorkshopAction;
use App\Actions\Workshop\CompleteWorkshopProcessingAction;
use Illuminate\Support\Facades\Gate;

// 1. Create dummy branch, customer, staff, workshop
$branch = Branch::firstOrCreate(['name' => 'Test Branch', 'type' => 'branch', 'is_active' => true]);
$workshop = Branch::firstOrCreate(['name' => 'Test Workshop', 'type' => 'workshop', 'is_active' => true]);

$staff = User::firstOrCreate(['email' => 'teststaff@laundry.com'], [
    'name' => 'Test Staff', 'role' => 'staff', 'branch_id' => $branch->id, 'password' => bcrypt('password')
]);
$workshopAdmin = User::firstOrCreate(['email' => 'testws@laundry.com'], [
    'name' => 'Test WS Admin', 'role' => 'workshop_admin', 'branch_id' => $workshop->id, 'password' => bcrypt('password')
]);

$customer = Customer::firstOrCreate(['phone' => '0771234567'], [
    'name' => 'Test Customer', 'branch_id' => $branch->id, 'is_active' => true
]);

echo "Setup complete. Testing flows...\n";

// 2. Create Order
try {
    auth()->login($staff);
    $order = Order::create([
        'order_number' => 'TST-'.time(),
        'branch_id' => $branch->id,
        'customer_id' => $customer->id,
        'created_by' => $staff->id,
        'status' => 'received',
        'subtotal' => 1000,
        'total' => 1000,
        'paid_amount' => 0,
        'payment_status' => 'unpaid',
        'payment_method' => 'cash'
    ]);
    echo "[PASS] Order Created successfully with ID: {$order->id}\n";
} catch (\Exception $e) {
    echo "[FAIL] Order Creation: " . $e->getMessage() . "\n";
}

// 3. Test Order Policy
try {
    // Should pass for branch staff
    if (Gate::allows('update', $order)) {
        echo "[PASS] Gate allows branch staff to update order\n";
    } else {
        echo "[FAIL] Gate blocks branch staff\n";
    }
} catch (\Exception $e) {
    echo "[FAIL] Gate check: " . $e->getMessage() . "\n";
}

// 4. Test Notification Job & Status Update
try {
    $action = new UpdateOrderStatusAction();
    $action->execute($order, ['status' => 'processing'], $staff->id);
    echo "[PASS] Order status updated via Action (Notification Job Dispatched)\n";
} catch (\Exception $e) {
    echo "[FAIL] Status Update Action: " . $e->getMessage() . "\n";
}

// 5. Test Workshop Dispatch Controller Logic
try {
    auth()->login($staff);
    $dispatchAction = new DispatchToWorkshopAction();
    $dispatchAction->execute($order, ['workshop_branch_id' => $workshop->id], $staff->id);
    echo "[PASS] Order Dispatched to Workshop\n";
} catch (\Exception $e) {
    echo "[FAIL] Workshop Dispatch: " . $e->getMessage() . "\n";
}

// 6. Test Workshop Operations
try {
    auth()->login($workshopAdmin);
    $dispatch = WorkshopDispatch::where('order_id', $order->id)->latest()->first();
    
    if (!$dispatch) {
        throw new \Exception("Dispatch record not found");
    }

    $receiveAction = new ReceiveWorkshopDispatchAction();
    $receiveAction->execute($dispatch, $workshopAdmin->id);
    echo "[PASS] Order Received at Workshop\n";

    $startAction = new StartProcessingWorkshopAction();
    $startAction->execute($dispatch, $workshopAdmin->id);
    echo "[PASS] Order Processing at Workshop\n";

    $completeAction = new CompleteWorkshopProcessingAction();
    $completeAction->execute($dispatch, [], $workshopAdmin->id);
    echo "[PASS] Order Completed at Workshop\n";
} catch (\Exception $e) {
    echo "[FAIL] Workshop Operations: " . $e->getMessage() . "\n";
}

// 7. Test $fillable explicitly by trying mass assignment
try {
    $testOrder = new Order();
    $testOrder->fill(['status' => 'delivered', 'not_in_fillable' => 'hacked']);
    if ($testOrder->status === 'delivered') {
        echo "[PASS] Mass assignment fillable successful\n";
    } else {
        echo "[FAIL] Fillable didn't set status\n";
    }
} catch (\Exception $e) {
    if (str_contains($e->getMessage(), 'fillable')) {
        echo "[FAIL] Fillable exception: " . $e->getMessage() . "\n";
    } else {
        echo "[FAIL] Unexpected error on fill: " . $e->getMessage() . "\n";
    }
}

echo "End of Test.\n";
