<?php

namespace App\Jobs;

use App\Models\BuildingFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Imagick;
use Symfony\Component\HttpFoundation\File\File;

class BuildingPdfToImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private BuildingFile $buildingFile;


    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param BuildingFile $file
     */
    public function __construct(BuildingFile $file)
    {
        $this->buildingFile = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = storage_path('app/files/building/'.$this->buildingFile->filename);
        if (file_exists($path) !== true) {
            return;
        }

        $file = new File($path);
        $mime = $file->getMimeType();
        if ($mime != 'application/pdf') {
            BuildingFile::withoutEvents(function() {
                $this->buildingFile->update([
                    'page_count' => 1,
                    'is_processing' => false,
                ]);
            });
            return;
        }

        $countPages = intval(`pdfinfo $path | grep Pages | sed 's/[^0-9]*//'`);

        for ($i = 1; $i <= $countPages; ++$i) {
            /*$image = trim(`pdftoppm -jpeg -f $i -l $i -r 300 $path 2>/dev/null`);
            $this->cacheImage($image, $this->buildingFile->filename, $i-1, 0);*/

            $image = trim(`pdftoppm -jpeg -f $i -l $i -r 100 -scale-to 250 $path 2>/dev/null`);
            $this->cacheImage($image, $this->buildingFile->filename, $i-1);
        }

        BuildingFile::withoutEvents(function() use($countPages) {
            $this->buildingFile->update([
                'page_count' => $countPages,
                'is_processing' => false,
            ]);
        });
    }
    
    private function cacheImage(?string $image, string $filename, int $page)
    {
        if (empty($image)) {
            return;
        }

        $cacheKey = $filename . 'thumbnail.[' . $page . ']';
        //$cacheKey = $filename.'['.$page.'];original='.$isOriginal;
        Cache::put($cacheKey, $image, now()->addDays(180));
    }
}
