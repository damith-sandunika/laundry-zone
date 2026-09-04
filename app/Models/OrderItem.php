<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'service_type_id', 'item_description',
        'quantity', 'weight_kg', 'unit_price', 'total_price',
        'turnaround_type_id', 'turnaround_extra_charge',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function turnaroundType()
    {
        return $this->belongsTo(TurnaroundType::class);
    }

    public function extras()
    {
        return $this->hasMany(OrderItemExtra::class);
    }
}
