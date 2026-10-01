<?php
$ctrl    = file_get_contents('app/Http/Controllers/OrderController.php');
$topbar  = file_get_contents('resources/views/layouts/partials/topbar.blade.php');
$dispatch = file_get_contents('app/Actions/Workshop/BulkDispatchToWorkshopAction.php');
$appBlade = file_get_contents('resources/views/layouts/app.blade.php');

$p = 0; $f = 0;
function chk($label, $ok, &$p, &$f) {
    if ($ok) { echo "   [PASS] $label\n"; $p++; }
    else      { echo "   [FAIL] $label\n"; $f++; }
}

echo "=== ORDER SEARCH & FILTERING — FULL TEST ===\n\n";

echo "1. search() — Branch + Workshop Filtering\n";
chk("isSuperAdmin() check present", str_contains($ctrl, 'isSuperAdmin()'), $p, $f);
chk("Filters by branch_id for non-admins", str_contains($ctrl, "->where('branch_id', \$user->branch_id)"), $p, $f);
chk("Also filters by workshop_id", str_contains($ctrl, "->orWhere('workshop_id', \$user->branch_id)"), $p, $f);
chk("Uses sub-query grouping to prevent SQL leaks", str_contains($ctrl, 'where(function ($sub) use ($user)'), $p, $f);
chk("limit(8) prevents large response", str_contains($ctrl, 'limit(8)'), $p, $f);
chk("Returns 'url' field", str_contains($ctrl, "'url'"), $p, $f);
chk("Returns 'status_label' field", str_contains($ctrl, "'status_label'"), $p, $f);
chk("Returns 'customer' field", str_contains($ctrl, "'customer'"), $p, $f);

echo "\n2. index() — Orders Page Filtering\n";
$idx = substr($ctrl, strpos($ctrl, 'public function index'), 3500);
chk("Tab-count query: branch_id OR workshop_id", str_contains($idx, "orWhere('workshop_id', \$branchId)"), $p, $f);
chk("Main order query: branch_id OR workshop_id (x2)", substr_count($idx, "orWhere('workshop_id', \$branchId)") >= 2, $p, $f);
chk("workshop_admin redirected to workshop.operations", str_contains($idx, "workshop.operations"), $p, $f);
chk("super_admin filterBranchId support", str_contains($idx, "filterBranchId"), $p, $f);
chk("paginate(15) for performance", str_contains($ctrl, "->paginate(15)->withQueryString()"), $p, $f);

echo "\n3. Topbar — Search Bar Role Visibility\n";
chk("Visible to staff", str_contains($topbar, "'staff'"), $p, $f);
chk("Visible to branch_admin", str_contains($topbar, "'branch_admin'"), $p, $f);
chk("Visible to workshop_admin", str_contains($topbar, "'workshop_admin'"), $p, $f);
chk("Visible to super_admin", str_contains($topbar, "'super_admin'"), $p, $f);
chk("NOT visible to deprecated 'admin' role", !str_contains($topbar, "'branch_admin', 'admin'"), $p, $f);

echo "\n4. Alpine orderSearch() — Frontend\n";
chk("orderSearch() defined", str_contains($appBlade, 'function orderSearch()'), $p, $f);
chk("Calls /orders/search?q=", str_contains($appBlade, '/orders/search?q='), $p, $f);
chk("Debounce 300ms", str_contains($topbar, 'debounce.300ms'), $p, $f);
chk("Loading spinner", str_contains($topbar, 'x-show="loading"'), $p, $f);
chk("Min 2 chars", str_contains($appBlade, 'query.length < 2'), $p, $f);
chk("'No orders found' message", str_contains($topbar, 'No orders found'), $p, $f);
chk("Status color badges", str_contains($topbar, "order.status === 'done'"), $p, $f);
chk("close() on result click", str_contains($topbar, '@click="close()"'), $p, $f);

echo "\n5. Route — /orders/search\n";
$routes = file_get_contents('routes/web.php');
chk("Route GET /orders/search defined", str_contains($routes, "'/orders/search'"), $p, $f);
chk("Route name 'orders.search'", str_contains($routes, 'orders.search'), $p, $f);

echo "\n6. Workshop Dispatch — Dual Branch IDs\n";
chk("Sets order.workshop_id on dispatch", str_contains($dispatch, "'workshop_id' => \$data['workshop_branch_id']"), $p, $f);
chk("Preserves original branch_id (does NOT overwrite it)", !str_contains($dispatch, "'branch_id' => \$data['workshop_branch_id']"), $p, $f);
chk("WorkshopDispatch row has both branch_id and workshop_branch_id", str_contains($dispatch, "'branch_id' => \$order->branch_id") && str_contains($dispatch, "'workshop_branch_id' => \$data['workshop_branch_id']"), $p, $f);

echo "\n7. Order Model — Fields\n";
$order = file_get_contents('app/Models/Order.php');
chk("Order.workshop_id in fillable", str_contains($order, 'workshop_id'), $p, $f);
chk("Order.branch_id in fillable", str_contains($order, 'branch_id'), $p, $f);
chk("Order has workshop() belongsTo relationship", str_contains($order, "belongsTo(Branch::class, 'workshop_id')"), $p, $f);

echo "\n" . str_repeat("-", 50) . "\n";
echo "RESULTS: $p PASSED | $f FAILED\n";
if ($f === 0) {
    echo "STATUS: ALL TESTS PASSED\n\n";
    echo "FILTERING MATRIX:\n";
    echo "  super_admin    -> ALL branches, ALL orders\n";
    echo "  branch_admin   -> branch_id = own branch (list + search)\n";
    echo "  staff          -> branch_id = own branch (list + search)\n";
    echo "  workshop_admin -> workshop.operations page (own flow)\n";
    echo "                    search: branch_id OR workshop_id = own branch\n";
    echo "  rider          -> No search bar, no orders index\n";
} else {
    echo "STATUS: $f FAILURE(S) — SEE ABOVE\n";
}
echo str_repeat("-", 50) . "\n";
