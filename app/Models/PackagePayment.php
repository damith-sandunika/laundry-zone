<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePayment extends Model
{
    protected $guarded = ['id'];

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class, 'customer_package_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            if ($payment->payment_method === 'cash') {
                $branchId = $payment->customerPackage->branch_id ?? null;
                if ($branchId) {
                    app(\App\Services\CashBalanceService::class)->incrementCashBalance($branchId, $payment->amount);
                }
            }
        });

        static::deleted(function ($payment) {
            if ($payment->payment_method === 'cash') {
                $branchId = $payment->customerPackage->branch_id ?? null;
                if ($branchId) {
                    app(\App\Services\CashBalanceService::class)->decrementCashBalance($branchId, $payment->amount);
                }
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

