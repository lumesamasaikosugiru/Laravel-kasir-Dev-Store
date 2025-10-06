<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'customer_id',
        'total_price',
        'date_sell',
        'status',
        'discount',
        'discount_amount',
        'total_payment',
        'payment_method',
        'payment_status'

    ];

    protected static function booted()
    {
        static::updated(function ($order) {

            $originalStatus = $order->getOriginal('status');

            if ($order->isDirty('status') && $order->status === 'Completed') {

                foreach ($order->orderDetail as $detail) {
                    $product = $detail->product;

                    if ($product) {
                        $product->decrement('stock', $detail->qty);
                    }
                }
            }

            if ($order->isDirty('status') && $originalStatus === 'Completed' && $order->status === 'Cancelled') {

                foreach ($order->orderDetail as $detail) {
                    $product = $detail->product;

                    if ($product) {
                        $product->increment('stock', $detail->qty);
                    }
                }
            }
        });
    }



    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class); //Satu order atau 1 struk bisa memiliki banya order detail (bisa beli banyak produk)
    }
}
