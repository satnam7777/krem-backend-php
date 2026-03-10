<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id','subscription_id','event','delivery_id','payload',
        'status','attempts','next_attempt_at','sent_at','last_error','last_http_status'
    ];

    protected $casts = [
        'payload' => 'array',
        'next_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
