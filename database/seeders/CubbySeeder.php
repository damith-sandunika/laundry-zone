<?php

namespace Database\Seeders;

use App\Models\Cubby;
use Illuminate\Database\Seeder;

class CubbySeeder extends Seeder
{
    public function run(): void
    {
        // 10 cubbies for Branch 1 (Colombo)
        for ($i = 1; $i <= 10; $i++) {
            Cubby::create([
                'branch_id' => 1,
                'name' => 'Cubby '.$i,
                'status' => 'empty',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6 cubbies for Branch 2 (Kandy)
        for ($i = 1; $i <= 6; $i++) {
            Cubby::create([
                'branch_id' => 2,
                'name' => 'Cubby '.$i,
                'status' => 'empty',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
