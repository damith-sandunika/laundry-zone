<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            // Add wa_message_id for Cloud API message tracking
            $table->string('wa_message_id')->nullable()->after('status');
            // Rename error_message to error for consistency (if column exists)
            if (Schema::hasColumn('whatsapp_logs', 'error_message')) {
                $table->renameColumn('error_message', 'error');
            } else {
                $table->text('error')->nullable()->after('wa_message_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->dropColumnIfExists('wa_message_id');
        });
    }
};
