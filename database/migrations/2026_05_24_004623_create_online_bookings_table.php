<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('customer_address')->nullable();
            $table->text('service_description'); // what they want washed
            $table->date('preferred_date');
            $table->enum('status', ['pending', 'confirmed', 'converted', 'cancelled'])->default('pending');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null'); // after converted to order
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_bookings');
    }
};
