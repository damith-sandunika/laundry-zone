<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'email', 'type', 'subject', 'body', 'status', 'response'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

