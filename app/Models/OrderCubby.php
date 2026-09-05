<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCubby extends Model
{
    protected $guarded = ['id'];

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

