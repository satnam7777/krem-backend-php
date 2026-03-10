<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientTreatment extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'client_id','appointment_id','service_id','performed_at','notes','performed_by_user_id'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'notes' => 'encrypted',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
