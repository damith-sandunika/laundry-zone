<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = ['branch_id', 'parent_id', 'name', 'is_active', 'image_path', 'order_index'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function parent()
    {
        return $this->belongsTo(ServiceCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ServiceCategory::class, 'parent_id')->orderBy('order_index');
    }

    public function services()
    {
        return $this->hasMany(ServiceType::class, 'category_id');
    }
}

