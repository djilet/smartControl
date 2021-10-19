<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;

class CheckListDemand extends Model
{
    use LoggingTraits;

    protected $fillable = [
        'description',
        'regulatory',
    ];

    protected $hidden = ['check_list_item_id'];

    public $timestamps = false;
}
