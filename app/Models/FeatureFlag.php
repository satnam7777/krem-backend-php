<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['salon_id','key','enabled','meta'];
    protected $casts = ['enabled'=>'boolean','meta'=>'array'];
}
