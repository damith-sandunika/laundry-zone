<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Branch;

class FiftyCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        if ($branches->isEmpty()) {
            return;
        }

        foreach ($branches as $branch) {
            for ($i = 1; $i <= 50; $i++) {
                $category = ServiceCategory::create([
                    'branch_id' => $branch->id,
                    'name' => 'Category ' . $i,
                    'is_active' => true,
                    'image_path' => null,
                ]);

                // Create a couple of dummy services for this category
                ServiceType::create([
                    'branch_id' => $branch->id,
                    'category_id' => $category->id,
                    'name' => 'Service A for Cat ' . $i,
                    'base_price' => rand(100, 1000),
                    'price_type' => 'per_item',
                    'color' => 'blue',
                    'is_active' => true,
                ]);
                
                ServiceType::create([
                    'branch_id' => $branch->id,
                    'category_id' => $category->id,
                    'name' => 'Service B for Cat ' . $i,
                    'base_price' => rand(100, 1000),
                    'price_type' => 'per_kg',
                    'color' => 'green',
                    'is_active' => true,
                ]);
            }
        }
    }
}
