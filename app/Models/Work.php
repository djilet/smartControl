<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use LoggingTraits;

    protected $with = ['contractor'];

    protected $hidden = ['pivot'];

    protected $fillable = ['title'];

    public function contractor()
    {
        return $this->belongsTo('App\Models\Contractor');
    }
}
