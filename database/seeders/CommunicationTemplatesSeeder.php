<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class CommunicationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyName = Setting::get('company_name', 'Laundry Zone');

        $templates = [
            // Walk-in
            [
                'type' => 'pos_walkin_placed',
                'name' => 'Walk-in Order Placed',
                'message' => 'Hi {customer_name}, your order #{order_id} has been placed successfully. We will let you know once it is completed. - ' . $companyName,
            ],
            [
                'type' => 'pos_walkin_ready',
                'name' => 'Walk-in Order Ready',
                'message' => 'Hi {customer_name}, great news! Your walk-in order #{order_id} is washed and ready to be picked up. Thank you! - ' . $companyName,
            ],
            [
                'type' => 'pos_walkin_completed',
                'name' => 'Walk-in Order Completed',
                'message' => 'Hi {customer_name}, your order #{order_id} is fully completed and settled. Thank you for your business! - ' . $companyName,
            ],

            // Collection
            [
                'type' => 'collection_order_placed',
                'name' => 'Collection Order Placed',
                'message' => 'Hi {customer_name}, your order #{order_id} has been placed at our collection branch and will be sent for processing.',
            ],
            [
                'type' => 'collection_order_ready',
                'name' => 'Collection Order Ready for Pickup/Delivery',
                'message' => 'Hi {customer_name}, great news! Your order #{order_id} is completed and ready for pickup or delivery.',
            ],
            [
                'type' => 'collection_order_completed',
                'name' => 'Collection Order Completed',
                'message' => 'Hi {customer_name}, your order #{order_id} is completed and settled. Thank you for choosing ' . $companyName . '!',
            ],

            // Delivery
            [
                'type' => 'delivery_order_confirmed',
                'name' => 'Delivery Order Confirmed',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} has been approved and confirmed. We will process it shortly.',
            ],
            [
                'type' => 'delivery_order_cancelled',
                'name' => 'Delivery Order Cancelled',
                'message' => 'Hi {customer_name}, unfortunately, your delivery order #{order_id} has been cancelled. Please contact us for more information.',
            ],
            [
                'type' => 'delivery_order_ready',
                'name' => 'Delivery Order Ready for Dispatch',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} is washed and ready to be dispatched to you!',
            ],
            [
                'type' => 'delivery_completed',
                'name' => 'Delivery Order Completed',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} is completed. Thank you for using ' . $companyName . '!',
            ],
            [
                'type' => 'rider_assigned',
                'name' => 'Rider Assigned',
                'message' => 'Hi {customer_name}, your order #{order_id} has been assigned to our delivery rider {rider_name} ({rider_phone}). Vehicle: {rider_vehicle_number}. - ' . $companyName,
            ],

            // Payments
            [
                'type' => 'bill_finalized',
                'name' => 'Payment Request for Order #{order_id}',
                'message' => 'Hi {customer_name}, the bill for your order #{order_id} has been finalized. The total amount is {total} and the amount due is {amount_due}. Please arrange the payment. Thank you! - ' . $companyName,
            ],
            [
                'type' => 'payment_received',
                'name' => 'Payment Received for Order #{order_id}',
                'message' => 'Hi {customer_name}, we have successfully received and approved a payment for your order #{order_id}. Thank you! - ' . $companyName,
            ],

            // Promotional
            [
                'type' => 'bulk_promotion',
                'name' => 'General Bulk Promotion',
                'message' => 'Hi {customer_name}, enjoy our special offer! Use promo code {promo_code} to get {discount} off at {branch_name}. Valid until {expiry_date}. - ' . $companyName,
            ],
            [
                'type' => 'birthday_promotion',
                'name' => 'Birthday Wish & Offer',
                'message' => 'Happy Birthday {customer_name}! As a gift from ' . $companyName . ', enjoy {discount} off on your next order at {branch_name}. Code: {promo_code}. Have a great day!',
            ],
        ];

        foreach ($templates as $t) {
            // SMS
            DB::table('sms_templates')->updateOrInsert(
                ['type' => $t['type']],
                [
                    'name' => $t['name'],
                    'message' => $t['message'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Email
            DB::table('email_templates')->updateOrInsert(
                ['type' => $t['type']],
                [
                    'name' => $t['name'],
                    'subject' => $t['name'], // Note: {order_id} in name is fine for subject
                    'body' => '<p>' . nl2br(htmlspecialchars($t['message'])) . '</p>',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // WhatsApp (Assuming we just match the naming for now)
            $metaTemplateName = str_replace(' ', '_', strtolower($t['type']));

            DB::table('whatsapp_templates')->updateOrInsert(
                ['type' => $t['type']],
                [
                    'name' => $t['name'],
                    'meta_template_name' => $metaTemplateName,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
