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
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable();

            // Edit tracking
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->string('original_payment_method', 50)->nullable();
            $table->text('edit_reason')->nullable();
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

