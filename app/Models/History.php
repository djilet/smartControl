<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = [
        'model',
        'model_id',
        'building_id',
        'user_id',
        'user',
        'action',
        'old_value',
        'new_value',
        'created_at',
    ];

    public $timestamps = false;

    protected $dates = [
        'created_at'
    ];

    protected $casts = [
        'user' => 'array',
        'old_value' => 'array',
        'new_value' => 'array',
    ];
}
