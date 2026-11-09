<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSmsPackage extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $table = 'platform_sms_packages';

    protected $fillable = [
        'name',
        'sms_count',
        'price',
        'rate_per_sms',
        'badge',
        'is_featured',
        'features',
        'is_active',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'sms_count' => 'integer',
            'price' => 'decimal:2',
            'rate_per_sms' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'order_index' => 'integer',
            'features' => 'array',
        ];
    }
}

