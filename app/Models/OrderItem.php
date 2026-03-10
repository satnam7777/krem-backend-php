<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'order_id','service_id','name','qty','unit_price_cents','total_cents'
    ];
}
