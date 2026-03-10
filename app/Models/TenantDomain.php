<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantDomain extends Model
{
    protected $connection = 'pgsql';
    protected $fillable = [
        'tenant_id',
        'host', // exact host match, e.g. demo.krema.ba or salon.com
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
