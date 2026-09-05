<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $whatsappLog;

    /**
     * Create a new job instance.
     */
    public function __construct(NotificationLog $whatsappLog)
    {
        $this->whatsappLog = $whatsappLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $apiUrl   = env('WHATSAPP_API_URL');
            $apiToken = env('WHATSAPP_API_TOKEN');

            if (!$apiUrl || !$apiToken) {
                throw new \Exception('WhatsApp API credentials not configured.');
            }
            
            $payload = json_decode($this->whatsappLog->payload, true);
            $templateName = $payload['template_name'] ?? 'default';
            $parameters = $payload['parameters'] ?? [];
            
            $components = [];
            if (!empty($parameters)) {
                $componentParams = [];
                foreach ($parameters as $param) {
                    $componentParams[] = [
                        'type' => 'text',
                        'text' => (string) $param
                    ];
                }
                
                $components[] = [
                    'type' => 'body',
                    'parameters' => $componentParams
                ];
            }

            $response = Http::timeout(10)
                ->withToken($apiToken)
                ->post($apiUrl, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $this->whatsappLog->recipient,
                    'type'              => 'template',
                    'template'          => [
                        'name'       => $templateName,
                        'language'   => [
                            'code' => 'en_US'
                        ],
                        'components' => $components
                    ]
                ]);

            if ($response->successful()) {
                $this->whatsappLog->update(['status' => 'sent', 'response' => $response->body()]);
            } else {
                $this->whatsappLog->update(['status' => 'failed', 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Error (Job): " . $e->getMessage());
            $this->whatsappLog->update(['status' => 'failed', 'response' => $e->getMessage()]);
        }
    }
}
