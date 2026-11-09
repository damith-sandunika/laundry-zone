<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('customer_type', ['walkin', 'corporate', 'online'])
                ->default('walkin')
                ->after('customer_mark')
                ->index();
        });

        // Populate existing customers based on is_corporate boolean
        DB::statement("UPDATE `customers` SET `customer_type` = IF(`is_corporate` = 1, 'corporate', 'walkin')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['customer_type']);
            $table->dropColumn('customer_type');
        });
    }
};
