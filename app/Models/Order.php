<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Order extends Model
{
    use BelongsToBranch, HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'total', 'discount', 'due_date', 'internal_notes'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $fillable = [
        'order_number', 'branch_id', 'customer_id', 'created_by', 'status', 
        'subtotal', 'discount', 'additional', 'delivery_fee', 'total', 
        'paid_amount', 'branch_profit', 'workshop_profit', 'requested_advance', 
        'payment_approved', 'payment_status', 'receipt_path', 'payment_method', 
        'loyalty_points_used', 'loyalty_points_earned', 'due_date', 'notes', 
        'is_online_booking', 'ready_at', 'delivered_at', 'picked_up_at', 
        'dispatched_to_workshop_at', 'returned_from_workshop_at', 
        'delivery_appointment_id', 'is_delivery_order', 'delivery_address', 'workshop_id', 'payment_token', 'deleted_by'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'is_online_booking' => 'boolean',
    ];

    // Status labels for display
    public static $statusLabels = [
        'pending' => 'Pending Pickup',
        'confirmed' => 'Confirmed',
        'picked_up' => 'Picked Up',
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
        'pending' => 'warning',
        'confirmed' => 'primary',
        'picked_up' => 'info',
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

    public function workshopDispatches()
    {
        return $this->hasMany(WorkshopDispatch::class, 'order_id');
    }

    public function latestWorkshopDispatch()
    {
        return $this->hasOne(WorkshopDispatch::class, 'order_id')->latestOfMany();
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by')->withoutGlobalScopes();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('id', 'desc');
    }

    public function correctionRequests()
    {
        return $this->hasMany(OrderCorrectionRequest::class);
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

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    protected static function booted()
    {
        static::created(function ($order) {
            $order->order_number = (string) $order->id;
            if (empty($order->payment_token)) {
                $order->payment_token = 'inv_' . bin2hex(random_bytes(16));
            }
            $order->saveQuietly();
        });
    }

    // Return a temporary ID. The booted method will immediately replace it with the real numeric ID.
    public static function generateOrderNumber(int $branchId, bool $isDelivery = false): string
    {
        return 'TEMP-' . uniqid('', true);
    }

    public function isPackageOrder()
    {
        return $this->items()->where('item_description', 'like', '%via Package%')->exists();
    }

    public function deliveryAppointment()
    {
        return $this->hasOne(DeliveryAppointment::class, 'order_id');
    }

    public function getRouteKeyName()
    {
        return 'order_number';
    }
}

