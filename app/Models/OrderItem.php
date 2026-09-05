<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

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

