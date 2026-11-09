<?php

namespace Database\Seeders;

use App\Models\Central\PlatformSmsPackage;
use Illuminate\Database\Seeder;

class PlatformSmsPackageSeeder extends Seeder
{
    public function run(): void
    {
        if (PlatformSmsPackage::count() === 0) {
            PlatformSmsPackage::create([
                'name' => 'Starter Pack',
                'sms_count' => 500,
                'price' => 1000.00,
                'rate_per_sms' => 2.00,
                'badge' => null,
                'is_featured' => false,
                'features' => ['500 SMS Credits', 'Instant Delivery', 'Gateway Support', 'No Expiry'],
                'is_active' => true,
                'order_index' => 1,
            ]);

            PlatformSmsPackage::create([
                'name' => 'Popular Pack',
                'sms_count' => 1000,
                'price' => 1800.00,
                'rate_per_sms' => 1.80,
                'badge' => 'Most Popular',
                'is_featured' => true,
                'features' => ['1,000 SMS Credits', 'Instant Delivery', 'Priority Gateway', 'Detailed Logs & Reports', 'No Expiry'],
                'is_active' => true,
                'order_index' => 2,
            ]);

            PlatformSmsPackage::create([
                'name' => 'Enterprise Pack',
                'sms_count' => 3000,
                'price' => 4500.00,
                'rate_per_sms' => 1.50,
                'badge' => 'Best Value',
                'is_featured' => false,
                'features' => ['3,000 SMS Credits', 'Lowest Rate (Rs. 1.50/SMS)', 'Dedicated Route', 'Automated Low Balance Alerts', 'No Expiry'],
                'is_active' => true,
                'order_index' => 3,
            ]);
        }
    }
}

