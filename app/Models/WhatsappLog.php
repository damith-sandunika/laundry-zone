<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $fillable = [
        'branch_id', 'order_id', 'customer_id',
        'phone', 'message_type', 'message',
        'status', 'wa_message_id', 'error', 'sent_at',
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

    // Status badge color
    public function statusColor(): string
    {
        return match ($this->status) {
            'sent' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'disabled' => 'gray',
            default => 'gray',
        };
    }
}
