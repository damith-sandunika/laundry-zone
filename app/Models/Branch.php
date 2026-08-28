<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name', 'address', 'latitude', 'longitude', 'phone', 'whatsapp_number', 'tagline', 'logo_path', 'logo_size', 'type', 'is_active', 'auto_close_time',
        'is_delivery_enabled', 'branch_profit_percentage'
    ];

    public function scopeWorkshops($query)
    {
        return $query->where('type', 'workshop');
    }

    public function scopeRegularBranches($query)
    {
        return $query->where('type', 'branch');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function cubbies()
    {
        return $this->hasMany(Cubby::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function onlineBookings()
    {
        return $this->hasMany(OnlineBooking::class);
    }
}
