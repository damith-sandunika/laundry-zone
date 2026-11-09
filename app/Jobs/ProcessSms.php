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
            // Check Tenant SMS Credit Balance (Central Landlord DB)
            $tenant = \App\Services\Tenancy\TenantManager::getTenant();
            if ($tenant) {
                // Refresh tenant data from landlord DB
                $centralTenant = \App\Models\Central\Tenant::find($tenant->id);
                if ($centralTenant && $centralTenant->sms_balance <= 0) {
                    Log::warning("ProcessSms Blocked: Tenant {$centralTenant->name} (ID: {$centralTenant->id}) has insufficient SMS credits (Balance: {$centralTenant->sms_balance}). Gateway paused.");
                    $this->smsLog->update([
                        'status' => 'failed',
                        'response' => json_encode([
                            'error' => 'Insufficient SMS Credits. Gateway paused. Please purchase an SMS bundle.',
                            'balance' => $centralTenant->sms_balance,
                        ]),
                    ]);
                    return;
                }
            }

            // Retrieve dynamic Per-Tenant SMS configurations
            $gateway  = Setting::get('sms_gateway', 'textware');
            $apiKey   = Setting::get('sms_api_key') ?: env('SMS_API_TOKEN');
            $mask     = Setting::get('sms_mask') ?: (Setting::get('company_name') ?: env('SMS_MASK', 'LaundryZone'));
            $apiUrl   = Setting::get('sms_api_url') ?: env('SMS_API_URL');

            if ($gateway === 'mock' || empty($apiKey)) {
                $maskedPhone = substr($this->smsLog->phone, 0, 4) . '****' . substr($this->smsLog->phone, -2);
                Log::info("ProcessSms [MOCK/TESTING]", [
                    'recipient' => $maskedPhone,
                    'mask'      => $mask,
                    'sms_log_id' => $this->smsLog->id,
                ]);
                $this->smsLog->update([
                    'status' => 'sent',
                    'response' => json_encode(['mock' => true, 'mask' => $mask, 'timestamp' => now()->toIso8601String()]),
                ]);

                // Deduct 1 credit from tenant balance
                if (!empty($centralTenant) && $centralTenant->sms_balance > 0) {
                    $centralTenant->decrement('sms_balance', 1);
                }
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
                // Deduct 1 credit upon successful delivery
                if (!empty($centralTenant) && $centralTenant->sms_balance > 0) {
                    $centralTenant->decrement('sms_balance', 1);
                }
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

