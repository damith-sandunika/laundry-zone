<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name'                => 'Wash & Iron',
                'description'         => 'Full wash and ironing service',
                'price_type'          => 'per_item',
                'base_price'          => 150.00,
                'loyalty_points_earn' => 5,
                'is_active'           => true,
            ],
            [
                'name'                => 'Wash Only',
                'description'         => 'Washing service only',
                'price_type'          => 'per_item',
                'base_price'          => 80.00,
                'loyalty_points_earn' => 3,
                'is_active'           => true,
            ],
            [
                'name'                => 'Dry Cleaning',
                'description'         => 'Professional dry cleaning',
                'price_type'          => 'per_item',
                'base_price'          => 350.00,
                'loyalty_points_earn' => 10,
                'is_active'           => true,
            ],
            [
                'name'                => 'Bedsheet Wash',
                'description'         => 'Bedsheets, curtains, heavy items',
                'price_type'          => 'per_kg',
                'base_price'          => 120.00,
                'loyalty_points_earn' => 4,
                'is_active'           => true,
            ],
            [
                'name'                => 'Iron Only',
                'description'         => 'Ironing service only',
                'price_type'          => 'per_item',
                'base_price'          => 60.00,
                'loyalty_points_earn' => 2,
                'is_active'           => true,
            ],
            [
                'name'                => 'Express Wash',
                'description'         => 'Same-day urgent wash service',
                'price_type'          => 'per_item',
                'base_price'          => 250.00,
                'loyalty_points_earn' => 8,
                'is_active'           => true,
            ],
        ];

        foreach ($services as $service) {
            ServiceType::create(array_merge($service, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
