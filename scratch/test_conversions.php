<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\User;

use Illuminate\Support\Facades\DB;

echo "=== 360 Laundry - Order Conversion & Payment Audit Test ===\n";

DB::beginTransaction();

try {
    $admin = User::whereIn('role', ['admin', 'super_admin'])->first();
    auth()->login($admin);

    // 1. Find a dummy delivery order
    $order = Order::where('is_delivery_order', true)->whereNotNull('delivery_appointment_id')->orderBy('id', 'desc')->first();
    
    if($order) {
        echo "[Initial] Order {$order->order_number}: Total={$order->total}, Paid={$order->paid_amount}, Status={$order->payment_status}, DeliveryFee={$order->delivery_fee}\n";

        // 2. Convert to Pickup via direct admin controller
        $controller = app(\App\Http\Controllers\OrderController::class);
        $request = new \Illuminate\Http\Request();
        $request->merge(['deduct_amount' => $order->delivery_fee]);
        $controller->convertToPickup($request, $order);

        $order->refresh();
        echo "[Converted to Pickup] Total={$order->total}, Paid={$order->paid_amount}, Status={$order->payment_status}, DeliveryFee={$order->delivery_fee}\n";

        $history = OrderStatusHistory::where('order_id', $order->id)->orderBy('id', 'desc')->first();
        echo "[History Note]: {$history->note}\n\n";
    }

    // 3. Find a dummy walkin order
    $order2 = Order::where('is_delivery_order', false)->orderBy('id', 'desc')->first();

    if ($order2) {
        echo "[Initial Walkin] Order {$order2->order_number}: Total={$order2->total}, Paid={$order2->paid_amount}, Status={$order2->payment_status}, DeliveryFee={$order2->delivery_fee}\n";

        // 4. Create correction request to convert to delivery (by staff)
        $staff = User::where('role', 'branch_staff')->first();
        auth()->login($staff);

        $req = new \Illuminate\Http\Request();
        $req->merge([
            'delivery_address' => 'Test Address',
            'delivery_fee' => 200,
            'contact_phone' => '0771234567'
        ]);
        $controller->convertToDelivery($req, $order2);
        $correction = OrderCorrectionRequest::where('order_id', $order2->id)->where('status', 'pending')->first();

        echo "[Correction Requested] Type={$correction->type}, Status={$correction->status}\n";

        // 5. Approve by Admin
        auth()->login($admin);
        $correctionController = app(\App\Http\Controllers\OrderCorrectionController::class);
        $correctionController->approve(new \Illuminate\Http\Request(), $correction);

        $order2->refresh();
        echo "[Correction Approved -> Delivery] Total={$order2->total}, Paid={$order2->paid_amount}, Status={$order2->payment_status}, DeliveryFee={$order2->delivery_fee}\n";

        $history2 = OrderStatusHistory::where('order_id', $order2->id)->orderBy('id', 'desc')->first();
        echo "[History Note]: {$history2->note}\n\n";
    }
} finally {
    DB::rollBack();
}

echo "Test complete.\n";
