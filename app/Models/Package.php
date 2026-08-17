<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class Package extends Model
{
    use HasFactory, BelongsToBranch;

    protected $guarded = [];

    public function services()
    {
        return $this->hasMany(PackageService::class);
    }
}
