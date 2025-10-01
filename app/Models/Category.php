<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'images',
        'is_active',
    ];


    public function product()
    {
        return $this->hasMany(Product::class);
    }
    public function subcategories()
    {
        return $this->hasMany(SubCategory::class);
    }


}
