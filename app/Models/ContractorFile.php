<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorFile extends Model
{
    protected $fillable = [
        'filename',
        'user_filename',
    ];
}
