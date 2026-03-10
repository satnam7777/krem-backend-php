<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends Model
{
    protected $connection = 'tenant';

    protected $table = 'staff';

    protected $fillable = [
        'salon_id','name','title','is_active','sort_order','avatar_url'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff')
            ->withPivot(['salon_id','price_cents_override','duration_min_override','is_active'])
            ->withTimestamps();
    }
}
