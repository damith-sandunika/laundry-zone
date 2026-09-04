<?php

namespace App\Models;

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

    // Generate unique order number per branch - race-condition safe via DB transaction lock
    public static function generateOrderNumber(int $branchId, bool $isDelivery = false): string
    {
        return \DB::transaction(function () use ($branchId, $isDelivery) {
            // Lock the branch row to prevent concurrent race conditions
            $branch = \App\Models\Branch::lockForUpdate()->find($branchId);
            
            if ($branch) {
                $branch->order_sequence += 1;
                $branch->save();
                $nextId = $branch->order_sequence;
            } else {
                $nextId = 1;
            }

            $prefix = $isDelivery ? "D{$branchId}" : "{$branchId}";
            $sequence = $nextId;

            return "{$prefix}-L-{$sequence}";
        });
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

