<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'name',
        'category_id',
        'sub_category_id',
        'brand_id',
        'price',
        'base_price',
        'stock',
        'sku',
        'barqode',
        'images',
        'in_stock',
        'is_active',
        'description',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function orderDetail()//satu produk bisa muncul di banyak detail order.
    {
        return $this->hasMany(OrderDetail::class);
    }

}
