<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankDeposit extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'deposited_at' => 'datetime',
        'edited_at'    => 'datetime',
        'amount'       => 'decimal:2',
        'is_edited'    => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function editedByUser()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    protected static function boot()
    {
        parent::boot();

        // When created: reduce cash balance
        static::created(function ($deposit) {
            app(\App\Services\CashBalanceService::class)->decrementCashBalance($deposit->branch_id, $deposit->amount);
        });

        // When soft-deleted: restore cash balance
        static::deleting(function ($deposit) {
            if (!$deposit->isForceDeleting()) {
                app(\App\Services\CashBalanceService::class)->incrementCashBalance($deposit->branch_id, $deposit->amount);
            }
        });
    }
}

