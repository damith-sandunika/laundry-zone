<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryAdvance extends Model
{
    protected $fillable = ['user_id', 'amount', 'date', 'reason'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

