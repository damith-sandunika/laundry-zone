<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = \App\Models\Branch::first()->id ?? 1;
        $catWashFold = ServiceCategory::firstOrCreate(['name' => 'Wash & Fold'], ['branch_id' => $branchId, 'is_active' => true]);
        $catDryClean = ServiceCategory::firstOrCreate(['name' => 'Dry Cleaning'], ['branch_id' => $branchId, 'is_active' => true]);
        $catSpecialty = ServiceCategory::firstOrCreate(['name' => 'Specialty items'], ['branch_id' => $branchId, 'is_active' => true]);

        $services = [
            [
                'name' => 'Wash & Iron',
                'description' => 'Full wash and ironing service',
                'price_type' => 'per_item',
                'base_price' => 150.00,
                'loyalty_points_earn' => 5,
                'is_active' => true,
                'category_id' => $catWashFold->id,
            ],
            [
                'name' => 'Wash Only',
                'description' => 'Washing service only',
                'price_type' => 'per_item',
                'base_price' => 80.00,
                'loyalty_points_earn' => 3,
                'is_active' => true,
                'category_id' => $catWashFold->id,
            ],
            [
                'name' => 'Dry Cleaning',
                'description' => 'Professional dry cleaning',
                'price_type' => 'per_item',
                'base_price' => 350.00,
                'loyalty_points_earn' => 10,
                'is_active' => true,
                'category_id' => $catDryClean->id,
            ],
            [
                'name' => 'Bedsheet Wash',
                'description' => 'Bedsheets, curtains, heavy items',
                'price_type' => 'per_kg',
                'base_price' => 120.00,
                'loyalty_points_earn' => 4,
                'is_active' => true,
                'category_id' => $catSpecialty->id,
            ],
            [
                'name' => 'Iron Only',
                'description' => 'Ironing service only',
                'price_type' => 'per_item',
                'base_price' => 60.00,
                'loyalty_points_earn' => 2,
                'is_active' => true,
                'category_id' => $catWashFold->id,
            ],
            [
                'name' => 'Express Wash',
                'description' => 'Same-day urgent wash service',
                'price_type' => 'per_item',
                'base_price' => 250.00,
                'loyalty_points_earn' => 8,
                'is_active' => true,
                'category_id' => $catWashFold->id,
            ],
        ];

        foreach ($services as $service) {
            ServiceType::updateOrCreate(
                ['name' => $service['name']], 
                array_merge($service, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
