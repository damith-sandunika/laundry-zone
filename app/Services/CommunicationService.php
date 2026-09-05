<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Models\SmsLog;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappLog;
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
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been placed successfully. We will let you know once it is completed. - 365 Laundry</p>
                ",
            ],
            'pos_walkin_ready' => [
                'subject' => "Walk-in Order Ready",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Great news! Your walk-in order <strong>#{$orderId}</strong> is washed and ready to be picked up. Thank you! - 365 Laundry</p>
                ",
            ],
            'pos_walkin_completed' => [
                'subject' => "Walk-in Order Completed",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> is fully completed and settled. Thank you for your business! - 365 Laundry</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order Number</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr>
                    </table>
                ",
            ],
            'delivery_completed' => [
                'subject' => "Delivery Order Completed",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>Your delivery order <strong>#{$orderId}</strong> is completed. Thank you for using 365 Laundry!</p>
                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order Number</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr>
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
                    <p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> is completed and settled. Thank you for choosing 365 Laundry!</p>
                ",
            ],
            'bill_finalized' => [
                'subject' => "Payment Request for Order #{$orderId}",
                'body'    => "
                    <p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p>
                    <p style='margin:0 0 16px;'>The bill for your order <strong>#{$orderId}</strong> has been finalized. The total amount is <strong>{$total}</strong> and the amount due is <strong>{$due}</strong>. Please arrange the payment. Thank you! - 365 Laundry</p>
                ",
            ]
        ];

        return $templates[$type] ?? ['subject' => "Order #{$orderId} Update", 'body' => "<p>Hi {$name}, there is an update on order #{$orderId}.</p>"];
    }

    public function sendBookingConfirmation(\App\Models\DeliveryAppointment $appointment) {
        if ($appointment->order) {
            $this->dispatch('delivery_order_placed', $appointment->order);
        }
    }

    public function sendPosWalkinPlaced(Order $order) { $this->dispatch('pos_walkin_placed', $order); }
    public function sendPosWalkinReady(Order $order) { $this->dispatch('pos_walkin_ready', $order); }
    public function sendPosWalkinCompleted(Order $order) { $this->dispatch('pos_walkin_completed', $order); }
    public function sendDeliveryCompleted(Order $order)  { $this->dispatch('delivery_completed', $order); }
    public function sendCollectionOrderPlaced(Order $order) { $this->dispatch('collection_order_placed', $order); }
    public function sendCollectionOrderReady(Order $order)  { $this->dispatch('collection_order_ready', $order); }
    public function sendCollectionOrderCompleted(Order $order) { $this->dispatch('collection_order_completed', $order); }
    public function sendFinalizedBill(Order $order) { $this->dispatch('bill_finalized', $order); }

    // ─── Master Dispatch ─────────────────────────────────────────────────────

    protected function dispatch(string $type, Order $order)
    {
        $isSmsEnabled   = Setting::where('key', 'is_sms_enabled')->value('value') === 'true';
        $isEmailEnabled = Setting::where('key', 'is_email_enabled')->value('value') === 'true';
        $isWhatsappEnabled = Setting::where('key', 'is_whatsapp_enabled')->value('value') === 'true';

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
            'order_id'       => $order->order_number,
            'total'          => 'Rs.' . number_format($order->total, 2),
            'amount_due'     => 'Rs.' . number_format(max(0, $order->total - $order->paid_amount), 2),
            'payment_status' => $paymentStatusText,
        ];

        // Process SMS — isolated so failure doesn't block WhatsApp/Email
        if ($isSmsEnabled && !empty($customer->phone)) {
            try {
                $smsTemplate = SmsTemplate::where('type', $type)->where('is_active', true)->first();
                if ($smsTemplate) {
                    $replacements = ['{customer_name}' => $data['customer_name'], '{order_id}' => $data['order_id'], '{total}' => $data['total'], '{amount_due}' => $data['amount_due'], '{payment_status}' => $data['payment_status']];
                    $message = str_replace(array_keys($replacements), array_values($replacements), $smsTemplate->message);
                    $this->sendSms($order, $customer->phone, $type, $message);
                }
            } catch (\Exception $e) {
                Log::error("CommunicationService SMS failed [{$type}] Order#{$order->order_number}: " . $e->getMessage());
            }
        }

        // Process WhatsApp — isolated so failure doesn't block SMS/Email
        if ($isWhatsappEnabled && !empty($customer->phone)) {
            try {
                $waTemplate = WhatsappTemplate::where('type', $type)->where('is_active', true)->first();
                if ($waTemplate && !empty($waTemplate->meta_template_name)) {
                    $parameters = [
                        $data['customer_name'],
                        $data['order_id'],
                        $data['total'],
                        $data['amount_due'],
                        $data['payment_status']
                    ];
                    $this->sendWhatsApp($order, $customer->phone, $waTemplate, $parameters);
                }
            } catch (\Exception $e) {
                Log::error("CommunicationService WhatsApp failed [{$type}] Order#{$order->order_number}: " . $e->getMessage());
            }
        }

        // Process Email — isolated so failure doesn't block SMS/WhatsApp
        if ($isEmailEnabled && !empty($customer->email)) {
            try {
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
            } catch (\Exception $e) {
                Log::error("CommunicationService Email failed [{$type}] Order#{$order->order_number}: " . $e->getMessage());
            }
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


