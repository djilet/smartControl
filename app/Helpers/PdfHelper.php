<?php


namespace App\Helpers;


use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\File\File;

class PdfHelper
{
    private string $dirname;
    private string $filename;

    /**
     * PdfHelper constructor.
     * @param string $dirname   Имя папки в storage/app/files
     * @param string $filename  Имя файла
     */
    public function __construct(string $dirname, string $filename)
    {
        $this->dirname = $dirname;
        $this->filename = $filename;
    }

    /**
     * Преобразование страницы pdf в изображение
     *
     * @param int $page     Номер страницы
     * @param string $crop  Обрезка в формате "x,y,width,height"
     * @return string|null  Изображение
     */
    public function image(int $page, string $crop = '')
    {
        return $this->toImage($page, false, $crop);
    }

    /**
     * Преобразование страницы pdf в изображение для превью
     *
     * @param int $page     Номер страницы
     * @param string $crop  Обрезка в формате "x,y,width,height"
     * @return string|null  Изображение
     */
    public function thumbnail(int $page, string $crop = '')
    {
        return $this->toImage($page, true, $crop);
    }

    /**
     * Преобразование страницы pdf в изображение
     *
     * @param int $page         Номер страницы
     * @param bool $isThumbnail Является ли изображение превью
     * @param string $crop      Обрезка в формате "x,y,width,height"
     * @return string|null      Изображение
     */
    private function toImage(int $page, bool $isThumbnail = false, string $crop = ''): ?string
    {
        $path = storage_path('app/files/'.$this->dirname.'/'.$this->filename);
        if (file_exists($path) !== true) {
            return null;
        }

        $file = new File($path);
        $mime = $file->getMimeType();
        if ($mime != 'application/pdf') {
            return null;
        }

        if ($isThumbnail) {
            $cacheKey = $this->filename . 'thumbnail.[' . $page . ']';
        } else {
            $cacheKey = $this->filename . '[' . $page . ']' . $crop . ';original=1';
        }
        if ($image = Cache::get($cacheKey)) {
            return $image;
        }

        $image = $this->convertWithPdfToPpm($path, $page, $isThumbnail, $crop);
        Cache::put($cacheKey, $image, now()->addDays(180));

        return $image;
    }

    private function convertWithPdfToPpm(string $path, int $page, bool $isThumbnail = false, $crop = '')
    {
        $command = 'pdftoppm -jpeg';

        if (!is_null($page)) {
            $command .= ' -f '.($page+1).' -l '.($page+1);
        }

        if ($isThumbnail) {
            $command .= ' -r 100  -scale-to 250';
        }
        else {
            $command .= ' -r 300';
        }

        $crop = explode(',', $crop);
        if (count($crop) == 4) {
            $x = $crop[0] ?? 0;
            $y = $crop[1] ?? 0;
            $width = $crop[2] ?? 0;
            $height = $crop[3] ?? 0;
            if ($width > 0 && $height > 0) {
                $command .= ' -x '.$x.' -y '.$y.' -W '.$width.' -H '.$height;
            }
        }

        $command .= ' '.$path.' 2>/dev/null';

        return trim(`$command`);
    }

}
