<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Customer extends Authenticatable
{
    use Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'address', 'title', 'email', 'date_of_birth', 'nic', 'customer_mark'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $fillable = [
        'branch_id', 'title', 'name', 'phone', 'email', 'password', 
        'remember_token', 'address', 'loyalty_points', 'is_active', 
        'default_discount', 'date_of_birth', 'nic', 'customer_mark'
    ];

    public function getAddressAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $value;
        }
    }

    public function setAddressAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['address'] = null;
        } else {
            $this->attributes['address'] = \Illuminate\Support\Facades\Crypt::encryptString($value);
        }
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
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

    public function deliveryAppointments()
    {
        return $this->hasMany(DeliveryAppointment::class);
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class);
    }
}

