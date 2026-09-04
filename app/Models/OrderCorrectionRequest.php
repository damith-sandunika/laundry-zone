<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCorrectionRequest extends Model
{
    protected $fillable = [
        'order_id',
        'requested_by',
        'approved_by',
        'status',
        'type',
        'old_data',
        'new_data',
        'reason',
        'resolved_at',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

