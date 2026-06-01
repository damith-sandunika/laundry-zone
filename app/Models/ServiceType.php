<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = [
        'name', 'description', 'price_type', 'base_price', 'loyalty_points_earn', 'is_active', 'icon', 'color'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function serviceInventories()
    {
        return $this->hasMany(ServiceInventory::class);
    }

    // Inventory items used for this service
    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class, 'service_inventory')
                    ->withPivot('qty_per_use', 'deduct_at_stage')
                    ->withTimestamps();
    }
}
