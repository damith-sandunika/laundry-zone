<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin (no branch restriction)
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@laundry.com',
            'phone' => '0771234567',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'branch_id' => null,
            'is_active' => true,
        ]);

        // Branch Admin - Colombo
        User::create([
            'name' => 'Colombo Admin',
            'email' => 'colombo@laundry.com',
            'phone' => '0772345678',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'branch_id' => 1,
            'is_active' => true,
        ]);

        // Staff - Colombo
        User::create([
            'name' => 'Nimal Staff',
            'email' => 'nimal@laundry.com',
            'phone' => '0773456789',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'branch_id' => 1,
            'is_active' => true,
        ]);

        // Branch Admin - Kandy
        User::create([
            'name' => 'Kandy Admin',
            'email' => 'kandy@laundry.com',
            'phone' => '0774567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'branch_id' => 2,
            'is_active' => true,
        ]);
    }
}
