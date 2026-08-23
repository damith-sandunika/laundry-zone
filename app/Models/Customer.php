<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use BelongsToBranch, Notifiable;

    protected $fillable = [
        'branch_id', 'name', 'phone', 'email', 'password', 'remember_token', 'address', 'loyalty_points', 'is_active',
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

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsappLog::class);
    }

    public function deliveryAppointments()
    {
        return $this->hasMany(DeliveryAppointment::class);
    }
}
