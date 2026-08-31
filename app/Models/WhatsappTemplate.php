<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'meta_template_name',
        'language_code',
        'is_active',
        'content'
    ];
}
