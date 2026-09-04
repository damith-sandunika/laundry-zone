<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Cubby extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id', 'name', 'status', 'is_active',
    ];

    public function orderCubbies()
    {
        return $this->hasMany(OrderCubby::class);
    }

    // Current active order in this cubby
    public function currentOrder()
    {
        return $this->hasOneThrough(Order::class, OrderCubby::class, 'cubby_id', 'id', 'id', 'order_id')
            ->whereNull('order_cubbies.released_at');
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }
}

