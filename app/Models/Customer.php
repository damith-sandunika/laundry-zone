<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id', 'name', 'phone', 'email', 'address', 'loyalty_points', 'is_active',
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
}
