<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'branch_id', 'customer_id', 'created_by',
        'status', 'subtotal', 'discount', 'additional', 'total', 'paid_amount',
        'payment_status', 'payment_method', 'loyalty_points_used',
        'loyalty_points_earned', 'due_date', 'notes', 'is_online_booking',
        'ready_at', 'delivered_at'
    ];

    protected $casts = [
        'due_date'       => 'datetime',
        'ready_at'       => 'datetime',
        'delivered_at'   => 'datetime',
        'is_online_booking' => 'boolean',
    ];

    // Status labels for display
    public static $statusLabels = [
        'received'  => 'Received',
        'done'      => 'Done',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    // Status colors for UI badges
    public static $statusColors = [
        'received'  => 'info',
        'done'      => 'success',
        'delivered' => 'secondary',
        'cancelled' => 'danger',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function cubby()
    {
        return $this->hasOne(OrderCubby::class)->whereNull('released_at');
    }

    public function cubbies()
    {
        return $this->hasMany(OrderCubby::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsappLog::class);
    }

    // Generate unique order number: U0001, U0002, ...
    public static function generateOrderNumber(): string
    {
        $last = self::max('id') ?? 0;
        return 'U' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
