<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable;

    protected $guarded = ['id'];

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
}

