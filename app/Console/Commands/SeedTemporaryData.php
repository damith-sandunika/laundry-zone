<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Extra;
use App\Models\TurnaroundType;
use App\Models\Branch;

class SeedTemporaryData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-temporary-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed temporary categories, subcategories, services, extras and turnaround types for testing.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up old data...');
        // Disable foreign key checks for truncation (SQLite uses a pragma, MySQL uses SET FOREIGN_KEY_CHECKS)
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        ServiceType::truncate();
        ServiceCategory::truncate();
        Extra::truncate();
        TurnaroundType::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $branchId = Branch::first()->id ?? 1;

        $this->info('Creating Standard Turnaround Type...');
        TurnaroundType::create([
            'branch_id' => $branchId,
            'name' => 'Standard',
            'markup_type' => 'fixed',
            'markup_value' => 0,
            'is_default' => true,
            'is_active' => true,
        ]);
        TurnaroundType::create([
            'branch_id' => $branchId,
            'name' => 'Express 24h',
            'markup_type' => 'percentage',
            'markup_value' => 50, // 50% extra
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->info('Creating Extras...');
        $extras = [
            ['name' => 'Hangers', 'price' => 30, 'price_type' => 'per_piece'],
            ['name' => 'Special Detergent', 'price' => 150, 'price_type' => 'per_piece'],
            ['name' => 'Fabric Softener', 'price' => 50, 'price_type' => 'per_piece'],
            ['name' => 'Stain Removal', 'price' => 200, 'price_type' => 'per_piece'],
            ['name' => 'Fold & Pack', 'price' => 100, 'price_type' => 'per_piece'],
        ];

        foreach ($extras as $extra) {
            Extra::create([
                'branch_id' => $branchId,
                'name' => $extra['name'],
                'price' => $extra['price'],
                'price_type' => $extra['price_type'],
                'is_active' => true,
            ]);
        }

        $this->info('Creating 20 Hierarchical Categories...');

        $hierarchy = [
            'Men\'s Wear' => [
                'Shirts' => ['Wash & Iron', 'Dry Clean', 'Iron Only'],
                'Trousers' => ['Wash & Iron', 'Dry Clean', 'Iron Only'],
                'Suits' => ['Dry Clean (2 Piece)', 'Dry Clean (3 Piece)', 'Steam Press'],
                'Traditional' => ['Sarong Wash & Iron', 'National Suit Dry Clean'],
            ],
            'Women\'s Wear' => [
                'Dresses' => ['Wash & Iron (Simple)', 'Dry Clean (Party Wear)', 'Iron Only'],
                'Sarees' => ['Dry Clean (Silk)', 'Dry Clean (Cotton)', 'Fall/Edging', 'Iron Only'],
                'Tops & Blouses' => ['Wash & Iron', 'Dry Clean'],
                'Skirts & Trousers' => ['Wash & Iron', 'Dry Clean'],
            ],
            'Kids Wear' => [
                'School Uniforms' => ['Wash & Iron (White)', 'Wash & Iron (Colored)', 'Iron Only'],
                'Casual Wear' => ['Wash & Fold', 'Wash & Iron'],
                'Party Wear' => ['Dry Clean', 'Steam Press'],
            ],
            'Household' => [
                'Bedding' => ['Bedsheet (Single)', 'Bedsheet (Double/King)', 'Pillow Cover', 'Duvet Cover', 'Comforter'],
                'Curtains & Drapes' => ['Light Curtain (Per KG)', 'Heavy Curtain (Per KG)', 'Dry Clean (Silk/Velvet)'],
                'Towels' => ['Bath Towel', 'Hand Towel', 'Wash & Fold (Per KG)'],
                'Rugs & Mats' => ['Small Mat', 'Large Rug (Per Sq Ft)'],
            ],
            'Winter Wear & Heavy' => [
                'Jackets' => ['Dry Clean (Leather)', 'Dry Clean (Fleece)', 'Wash & Dry'],
                'Sweaters' => ['Dry Clean', 'Wash & Fold'],
                'Blankets' => ['Wash & Dry (Single)', 'Wash & Dry (Double)'],
            ],
            'Accessories & Footwear' => [
                'Shoes' => ['Sneaker Cleaning', 'Leather Polish'],
                'Bags' => ['Handbag Clean', 'Backpack Wash'],
                'Caps & Hats' => ['Cap Wash & Shape'],
            ],
            'Specialized Care' => [
                'Wedding Attire' => ['Wedding Dress Dry Clean', 'Groom Suit Dry Clean'],
                'Soft Toys' => ['Small Toy Wash', 'Large Toy Wash'],
                'Pet Items' => ['Pet Bed Wash', 'Pet Blanket Wash'],
            ]
        ];

        $colors = ['blue', 'green', 'purple', 'orange', 'teal', 'red'];
        $catOrder = 1;
        $srvOrder = 1;

        foreach ($hierarchy as $parentName => $subcategories) {
            $parent = ServiceCategory::create([
                'branch_id' => $branchId,
                'name' => $parentName,
                'is_active' => true,
                'order_index' => $catOrder++,
            ]);

            foreach ($subcategories as $subName => $servicesList) {
                $sub = ServiceCategory::create([
                    'branch_id' => $branchId,
                    'parent_id' => $parent->id,
                    'name' => $subName,
                    'is_active' => true,
                    'order_index' => $catOrder++,
                ]);

                foreach ($servicesList as $idx => $serviceName) {
                    $priceType = str_contains(strtolower($serviceName), 'per kg') ? 'per_kg' : 'per_piece';
                    $basePrice = rand(10, 100) * 10; // Random price between 100 and 1000

                    ServiceType::create([
                        'branch_id' => $branchId,
                        'category_id' => $sub->id,
                        'name' => $serviceName,
                        'base_price' => $basePrice,
                        'price_type' => $priceType,
                        'is_active' => true,
                        'order_index' => $srvOrder++,
                        'color' => $colors[array_rand($colors)],
                    ]);
                }
            }
        }

        $this->info('Dummy data seeded successfully! You can now test the settings and POS interfaces.');
    }
}
