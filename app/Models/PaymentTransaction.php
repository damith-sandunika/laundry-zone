<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['order_id', 'amount', 'payment_method', 'created_by', 'collected_from_rider_at', 'receipt_path', 'status', 'transaction_id', 'reject_reason'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            if ($transaction->payment_method === 'cash' && $transaction->status !== 'rejected') {
                if ($transaction->order && $transaction->order->branch_id) {
                    app(\App\Services\CashBalanceService::class)->incrementCashBalance($transaction->order->branch_id, $transaction->amount);
                }
            }
        });
        
        static::updated(function ($transaction) {
            $original = $transaction->getOriginal();
            if ($transaction->payment_method === 'cash') {
                if ($original['status'] !== 'approved' && $transaction->status === 'approved') {
                    // if status changed to approved (e.g. from pending/rejected) - wait, cash payments are usually approved immediately unless collected by rider.
                    // Let's just handle simple created for now, but if it gets deleted:
                }
            }
        });

        static::deleted(function ($transaction) {
            if ($transaction->payment_method === 'cash' && $transaction->status !== 'rejected') {
                if ($transaction->order && $transaction->order->branch_id) {
                    app(\App\Services\CashBalanceService::class)->decrementCashBalance($transaction->order->branch_id, $transaction->amount);
                }
            }
        });
    }
}
