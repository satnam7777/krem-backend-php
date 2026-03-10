<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAuditLog extends Model
{
    protected $connection = 'pgsql';
    protected $fillable = [
        'tenant_id','user_id','action','ip','user_agent','meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
