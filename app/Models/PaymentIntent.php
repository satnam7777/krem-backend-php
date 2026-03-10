<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentIntent extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id','order_id','provider','provider_intent_id',
        'amount_cents','currency','status','provider_payload'
    ];

    protected $casts = [
        'provider_payload' => 'array',
    ];
}
