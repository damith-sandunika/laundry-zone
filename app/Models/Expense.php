<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToBranch;

    protected $guarded = ['id'];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($expense) {
            // Deduct cash from branch for any expense logged
            if ($expense->branch_id) {
                app(\App\Services\CashBalanceService::class)->decrementCashBalance($expense->branch_id, $expense->amount);
            }
        });

        static::deleted(function ($expense) {
            if ($expense->branch_id) {
                app(\App\Services\CashBalanceService::class)->incrementCashBalance($expense->branch_id, $expense->amount);
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

