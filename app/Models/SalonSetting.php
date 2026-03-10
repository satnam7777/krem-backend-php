<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalonSetting extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['salon_id','key','type','value'];
    protected $casts = ['value'=>'array'];
}
