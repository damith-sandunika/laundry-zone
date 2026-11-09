<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\Setting;
use App\Traits\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    protected $emailLog;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailLog $emailLog)
    {
        $this->emailLog = $emailLog;
        $this->captureTenantContext();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $companyName = Setting::where('key', 'company_name')->value('value') ?? config('app.name');
            $logoPath    = Setting::where('key', 'global_logo_path')->value('value');
            $logoUrl     = null;
            if ($logoPath) {
                $logoUrl = \Illuminate\Support\Str::startsWith($logoPath, 'http')
                    ? $logoPath
                    : asset('storage/' . $logoPath);
            }

            $logoHtml = $logoUrl
                ? "<img src=\"{$logoUrl}\" alt=\"{$companyName}\" style=\"max-height:60px;max-width:180px;object-fit:contain;\">"
                : "<span style=\"font-size:22px;font-weight:900;color:#fff;letter-spacing:1px;\">{$companyName}</span>";

            $htmlBody = "<!DOCTYPE html>
<html lang=\"en\">
<head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>{$this->emailLog->subject}</title></head>
<body style=\"margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f1f5f9;padding:30px 0;\">
  <tr><td align=\"center\">
    <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);\">
      <tr><td style=\"background:linear-gradient(135deg,#1e40af,#3b82f6);padding:24px 36px;text-align:center;\">{$logoHtml}</td></tr>
      <tr><td style=\"padding:32px 36px;font-size:14px;color:#374151;line-height:1.7;\">{$this->emailLog->body}</td></tr>
      <tr><td style=\"background:#f8fafc;padding:18px 36px;text-align:center;border-top:1px solid #e2e8f0;\">
        <p style=\"margin:0 0 4px;font-size:12px;color:#64748b;\">&copy; " . date('Y') . " {$companyName}. All rights reserved.</p>
        <p style=\"margin:0;font-size:11px;color:#94a3b8;\">This is an automated message. Please do not reply to this email.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>";

            $fromName    = Setting::where('key', 'mail_from_name')->value('value') ?: config('mail.from.name', $companyName);
            $fromAddress = Setting::where('key', 'mail_from_address')->value('value') ?: config('mail.from.address');

            Mail::html($htmlBody, function ($msg) use ($fromName, $fromAddress) {
                $msg->to($this->emailLog->email)
                    ->subject($this->emailLog->subject)
                    ->replyTo($fromAddress, $fromName)
                    ->getHeaders()
                        ->addTextHeader('List-Unsubscribe', '<mailto:' . $fromAddress . '?subject=unsubscribe>')
                        ->addTextHeader('Precedence', 'bulk')
                        ->addTextHeader('X-Mailer', 'LaundryZone-Mailer');
            });

            $this->emailLog->update(['status' => 'sent', 'response' => 'Sent successfully via SMTP']);
        } catch (\Exception $e) {
            Log::error("Email Error (Job): " . $e->getMessage());
            $this->emailLog->update(['status' => 'failed', 'response' => $e->getMessage()]);
        }
    }
}

