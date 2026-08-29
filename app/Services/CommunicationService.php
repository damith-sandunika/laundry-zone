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

        $paymentStatusText = 'Pending';
        if ($order->paid_amount >= $order->total && $order->total > 0) {
            $paymentStatusText = 'Paid in Full';
        } elseif ($order->paid_amount > 0) {
            $paymentStatusText = 'Partially Paid (Rs.' . number_format($order->paid_amount, 2) . ' paid)';
        } elseif ($order->total == 0) {
            $paymentStatusText = 'No Charge';
        }

        $replacements = [
            '{customer_name}' => $customer->name,
            '{order_id}' => $order->id,
            '{total}' => 'Rs.' . number_format($order->total, 2),
            '{amount_due}' => 'Rs.' . number_format(max(0, $order->total - $order->paid_amount), 2),
            '{payment_status}' => $paymentStatusText,
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
            $companyName = Setting::where('key', 'company_name')->value('value') ?? config('app.name');
            $fromName    = config('mail.from.name', $companyName);
            $fromAddress = config('mail.from.address');

            // Using Laravel Mail to send raw HTML
            Mail::html($body, function ($msg) use ($email, $subject, $fromName, $fromAddress) {
                $msg->to($email)
                    ->subject($subject)
                    ->replyTo($fromAddress, $fromName)
                    ->getHeaders()
                        ->addTextHeader('List-Unsubscribe', '<mailto:' . $fromAddress . '?subject=unsubscribe>')
                        ->addTextHeader('Precedence', 'bulk')
                        ->addTextHeader('X-Mailer', 'RapidLaundromat-Mailer');
            });
            
            $log->update(['status' => 'sent', 'response' => 'Sent successfully via SMTP']);
            // Write success to public file for easy debugging
            file_put_contents(public_path('email_debug.txt'), date('Y-m-d H:i:s') . " - SUCCESS sending " . $type . " to " . $email . PHP_EOL, FILE_APPEND);
        } catch (\Exception $e) {
            Log::error("Email Error: " . $e->getMessage());
            $log->update(['status' => 'failed', 'response' => $e->getMessage()]);
            // Write to a public file for easy user debugging
            file_put_contents(public_path('email_debug.txt'), date('Y-m-d H:i:s') . " - ERROR sending " . $type . ": " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }
}
