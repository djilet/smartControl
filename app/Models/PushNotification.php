<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'model_id',
        'title',
        'description',
        'is_read',
    ];
}
