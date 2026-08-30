<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== EMAIL DEBUG CHECK ===\n\n";

// 1. Check is_email_enabled
$enabled = App\Models\Setting::where('key','is_email_enabled')->value('value');
echo "is_email_enabled: " . ($enabled ?? 'NOT SET') . "\n";

// 2. Check email templates
$templates = App\Models\EmailTemplate::select('type','is_active', 'subject')->get();
echo "\nEmail Templates in DB:\n";
foreach($templates as $t) {
    echo "  [{$t->type}] active=" . ($t->is_active ? 'YES' : 'NO') . "\n";
}

// 3. Check last 5 email logs
echo "\nLast 10 Email Logs:\n";
$logs = App\Models\EmailLog::latest()->take(10)->get(['type','email','status','response','created_at']);
foreach($logs as $l) {
    echo "  [{$l->created_at}] {$l->type} -> {$l->email} | {$l->status}";
    if ($l->status === 'failed') echo " | " . substr($l->response, 0, 80);
    echo "\n";
}

// 4. Check MAIL config
echo "\nMAIL Config:\n";
echo "  MAILER: " . config('mail.default') . "\n";
echo "  HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "  USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "  FROM: " . config('mail.from.address') . "\n";
