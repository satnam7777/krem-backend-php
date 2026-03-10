<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantMembership extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'role',
        'status',
    ];
}
