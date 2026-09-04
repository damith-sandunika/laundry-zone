<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'phone', 'type', 'message', 'status', 'response'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

