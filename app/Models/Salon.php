<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salon extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'slug', 'currency', 'timezone', 'status',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(SalonMember::class);
    }
}
