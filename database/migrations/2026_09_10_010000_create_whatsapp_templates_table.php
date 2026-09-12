<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('whatsapp_templates')) {
            Schema::create('whatsapp_templates', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique(); 
                $table->string('name');
                $table->string('meta_template_name')->nullable();
                $table->string('language_code')->default('en');
                $table->boolean('is_active')->default(true);
                $table->text('content')->nullable();
                $table->timestamps();
            });

            // Seed initial templates
            $defaults = [
                ['type' => 'pos_walkin_placed', 'name' => 'Walk-in Order Placed', 'content' => 'Hi {customer_name}, your order #{order_id} has been placed successfully. We will let you know once it is completed.'],
                ['type' => 'pos_walkin_ready', 'name' => 'Walk-in Order Ready', 'content' => 'Hi {customer_name}, great news! Your order #{order_id} is washed and ready to be picked up. Thank you! - 365laundry.lk'],
                ['type' => 'pos_walkin_completed', 'name' => 'Walk-in Order Completed', 'content' => 'Hi {customer_name}, your order #{order_id} is fully completed. Total: {total}. Payment: {payment_status}. Thank you! - 365laundry.lk'],
                ['type' => 'collection_order_placed', 'name' => 'Collection Order Placed', 'content' => 'Hi {customer_name}, your order #{order_id} has been placed at our collection branch and will be sent for processing.'],
                ['type' => 'collection_order_ready', 'name' => 'Collection Order Ready for Pickup', 'content' => 'Hi {customer_name}, great news! Your order #{order_id} is completed and ready for pickup.'],
                ['type' => 'collection_order_completed', 'name' => 'Collection Order Completed', 'content' => 'Hi {customer_name}, your order #{order_id} is completed and settled. Thank you for choosing 365laundry.lk!'],
                ['type' => 'delivery_order_confirmed', 'name' => 'Delivery Order Confirmed', 'content' => 'Hi {customer_name}, your delivery order #{order_id} has been approved and confirmed. We will process it shortly. Track your order here: {tracking_link}'],
                ['type' => 'delivery_order_cancelled', 'name' => 'Delivery Order Cancelled', 'content' => 'Hi {customer_name}, unfortunately, your delivery order #{order_id} has been cancelled. Please contact us for details. Track your order here: {tracking_link}'],
                ['type' => 'delivery_order_ready', 'name' => 'Delivery Order Ready for Dispatch', 'content' => 'Hi {customer_name}, your delivery order #{order_id} is washed and ready to be dispatched to you! Track your order here: {tracking_link}'],
                ['type' => 'delivery_completed', 'name' => 'Delivery Order Completed', 'content' => 'Hi {customer_name}, your delivery order #{order_id} has been successfully delivered. Thank you for using 365laundry.lk! Track your order here: {tracking_link}'],
                ['type' => 'bill_finalized', 'name' => 'Payment Request', 'content' => 'Hi {customer_name}, the bill for order #{order_id} is finalized. Total: {total}. Requested Amount: {requested_amount}. Please arrange the payment.'],
                ['type' => 'payment_received', 'name' => 'Payment Received', 'content' => 'Hi {customer_name}, we have received and approved a payment for your order #{order_id}. Total: {total}. Status: {payment_status}.'],
                ['type' => 'rider_assigned', 'name' => 'Delivery Rider Assigned', 'content' => 'Hi {customer_name}, your order #{order_id} has been assigned to our rider {rider_name} (Phone: {rider_phone}, Vehicle: {rider_vehicle_number}). - 365laundry.lk'],
            ];

            foreach ($defaults as $default) {
                DB::table('whatsapp_templates')->insert([
                    'type' => $default['type'],
                    'name' => $default['name'],
                    'content' => $default['content'],
                    'is_active' => true,
                    'language_code' => 'en',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
