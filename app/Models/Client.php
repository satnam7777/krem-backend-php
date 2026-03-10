<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    protected $connection = 'tenant';

    use SoftDeletes;

    protected $fillable = [
        'salon_id','first_name','last_name','phone','email','gender','date_of_birth','status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(ClientConsent::class);
    }

    public function medicalProfile()
    {
        return $this->hasOne(MedicalProfile::class);
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(ClientTreatment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClientAttachment::class);
    }
}
