<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsappTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'type' => 'order_confirmation',
                'name' => 'Order Confirmation Message',
                'content' => "Hello [CustomerName],\n\nYour laundry order #[OrderNumber] has been received successfully. Your total bill is [TotalAmount].\n\nThank you for choosing us!",
                'available_tags' => json_encode(['[CustomerName]', '[OrderNumber]', '[TotalAmount]']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'order_ready',
                'name' => 'Order Ready Message',
                'content' => "Hello [CustomerName],\n\nYour laundry order #[OrderNumber] is now ready for pickup! It is securely stored in [CubbetName]. Total due: [TotalAmount].\n\nSee you soon!",
                'available_tags' => json_encode(['[CustomerName]', '[OrderNumber]', '[TotalAmount]', '[CubbetName]']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'order_delivered',
                'name' => 'Order Delivered (Thank You) Message',
                'content' => "Hello [CustomerName],\n\nThank you for picking up your order #[OrderNumber]. We hope you are satisfied with our service.\n\nHave a great day!",
                'available_tags' => json_encode(['[CustomerName]', '[OrderNumber]', '[TotalAmount]']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($templates as $template) {
            DB::table('whatsapp_templates')->updateOrInsert(
                ['type' => $template['type']],
                $template
            );
        }
    }
}
