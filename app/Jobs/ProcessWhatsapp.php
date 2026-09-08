<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $whatsappLog;
    protected $parameters;

    /**
     * Create a new job instance.
     */
    public function __construct(WhatsappLog $whatsappLog, array $parameters = [])
    {
        $this->whatsappLog = $whatsappLog;
        $this->parameters = $parameters;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $phoneId  = \App\Models\Setting::where('key', 'whatsapp_phone_id')->value('value');
            $apiToken = \App\Models\Setting::where('key', 'whatsapp_access_token')->value('value');

            if (!$phoneId || !$apiToken) {
                throw new \Exception('WhatsApp API credentials not configured in settings.');
            }
            
            $apiUrl = "https://graph.facebook.com/v17.0/{$phoneId}/messages";
            
            $templateName = $this->whatsappLog->template_name ?? 'default';
            
            $components = [];
            if (!empty($this->parameters)) {
                $componentParams = [];
                foreach ($this->parameters as $param) {
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

            $template = \App\Models\WhatsappTemplate::where('meta_template_name', $templateName)->first();
            $languageCode = $template ? $template->language_code : 'en_US';

            $response = Http::timeout(10)
                ->withToken($apiToken)
                ->post($apiUrl, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $this->whatsappLog->phone,
                    'type'              => 'template',
                    'template'          => [
                        'name'       => $templateName,
                        'language'   => [
                            'code' => $languageCode
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
