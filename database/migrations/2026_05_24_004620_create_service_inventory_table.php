<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Links service types to inventory items
        // e.g. "Washing" uses 50ml of Detergent per item
        Schema::create('service_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->decimal('qty_per_use', 10, 2)->default(0); // how much used per order item
            $table->enum('deduct_at_stage', ['washing', 'drying'])->default('washing');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_inventory');
    }
};
