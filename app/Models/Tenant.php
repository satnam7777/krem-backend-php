<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'pgsql';
    protected $fillable = [
        'name',
        'slug',
        'db_name',
        'status', // active, suspended
        'created_by',
    ];

    public function domains()
    {
        return $this->hasMany(TenantDomain::class);
    }
}
