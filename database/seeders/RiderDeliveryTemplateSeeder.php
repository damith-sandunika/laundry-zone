<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiderDeliveryTemplateSeeder extends Seeder
{
    /**
     * Seed default SMS, Email, and WhatsApp templates for Rider Delivery assignments.
     */
    public function run(): void
    {
        // 1. SMS Template
        DB::table('sms_templates')->updateOrInsert(
            ['type' => 'rider_assigned'],
            [
                'name' => 'Delivery Rider Assigned',
                'message' => 'Hi {customer_name}, your order #{order_id} has been assigned to our rider {rider_name} (Phone: {rider_phone}, Vehicle: {rider_vehicle_number}). - Rapid Laundromat',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 2. Email Template
        DB::table('email_templates')->updateOrInsert(
            ['type' => 'rider_assigned'],
            [
                'name' => 'Delivery Rider Assigned',
                'subject' => 'Delivery Rider Assigned for Order #{order_id}',
                'body' => '<p>Hi <strong>{customer_name}</strong>,</p><p>Your order <strong>#{order_id}</strong> has been assigned to our rider <strong>{rider_name}</strong>.</p><p>Rider Contact: <strong>{rider_phone}</strong><br>Vehicle Number: <strong>{rider_vehicle_number}</strong></p><p>Thank you for choosing Rapid Laundromat!</p>',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 3. WhatsApp Template
        DB::table('whatsapp_templates')->updateOrInsert(
            ['type' => 'rider_assigned'],
            [
                'name' => 'Delivery Rider Assigned',
                'meta_template_name' => 'rider_assigned_notification',
                'language_code' => 'en',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
