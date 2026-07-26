<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class WhatsappLog extends Model
{
    use BelongsToBranch;
    protected $fillable = [
        'branch_id', 'order_id', 'customer_id', 'phone', 'message_type', 'message', 'status', 'sent_at', 'error_message'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
