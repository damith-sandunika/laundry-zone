<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id', 'name', 'unit', 'current_stock', 'min_stock', 'cost_per_unit', 'is_active', 'low_stock_threshold', 'location',
    ];

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function serviceInventories()
    {
        return $this->hasMany(ServiceInventory::class);
    }

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'service_inventory')
            ->withPivot('qty_per_use', 'deduct_at_stage')
            ->withTimestamps();
    }

    // Check if stock is low
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }
}

