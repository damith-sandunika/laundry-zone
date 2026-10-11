<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageService extends Model
{
    protected $guarded = ['id'];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}

