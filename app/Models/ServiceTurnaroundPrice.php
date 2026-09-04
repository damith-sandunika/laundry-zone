<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTurnaroundPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'turnaround_type_id',
        'flat_extra_price',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_id');
    }

    public function turnaroundType()
    {
        return $this->belongsTo(TurnaroundType::class);
    }
}

