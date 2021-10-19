<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\File;

class BuildingWork extends Model
{
    use LoggingTraits;

    protected $with = ['work', 'contractor', 'files'];

    protected $hidden = ['building_id', 'work_id', 'contractor_id'];

    protected $fillable = ['building_id', 'work_id', 'contractor_id'];

    protected $appends = ['images'];

    public $timestamps = false;

    public function work()
    {
        return $this->belongsTo('App\Models\Work');
    }

    public function contractor()
    {
        return $this->belongsTo('App\Models\Contractor');
    }

    public function files()
    {
        return $this->hasMany('App\Models\BuildingFile');
    }

    public function getImagesAttribute() {
        $result = [];

        /** @var BuildingFile $buildingFile */
        foreach ($this->files as $buildingFile) {
            $file = new File(storage_path('app/files/building/'.$buildingFile->filename));
            $mime = $file->getMimeType();
            if ($mime == 'application/pdf' && $buildingFile->page_count > 0) {
                for ($i = 0; $i < $buildingFile->page_count; ++$i) {
                    $result[] = [
                        'original' => route('file.image.show', [$file->getFilename(), 'page' => $i]),
                        'thumbnail' => route('file.image.thumbnail', [$file->getFilename(), 'page' => $i]),
                        'is_actual' => $buildingFile->is_actual,
                    ];
                }
            } else if (preg_match('/^image\//', $mime)) {
                $result[] = [
                    'original' => route('file.image.show', [$file->getFilename()]),
                    'thumbnail' => route('file.image.thumbnail', [$file->getFilename()]),
                    'is_actual' => $buildingFile->is_actual,
                ];
            }
        }

        return $result;
    }

}
