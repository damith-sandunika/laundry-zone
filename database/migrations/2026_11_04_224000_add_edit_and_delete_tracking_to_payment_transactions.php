<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Soft delete support
            if (!Schema::hasColumn('payment_transactions', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('payment_transactions', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payment_transactions', 'delete_reason')) {
                $table->text('delete_reason')->nullable();
            }

            // Edit tracking
            if (!Schema::hasColumn('payment_transactions', 'is_edited')) {
                $table->boolean('is_edited')->default(false);
            }
            if (!Schema::hasColumn('payment_transactions', 'edited_at')) {
                $table->timestamp('edited_at')->nullable();
            }
            if (!Schema::hasColumn('payment_transactions', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payment_transactions', 'original_amount')) {
                $table->decimal('original_amount', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('payment_transactions', 'original_payment_method')) {
                $table->string('original_payment_method', 50)->nullable();
            }
            if (!Schema::hasColumn('payment_transactions', 'edit_reason')) {
                $table->text('edit_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropConstrainedForeignId('edited_by');
            $table->dropColumn([
                'delete_reason',
                'is_edited',
                'edited_at',
                'original_amount',
                'original_payment_method',
                'edit_reason',
            ]);
        });
    }
};

