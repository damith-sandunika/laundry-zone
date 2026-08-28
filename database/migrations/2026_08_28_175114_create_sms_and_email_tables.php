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
        // Drop old whatsapp tables
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('whatsapp_templates');

        // Create sms_templates
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // e.g. order_ready, order_delivered
            $table->string('name');
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create sms_logs
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone');
            $table->string('type');
            $table->text('message');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->text('response')->nullable();
            $table->timestamps();
        });

        // Create email_templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); 
            $table->string('name');
            $table->string('subject');
            $table->text('body'); // HTML content
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create email_logs
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('type');
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_and_email_tables');
    }
};
