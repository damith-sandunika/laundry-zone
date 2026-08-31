<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'phone',
        'type',
        'meta_template_name',
        'status',
        'response'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
