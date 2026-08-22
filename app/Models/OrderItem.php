<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'service_type_id', 'item_description',
        'quantity', 'weight_kg', 'unit_price', 'total_price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
