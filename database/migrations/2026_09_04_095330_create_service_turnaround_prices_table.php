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
        Schema::create('service_turnaround_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service_types')->cascadeOnDelete(); // Note: services are in service_types table
            $table->foreignId('turnaround_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('flat_extra_price', 10, 2)->default(0); // Optional flat override per service
            $table->timestamps();
            
            $table->unique(['service_id', 'turnaround_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_turnaround_prices');
    }
};
