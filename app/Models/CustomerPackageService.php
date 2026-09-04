<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPackageService extends Model
{
    protected $guarded = [];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class);
    }
}

