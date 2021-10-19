<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Models\BuildingFile;
use App\Models\CheckListImage;
use App\Models\TemporaryFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use Symfony\Component\HttpFoundation\File\File;

class FileController extends Controller
{

    public function file(Request $request, $folder, $filename)
    {
        $path = 'files/'.$folder.'/'.$filename;
        $storage = Storage::disk('local');

        if (!$storage->exists($path)) {
            return response()->json(['errors' => 'File not found'], 404);
        }

        if ($folder == 'building') {
            $file = BuildingFile::where('filename', $filename)->firstOrFail();
        } else if ($folder == 'markers') {
            $file = CheckListImage::findOrFail($filename);
        }

        $mime = $storage->mimeType($path);
        return response(
            $storage->get($path), 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.($file->user_filename ?? $filename).'"'
            ]
        );
    }

    public function image(Request $request, $filename)
    {
        $path = storage_path('app/files/building/'.$filename);
        if (file_exists($path) !== true) {
            return response()->json(['errors' => 'File not found'], 404);
        }

        $isOriginal = intval($request->get('original', 0));
        $addWidthHeader = intval($request->get('addWidthHeader', 0));
        $crop = $request->get('crop', '');
        
        $headers = [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ];
        
        $file = new File($path);
        $mime = $file->getMimeType();
        if ($mime == 'application/pdf') {
            $page = $request->get('page', 0);

            $cacheKey = $filename . '[' . $page . ']' . $crop.';original='.$isOriginal;
            if ($image = Cache::get($cacheKey)) {
                if($addWidthHeader) {
                    $headers['X-Image-Width'] = getimagesizefromstring($image)[0] ?? 0;
                }
                return response($image, 200, $headers);
            }

            $image = $this->convertWithPdfToPpm($path, $page, false, $isOriginal, $crop);
            Cache::put($cacheKey, $image, now()->addDays(5));
            if($addWidthHeader) {
                $headers['X-Image-Width'] = getimagesizefromstring($image)[0] ?? 0;
            }
        }
        else
        {
            $cacheKey = $filename;
            if ($crop != '') {
                $cacheKey .= '&crop='. $crop;
            }
            $cacheKey .= ';original='.$isOriginal;

            if ($image = Cache::get($cacheKey)) {
                if($addWidthHeader) {
                    $headers['X-Image-Width'] = getimagesizefromstring($image)[0] ?? 0;
                }
                return response($image, 200, $headers);
            }

            $image = $this->convertWithImagick($path, null, false, $isOriginal, $crop);
            Cache::put($cacheKey, $image, now()->addDays(5));
            if($addWidthHeader) {
                $headers['X-Image-Width'] = getimagesizefromstring($image)[0] ?? 0;
            }
        }

        return response($image, 200, $headers);
    }

    public function thumbnail(Request $request, $filename)
    {
        $path = storage_path('app/files/building/'.$filename);
        if (file_exists($path) !== true) {
            return response()->json(['errors' => 'File not found'], 404);
        }

        $file = new File($path);
        $mime = $file->getMimeType();
        if ($mime == 'application/pdf') {
            $page = $request->get('page', 0);
            $cacheKey = $filename . 'thumbnail.[' . $page . ']';
            if ($image = Cache::get($cacheKey)) {
                return response($image, 200, [
                    'Content-Type' => 'image/jpeg',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"'
                ]);
            }

            $image = $this->convertWithPdfToPpm($path, $page, true);
            Cache::put($cacheKey, $image, now()->addDays(5));
        }
        else
        {
            $cacheKey = $filename . 'thumbnail';
            if ($image = Cache::get($cacheKey)) {
                return response($image, 200, [
                    'Content-Type' => 'image/jpeg',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"'
                ]);
            }

            $image = $this->convertWithImagick($path, null, true);
            Cache::put($cacheKey, $image, now()->addDays(5));
        }

        return response($image, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    }

    private function convertWithImagick(string $path, ?int $page, bool $isThumbnail = false, $isOriginal = false, $crop = '')
    {
        $imagick = new Imagick();
        if ($isThumbnail || !$isOriginal) {
            $imagick->setResolution(72, 72);
        } else {
            $imagick->setResolution(300, 300);
        }

        if (!is_null($page)) {
            $imagick->readImage($path . '[' . $page . ']');
            $imagick->setOption('pdf:fit-to-page', true);
        } else {
            $imagick->readImage($path);
        }
        $imagick->setImageFormat("jpeg");
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

        if ($isThumbnail) {
            $imagick->setImageCompressionQuality(70);
            $imagick->scaleImage(0, 250);
        } else if ($isOriginal) {
            $imagick->setImageCompressionQuality(90);
            $this->cropImagick($imagick, $crop ?? '');
        } else {
            $imagick->setImageCompressionQuality(90);
            $imagick->scaleImage(0, 800);
            $this->cropImagick($imagick, $crop ?? '');
        }

        return $imagick->getImageBlob();
    }

    private function cropImagick(Imagick $imagick, string $crop): Imagick
    {
        $crop = explode(',', $crop);
        if (count($crop) != 4) {
            return $imagick;
        }

        $x = $crop[0] ?? 0;
        $y = $crop[1] ?? 0;
        $width = $crop[2] ?? 0;
        $height = $crop[3] ?? 0;

        if ($width == 0 || $height == 0) {
            return $imagick;
        }

        $imagick->cropImage($width, $height, $x, $y);
        return $imagick;
    }
    
    private function convertWithPdfToPpm(string $path, ?int $page, bool $isThumbnail = false, $isOriginal = false, $crop = '')
    {
        $command = 'pdftoppm -jpeg';
        
        if (!is_null($page)) {
            $command .= ' -f '.($page+1).' -l '.($page+1);
        }
        
        if ($isThumbnail || !$isOriginal) {
            $command .= ' -r 100  -scale-to 250';
        }
        else if ($isOriginal){
            $command .= ' -r 300';        
        }
        else {
            $command .= ' -r 300 -scale-to 800';
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
        $image = trim(`$command`);
        return $image;
    }

    public function upload(Request $request)
    {
        $file = $request->file('file');

        if (is_array($file)) {
            $result = [];

            foreach ($file as $item) {
                $filename = $this->saveFileToTmpDirectory($item);
                $result['filename'][] = $filename;
            }

            return response()->json($result, 200);
        }

        $filename = $this->saveFileToTmpDirectory($file);

        return response()->json([
            'filename' => $filename
        ], 200);
    }

    private function saveFileToTmpDirectory(UploadedFile $file)
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('tmp', $filename);

        $tmpFileDb = new TemporaryFile();
        $tmpFileDb->id = $filename;
        $tmpFileDb->user_filename = $file->getClientOriginalName();
        $tmpFileDb->created_at = Carbon::now();
        $tmpFileDb->save();

        return $filename;
    }
}
