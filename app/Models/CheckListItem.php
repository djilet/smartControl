<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;

class CheckListItem extends Model
{
    use LoggingTraits;

    protected $with = ['checkList', 'demands', 'statusHistory', 'files'];
    protected $hidden = ['check_list_id'];

    protected $fillable = [
        'image',
        'image_size',
        'image_crop',
        'coor',
        'status',
        'desc',
        'date_elimination',
        'scale',
    ];

    protected $dates = [
        'date_elimination',
    ];

    public function statusHistory()
    {
        return $this->hasMany(CheckListItemStatusHistory::class);
    }

    public function files()
    {
        return $this->hasMany(CheckListImage::class);
    }

    public function checkList()
    {
        return $this->belongsTo(CheckList::class, 'check_list_id')->setEagerLoads([]);
    }

    public function building()
    {
        return $this->hasOneThrough(
            Building::class,
            CheckList::class,
            'id',
            'id',
            'check_list_id',
            'building_id'
        )->setEagerLoads([]);
    }

    public function contractor()
    {
        return $this->hasOneThrough(
            Contractor::class,
            CheckList::class,
            'id',
            'id',
            'check_list_id',
            'contractor_id'
        )->setEagerLoads([]);
    }

    public function work()
    {
        return $this->hasOneThrough(
            Work::class,
            CheckList::class,
            'id',
            'id',
            'check_list_id',
            'work_id'
        );
    }

    public function demands()
    {
        return $this->hasMany(CheckListDemand::class);
    }

    public function getImages()
    {
        $result = [];

        foreach ($this->files as $file) {
            $path = storage_path('app/files/markers/' . $file->filename);
            $ext = pathinfo($path)['extension'];

            if (file_exists($path) !== true || !in_array($ext, ['jpg','jpeg','png'])) {
                continue;
            }

            $result[] = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($path));
        }
        return $result;
    }

}
