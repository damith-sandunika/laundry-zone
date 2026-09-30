<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionalCampaign extends Model
{
    protected $guarded = ['id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
