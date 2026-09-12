<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employee_profiles', 'vehicle_number')) {
            Schema::table('employee_profiles', function (Blueprint $table) {
                $table->string('vehicle_number')->nullable()->after('emergency_contact');
            });
        }

        // Insert default rider_assigned template into sms_templates if missing
        if (Schema::hasTable('sms_templates')) {
            $smsExists = DB::table('sms_templates')->where('type', 'rider_assigned')->exists();
            if (!$smsExists) {
                DB::table('sms_templates')->insert([
                    'type' => 'rider_assigned',
                    'name' => 'Delivery Rider Assigned',
                    'message' => 'Hi {customer_name}, your order #{order_id} has been assigned to our rider {rider_name} (Phone: {rider_phone}, Vehicle: {rider_vehicle_number}). - 365laundry.lk',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Insert default rider_assigned template into email_templates if missing
        if (Schema::hasTable('email_templates')) {
            $emailExists = DB::table('email_templates')->where('type', 'rider_assigned')->exists();
            if (!$emailExists) {
                DB::table('email_templates')->insert([
                    'type' => 'rider_assigned',
                    'name' => 'Delivery Rider Assigned',
                    'subject' => 'Delivery Rider Assigned for Order #{order_id}',
                    'body' => '<p>Hi <strong>{customer_name}</strong>,</p><p>Your order <strong>#{order_id}</strong> has been assigned to our rider <strong>{rider_name}</strong>.</p><p>Rider Contact: <strong>{rider_phone}</strong><br>Vehicle Number: <strong>{rider_vehicle_number}</strong></p><p>Thank you for choosing 365laundry.lk!</p>',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Insert default rider_assigned template into whatsapp_templates if missing
        if (Schema::hasTable('whatsapp_templates')) {
            $waExists = DB::table('whatsapp_templates')->where('type', 'rider_assigned')->exists();
            if (!$waExists) {
                DB::table('whatsapp_templates')->insert([
                    'type' => 'rider_assigned',
                    'name' => 'Delivery Rider Assigned',
                    'meta_template_name' => 'rider_assigned_notification',
                    'language_code' => 'en',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_profiles', 'vehicle_number')) {
            Schema::table('employee_profiles', function (Blueprint $table) {
                $table->dropColumn('vehicle_number');
            });
        }

        if (Schema::hasTable('sms_templates')) {
            DB::table('sms_templates')->where('type', 'rider_assigned')->delete();
        }
        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->where('type', 'rider_assigned')->delete();
        }
        if (Schema::hasTable('whatsapp_templates')) {
            DB::table('whatsapp_templates')->where('type', 'rider_assigned')->delete();
        }
    }
};
