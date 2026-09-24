<?php
// Let's create a route that simply outputs the length of the orders page when authenticated as branch admin.
$script = <<<'EOD'
<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('email', 'admin@360laundry.lk')->first(); // Assuming super admin
Auth::login($user);

ob_start();
app()->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    Illuminate\Http\Request::create('/orders', 'GET')
)->send();
$content = ob_get_clean();

file_put_contents(__DIR__ . '/test_orders_auth.html', $content);
echo "Lines: " . count(explode("\n", $content));
EOD;

file_put_contents(__DIR__ . '/test_auth.php', $script);
echo "Ready\n";
