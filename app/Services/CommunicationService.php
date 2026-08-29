<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Models\SmsLog;
use App\Models\EmailLog;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommunicationService
{
    // ─── Hardcoded Email Subjects & HTML Bodies ─────────────────────────────

    private function getEmailContent(string $type, array $data): array
    {
        $name    = $data['customer_name'];
        $orderId = $data['order_id'];
        $total   = $data['total'];
        $due     = $data['amount_due'];
        $payment = $data['payment_status'];

        $templates = [
            'order_confirmation' => [
                'subject' => "Order #{$orderId} Confirmed – RAPID Laundromat",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 12px;'>Your order <strong>#{$orderId}</strong> has been <span style='color:#16a34a;font-weight:700;'>confirmed</span> and is now being processed.</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;border-radius:6px 0 0 0;'>Order ID</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;border-radius:0 0 0 6px;'>Payment</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr>
                    </table>
                    <p style='margin:0;color:#64748b;font-size:13px;'>We'll notify you when your order is ready. Thank you for choosing RAPID Laundromat!</p>
                ",
            ],
            'bill_finalize' => [
                'subject' => "Order #{$orderId} – Bill Finalized",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 12px;'>Your laundry has been assessed and the final bill for order <strong>#{$orderId}</strong> is now confirmed.</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order ID</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total Amount</td><td style='padding:8px 12px;background:#f8fafc;color:#dc2626;font-weight:700;'>{$total}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Amount Due</td><td style='padding:8px 12px;background:#f8fafc;color:#dc2626;font-weight:700;'>{$due}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr>
                    </table>
                    <p style='margin:0;color:#64748b;font-size:13px;'>Please be ready to settle the payment when collecting your order. Thank you!</p>
                ",
            ],
            'order_ready' => [
                'subject' => "Order #{$orderId} is Ready for Pickup / Delivery 🎉",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Great news! Your order <strong>#{$orderId}</strong> is <span style='color:#16a34a;font-weight:700;'>ready</span> for pickup or delivery.</p>
                    <div style='background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:16px 20px;margin:16px 0;'>
                        <p style='margin:0;font-weight:700;color:#15803d;font-size:15px;'>✅ Your clothes are cleaned and ready!</p>
                    </div>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order ID</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr>
                    </table>
                    <p style='margin:0;color:#64748b;font-size:13px;'>Please visit your branch or await your delivery rider. Thank you for choosing RAPID Laundromat!</p>
                ",
            ],
            'order_delivered' => [
                'subject' => "Order #{$orderId} Delivered – Thank You!",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been successfully <span style='color:#16a34a;font-weight:700;'>delivered/collected</span>. We hope you're happy with our service!</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order ID</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total Paid</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr>
                    </table>
                    <p style='margin:0;color:#64748b;font-size:13px;'>We look forward to serving you again. — RAPID Laundromat 🧺</p>
                ",
            ],
            'delivery_confirmation' => [
                'subject' => "Order #{$orderId} – Rider On the Way 🚴",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Our delivery rider is on the way with your order <strong>#{$orderId}</strong>. Please be available to receive it.</p>
                    <div style='background:#eff6ff;border:1.5px solid #93c5fd;border-radius:12px;padding:16px 20px;margin:16px 0;'>
                        <p style='margin:0;font-weight:700;color:#1d4ed8;font-size:15px;'>🚴 Rider is heading to your location!</p>
                    </div>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order ID</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Amount Due</td><td style='padding:8px 12px;background:#f8fafc;color:#dc2626;font-weight:700;'>{$due}</td></tr>
                    </table>
                    <p style='margin:0;color:#64748b;font-size:13px;'>Please prepare the exact amount if paying by cash. Thank you!</p>
                ",
            ],
            'delivery_thank_you' => [
                'subject' => "Thank You for Using RAPID Laundromat Delivery!",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Thank you for using our home delivery service for order <strong>#{$orderId}</strong>. Your satisfaction is our priority!</p>
                    <div style='background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:16px 20px;margin:16px 0;'>
                        <p style='margin:0;font-weight:700;color:#15803d;'>⭐ We'd love to hear your feedback!</p>
                    </div>
                    <p style='margin:16px 0 0;color:#64748b;font-size:13px;'>We hope to serve you again soon. — RAPID Laundromat 🧺</p>
                ",
            ],
            'order_dispatched_to_workshop' => [
                'subject' => "Order #{$orderId} – Sent to Workshop",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been dispatched to our central workshop for specialized processing.</p>
                    <p style='margin:0;color:#64748b;font-size:13px;'>We'll notify you as soon as it's ready. Thank you for your patience!</p>
                ",
            ],
            'order_processing' => [
                'subject' => "Order #{$orderId} – Now Processing",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> is currently being processed at our workshop. We're taking great care of your items!</p>
                    <p style='margin:0;color:#64748b;font-size:13px;'>We'll update you once it's ready. Thank you for choosing RAPID Laundromat!</p>
                ",
            ],
            'order_returned_from_workshop' => [
                'subject' => "Order #{$orderId} – Back from Workshop",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been returned from our workshop to the branch. It will be ready for pickup/delivery shortly!</p>
                    <p style='margin:0;color:#64748b;font-size:13px;'>Thank you for your patience. — RAPID Laundromat 🧺</p>
                ",
            ],
            'order_cancelled' => [
                'subject' => "Order #{$orderId} – Cancelled",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>We regret to inform you that your order <strong>#{$orderId}</strong> has been <span style='color:#dc2626;font-weight:700;'>cancelled</span>.</p>
                    <p style='margin:0;color:#64748b;font-size:13px;'>If you have any questions, please contact your nearest RAPID Laundromat branch. We apologize for the inconvenience.</p>
                ",
            ],
        ];

        return $templates[$type] ?? ['subject' => "Order #{$orderId} Update", 'body' => "<p>Hi {$name}, there is an update on order #{$orderId}.</p>"];
    }

    // ─── Public Methods ──────────────────────────────────────────────────────

    public function sendOrderConfirmation(Order $order) { $this->dispatch('order_confirmation', $order); }
    public function sendOrderReady(Order $order)         { $this->dispatch('order_ready', $order); }
    public function sendOrderDelivered(Order $order)     { $this->dispatch('order_delivered', $order); }
    public function sendFinalizedBill(Order $order)      { $this->dispatch('bill_finalize', $order); }
    public function sendDeliveryConfirmation(Order $order) { $this->dispatch('delivery_confirmation', $order); }
    public function sendDeliveryThankYou(Order $order)   { $this->dispatch('delivery_thank_you', $order); }
    public function sendOrderDispatchedToWorkshop(Order $order) { $this->dispatch('order_dispatched_to_workshop', $order); }
    public function sendOrderProcessing(Order $order)    { $this->dispatch('order_processing', $order); }
    public function sendOrderReturnedFromWorkshop(Order $order) { $this->dispatch('order_returned_from_workshop', $order); }
    public function sendOrderCancelled(Order $order)     { $this->dispatch('order_cancelled', $order); }

    // ─── Master Dispatch ─────────────────────────────────────────────────────

    protected function dispatch(string $type, Order $order)
    {
        $isSmsEnabled   = Setting::where('key', 'is_sms_enabled')->value('value') === 'true';
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

        $data = [
            'customer_name'  => $customer->name,
            'order_id'       => $order->id,
            'total'          => 'Rs.' . number_format($order->total, 2),
            'amount_due'     => 'Rs.' . number_format(max(0, $order->total - $order->paid_amount), 2),
            'payment_status' => $paymentStatusText,
        ];

        // Process SMS
        if ($isSmsEnabled && !empty($customer->phone)) {
            $smsTemplate = SmsTemplate::where('type', $type)->where('is_active', true)->first();
            if ($smsTemplate) {
                $replacements = ['{customer_name}' => $data['customer_name'], '{order_id}' => $data['order_id'], '{total}' => $data['total'], '{amount_due}' => $data['amount_due'], '{payment_status}' => $data['payment_status']];
                $message = str_replace(array_keys($replacements), array_values($replacements), $smsTemplate->message);
                $this->sendSms($order, $customer->phone, $type, $message);
            }
        }

        // Process Email
        if ($isEmailEnabled && !empty($customer->email)) {
            // Use DB template body/subject if it exists, otherwise use hardcoded fallback
            $dbTemplate = \App\Models\EmailTemplate::where('type', $type)->first();
            if ($dbTemplate && !empty($dbTemplate->body)) {
                $replacements = [
                    '{customer_name}'  => $data['customer_name'],
                    '{order_id}'       => $data['order_id'],
                    '{total}'          => $data['total'],
                    '{amount_due}'     => $data['amount_due'],
                    '{payment_status}' => $data['payment_status'],
                ];
                $subject = str_replace(array_keys($replacements), array_values($replacements), $dbTemplate->subject);
                $body    = str_replace(array_keys($replacements), array_values($replacements), $dbTemplate->body);
            } else {
                $content = $this->getEmailContent($type, $data);
                $subject = $content['subject'];
                $body    = $content['body'];
            }
            $this->sendEmail($order, $customer->email, $type, $subject, $body);
        }
    }

    // ─── SMS Sender ──────────────────────────────────────────────────────────

    protected function sendSms(Order $order, string $phone, string $type, string $message)
    {
        $log = SmsLog::create([
            'order_id' => $order->id,
            'phone'    => $phone,
            'type'     => $type,
            'message'  => $message,
            'status'   => 'pending',
        ]);

        try {
            $apiUrl   = env('SMS_API_URL');
            $apiToken = env('SMS_API_TOKEN');
            $mask     = env('SMS_MASK', 'RAPIDLAUNDROMAT');

            if (!$apiUrl || !$apiToken) {
                throw new \Exception('SMS API credentials not configured.');
            }

            $response = Http::timeout(10)->post($apiUrl, [
                'token' => $apiToken,
                'mask'  => $mask,
                'to'    => $phone,
                'text'  => $message,
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

    // ─── Email Sender ─────────────────────────────────────────────────────────

    protected function sendEmail(Order $order, string $email, string $type, string $subject, string $body)
    {
        $log = EmailLog::create([
            'order_id' => $order->id,
            'email'    => $email,
            'type'     => $type,
            'subject'  => $subject,
            'body'     => $body,
            'status'   => 'pending',
        ]);

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
<head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>{$subject}</title></head>
<body style=\"margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;\">
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f1f5f9;padding:30px 0;\">
  <tr><td align=\"center\">
    <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);\">
      <tr><td style=\"background:linear-gradient(135deg,#1e40af,#3b82f6);padding:24px 36px;text-align:center;\">{$logoHtml}</td></tr>
      <tr><td style=\"padding:32px 36px;font-size:14px;color:#374151;line-height:1.7;\">{$body}</td></tr>
      <tr><td style=\"background:#f8fafc;padding:18px 36px;text-align:center;border-top:1px solid #e2e8f0;\">
        <p style=\"margin:0 0 4px;font-size:12px;color:#64748b;\">&copy; " . date('Y') . " {$companyName}. All rights reserved.</p>
        <p style=\"margin:0;font-size:11px;color:#94a3b8;\">This is an automated message. Please do not reply to this email.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>";

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
