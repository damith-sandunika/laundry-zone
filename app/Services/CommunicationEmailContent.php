<?php

namespace App\Services;

use App\Models\Setting;

class CommunicationEmailContent
{
    public static function getContent(string $type, array $data): array
    {
        $name    = $data['customer_name'];
        $orderId = $data['order_id'];
        $total   = $data['total'];
        $due     = $data['amount_due'];
        $payment = $data['payment_status'];
        $company = Setting::get('company_name', 'Laundry Zone');

        $templates = [
            'pos_walkin_placed' => [
                'subject' => "Walk-in Order Placed",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been placed successfully. We will let you know once it is completed. - {$company}</p>",
            ],
            'pos_walkin_ready' => [
                'subject' => "Walk-in Order Ready",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Great news! Your walk-in order <strong>#{$orderId}</strong> is washed and ready to be picked up. Thank you! - {$company}</p>",
            ],
            'pos_walkin_completed' => [
                'subject' => "Walk-in Order Completed",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> is fully completed and settled. Thank you for your business! - {$company}</p><table style='width:100%;border-collapse:collapse;margin:16px 0;'><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order Number</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr></table>",
            ],
            'collection_order_placed' => [
                'subject' => "Collection Order Placed",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been placed at our collection branch and will be sent for processing.</p>",
            ],
            'collection_order_ready' => [
                'subject' => "Collection Order Ready for Pickup/Delivery",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Great news! Your order <strong>#{$orderId}</strong> is completed and ready for pickup or delivery.</p>",
            ],
            'collection_order_completed' => [
                'subject' => "Collection Order Completed",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> is completed and settled. Thank you for choosing {$company}!</p>",
            ],
            'delivery_order_confirmed' => [
                'subject' => "Delivery Order Confirmed",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your delivery order <strong>#{$orderId}</strong> has been approved and confirmed. We will process it shortly.</p>",
            ],
            'delivery_order_cancelled' => [
                'subject' => "Delivery Order Cancelled",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Unfortunately, your delivery order <strong>#{$orderId}</strong> has been cancelled. Please contact us for more information.</p>",
            ],
            'delivery_order_ready' => [
                'subject' => "Delivery Order Ready for Dispatch",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your delivery order <strong>#{$orderId}</strong> is washed and ready to be dispatched to you!</p>",
            ],
            'delivery_completed' => [
                'subject' => "Delivery Order Completed",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your delivery order <strong>#{$orderId}</strong> has been successfully delivered. Thank you for using {$company}!</p><table style='width:100%;border-collapse:collapse;margin:16px 0;'><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order Number</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr></table>",
            ],
            'bill_finalized' => [
                'subject' => "Payment Request for Order #{$orderId}",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>The bill for your order <strong>#{$orderId}</strong> has been finalized. The total amount is <strong>{$total}</strong> and the amount due is <strong>{$due}</strong>. Please arrange the payment. Thank you! - {$company}</p>",
            ],
            'payment_received' => [
                'subject' => "Payment Received for Order #{$orderId}",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>We have successfully received and approved a payment for your order <strong>#{$orderId}</strong>.</p><table style='width:100%;border-collapse:collapse;margin:16px 0;'><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Order Number</td><td style='padding:8px 12px;background:#f8fafc;'>#{$orderId}</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Total Amount</td><td style='padding:8px 12px;background:#f8fafc;'>{$total}</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Payment Status</td><td style='padding:8px 12px;background:#f8fafc;'>{$payment}</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Amount Due</td><td style='padding:8px 12px;background:#f8fafc;'>{$due}</td></tr></table><p style='margin:16px 0 0;'>Thank you! - {$company}</p>",
            ],
            'rider_assigned' => [
                'subject' => "Delivery Rider Assigned for Order #{$orderId}",
                'body'    => "<p style='font-size:16px;margin:0 0 12px;'>Hi <strong>{$name}</strong>,</p><p style='margin:0 0 16px;'>Your order <strong>#{$orderId}</strong> has been assigned to our delivery rider.</p><table style='width:100%;border-collapse:collapse;margin:16px 0;'><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Rider Name</td><td style='padding:8px 12px;background:#f8fafc;'>" . ($data['rider_name'] ?? '') . "</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Rider Phone</td><td style='padding:8px 12px;background:#f8fafc;'>" . ($data['rider_phone'] ?? '') . "</td></tr><tr><td style='padding:8px 12px;background:#f1f5f9;font-weight:700;'>Vehicle Number</td><td style='padding:8px 12px;background:#f8fafc;'>" . ($data['rider_vehicle_number'] ?? 'N/A') . "</td></tr></table><p style='margin:16px 0 0;'>Thank you! - {$company}</p>",
            ]
        ];

        return $templates[$type] ?? [
            'subject' => "Order #{$orderId} Update",
            'body'    => "<p>Hi {$name}, there is an update on order #{$orderId}.</p>"
        ];
    }
}
