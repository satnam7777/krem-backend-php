<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalProfile extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'client_id','allergies','contraindications','medications','conditions','notes'
    ];

    protected $casts = [
        'allergies' => 'encrypted:array',
        'contraindications' => 'encrypted:array',
        'medications' => 'encrypted:array',
        'conditions' => 'encrypted:array',
        'notes' => 'encrypted',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
