<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = [
            ['name' => 'PickMe', 'is_system' => true],
            ['name' => 'salary', 'is_system' => true],
            ['name' => 'supplier', 'is_system' => true],
            ['name' => 'rent', 'is_system' => false],
            ['name' => 'utilities', 'is_system' => false],
            ['name' => 'other', 'is_system' => false],
        ];

        foreach ($categories as $cat) {
            \App\Models\ExpenseCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['is_system' => $cat['is_system']]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not removing them in down() as they might have associated expenses
    }
};
