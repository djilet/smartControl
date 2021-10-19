<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckListImage extends Model
{

    protected $primaryKey = 'filename';

    protected $fillable = [
        'filename',
        'user_filename',
    ];

    protected $appends = ['url'];

    public $incrementing = false;

    public function getUrlAttribute()
    {
        return route('file.show', ['markers', $this->filename]);
    }
}
