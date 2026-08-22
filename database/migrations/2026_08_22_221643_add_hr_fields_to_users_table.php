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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nic')->nullable()->after('phone');
            $table->date('dob')->nullable()->after('nic');
            $table->text('address')->nullable()->after('dob');
            $table->string('emergency_contact')->nullable()->after('address');
            $table->decimal('basic_salary', 10, 2)->nullable()->after('emergency_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nic', 'dob', 'address', 'emergency_contact', 'basic_salary']);
        });
    }
};
