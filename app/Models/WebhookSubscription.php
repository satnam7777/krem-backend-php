<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookSubscription extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id','name','target_url','enabled','secret','events'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'events' => 'array',
    ];
}
