<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add petty_cash_balance to branches
        if (!Schema::hasColumn('branches', 'petty_cash_balance')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->decimal('petty_cash_balance', 10, 2)->default(0.00)->after('cash_balance');
            });
        }

        // Create petty_cash_topups table
        if (!Schema::hasTable('petty_cash_topups')) {
            Schema::create('petty_cash_topups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Who topped up
                $table->decimal('amount', 10, 2);
                $table->string('reference_note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_topups');
        
        if (Schema::hasColumn('branches', 'petty_cash_balance')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropColumn('petty_cash_balance');
            });
        }
    }
};
