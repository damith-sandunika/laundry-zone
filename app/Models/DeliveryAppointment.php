<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryAppointment extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'branch_id', 'pickup_address', 'delivery_address',
        'latitude', 'longitude', 'map_address', 'item_description',
        'pickup_scheduled_at', 'delivery_scheduled_at', 'status', 'notes', 'contact_phone',
        'order_id', 'picked_up_at', 'payment_method', 'payment_timing', 'delivery_preference',
        'delivery_lat', 'delivery_lng', 'delivery_cost', 'receipt_path', 'cart_json',
        'cancel_reason', 'cancelled_by'
    ];

    protected $casts = [
        'pickup_scheduled_at' => 'datetime',
        'delivery_scheduled_at' => 'datetime',
        'picked_up_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withoutGlobalScopes();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function deliveryTasks(): HasMany
    {
        return $this->hasMany(DeliveryTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Status label helper
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'rider_assigned' => 'Rider Assigned',
            'picked_up' => 'Picked Up',
            'order_created' => 'Order Created',
            'at_branch' => 'At Branch',
            'processing' => 'Processing',
            'ready' => 'Ready',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    // Status badge color helper
    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'rider_assigned' => 'indigo',
            'picked_up' => 'purple',
            'at_branch' => 'orange',
            'processing' => 'cyan',
            'ready' => 'teal',
            'out_for_delivery' => 'blue',
            'delivered' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}

