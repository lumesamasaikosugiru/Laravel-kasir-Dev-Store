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
        'total_payment'

    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class); //Satu order atau 1 struk bisa memiliki banya order detail (bisa beli banyak produk)
    }
}
