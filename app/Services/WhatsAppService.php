<?php

namespace App\Services;

use App\Models\WhatsappLog;
use App\Models\Order;
use App\Models\DeliveryAppointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WhatsAppService
{
    private string $apiUrl;
    private string $phoneNumberId;
    private string $accessToken;
    private bool $enabled;

    public function __construct()
    {
        $this->apiUrl        = config('whatsapp.api_url');
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->accessToken   = config('whatsapp.access_token');
        $this->enabled       = config('whatsapp.enabled', false);
    }

    // ─── Send a plain text WhatsApp message ───
    public function sendText(string $phone, string $message, ?int $orderId = null, ?int $customerId = null): bool
    {
        $phone = $this->formatPhone($phone);

        // Always log to DB first (even if disabled)
        $log = WhatsappLog::create([
            'order_id'     => $orderId,
            'customer_id'  => $customerId,
            'phone'        => $phone,
            'message_type' => 'text',
            'message'      => $message,
            'status'       => 'pending',
        ]);

        if (!$this->enabled || empty($this->accessToken) || $this->accessToken === 'your_permanent_access_token_here') {
            $log->update(['status' => 'disabled']);
            return false;
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => ['preview_url' => false, 'body' => $message],
                ]);

            if ($response->successful()) {
                $log->update(['status' => 'sent', 'wa_message_id' => $response->json('messages.0.id')]);
                return true;
            }

            $log->update(['status' => 'failed', 'error' => $response->body()]);
            Log::error('WhatsApp send failed', ['response' => $response->body()]);
            return false;

        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            Log::error('WhatsApp exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ─── Send message with an image (QR Code) ───
    public function sendWithQrCode(string $phone, string $message, string $qrContent, ?int $orderId = null, ?int $customerId = null): bool
    {
        // Generate QR code and save temporarily
        $qrPath = $this->generateQrCodeImage($qrContent, $orderId ?? uniqid());

        $phone = $this->formatPhone($phone);

        $log = WhatsappLog::create([
            'order_id'     => $orderId,
            'customer_id'  => $customerId,
            'phone'        => $phone,
            'message_type' => 'qr_code',
            'message'      => $message,
            'status'       => 'pending',
        ]);

        if (!$this->enabled || empty($this->accessToken) || $this->accessToken === 'your_permanent_access_token_here') {
            $log->update(['status' => 'disabled']);
            return false;
        }

        try {
            // Step 1: Upload image to WhatsApp Media API
            $mediaId = $this->uploadMedia($qrPath);

            if (!$mediaId) {
                // Fallback: send as text if media upload fails
                return $this->sendText($phone, $message . "\n\n🔗 Order QR: " . $qrContent, $orderId, $customerId);
            }

            // Step 2: Send image with caption
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $phone,
                    'type'              => 'image',
                    'image'             => [
                        'id'      => $mediaId,
                        'caption' => $message,
                    ],
                ]);

            // Cleanup temp file
            @unlink($qrPath);

            if ($response->successful()) {
                $log->update(['status' => 'sent', 'wa_message_id' => $response->json('messages.0.id')]);
                return true;
            }

            $log->update(['status' => 'failed', 'error' => $response->body()]);
            return false;

        } catch (\Exception $e) {
            @unlink($qrPath ?? null);
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            Log::error('WhatsApp QR send exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ─── Generate QR Code PNG image and return its path ───
    public function generateQrCodeImage(string $content, $identifier = 'order'): string
    {
        $dir = public_path('qrcodes');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = "qr_{$identifier}_" . time() . ".png";
        $path     = $dir . DIRECTORY_SEPARATOR . $filename;

        // Use external API since Imagick is not installed locally
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($content) . "&ecc=H&margin=2";
        
        try {
            $imageContent = file_get_contents($qrUrl);
            if ($imageContent) {
                file_put_contents($path, $imageContent);
            } else {
                throw new \Exception("Failed to download QR code");
            }
        } catch (\Exception $e) {
            // Fallback: If external API fails, we could just create an empty file or rethrow
            throw $e;
        }

        return $path;
    }

    // ─── Get public URL for QR code ───
    public function getQrCodeUrl(string $content, $identifier = 'order'): string
    {
        $path     = $this->generateQrCodeImage($content, $identifier);
        $filename = basename($path);
        return asset("qrcodes/{$filename}");
    }

    // ─── Upload media to WhatsApp and return media_id ───
    private function uploadMedia(string $filePath): ?string
    {
        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(30)
                ->attach('file', file_get_contents($filePath), basename($filePath), ['Content-Type' => 'image/png'])
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/media", [
                    'messaging_product' => 'whatsapp',
                    'type'              => 'image/png',
                ]);

            if ($response->successful()) {
                return $response->json('id');
            }

            Log::error('WhatsApp media upload failed', ['response' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('WhatsApp media upload exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ─── Order Received notification with QR ───
    public function notifyOrderReceived(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) return false;

        $branch    = $order->branch;
        $qrContent = url("/orders/{$order->id}/track");

        $message = "🧺 *Order Received!*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Order #: *{$order->id}*\n";
        $message .= "🏪 Branch: *{$branch->name}*\n";
        $message .= "👤 Name: *{$customer->name}*\n";
        if ($order->due_date) {
            $message .= "📅 Ready By: *" . \Carbon\Carbon::parse($order->due_date)->format('d M Y') . "*\n";
        }
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Scan the QR code to track your order status_";

        return $this->sendWithQrCode($customer->phone, $message, $qrContent, $order->id, $customer->id);
    }

    // ─── Order Ready notification ───
    public function notifyOrderReady(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) return false;

        $message = "✅ *Your Laundry is Ready!*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Order #: *{$order->id}*\n";
        $message .= "🏪 Branch: *{$order->branch->name}*\n";
        $message .= "💰 Total: *Rs." . number_format($order->total, 2) . "*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Please collect your laundry at your earliest convenience._\n";
        $message .= "_Thank you for choosing us! 🙏_";

        return $this->sendText($customer->phone, $message, $order->id, $customer->id);
    }

    // ─── Delivery Appointment confirmed notification ───
    public function notifyDeliveryConfirmed(DeliveryAppointment $appointment): bool
    {
        if (!$appointment->contact_phone) return false;

        $message = "🚚 *Pickup Confirmed!*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Booking #: *{$appointment->id}*\n";
        $message .= "📅 Scheduled: *" . $appointment->pickup_scheduled_at?->format('d M Y, H:i') . "*\n";
        $message .= "📍 From: *{$appointment->pickup_address}*\n";
        $message .= "🏪 Branch: *{$appointment->branch->name}*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Our rider will contact you before arriving. Thank you! 🙏_";

        return $this->sendText($appointment->contact_phone, $message);
    }

    // ─── Rider assigned notification ───
    public function notifyRiderAssigned(DeliveryAppointment $appointment, \App\Models\User $rider): bool
    {
        if (!$appointment->contact_phone) return false;

        $message = "🏍️ *Rider On The Way!*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Booking #: *{$appointment->id}*\n";
        $message .= "👤 Rider: *{$rider->name}*\n";
        $message .= "📞 Contact: *{$rider->phone}*\n";
        $message .= "📍 Pickup From: *{$appointment->pickup_address}*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Your rider is heading to you. Please be available. 🙏_";

        return $this->sendText($appointment->contact_phone, $message);
    }
    // ─── Booking Received (Customer Website) ───
    public function notifyBookingReceived(DeliveryAppointment $appointment): bool
    {
        if (!$appointment->contact_phone) return false;

        $message = "✅ *Booking Received!*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Ref #: *{$appointment->id}*\n";
        $message .= "📅 Requested: *" . $appointment->pickup_scheduled_at?->format('d M Y, H:i') . "*\n";
        $message .= "📍 From: *{$appointment->pickup_address}*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_We will confirm your pickup shortly. Thank you! 🙏_";

        return $this->sendText($appointment->contact_phone, $message);
    }

    // ─── Booking Cancelled ───
    public function notifyBookingCancelled(DeliveryAppointment $appointment, string $reason = ''): bool
    {
        if (!$appointment->contact_phone) return false;

        $message = "❌ *Booking Cancelled*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Ref #: *{$appointment->id}*\n";
        if ($reason) {
            $message .= "ℹ️ Reason: *{$reason}*\n";
        }
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_We apologize for any inconvenience. Please contact us for details._";

        return $this->sendText($appointment->contact_phone, $message);
    }

    // ─── Rider On Way / Arriving ───
    public function notifyRiderArriving(DeliveryAppointment $appointment, \App\Models\User $rider): bool
    {
        if (!$appointment->contact_phone) return false;

        $message = "🚗 *Rider is Arriving!*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Ref #: *{$appointment->id}*\n";
        $message .= "👤 Rider: *{$rider->name}*\n";
        $message .= "📞 Contact: *{$rider->phone}*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Our rider is heading to your location right now. Please be ready! 🙏_";

        return $this->sendText($appointment->contact_phone, $message);
    }

    // ─── Order Created by Rider (Picked Up) ───
    public function notifyOrderCreatedByRider(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) return false;

        $qrContent = url("/orders/{$order->id}/track");

        $message = "📦 *Laundry Picked Up!*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 Order #: *{$order->id}*\n";
        $message .= "👤 Name: *{$customer->name}*\n";
        $message .= "👕 Items: *" . $order->items->sum('quantity') . " items*\n";
        $message .= "💰 Total: *Rs." . number_format($order->total, 2) . "*\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "_Scan the QR code to track your order status_";

        return $this->sendWithQrCode($customer->phone, $message, $qrContent, $order->id, $customer->id);
    }
    // ─── Format phone number (ensure it starts with country code) ───
    private function formatPhone(string $phone): string
    {
        // Remove all non-digits
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Sri Lanka: if starts with 0, replace with 94
        if (str_starts_with($phone, '0')) {
            $phone = '94' . substr($phone, 1);
        }

        // If no country code prefix, add 94 (LK default)
        if (!str_starts_with($phone, '94') && strlen($phone) === 9) {
            $phone = '94' . $phone;
        }

        return $phone;
    }

    // ─── Check if WhatsApp is configured ───
    public function isConfigured(): bool
    {
        return $this->enabled
            && !empty($this->accessToken)
            && $this->accessToken !== 'your_permanent_access_token_here'
            && !empty($this->phoneNumberId)
            && $this->phoneNumberId !== 'your_phone_number_id_here';
    }

    // ─── Get status (for settings page) ───
    public function getStatus(): array
    {
        return [
            'connected' => $this->isConfigured(),
            'enabled'   => $this->enabled,
            'provider'  => 'WhatsApp Cloud API (Meta)',
        ];
    }
}
