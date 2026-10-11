<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPackageService extends Model
{
    protected $guarded = ['id'];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class);
    }
}

