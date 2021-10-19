<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingHandbookSection extends Model
{
    use \Awobaz\Compoships\Compoships;

    protected $with = ['sections', 'snips'];

    public function sections()
    {
        return $this->hasMany(BuildingHandbookSection::class, 'pid');
    }

    public function snips()
    {
        return $this->hasMany('App\Models\BuildingHandbookSnip', 'section_id');
    }

    public function sectionIds()
    {
        return $this->hasMany(BuildingHandbookSection::class, 'pid')
            ->setEagerLoads([])
            ->select('id', 'title', 'pid')
            ->with('sectionIds', 'snips');
    }

}
