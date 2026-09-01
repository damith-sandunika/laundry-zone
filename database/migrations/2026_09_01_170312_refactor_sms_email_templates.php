<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove old templates
        DB::table('sms_templates')->truncate();
        DB::table('email_templates')->truncate();

        $templates = [
            [
                'type' => 'pos_walkin_completed',
                'name' => 'Walk-in Order Completed',
                'description' => 'Sent when a walk-in POS order is handed over to the customer.',
                'message' => 'Hi {customer_name}, your order #{order_id} is fully completed and settled. Thank you for your business! - RAPID Laundromat',
            ],
            [
                'type' => 'delivery_completed',
                'name' => 'Delivery Order Completed',
                'description' => 'Sent when a delivery order is successfully delivered to the customer.',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} is completed. Thank you for using RAPID Laundromat!',
            ],
            [
                'type' => 'collection_order_placed',
                'name' => 'Collection Order Placed',
                'description' => 'Sent when a new order is placed at a collection branch.',
                'message' => 'Hi {customer_name}, your order #{order_id} has been placed at our collection branch and will be sent for processing.',
            ],
            [
                'type' => 'collection_order_ready',
                'name' => 'Collection Order Ready for Pickup/Delivery',
                'description' => 'Sent when the order returns from the workshop and is ready at the collection branch.',
                'message' => 'Hi {customer_name}, great news! Your order #{order_id} is completed and ready for pickup or delivery.',
            ],
            [
                'type' => 'collection_order_completed',
                'name' => 'Collection Order Completed',
                'description' => 'Sent when a collection branch order is finally handed over to the customer.',
                'message' => 'Hi {customer_name}, your order #{order_id} is completed and settled. Thank you for choosing RAPID Laundromat!',
            ]
        ];

        $smsTemplates = [];
        $emailTemplates = [];

        foreach ($templates as $t) {
            $smsTemplates[] = [
                'type' => $t['type'],
                'name' => $t['name'],
                'message' => $t['message'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $emailTemplates[] = [
                'type' => $t['type'],
                'name' => $t['name'],
                'subject' => $t['name'],
                'body' => '<p>' . $t['message'] . '</p>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('sms_templates')->insert($smsTemplates);
        DB::table('email_templates')->insert($emailTemplates);
    }

    public function down(): void
    {
        // Just truncate in rollback
        DB::table('sms_templates')->truncate();
        DB::table('email_templates')->truncate();
    }
};
