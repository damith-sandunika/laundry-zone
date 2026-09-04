<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRange extends Model
{
    protected $fillable = ['branch_id', 'min_distance_km', 'max_distance_km', 'cost'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

