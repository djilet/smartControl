<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use LoggingTraits, SoftDeletes;

    protected $fillable = [
        'title',
        'username',
        'email',
        'phone',
    ];

    protected $casts = [
        'building_closed' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(ContractorItem::class);
    }

    public function worksAnalytic(?int $buildingId = null)
    {
        $builder = $this->hasManyThrough(
                Work::class,
                CheckList::class,
                'contractor_id',
                'id',
                'id',
                'work_id'
            )
            ->distinct()
            ->setEagerLoads([]);

        if ($buildingId != null) {
            $builder->where('check_lists.building_id', $buildingId);
        }

        return $builder;
    }

    public function demandsAnalytic(int $buildingId)
    {
        return CheckListDemand::select('check_list_demands.*', 'works.title as work_title')
            ->join('check_list_items', 'check_list_items.id', 'check_list_demands.check_list_item_id')
            ->join('check_lists', 'check_lists.id', 'check_list_items.check_list_id')
            ->join('works', 'works.id', 'check_lists.work_id')
            ->where('check_lists.contractor_id', $this->id)
            ->where('check_lists.building_id', $buildingId)
            ->get();
    }

}
