<?php

namespace Database\Seeders;

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
                'content' => "🧺 *Order Received!*\nHello [CustomerName], your laundry order *#[OrderNumber]* has been placed.\n\n📋 *Items:*\n[OrderDetails]\n\n💰 Total: *[TotalAmount]*\n📅 Pickup: *[PickupDate]*\n\nThank you! We'll notify you when ready. 😊",
                'available_tags' => json_encode(['[CustomerName]', '[OrderNumber]', '[TotalAmount]', '[PickupDate]', '[PickupTime]', '[OrderDetails]']),
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
            ],
            [
                'type' => 'low_stock_alert',
                'name' => 'Low Stock Alert',
                'content' => "⚠️ *LOW STOCK ALERT*\n━━━━━━━━━━━━━━━━━━━━\n🏢 Branch: [BranchName]\n📦 Item: [ItemName]\n📉 Current Stock: [CurrentStock]\n━━━━━━━━━━━━━━━━━━━━\nPlease restock immediately.",
                'available_tags' => json_encode(['[BranchName]', '[ItemName]', '[CurrentStock]']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('whatsapp_templates')->updateOrInsert(
                ['type' => $template['type']],
                $template
            );
        }
    }
}
