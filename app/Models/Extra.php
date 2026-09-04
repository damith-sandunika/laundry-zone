<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'price_type',
        'price',
        'is_active',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
