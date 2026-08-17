<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // cash, card, cod, koko, bank, online
            $table->string('display_name')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
