<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupTimePeriod extends Model
{
    protected $fillable = ['label', 'start_time', 'end_time', 'is_active'];
}
