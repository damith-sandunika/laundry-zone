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
            ->logOnly(['name', 'phone', 'address', 'title', 'email'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $guarded = ['id'];

    protected $casts = [
        'address' => 'encrypted',
    ];

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

