<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;

class SmsWebhookController extends Controller
{
    public function handleCallback(Request $request)
    {
        // Example Payload:
        // { "event": "sms.status_update", "data": { "id": "msg_abc123", "status": "sent", "numbers": "94771234567" } }
        
        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'sms.status_update' && !empty($data['numbers'])) {
            $phone = $data['numbers'];
            $status = $data['status'] ?? 'unknown';

            // Find the most recent pending or sent log for this number
            $log = SmsLog::where('phone', $phone)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($log) {
                $log->update(['status' => $status]);
                Log::info("SMS Webhook: Updated status for phone {$phone} to {$status}");
            } else {
                Log::warning("SMS Webhook: No log found for phone {$phone}");
            }
        }

        return response()->json(['success' => true]);
    }
}
