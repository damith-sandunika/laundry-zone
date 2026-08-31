<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $smsLog;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsLog $smsLog)
    {
        $this->smsLog = $smsLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $apiUrl   = env('SMS_API_URL');
            $apiToken = env('SMS_API_TOKEN');
            $mask     = env('SMS_MASK', 'RAPIDLAUNDROMAT');

            if (!$apiUrl || !$apiToken) {
                throw new \Exception('SMS API credentials not configured.');
            }

            $response = Http::timeout(10)
                ->withHeaders(['X-API-Key' => $apiToken])
                ->post($apiUrl, [
                    'mask'    => $mask,
                    'to'      => $this->smsLog->phone,
                    'message' => $this->smsLog->message,
                ]);

            if ($response->successful()) {
                $this->smsLog->update(['status' => 'sent', 'response' => $response->body()]);
            } else {
                $this->smsLog->update(['status' => 'failed', 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("SMS Error (Job): " . $e->getMessage());
            $this->smsLog->update(['status' => 'failed', 'response' => $e->getMessage()]);
        }
    }
}
