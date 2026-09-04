<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'branch_id', 'method', 'display_name', 'instructions', 'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // List of all supported payment methods
    public static function allMethods(): array
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card (POS)',
            'cod' => 'Cash on Delivery',
            'koko' => 'Koko Pay',
            'bank' => 'Bank Transfer',
            'online' => 'Online Gateway',
        ];
    }
}

