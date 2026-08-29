<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Models\SmsLog;
use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommunicationService
{
    /**
     * Send Order Confirmation
     */
    public function sendOrderConfirmation(Order $order)
    {
        $this->dispatch('order_confirmation', $order);
    }

    /**
     * Send Order Ready
     */
    public function sendOrderReady(Order $order)
    {
        $this->dispatch('order_ready', $order);
    }

    /**
     * Send Order Delivered
     */
    public function sendOrderDelivered(Order $order)
    {
        $this->dispatch('order_delivered', $order);
    }

    /**
     * Send Finalize Bill
     */
    public function sendFinalizedBill(Order $order)
    {
        $this->dispatch('bill_finalize', $order);
    }

    /**
     * Send Delivery Confirmation
     */
    public function sendDeliveryConfirmation(Order $order)
    {
        $this->dispatch('delivery_confirmation', $order);
    }

    /**
     * Send Delivery Thank You
     */
    public function sendDeliveryThankYou(Order $order)
    {
        $this->dispatch('delivery_thank_you', $order);
    }

    /**
     * Master Dispatch method to handle both SMS and Email based on global settings
     */
    protected function dispatch(string $type, Order $order)
    {
        $isSmsEnabled = Setting::where('key', 'is_sms_enabled')->value('value') === 'true';
        $isEmailEnabled = Setting::where('key', 'is_email_enabled')->value('value') === 'true';

        $customer = $order->customer;
        if (!$customer) return;

        $replacements = [
            '{customer_name}' => $customer->name,
            '{order_id}' => $order->id,
            '{total}' => 'Rs.' . number_format($order->total, 2),
            '{amount_due}' => 'Rs.' . number_format(max(0, $order->total - $order->paid_amount), 2),
        ];

        // Process SMS
        if ($isSmsEnabled && !empty($customer->phone)) {
            $smsTemplate = SmsTemplate::where('type', $type)->where('is_active', true)->first();
            if ($smsTemplate) {
                $message = str_replace(array_keys($replacements), array_values($replacements), $smsTemplate->message);
                $this->sendSms($order, $customer->phone, $type, $message);
            }
        }

        // Process Email
        if ($isEmailEnabled && !empty($customer->email)) {
            $emailTemplate = EmailTemplate::where('type', $type)->where('is_active', true)->first();
            if ($emailTemplate) {
                $subject = str_replace(array_keys($replacements), array_values($replacements), $emailTemplate->subject);
                $body = str_replace(array_keys($replacements), array_values($replacements), $emailTemplate->body);
                $this->sendEmail($order, $customer->email, $type, $subject, $body);
            }
        }
    }

    /**
     * Generic SMS Sender (Placeholder for real API)
     */
    protected function sendSms(Order $order, string $phone, string $type, string $message)
    {
        $log = SmsLog::create([
            'order_id' => $order->id,
            'phone' => $phone,
            'type' => $type,
            'message' => $message,
            'status' => 'pending'
        ]);

        try {
            // Using generic gateway logic with configurable mask
            $apiUrl = env('SMS_API_URL');
            $apiToken = env('SMS_API_TOKEN');
            $mask = env('SMS_MASK', 'RAPIDLAUNDROMAT');
            
            if (!$apiUrl || !$apiToken) {
                throw new \Exception('SMS API credentials not configured.');
            }

            // A standard GET/POST payload structure, which the user can adapt via env if needed.
            // Often Gateways accept: mask, to, text, token etc.
            $response = Http::timeout(10)->post($apiUrl, [
                'token' => $apiToken,
                'mask' => $mask,
                'to' => $phone,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $log->update(['status' => 'sent', 'response' => $response->body()]);
            } else {
                $log->update(['status' => 'failed', 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("SMS Error: " . $e->getMessage());
            $log->update(['status' => 'failed', 'response' => $e->getMessage()]);
        }
    }

    /**
     * Generic Email Sender
     */
    protected function sendEmail(Order $order, string $email, string $type, string $subject, string $body)
    {
        $log = EmailLog::create([
            'order_id' => $order->id,
            'email'    => $email,
            'type'     => $type,
            'subject'  => $subject,
            'body'     => $body,
            'status'   => 'pending'
        ]);

        try {
            // Fetch company logo and name from settings
            $companyName = Setting::where('key', 'company_name')->value('value') ?? config('app.name');
            $logoPath    = Setting::where('key', 'global_logo_path')->value('value');
            $logoUrl     = null;
            if ($logoPath) {
                $logoUrl = \Illuminate\Support\Str::startsWith($logoPath, 'http')
                    ? $logoPath
                    : asset('storage/' . $logoPath);
            }

            // Wrap the plain body in a branded HTML email template
            $htmlBody = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . e($subject) . '</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:30px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);">
      
      <!-- Header / Logo -->
      <tr>
        <td style="background:linear-gradient(135deg,#1e40af,#3b82f6);padding:28px 36px;text-align:center;">
          ' . ($logoUrl
              ? '<img src="' . $logoUrl . '" alt="' . e($companyName) . '" style="max-height:70px;max-width:200px;object-fit:contain;">'
              : '<span style="font-size:24px;font-weight:900;color:#ffffff;letter-spacing:1px;">' . e($companyName) . '</span>') . '
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding:36px 36px 24px;">
          ' . $body . '
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f8fafc;padding:20px 36px;text-align:center;border-top:1px solid #e2e8f0;">
          <p style="margin:0 0 6px;font-size:12px;color:#64748b;">© ' . date('Y') . ' ' . e($companyName) . '. All rights reserved.</p>
          <p style="margin:0;font-size:11px;color:#94a3b8;">This is an automated message. Please do not reply to this email.</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>';

            $fromName    = config('mail.from.name', $companyName);
            $fromAddress = config('mail.from.address');

            Mail::html($htmlBody, function ($msg) use ($email, $subject, $fromName, $fromAddress) {
                $msg->to($email)
                    ->subject($subject)
                    ->replyTo($fromAddress, $fromName)
                    ->getHeaders()
                        ->addTextHeader('List-Unsubscribe', '<mailto:' . $fromAddress . '?subject=unsubscribe>')
                        ->addTextHeader('Precedence', 'bulk')
                        ->addTextHeader('X-Mailer', 'RapidLaundromat-Mailer');
            });

            $log->update(['status' => 'sent', 'response' => 'Sent successfully via SMTP']);
        } catch (\Exception $e) {
            Log::error("Email Error: " . $e->getMessage());
            $log->update(['status' => 'failed', 'response' => $e->getMessage()]);
        }
    }
}
