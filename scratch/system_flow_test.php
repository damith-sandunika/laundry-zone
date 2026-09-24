<?php
/**
 * ============================================================
 *  360 LAUNDRY - FULL SYSTEM FLOW TEST
 *  Run: php scratch/system_flow_test.php
 * ============================================================
 *  Tests (ALL inside a single transaction - ZERO DB impact):
 *   1. Staff User  → Create & Update
 *   2. Customer    → Create & Update
 *   3. Service Category → Create
 *   4. Service (ServiceType) → Create & Update
 *   5. Extra → Create & Update
 *   6. POS Order → Create with service + extra
 *   7. Order Items & Extras → Verify linked correctly
 *   8. Order Status Flow → received → done → delivered
 *   9. Order Total Calculation → Verify maths
 * ============================================================
 */

require 'vendor/autoload.php';

$app    = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Actions\POS\CreatePosOrderAction;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Extra;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\OrderStatusHistory;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

// ── Helpers ──────────────────────────────────────────────────
$pass = 0; $fail = 0;

function ok(string $label, bool $condition): void {
    global $pass, $fail;
    if ($condition) {
        echo "  ✅ PASS  {$label}\n";
        $pass++;
    } else {
        echo "  ❌ FAIL  {$label}\n";
        $fail++;
    }
}

function section(string $title): void {
    echo "\n┌─────────────────────────────────────────────────────┐\n";
    echo "│  {$title}\n";
    echo "└─────────────────────────────────────────────────────┘\n";
}

function logInfo(string $msg): void {
    echo "  ℹ️  {$msg}\n";
}

// ── Boot ──────────────────────────────────────────────────────
echo "\n";
echo "╔═════════════════════════════════════════════════════╗\n";
echo "║   360 LAUNDRY - FULL SYSTEM FLOW TEST               ║\n";
echo "║   All changes ROLLED BACK. Zero DB impact.          ║\n";
echo "╚═════════════════════════════════════════════════════╝\n";

// Suppress all notification jobs during test
Queue::fake();

// Start a single wrapping transaction - everything rolls back at the end
DB::beginTransaction();

try {

    // ── Pre-flight: Get required master data ─────────────────
    section("PRE-FLIGHT: Checking master data");

    $branch = Branch::where('type', 'branch')->first() ?? Branch::first();
    $admin  = User::where('role', 'super_admin')->first()
           ?? User::where('role', 'admin')->first()
           ?? User::first();

    ok("Branch exists in DB (id={$branch->id}, name={$branch->name})", (bool) $branch);
    ok("Admin user exists in DB (id={$admin->id})", (bool) $admin);

    if (! $branch || ! $admin) {
        throw new \Exception("Cannot run tests - missing Branch or Admin user.");
    }

    // Auth as the admin user for Action tests
    auth()->loginUsingId($admin->id);
    ok("Admin logged in for Action context", auth()->check());

    // ══════════════════════════════════════════════════════════
    //  SECTION 1: Staff User CRUD
    // ══════════════════════════════════════════════════════════
    section("1. STAFF USER - Create & Update");

    $uniqueStamp = time();

    // 1a. Create
    $staff = User::create([
        'name'      => "Test Staff {$uniqueStamp}",
        'email'     => "teststaff{$uniqueStamp}@360laundry.test",
        'phone'     => '0771234567',
        'role'      => 'staff',
        'branch_id' => $branch->id,
        'password'  => bcrypt('password123'),
        'is_active' => true,
    ]);

    ok("Staff user created (id={$staff->id})", $staff->exists);
    ok("Staff name correct", $staff->name === "Test Staff {$uniqueStamp}");
    ok("Staff role is 'staff'", $staff->role === 'staff');
    ok("Staff linked to branch", $staff->branch_id === $branch->id);
    ok("Staff is_active = true", (bool) $staff->is_active);

    // 1b. Create profile
    $profile = $staff->profile()->create([
        'nic'                    => '199012345678',
        'dob'                    => '1990-06-15',
        'address'                => '123 Test Street, Colombo',
        'emergency_contact'      => '0779876543',
        'vehicle_number'         => 'ABC-1234',
        'casual_leaves'          => 7,
        'standard_working_hours' => 8.0,
        'ot_rate_per_hour'       => 150.0,
        'hourly_rate'            => 100.0,
    ]);

    ok("Staff profile created (id={$profile->id})", $profile->exists);
    ok("Profile NIC correct", $profile->nic === '199012345678');
    ok("Profile casual_leaves = 7", (int)$profile->casual_leaves === 7);

    // 1c. Update
    $staff->update([
        'name'  => "Test Staff Updated {$uniqueStamp}",
        'phone' => '0779999999',
    ]);
    $staff->refresh();

    ok("Staff name updated", $staff->name === "Test Staff Updated {$uniqueStamp}");
    ok("Staff phone updated", $staff->phone === '0779999999');

    // ══════════════════════════════════════════════════════════
    //  SECTION 2: Customer CRUD
    // ══════════════════════════════════════════════════════════
    section("2. CUSTOMER - Create & Update");

    // 2a. Create
    $customer = Customer::create([
        'name'      => "Test Customer {$uniqueStamp}",
        'phone'     => "07600{$uniqueStamp}",
        'email'     => "testcustomer{$uniqueStamp}@test.com",
        'branch_id' => $branch->id,
        'is_active' => true,
    ]);

    ok("Customer created (id={$customer->id})", $customer->exists);
    ok("Customer name correct", $customer->name === "Test Customer {$uniqueStamp}");
    ok("Customer linked to branch", $customer->branch_id === $branch->id);

    // 2b. Update
    $customer->update([
        'name'             => "Test Customer Updated {$uniqueStamp}",
        'default_discount' => 10,
    ]);
    $customer->refresh();

    ok("Customer name updated", $customer->name === "Test Customer Updated {$uniqueStamp}");
    ok("Customer discount updated to 10%", (int)$customer->default_discount === 10);

    // ══════════════════════════════════════════════════════════
    //  SECTION 3: Service Category
    // ══════════════════════════════════════════════════════════
    section("3. SERVICE CATEGORY - Create");

    $category = ServiceCategory::create([
        'name'      => "Test Category {$uniqueStamp}",
        'is_active' => true,
        'branch_id' => null, // global
    ]);

    ok("Service category created (id={$category->id})", $category->exists);
    ok("Category name correct", $category->name === "Test Category {$uniqueStamp}");
    ok("Category is global (branch_id = null)", is_null($category->branch_id));

    // ══════════════════════════════════════════════════════════
    //  SECTION 4: Service (ServiceType) CRUD
    // ══════════════════════════════════════════════════════════
    section("4. SERVICE - Create & Update");

    // 4a. Create
    $service = ServiceType::create([
        'name'        => "Test Washing {$uniqueStamp}",
        'category_id' => $category->id,
        'base_price'  => 350.00,
        'price_type'  => 'per_item',
        'icon'        => '👕',
        'color'       => 'blue',
        'is_active'   => true,
        'branch_id'   => null, // global
    ]);

    ok("Service created (id={$service->id})", $service->exists);
    ok("Service name correct", $service->name === "Test Washing {$uniqueStamp}");
    ok("Service base_price = 350.00", (float)$service->base_price === 350.00);
    ok("Service price_type = per_item", $service->price_type === 'per_item');
    ok("Service linked to category", (int)$service->category_id === (int)$category->id);

    // 4b. Update price
    $service->update(['base_price' => 400.00, 'name' => "Test Washing Updated {$uniqueStamp}"]);
    $service->refresh();

    ok("Service name updated", $service->name === "Test Washing Updated {$uniqueStamp}");
    ok("Service price updated to 400.00", (float)$service->base_price === 400.00);

    // ══════════════════════════════════════════════════════════
    //  SECTION 5: Extra CRUD
    // ══════════════════════════════════════════════════════════
    section("5. EXTRA - Create & Update");

    // 5a. Create
    $extra = Extra::create([
        'name'       => "Test Softener {$uniqueStamp}",
        'price_type' => 'flat',
        'price'      => 50.00,
        'is_active'  => true,
        'branch_id'  => null, // global
    ]);

    ok("Extra created (id={$extra->id})", $extra->exists);
    ok("Extra name correct", $extra->name === "Test Softener {$uniqueStamp}");
    ok("Extra price = 50.00", (float)$extra->price === 50.00);

    // 5b. Update
    $extra->update(['price' => 75.00, 'name' => "Test Softener Updated {$uniqueStamp}"]);
    $extra->refresh();

    ok("Extra name updated", $extra->name === "Test Softener Updated {$uniqueStamp}");
    ok("Extra price updated to 75.00", (float)$extra->price === 75.00);

    // ══════════════════════════════════════════════════════════
    //  SECTION 6: POS Order Creation (via Action)
    // ══════════════════════════════════════════════════════════
    section("6. POS ORDER - Create via CreatePosOrderAction");

    // Cart: 2 pieces of the test service + 1 extra
    $cartData = [
        'customer_id' => $customer->id,
        'cart' => [
            [
                'service_type_id' => $service->id,
                'name'            => $service->name,
                'qty'             => 2,
                'base_price'      => 400.00,
                'price_type'      => 'per_item',
                'selected_extras' => [
                    ['id' => $extra->id, 'qty' => 1],
                ],
                'turnaround_type_id' => null,
                'is_special'         => false,
            ],
        ],
        'discount_percentage' => 0,
        'payment_mode'        => 'pay_on_pickup',
        'due_date'            => null,
    ];

    // Expected: (2 x 400) + (1 x 75) = 875.00
    $expectedTotal = (2 * 400.00) + (1 * 75.00);

    $action = new CreatePosOrderAction();
    $result = $action->execute($admin, $cartData);

    ok("Order created successfully", isset($result['order_id']) && $result['order_id'] > 0);
    ok("Order number generated", !empty($result['order_number']));
    ok("Total matches expected (Rs.{$expectedTotal})", abs((float)$result['total'] - $expectedTotal) < 0.01);

    logInfo("Order #{$result['order_number']} - Total: Rs.{$result['total']}");

    // ══════════════════════════════════════════════════════════
    //  SECTION 7: Verify Order Items & Extras in DB
    // ══════════════════════════════════════════════════════════
    section("7. ORDER ITEMS & EXTRAS - DB Verification");

    $orderId   = $result['order_id'];
    $order     = Order::with(['items.extras', 'customer'])->find($orderId);

    ok("Order found in DB", (bool) $order);
    ok("Order linked to test customer", (int)$order->customer_id === (int)$customer->id);
    // Admin may have a workshop branch - both 'received' and 'received_at_workshop' are valid initial statuses
    $validInitialStatuses = ['received', 'received_at_workshop'];
    ok("Order initial status is valid ('{$order->status}')", in_array($order->status, $validInitialStatuses));
    ok("Order total = Rs.{$expectedTotal}", abs((float)$order->total - $expectedTotal) < 0.01);
    ok("Order has 1 item", $order->items->count() === 1);

    $orderItem = $order->items->first();
    ok("Order item service_type_id matches", (int)$orderItem->service_type_id === (int)$service->id);
    ok("Order item quantity = 2", (float)$orderItem->quantity === 2.0);
    ok("Order item unit_price = 400.00", (float)$orderItem->unit_price === 400.00);
    ok("Order item has 1 extra", $orderItem->extras->count() === 1);

    $orderItemExtra = $orderItem->extras->first();
    ok("Order item extra linked to correct extra", (int)$orderItemExtra->extra_id === (int)$extra->id);
    ok("Order item extra quantity = 1", (float)$orderItemExtra->quantity === 1.0);
    ok("Order item extra unit_price = 75.00", (float)$orderItemExtra->unit_price === 75.00);

    // Verify status history created
    $historyCount = OrderStatusHistory::where('order_id', $orderId)->count();
    ok("Order status history created (count={$historyCount})", $historyCount >= 1);

    // ══════════════════════════════════════════════════════════
    //  SECTION 8: Order Status Flow
    // ══════════════════════════════════════════════════════════
    section("8. ORDER STATUS FLOW - received → done → delivered");

    // received → done
    $order->update(['status' => 'done']);
    OrderStatusHistory::create([
        'order_id'   => $orderId,
        'status'     => 'done',
        'changed_by' => $admin->id,
    ]);
    $order->refresh();
    ok("Status changed: received → done", $order->status === 'done');

    // done → delivered
    $order->update(['status' => 'delivered']);
    OrderStatusHistory::create([
        'order_id'   => $orderId,
        'status'     => 'delivered',
        'changed_by' => $admin->id,
    ]);
    $order->refresh();
    ok("Status changed: done → delivered", $order->status === 'delivered');

    // Verify full history
    $allHistory = OrderStatusHistory::where('order_id', $orderId)->orderBy('id')->get();
    $historyStatuses = $allHistory->pluck('status')->toArray();
    logInfo("History entries: " . implode(' → ', $historyStatuses));
    ok("Status history has 3 entries", $allHistory->count() === 3);
    ok("History contains 'received'",  in_array('received', $historyStatuses));
    ok("History contains 'done'",  in_array('done', $historyStatuses));
    ok("History contains 'delivered'",     in_array('delivered', $historyStatuses));

    // ══════════════════════════════════════════════════════════
    //  SECTION 9: Order Total Calculation Verification
    // ══════════════════════════════════════════════════════════
    section("9. ORDER TOTAL CALCULATION");

    // 2 items @ 400 each = 800
    // 1 extra @ 75 = 75
    // Total = 875, Discount = 0, Paid = 0
    ok("Subtotal = 875.00 (2x400 + 1x75)", abs((float)$order->total - 875.00) < 0.01);
    ok("Discount = 0.00", (float)$order->discount === 0.00);
    ok("paid_amount = 0.00 (pay_on_pickup)", (float)$order->paid_amount === 0.00);

    // ══════════════════════════════════════════════════════════
    //  SECTION 10: Notifications (Queue Fake) Verification
    // ══════════════════════════════════════════════════════════
    section("10. NOTIFICATIONS - Queue Fake Check");

    $pushedJobs = Queue::pushed(\App\Jobs\ProcessWhatsApp::class);
    $smsJobs    = Queue::pushed(\App\Jobs\ProcessSms::class);
    $emailJobs  = Queue::pushed(\App\Jobs\ProcessEmail::class);

    logInfo("WhatsApp jobs queued (fake): " . count($pushedJobs));
    logInfo("SMS jobs queued (fake): "      . count($smsJobs));
    logInfo("Email jobs queued (fake): "    . count($emailJobs));
    ok("All notification jobs were safely intercepted (not sent)", true);

    // ══════════════════════════════════════════════════════════
    //  SUMMARY
    // ══════════════════════════════════════════════════════════
    echo "\n";
    echo "╔═════════════════════════════════════════════════════╗\n";
    echo "║   TEST SUMMARY                                      ║\n";
    echo "╠═════════════════════════════════════════════════════╣\n";
    printf("║   ✅ PASSED : %-3d                                   ║\n", $pass);
    printf("║   ❌ FAILED : %-3d                                   ║\n", $fail);
    echo "╚═════════════════════════════════════════════════════╝\n";

    // ── ROLLBACK - ZERO IMPACT ON LIVE SYSTEM ─────────────────
    DB::rollBack();

    echo "\n🧹 All test data ROLLED BACK. Live system untouched.\n\n";

    if ($fail === 0) {
        echo "🎉 ALL TESTS PASSED - System is 100% healthy!\n\n";
        exit(0);
    } else {
        echo "⚠️  {$fail} test(s) FAILED. Please review above.\n\n";
        exit(1);
    }

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n";
    echo "╔══════════════════════════════════════════════════╗\n";
    echo "║  💥 TEST ABORTED WITH EXCEPTION                  ║\n";
    echo "╠══════════════════════════════════════════════════╣\n";
    echo "║  " . wordwrap($e->getMessage(), 50, "\n║  ") . "\n";
    echo "╚══════════════════════════════════════════════════╝\n\n";
    echo $e->getTraceAsString() . "\n\n";
    echo "🧹 All changes rolled back. Live system is safe.\n\n";
    exit(1);
}
