<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineBooking extends Model
{
    protected $fillable = [
        'branch_id', 'customer_name', 'customer_phone', 'customer_email',
        'customer_address', 'service_description', 'preferred_date',
        'status', 'order_id', 'notes'
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
