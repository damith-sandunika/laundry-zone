<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class OnlineBooking extends Model
{
    use BelongsToBranch;

    protected $guarded = ['id'];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

