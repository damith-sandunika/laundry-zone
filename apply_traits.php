<?php
$modelsPath = __DIR__ . '/app/Models';
$modelsToUpdate = ['Customer.php', 'Order.php', 'InventoryItem.php', 'Cubby.php', 'Expense.php', 'OnlineBooking.php', 'RegisterSession.php', 'User.php'];

foreach ($modelsToUpdate as $modelFile) {
    $filePath = $modelsPath . '/' . $modelFile;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Add use App\Traits\BelongsToBranch;
        if (strpos($content, 'use App\Traits\BelongsToBranch;') === false) {
            $content = preg_replace('/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/', "use Illuminate\\Database\\Eloquent\\Model;\nuse App\\Traits\\BelongsToBranch;", $content);
        }
        
        // Ensure User uses BelongsToBranch as well (it uses Authenticatable)
        if ($modelFile === 'User.php' && strpos($content, 'use App\Traits\BelongsToBranch;') === false) {
            $content = preg_replace('/use Illuminate\\\\Foundation\\\\Auth\\\\User as Authenticatable;/', "use Illuminate\\Foundation\\Auth\\User as Authenticatable;\nuse App\\Traits\\BelongsToBranch;", $content);
        }

        // Add use BelongsToBranch; inside class
        if (strpos($content, 'use BelongsToBranch;') === false) {
            $content = preg_replace('/class [a-zA-Z0-9_]+ extends [a-zA-Z0-9_]+\s*\{/', "$0\n    use BelongsToBranch;\n", $content);
        }
        
        // Remove existing branch() method to avoid conflict
        $content = preg_replace('/\s*public function branch\(\)\s*\{\s*return \$this->belongsTo\(Branch::class\);\s*\}/s', '', $content);
        $content = preg_replace('/\s*public function branch\(\)\s*\{\s*return \$this->belongsTo\(\\\App\\\Models\\\Branch::class\);\s*\}/s', '', $content);

        file_put_contents($filePath, $content);
        echo "Updated $modelFile\n";
    } else {
        echo "File $modelFile not found\n";
    }
}
