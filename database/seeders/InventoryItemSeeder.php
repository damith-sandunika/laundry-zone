<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\ServiceInventory;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        // Branch 1 - Colombo inventory
        $items = [
            [
                'branch_id'     => 1,
                'name'          => 'Washing Detergent',
                'unit'          => 'ml',
                'current_stock' => 10000,
                'min_stock'     => 2000,
                'cost_per_unit' => 0.05,
            ],
            [
                'branch_id'     => 1,
                'name'          => 'Fabric Softener',
                'unit'          => 'ml',
                'current_stock' => 5000,
                'min_stock'     => 1000,
                'cost_per_unit' => 0.08,
            ],
            [
                'branch_id'     => 1,
                'name'          => 'Bleach',
                'unit'          => 'ml',
                'current_stock' => 3000,
                'min_stock'     => 500,
                'cost_per_unit' => 0.03,
            ],
            [
                'branch_id'     => 1,
                'name'          => 'Dry Clean Solvent',
                'unit'          => 'ml',
                'current_stock' => 8000,
                'min_stock'     => 1500,
                'cost_per_unit' => 0.20,
            ],
            [
                'branch_id'     => 1,
                'name'          => 'Starch Spray',
                'unit'          => 'ml',
                'current_stock' => 2000,
                'min_stock'     => 400,
                'cost_per_unit' => 0.10,
            ],
        ];

        $createdIds = [];
        foreach ($items as $item) {
            $inv = InventoryItem::create(array_merge($item, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            $createdIds[$item['name']] = $inv->id;
        }

        // Branch 2 - Kandy inventory (same items different stock)
        $branch2Items = [
            ['branch_id' => 2, 'name' => 'Washing Detergent',  'unit' => 'ml', 'current_stock' => 6000, 'min_stock' => 1500, 'cost_per_unit' => 0.05],
            ['branch_id' => 2, 'name' => 'Fabric Softener',    'unit' => 'ml', 'current_stock' => 3000, 'min_stock' => 800,  'cost_per_unit' => 0.08],
            ['branch_id' => 2, 'name' => 'Bleach',             'unit' => 'ml', 'current_stock' => 1500, 'min_stock' => 400,  'cost_per_unit' => 0.03],
            ['branch_id' => 2, 'name' => 'Dry Clean Solvent',  'unit' => 'ml', 'current_stock' => 4000, 'min_stock' => 1000, 'cost_per_unit' => 0.20],
        ];

        foreach ($branch2Items as $item) {
            InventoryItem::create(array_merge($item, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Assign inventory to service types
        // Service 1 = Wash & Iron, Service 2 = Wash Only, Service 3 = Dry Cleaning
        // Service 4 = Bedsheet, Service 5 = Iron Only, Service 6 = Express Wash

        $links = [
            // Wash & Iron → Detergent 100ml + Softener 50ml + Starch 30ml at washing stage
            ['service_type_id' => 1, 'inventory_item_id' => $createdIds['Washing Detergent'],  'qty_per_use' => 100, 'deduct_at_stage' => 'washing'],
            ['service_type_id' => 1, 'inventory_item_id' => $createdIds['Fabric Softener'],    'qty_per_use' => 50,  'deduct_at_stage' => 'washing'],
            ['service_type_id' => 1, 'inventory_item_id' => $createdIds['Starch Spray'],       'qty_per_use' => 30,  'deduct_at_stage' => 'drying'],

            // Wash Only → Detergent 100ml + Softener 50ml
            ['service_type_id' => 2, 'inventory_item_id' => $createdIds['Washing Detergent'],  'qty_per_use' => 100, 'deduct_at_stage' => 'washing'],
            ['service_type_id' => 2, 'inventory_item_id' => $createdIds['Fabric Softener'],    'qty_per_use' => 50,  'deduct_at_stage' => 'washing'],

            // Dry Cleaning → Solvent 200ml
            ['service_type_id' => 3, 'inventory_item_id' => $createdIds['Dry Clean Solvent'],  'qty_per_use' => 200, 'deduct_at_stage' => 'washing'],

            // Bedsheet → Detergent 200ml + Bleach 50ml + Softener 100ml
            ['service_type_id' => 4, 'inventory_item_id' => $createdIds['Washing Detergent'],  'qty_per_use' => 200, 'deduct_at_stage' => 'washing'],
            ['service_type_id' => 4, 'inventory_item_id' => $createdIds['Bleach'],             'qty_per_use' => 50,  'deduct_at_stage' => 'washing'],
            ['service_type_id' => 4, 'inventory_item_id' => $createdIds['Fabric Softener'],    'qty_per_use' => 100, 'deduct_at_stage' => 'washing'],

            // Express Wash → Detergent 100ml + Softener 50ml (same as wash)
            ['service_type_id' => 6, 'inventory_item_id' => $createdIds['Washing Detergent'],  'qty_per_use' => 100, 'deduct_at_stage' => 'washing'],
            ['service_type_id' => 6, 'inventory_item_id' => $createdIds['Fabric Softener'],    'qty_per_use' => 50,  'deduct_at_stage' => 'washing'],
        ];

        foreach ($links as $link) {
            ServiceInventory::create(array_merge($link, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
