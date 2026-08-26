<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCubby extends Model
{
    protected $fillable = [
        'order_id', 'cubby_id', 'assigned_at', 'released_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cubby()
    {
        return $this->belongsTo(Cubby::class)->withoutGlobalScopes();
    }
}
