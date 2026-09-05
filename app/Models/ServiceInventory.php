<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceInventory extends Model
{
    protected $table = 'service_inventory';

    protected $guarded = ['id'];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}

