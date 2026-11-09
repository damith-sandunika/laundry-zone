<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Models\SmsLog;
use App\Traits\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    protected $smsLog;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsLog $smsLog)
    {
        $this->smsLog = $smsLog;
        $this->captureTenantContext();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Retrieve dynamic Per-Tenant SMS configurations
            $gateway  = Setting::get('sms_gateway', 'textware');
            $apiKey   = Setting::get('sms_api_key') ?: env('SMS_API_TOKEN');
            $mask     = Setting::get('sms_mask') ?: (Setting::get('company_name') ?: env('SMS_MASK', 'LaundryZone'));
            $apiUrl   = Setting::get('sms_api_url') ?: env('SMS_API_URL');

            if ($gateway === 'mock' || empty($apiKey)) {
                Log::info("ProcessSms [MOCK/TESTING] Sent to {$this->smsLog->phone} with Mask [{$mask}]: {$this->smsLog->message}");
                $this->smsLog->update([
                    'status' => 'sent',
                    'response' => json_encode(['mock' => true, 'mask' => $mask, 'timestamp' => now()->toIso8601String()]),
                ]);
                return;
            }

            // Standardize mobile number for Sri Lankan / International format
            $rawPhone = preg_replace('/[^0-9]/', '', $this->smsLog->phone);
            $formattedPhone = ltrim($rawPhone, '0');
            if (strlen($formattedPhone) === 9) {
                $formattedPhone = '94' . $formattedPhone;
            }

            $endpoint = $apiUrl ?: 'https://api.textware.lk/sms/send';

            // Send via HTTP gateway with proper tenant mask
            $response = Http::timeout(12)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'X-API-Key'     => $apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->post($endpoint, [
                    'mask'    => $mask,
                    'sender'  => $mask,
                    'to'      => $formattedPhone,
                    'message' => $this->smsLog->message,
                ]);

            if ($response->successful()) {
                $this->smsLog->update(['status' => 'sent', 'response' => $response->body()]);
            } else {
                Log::warning("ProcessSms Gateway Failed for [{$mask}] -> {$this->smsLog->phone}: " . $response->body());
                $this->smsLog->update(['status' => 'failed', 'response' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error("ProcessSms Exception (Job): " . $e->getMessage());
            $this->smsLog->update(['status' => 'failed', 'response' => $e->getMessage()]);
        }
    }
}

