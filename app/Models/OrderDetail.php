<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    //

    protected $fillable = [
        'product_id',
        'order_id',
        'qty',
        'subtotal',
    ];


    protected static function booted()
    {
        static::created(function ($orderDetail) {

            if ($orderDetail->order->status === 'Completed') {
                $product = $orderDetail->product;

                if ($product) {
                    $product->decrement('stock', $orderDetail->qty);
                }
            }
        });
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
