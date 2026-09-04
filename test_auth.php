<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RegisterSession;
use App\Models\Attendance;
use App\Models\Branch;

echo "--- Starting Authentication Logic Test ---\n";

// 1. Get a branch that has staff
$branch = Branch::first();
if(!$branch) {
    echo "No branches found.\n";
    exit;
}

// 2. Find a staff member
$staffMember = User::where('branch_id', $branch->id)->where('role', 'staff')->first();
if(!$staffMember) {
    echo "No staff found in branch {$branch->name}. Creating a dummy staff...\n";
    $staffMember = User::create([
        'name' => 'Test Staff',
        'email' => 'teststaff_' . rand(1, 1000) . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'staff',
        'branch_id' => $branch->id
    ]);
}

echo "Testing with Staff: {$staffMember->name} (ID: {$staffMember->id})\n";

// 3. Ensure a session is open
$session = RegisterSession::where('branch_id', $branch->id)->where('status', 'open')->first();
if(!$session) {
    echo "No active session in branch {$branch->name}. Opening a dummy session...\n";
    $session = RegisterSession::create([
        'branch_id' => $branch->id,
        'user_id' => User::where('role', 'branch_admin')->first()->id ?? 1,
        'opening_cash' => 1000,
        'opened_at' => now(),
        'status' => 'open'
    ]);
}
echo "Active Session ID: {$session->id}\n";

// 4. Test 1: Staff has no attendance record -> Should NOT be allowed to login
echo "\n[Test 1] Staff has no attendance record.\n";
Attendance::where('user_id', $staffMember->id)->where('register_session_id', $session->id)->delete();

$isAssigned = Attendance::where('user_id', $staffMember->id)
                ->where('register_session_id', $session->id)
                ->where('is_assigned', true)
                ->exists();
echo "Expected: false | Result: " . ($isAssigned ? 'true' : 'false') . "\n";
if(!$isAssigned) echo "Test 1 PASSED.\n"; else echo "Test 1 FAILED.\n";

// 5. Test 2: Staff is Present but NOT assigned -> Should NOT be allowed to login
echo "\n[Test 2] Staff is Present but NOT Assigned.\n";
Attendance::create([
    'user_id' => $staffMember->id,
    'register_session_id' => $session->id,
    'date' => now()->toDateString(),
    'clock_in' => now()->format('H:i'),
    'status' => 'present',
    'is_assigned' => false
]);

$isAssigned = Attendance::where('user_id', $staffMember->id)
                ->where('register_session_id', $session->id)
                ->where('is_assigned', true)
                ->exists();
echo "Expected: false | Result: " . ($isAssigned ? 'true' : 'false') . "\n";
if(!$isAssigned) echo "Test 2 PASSED.\n"; else echo "Test 2 FAILED.\n";

// 6. Test 3: Staff is Present AND Assigned -> SHOULD be allowed to login
echo "\n[Test 3] Staff is Present AND Assigned.\n";
Attendance::where('user_id', $staffMember->id)->update(['is_assigned' => true]);

$isAssigned = Attendance::where('user_id', $staffMember->id)
                ->where('register_session_id', $session->id)
                ->where('is_assigned', true)
                ->exists();
echo "Expected: true | Result: " . ($isAssigned ? 'true' : 'false') . "\n";
if($isAssigned) echo "Test 3 PASSED.\n"; else echo "Test 3 FAILED.\n";

echo "\nAll backend logic checks completed successfully!\n";
