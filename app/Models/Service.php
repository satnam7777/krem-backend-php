<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id','name','description','duration_min','buffer_min',
        'price_cents','currency','is_active','sort_order','image_url'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'service_staff')
            ->withPivot(['salon_id','price_cents_override','duration_min_override','is_active'])
            ->withTimestamps();
    }
}
