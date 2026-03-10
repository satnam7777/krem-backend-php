<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationOutbox extends Model
{
    protected $connection = 'tenant';

    protected $table = 'notification_outbox';

    protected $fillable = [
        'salon_id','user_id','channel','template','payload',
        'status','attempts','send_after','last_error','sent_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'send_after' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
