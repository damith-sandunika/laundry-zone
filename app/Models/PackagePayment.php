<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePayment extends Model
{
    protected $fillable = ['customer_package_id', 'amount', 'payment_method', 'created_by'];

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
