<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhookEvent extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['provider','event_id','received_at','payload'];
    protected $casts = ['received_at'=>'datetime','payload'=>'array'];
}
