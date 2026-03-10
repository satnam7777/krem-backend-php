<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAttachment extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'client_id','treatment_id','kind','disk','path','mime','size','original_name','sha256','uploaded_by_user_id'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
