<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'salon_id',
        'client_id',
        'staff_profile_id',
        'booking_channel',
        'appointment_date',
        'start_at',
        'end_at',
        'status',
        'subtotal',
        'discount_amount',
        'total_amount',
        'deposit_amount',
        'notes',
        'internal_note',
        'cancelled_at',
        'cancellation_reason',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];
}
