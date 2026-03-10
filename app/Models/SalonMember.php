<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalonMember extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id', 'user_id', 'role', 'status', 'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
