<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDeposit extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'amount',
        'reference_number',
        'notes',
        'deposited_at'
    ];

    protected $casts = [
        'deposited_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($deposit) {
            app(\App\Services\CashBalanceService::class)->decrementCashBalance($deposit->branch_id, $deposit->amount);
        });

        static::deleted(function ($deposit) {
            app(\App\Services\CashBalanceService::class)->incrementCashBalance($deposit->branch_id, $deposit->amount);
        });
    }
}

