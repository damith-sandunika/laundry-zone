<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceInventory extends Model
{
    protected $table = 'service_inventory';

    protected $fillable = [
        'service_type_id', 'inventory_item_id', 'qty_per_use', 'deduct_at_stage',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}

