<?php

namespace App\Models;

use App\Jobs\BuildingPdfToImagesJob;
use App\Traits\LoggingTraits;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BuildingFile
 *
 * @property string filename Имя файла
 * @property int page_count Количество страниц
 * @property bool is_processing Находится ли в проуессе обработки
 *
 * @package App\Models
 */
class BuildingFile extends Model
{
    use LoggingTraits;

    protected $fillable = ['filename', 'page_count', 'is_processing', 'is_actual'];

    protected $hidden = ['building_work_id'];

    protected $appends = ['url'];

    protected $casts = [
        'is_processing' => 'boolean',
        'is_actual' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($file) {
            BuildingPdfToImagesJob::dispatch($file);
        });
    }

    public function getUrlAttribute()
    {
        return route('file.show', ['building', $this->filename]);
    }
}
