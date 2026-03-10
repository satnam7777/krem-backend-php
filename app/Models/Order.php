<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id','appointment_id','user_id','total_cents','currency','status','reference','meta','paid_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
