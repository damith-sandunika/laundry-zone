<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            if ($transaction->payment_method === 'cash' && $transaction->status !== 'rejected') {
                if ($transaction->order && $transaction->order->branch_id) {
                    if ($transaction->amount > 0) {
                        app(\App\Services\CashBalanceService::class)->incrementCashBalance($transaction->order->branch_id, $transaction->amount);
                    } elseif ($transaction->amount < 0) {
                        app(\App\Services\CashBalanceService::class)->decrementCashBalance($transaction->order->branch_id, abs($transaction->amount));
                    }
                }
            }
        });

        static::deleted(function ($transaction) {
            if ($transaction->payment_method === 'cash' && $transaction->status !== 'rejected') {
                if ($transaction->order && $transaction->order->branch_id) {
                    if ($transaction->amount > 0) {
                        app(\App\Services\CashBalanceService::class)->decrementCashBalance($transaction->order->branch_id, $transaction->amount);
                    } elseif ($transaction->amount < 0) {
                        app(\App\Services\CashBalanceService::class)->incrementCashBalance($transaction->order->branch_id, abs($transaction->amount));
                    }
                }
            }
        });
    }
}
