<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Models\EmailTemplate;
use App\Models\SmsLog;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\User;
use App\Jobs\ProcessSms;
use App\Jobs\ProcessEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommunicationService
{
    public function sendPosWalkinPlaced(Order $order) { $this->dispatch('pos_walkin_placed', $order); }
    public function sendPosWalkinReady(Order $order) { $this->dispatch('pos_walkin_ready', $order); }
    public function sendPosWalkinCompleted(Order $order) { $this->dispatch('pos_walkin_completed', $order); }
    public function sendCollectionOrderPlaced(Order $order) { $this->dispatch('collection_order_placed', $order); }
    public function sendCollectionOrderReady(Order $order) { $this->dispatch('collection_order_ready', $order); }
    public function sendCollectionOrderCompleted(Order $order) { $this->dispatch('collection_order_completed', $order); }
    public function sendDeliveryOrderConfirmed(Order $order) { $this->dispatch('delivery_order_confirmed', $order); }
    public function sendDeliveryOrderCancelled(Order $order) { $this->dispatch('delivery_order_cancelled', $order); }
    public function sendDeliveryOrderReady(Order $order) { $this->dispatch('delivery_order_ready', $order); }
    public function sendDeliveryCompleted(Order $order) { $this->dispatch('delivery_completed', $order); }

    public function sendFinalizedBill(Order $order, $requestedAmount = null, $paymentLink = null)
    {
        $this->dispatch('bill_finalized', $order, ['requested_amount' => $requestedAmount, 'payment_link' => $paymentLink]);
    }

    public function sendPaymentReceived(Order $order)
    {
        $this->dispatch('payment_received', $order);
    }

    public function sendRiderAssigned(Order $order, User $rider)
    {
        $this->dispatch('rider_assigned', $order, ['rider' => $rider]);
    }

    protected function dispatch(string $type, Order $order, array $extraData = [])
    {
        $settings = Cache::remember('global_comm_settings', 300, function () {
            return Setting::whereIn('key', ['is_sms_enabled', 'is_email_enabled'])->pluck('value', 'key');
        });

        $isSmsEnabled = ($settings->get('is_sms_enabled') === 'true');
        $isEmailEnabled = ($settings->get('is_email_enabled') === 'true');

        $customer = $order->customer;
        if (!$customer) {
            return;
        }

        $paymentStatusText = 'Pending';
        if ($order->paid_amount >= $order->total && $order->total > 0) {
            $paymentStatusText = 'Paid in Full';
        } elseif ($order->paid_amount > 0) {
            $paymentStatusText = 'Partially Paid (Rs.' . number_format($order->paid_amount, 2) . ' paid)';
        } elseif ($order->total == 0) {
            $paymentStatusText = 'No Charge';
        }

        $title = $customer->title ? trim($customer->title) . ' ' : '';
        $requestedAmountRaw = $extraData['requested_amount'] ?? ($order->requested_advance > 0 ? $order->requested_advance : max(0, $order->total - $order->paid_amount));

        $rider = $extraData['rider'] ?? null;
        $riderVehicle = ($rider && $rider->profile && !empty($rider->profile->vehicle_number)) ? $rider->profile->vehicle_number : 'N/A';

        $data = [
            'customer_name' => trim($title . $customer->name),
            'order_id' => $order->order_number,
            'total' => 'Rs.' . number_format($order->total, 2),
            'amount_due' => 'Rs.' . number_format(max(0, $order->total - $order->paid_amount), 2),
            'payment_status' => $paymentStatusText,
            'tracking_link' => $extraData['payment_link'] ?? url("/customer/orders/{$order->order_number}"),
            'requested_amount' => 'Rs.' . number_format($requestedAmountRaw, 2),
            'rider_name' => $rider ? $rider->name : '',
            'rider_phone' => $rider ? $rider->phone : '',
            'rider_vehicle_number' => $riderVehicle,
        ];

        $replacements = [
            '{customer_name}' => $data['customer_name'],
            '{order_id}' => $data['order_id'],
            '{total}' => $data['total'],
            '{amount_due}' => $data['amount_due'],
            '{payment_status}' => $data['payment_status'],
            '{tracking_link}' => $data['tracking_link'],
            '{requested_amount}' => $data['requested_amount'],
            '{rider_name}' => $data['rider_name'],
            '{rider_phone}' => $data['rider_phone'],
            '{rider_vehicle_number}' => $data['rider_vehicle_number'],
        ];

        if ($isSmsEnabled && !empty($customer->phone)) {
            try {
                $smsTemplate = SmsTemplate::where('type', $type)->where('is_active', true)->first();
                if ($smsTemplate) {
                    $message = str_replace(array_keys($replacements), array_values($replacements), $smsTemplate->message);
                    $this->sendSms($order, $customer->phone, $type, $message);
                }
            } catch (\Throwable $e) {
                Log::channel('communications')->error("CommunicationService SMS failed [{$type}] Order#{$order->order_number}: " . $e->getMessage());
            }
        }

        if ($isEmailEnabled && !empty($customer->email)) {
            try {
                $dbTemplate = EmailTemplate::where('type', $type)->where('is_active', true)->first();
                if ($dbTemplate && !empty($dbTemplate->body)) {
                    $subject = str_replace(array_keys($replacements), array_values($replacements), $dbTemplate->subject);
                    $body = str_replace(array_keys($replacements), array_values($replacements), $dbTemplate->body);
                } else {
                    $content = CommunicationEmailContent::getContent($type, $data);
                    $subject = $content['subject'];
                    $body = $content['body'];
                }
                $this->sendEmail($order, $customer->email, $type, $subject, $body);
            } catch (\Throwable $e) {
                Log::channel('communications')->error("CommunicationService Email failed [{$type}] Order#{$order->order_number}: " . $e->getMessage());
            }
        }
    }

    protected function sendSms(Order $order, string $phone, string $type, string $message)
    {
        $log = SmsLog::create([
            'order_id' => $order->id,
            'phone' => $phone,
            'type' => $type,
            'message' => $message,
            'status' => 'pending',
        ]);
        ProcessSms::dispatch($log);
    }

    public function sendDirectSMS(string $phone, string $message)
    {
        $gateway = Setting::get('sms_gateway', 'mock');
        $apiKey = Setting::get('sms_api_key');

        if ($gateway === 'mock' || !$apiKey) {
            Log::info("MOCK DIRECT SMS TO {$phone}: {$message}");
            return true;
        }

        try {
            $formattedPhone = ltrim($phone, '+0');
            if (strlen($formattedPhone) == 9) {
                $formattedPhone = '94' . $formattedPhone;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.textware.lk/sms/send', [
                'to' => $formattedPhone,
                'message' => $message,
                'sender' => Setting::get('sms_sender_id', 'LaundryZone'),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Direct SMS Error", ['phone' => $phone, 'error' => $e->getMessage()]);
            return false;
        }
    }

    protected function sendEmail(Order $order, string $email, string $type, string $subject, string $body)
    {
        $log = EmailLog::create([
            'order_id' => $order->id,
            'email' => $email,
            'type' => $type,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
        ]);
        ProcessEmail::dispatch($log);
    }
}
