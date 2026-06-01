<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'whatsapp_number', 'is_active', 'tagline', 'logo_path'
    ];

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
