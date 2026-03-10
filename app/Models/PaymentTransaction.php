<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id','order_id','payment_intent_id','provider','provider_txn_id',
        'amount_cents','currency','type','status','failure_reason','provider_payload'
    ];

    protected $casts = [
        'provider_payload' => 'array',
    ];
}
