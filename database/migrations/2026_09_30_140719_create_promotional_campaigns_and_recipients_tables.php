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
        Schema::create('promotional_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('channel', ['sms', 'email', 'whatsapp'])->default('sms');
            $table->enum('audience_type', ['all', 'top_customers', 'inactive', 'branch_specific', 'birthday']);
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->json('audience_conditions')->nullable();
            
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('recipients_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotional_campaign_id')->constrained('promotional_campaigns')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['promotional_campaign_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('promotional_campaigns');
    }
};
