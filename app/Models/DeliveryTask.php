<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTask extends Model
{
    protected $fillable = [
        'delivery_appointment_id', 'order_id', 'rider_id', 'branch_id',
        'task_type', 'status', 'pickup_location', 'dropoff_location',
        'rider_lat', 'rider_lng', 'accepted_at', 'completed_at', 'notes'
    ];

    protected $casts = [
        'accepted_at'  => 'datetime',
        'completed_at' => 'datetime',
        'rider_lat'    => 'decimal:7',
        'rider_lng'    => 'decimal:7',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(DeliveryAppointment::class, 'delivery_appointment_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'assigned'   => 'Assigned',
            'accepted'   => 'Accepted',
            'on_way'     => 'On The Way',
            'arrived'    => 'Arrived',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'assigned'  => 'yellow',
            'accepted'  => 'blue',
            'on_way'    => 'indigo',
            'arrived'   => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }
}
