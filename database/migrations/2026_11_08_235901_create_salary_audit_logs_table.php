<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type'); // App\Models\SalaryAdvance or App\Models\Expense
            $table->unsignedBigInteger('auditable_id');
            $table->string('action'); // created, edited, deleted
            $table->decimal('old_amount', 12, 2)->nullable();
            $table->decimal('new_amount', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_audit_logs');
    }
};
