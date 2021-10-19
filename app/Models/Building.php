<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use LoggingTraits, SoftDeletes;

    protected $with = ['works'];

    protected $fillable = [
        'title',
        'address',
        'floors',
        'floors_ext',
        'responsible',
        'area',
        'closed',
    ];

    protected $hidden = ['user_created'];

    protected $casts = [
        'closed' => 'boolean',
        'floors_ext' => 'array',
    ];

    public function works()
    {
        return $this->hasMany('App\Models\BuildingWork');
    }

    public function markers()
    {
        return $this->hasManyThrough(
            CheckListItem::class,
            CheckList::class,
            'building_id',
            'check_list_id',
            'id',
            'id'
        )
            ->join('contractors', 'contractors.id', 'check_lists.contractor_id')
            ->whereNull('contractors.deleted_at')
            ->setEagerLoads([]);
    }

    public function worksAnalytic()
    {
        return $this->hasManyThrough(
            Work::class,
            CheckList::class,
            'building_id',
            'id',
            'id',
            'work_id'
        )
            ->distinct()
            ->join('contractors', 'contractors.id', 'check_lists.contractor_id')
            ->whereNull('contractors.deleted_at')
            ->setEagerLoads([]);
    }

}
