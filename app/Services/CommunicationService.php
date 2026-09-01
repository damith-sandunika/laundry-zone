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
            'pos_walkin_placed' => [
                'subject' => "Walk-in Order Placed",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been placed successfully. We will let you know once it is completed. - RAPID Laundromat</p>
                ",
            ],
            'pos_walkin_completed' => [
                'subject' => "Walk-in Order Completed",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> is fully completed and settled. Thank you for your business! - RAPID Laundromat</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order ID</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr>
                    </table>
                ",
            ],
            'delivery_completed' => [
                'subject' => "Delivery Order Completed",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your delivery order <strong>#{$orderId}</strong> is completed. Thank you for using RAPID Laundromat!</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order ID</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr>
                    </table>
                ",
            ],
            'collection_order_placed' => [
                'subject' => "Collection Order Placed",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been placed at our collection branch and will be sent for processing.</p>
                ",
            ],
            'collection_order_ready' => [
                'subject' => "Collection Order Ready for Pickup/Delivery",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Great news! Your order <strong>#{$orderId}</strong> is completed and ready for pickup or delivery.</p>
                ",
            ],
            'collection_order_completed' => [
                'subject' => "Collection Order Completed",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> is completed and settled. Thank you for choosing RAPID Laundromat!</p>
                ",
            ]
        ];

        return $templates[$type] ?? ['subject' => "Order #{$orderId} Update", 'body' => "<p>Hi {$name}, there is an update on order #{$orderId}.</p>"];
    }

    public function sendPosWalkinPlaced(Order $order) { $this->dispatch('pos_walkin_placed', $order); }
    public function sendPosWalkinCompleted(Order $order) { $this->dispatch('pos_walkin_completed', $order); }
    public function sendDeliveryCompleted(Order $order)  { $this->dispatch('delivery_completed', $order); }
    public function sendCollectionOrderPlaced(Order $order) { $this->dispatch('collection_order_placed', $order); }
    public function sendCollectionOrderReady(Order $order)  { $this->dispatch('collection_order_ready', $order); }
    public function sendCollectionOrderCompleted(Order $order) { $this->dispatch('collection_order_completed', $order); }

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

        $title = $customer->title ? trim($customer->title) . ' ' : '';
        $data = [
            'customer_name'  => trim($title . $customer->name),
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

        \App\Jobs\ProcessSms::dispatch($log);
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

        \App\Jobs\ProcessEmail::dispatch($log);
    }
}
