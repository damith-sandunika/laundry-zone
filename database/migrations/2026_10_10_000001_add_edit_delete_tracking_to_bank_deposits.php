<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_deposits', function (Blueprint $table) {
            // Soft delete support
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('delete_reason')->nullable();

            // Edit tracking
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('edit_notes')->nullable();

            // Store original amount before edit (for reverting cash balance)
            $table->decimal('original_amount', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bank_deposits', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropColumn(['delete_reason', 'is_edited', 'edited_at', 'edit_notes', 'original_amount']);
            $table->dropConstrainedForeignId('edited_by');
        });
    }
};
