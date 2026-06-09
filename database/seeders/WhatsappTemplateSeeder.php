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
                'content' => "Hello [CustomerName],\n\nYour laundry order #[OrderNumber] has been received successfully. Your total bill is [TotalAmount].\nExpected Pickup: [PickupDate]\n\nThank you for choosing us!",
                'available_tags' => json_encode(['[CustomerName]', '[OrderNumber]', '[TotalAmount]', '[PickupDate]']),
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
            ],
            [
                'type' => 'shift_open',
                'name' => 'Shift Open Notification',
                'content' => "*[BranchName] - Shift Opened* 🟢\n━━━━━━━━━━━━━━━━━━━━\n📅 Date: [Date]\n🕐 Time: [Time]\n👤 Opened By: [OpenedBy]\n━━━━━━━━━━━━━━━━━━━━\n💵 *Opening Balance: Rs.[OpeningBalance]*\n━━━━━━━━━━━━━━━━━━━━\n_Have a great day! - SmartLaundry System_",
                'available_tags' => json_encode(['[BranchName]', '[Date]', '[Time]', '[OpenedBy]', '[OpeningBalance]']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'shift_close',
                'name' => 'End of Day Report',
                'content' => "*[BranchName] - End of Day Report* 🔴\n━━━━━━━━━━━━━━━━━━━━\n📅 Date: [Date]\n🕐 Shift: [ShiftTime]\n━━━━━━━━━━━━━━━━━━━━\n📦 Total Orders: [TotalOrders]\n✅ Delivered: [DeliveredCount]\n⏳ Pending: [PendingCount]\n━━━━━━━━━━━━━━━━━━━━\n[ServiceBreakdown]\n💰 Sales Collected: Rs.[SalesCollected]\n📤 Expenses: Rs.[Expenses]\n🏦 Opening Balance: Rs.[OpeningBalance]\n💵 *Final Cash Count: Rs.[FinalCash]*\n[InventoryConsumed]\n[StockRemaining]\n━━━━━━━━━━━━━━━━━━━━\n_Powered by SmartLaundry System_",
                'available_tags' => json_encode(['[BranchName]', '[Date]', '[ShiftTime]', '[TotalOrders]', '[DeliveredCount]', '[PendingCount]', '[ServiceBreakdown]', '[SalesCollected]', '[Expenses]', '[OpeningBalance]', '[FinalCash]', '[InventoryConsumed]', '[StockRemaining]']),
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
