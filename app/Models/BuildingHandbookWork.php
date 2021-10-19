<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingHandbookWork extends Model
{
    use \Awobaz\Compoships\Compoships;

    public $incrementing = false;
    public $timestamps = false;
    protected $with = ['work', 'sections'];
    protected $hidden = ['handbook_id', 'work_id'];

    public function work()
    {
        return $this->belongsTo('App\Models\HandbookWork')->setEagerLoads([]);
    }

    public function sections()
    {
        return $this->hasMany(
            'App\Models\BuildingHandbookSection',
            ['handbook_id', 'work_id'],
            ['handbook_id', 'work_id']
        );
    }

}
