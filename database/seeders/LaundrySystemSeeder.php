<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use App\Models\ServiceType;
use App\Models\ServiceCategory;

class LaundrySystemSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Branch::truncate();
        ServiceType::truncate();
        ServiceCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Branches
        $b1 = Branch::create(['name' => 'Colombo Main Branch', 'phone' => '0112345678', 'address' => 'Colombo 07', 'is_active' => true, 'is_delivery_enabled' => true]);
        $b2 = Branch::create(['name' => 'Nugegoda Branch', 'phone' => '0112345679', 'address' => 'Nugegoda', 'is_active' => true, 'is_delivery_enabled' => true]);
        $b3 = Branch::create(['name' => 'Malabe Branch', 'phone' => '0112345680', 'address' => 'Malabe', 'is_active' => true, 'is_delivery_enabled' => true]);

        // Create Super Admin
        User::create([
            'name' => 'Main Admin',
            'email' => 'admin@laundryzone.lk',
            'phone' => '0770000000',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'branch_id' => null,
            'is_active' => true,
        ]);

        // Create Branch Admins
        User::create(['name' => 'Colombo Manager', 'email' => 'colombo@laundryzone.lk', 'phone' => '0770000001', 'password' => Hash::make('password'), 'role' => 'admin', 'branch_id' => $b1->id, 'is_active' => true]);
        User::create(['name' => 'Nugegoda Manager', 'email' => 'nugegoda@laundryzone.lk', 'phone' => '0770000002', 'password' => Hash::make('password'), 'role' => 'admin', 'branch_id' => $b2->id, 'is_active' => true]);
        User::create(['name' => 'Malabe Manager', 'email' => 'malabe@laundryzone.lk', 'phone' => '0770000003', 'password' => Hash::make('password'), 'role' => 'admin', 'branch_id' => $b3->id, 'is_active' => true]);

        // Create Service Categories
        $cat1 = ServiceCategory::create(['name' => 'Wash & Fold', 'is_active' => true]);
        $cat2 = ServiceCategory::create(['name' => 'Wash & Iron', 'is_active' => true]);
        $cat3 = ServiceCategory::create(['name' => 'Dry Cleaning', 'is_active' => true]);

        // Create Service Types (Global, so branch_id = null)
        ServiceType::create(['name' => 'Normal Wash & Fold (1kg)', 'category_id' => $cat1->id, 'base_price' => 500, 'is_active' => true]);
        ServiceType::create(['name' => 'Wash & Iron (Shirt)', 'category_id' => $cat2->id, 'base_price' => 250, 'is_active' => true]);
        ServiceType::create(['name' => 'Wash & Iron (Trouser)', 'category_id' => $cat2->id, 'base_price' => 300, 'is_active' => true]);
        ServiceType::create(['name' => 'Dry Cleaning (Suit)', 'category_id' => $cat3->id, 'base_price' => 1500, 'is_active' => true]);
        ServiceType::create(['name' => 'Dry Cleaning (Dress)', 'category_id' => $cat3->id, 'base_price' => 1200, 'is_active' => true]);
    }
}
