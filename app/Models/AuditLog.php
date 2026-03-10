<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id','actor_user_id','actor_role','action','context','ip','user_agent'
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
