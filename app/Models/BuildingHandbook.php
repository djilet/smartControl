<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingHandbook extends Model
{

    protected $fillable = ['title'];
    protected $with = ['works'];

    public function works()
    {
        return $this->hasMany(
                'App\Models\BuildingHandbookWork',
                'handbook_id'
            );
    }
}
