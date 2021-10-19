<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;

class ContractorItemEliminationDate extends Model
{
    use LoggingTraits;

    protected $fillable = [
        'sum',
        'date',
    ];

    protected $hidden = ['contractor_item_id'];

    protected $dates = [
        'date',
    ];
}
