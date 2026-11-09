<?php

namespace Database\Migrations;

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
        $conn = Schema::connection('landlord');

        if (!$conn->hasTable('platform_sms_packages')) {
            $conn->create('platform_sms_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('sms_count');
                $table->decimal('price', 10, 2);
                $table->decimal('rate_per_sms', 8, 2)->default(2.00);
                $table->string('badge')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('order_index')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('platform_sms_packages');
    }
};

