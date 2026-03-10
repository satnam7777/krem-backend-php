<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalonInvite extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id', 'email', 'role',
        'token_hash', 'expires_at', 'accepted_at', 'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];
}
