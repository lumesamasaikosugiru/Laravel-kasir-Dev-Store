<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [
        'name',
        'category_id',
        'price',
        'stock',
        'images',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orderDetail()//satu produk bisa muncul di banyak detail order.
    {
        return $this->hasMany(OrderDetail::class);
    }

}
