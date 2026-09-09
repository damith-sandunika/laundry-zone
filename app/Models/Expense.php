<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Expense extends Model
{
    use BelongsToBranch, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['description', 'amount', 'category_id', 'expense_date'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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
            if ($expense->branch_id) {
                $branch = \App\Models\Branch::find($expense->branch_id);
                if ($branch) {
                    $branch->petty_cash_balance -= $expense->amount;
                    $branch->save();
                }
            }
        });

        static::updated(function ($expense) {
            if ($expense->branch_id && $expense->isDirty('amount')) {
                $branch = \App\Models\Branch::find($expense->branch_id);
                if ($branch) {
                    // Add back the old amount, deduct the new amount
                    $branch->petty_cash_balance += $expense->getOriginal('amount');
                    $branch->petty_cash_balance -= $expense->amount;
                    $branch->save();
                }
            }
        });

        static::deleted(function ($expense) {
            if ($expense->branch_id) {
                $branch = \App\Models\Branch::find($expense->branch_id);
                if ($branch) {
                    $branch->petty_cash_balance += $expense->amount;
                    $branch->save();
                }
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

