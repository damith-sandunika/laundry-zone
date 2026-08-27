<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'order_number', 'branch_id', 'customer_id', 'created_by', 'workshop_id',
        'status', 'subtotal', 'discount', 'additional', 'delivery_fee', 'total', 'paid_amount', 'requested_advance',
        'payment_status', 'payment_method', 'loyalty_points_used',
        'loyalty_points_earned', 'due_date', 'notes', 'is_online_booking',
        'ready_at', 'delivered_at', 'picked_up_at', 'dispatched_to_workshop_at', 'returned_from_workshop_at',
        'is_delivery_order', 'delivery_appointment_id',
        'delivery_address', 'receipt_path', 'payment_approved',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'is_online_booking' => 'boolean',
    ];

    // Status labels for display
    public static $statusLabels = [
        'received' => 'Received',
        'dispatched_to_workshop' => 'Sent to Workshop',
        'processing' => 'At Workshop',
        'returned_from_workshop' => 'Returning to Branch',
        'done' => 'Done',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    // Status colors for UI badges
    public static $statusColors = [
        'received' => 'info',
        'dispatched_to_workshop' => 'warning',
        'processing' => 'primary',
        'returned_from_workshop' => 'warning',
        'done' => 'success',
        'delivered' => 'secondary',
        'cancelled' => 'danger',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withoutGlobalScopes();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    public function workshop()
    {
        return $this->belongsTo(Branch::class, 'workshop_id')->withoutGlobalScopes();
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

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // Generate unique order number: U0001, U0002, ...
    public static function generateOrderNumber(): string
    {
        $last = self::withoutGlobalScope(BranchScope::class)->max('id') ?? 0;

        return 'U'.str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function isPackageOrder()
    {
        return $this->items()->where('item_description', 'like', '%via Package%')->exists();
    }

    public function deliveryAppointment()
    {
        return $this->hasOne(DeliveryAppointment::class, 'order_id');
    }
}
