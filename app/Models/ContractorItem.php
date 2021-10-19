<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;

class ContractorItem extends Model
{
    use LoggingTraits;

    protected $fillable = [
        'cost',
        'sum',
        'date',
        'building_id',
        'contractor_id',
        'work_id',
        'editable',
    ];

    protected $casts = [
        'editable' => 'boolean',
    ];

    protected $with = ['files', 'eliminationDates', 'building', 'work'];

    public function files()
    {
        return $this->hasMany(ContractorFile::class);
    }

    public function eliminationDates()
    {
        return $this->hasMany(ContractorItemEliminationDate::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class)->setEagerLoads([]);
    }

    public function work()
    {
        return $this->belongsTo(Work::class)->setEagerLoads([]);
    }

}
