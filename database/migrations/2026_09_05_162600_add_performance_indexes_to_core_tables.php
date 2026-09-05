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
        $this->addIndexSafe('orders', 'status');
        $this->addIndexSafe('orders', 'payment_status');
        $this->addIndexSafe('orders', 'created_at');
        $this->addIndexSafe('orders', 'due_date');
        
        $this->addIndexSafe('customers', 'phone');
        
        $this->addIndexSafe('delivery_appointments', 'status');
        
        $this->addIndexSafe('expenses', 'branch_id');
        $this->addIndexSafe('expenses', 'expense_date');
        
        $this->addIndexSafe('payment_transactions', 'created_at');
        
        $this->addIndexSafe('bank_deposits', 'branch_id');
        $this->addIndexSafe('bank_deposits', 'deposited_at');
    }

    private function addIndexSafe($table, $column) {
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        } catch (\Exception $e) {
            // Index likely already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_deposits', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['deposited_at']);
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['expense_date']);
        });

        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['due_date']);
        });
    }
};
