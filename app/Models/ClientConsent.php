<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientConsent extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'client_id','type','version','text_hash','accepted_at','source','recorded_by_user_id','meta'
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
