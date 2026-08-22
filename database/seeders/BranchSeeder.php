<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::insert([
            [
                'name' => 'Main Branch - Colombo',
                'address' => 'No. 45, Main Street, Colombo 03',
                'phone' => '0112345678',
                'whatsapp_number' => '94771234567',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Branch 2 - Kandy',
                'address' => 'No. 12, Peradeniya Road, Kandy',
                'phone' => '0812345678',
                'whatsapp_number' => '94761234567',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
