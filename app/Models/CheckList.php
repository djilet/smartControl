<?php

namespace App\Models;

use App\Helpers\PdfHelper;
use App\Traits\LoggingTraits;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CheckList extends Model
{
    use LoggingTraits, SoftDeletes;

    protected $fillable = [
        'type',
        'contractor_id',
        'building_id',
        'floor',
        'work_id',
        'contractor_representative',
        'number_ks',
        'sum_ks',
        'date_from',
        'date_to',
        'status',
        'pdf_filename',
    ];

    protected $dates = [
        'date',
        'date_from',
        'date_to',
    ];

    protected $hidden = ['contractor_id', 'work_id', 'building_id', 'creator_id'];

    protected $with = ['building', 'work', 'contractor', 'creator', 'items'];

    protected $casts = [
        'accepted' => 'boolean',
    ];

    public function building()
    {
        return $this->belongsTo('App\Models\Building')->setEagerLoads([]);
    }

    public function work()
    {
        return $this->belongsTo('App\Models\Work');
    }

    public function contractor()
    {
        return $this->belongsTo('App\Models\Contractor');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id')->setEagerLoads([]);
    }

    public function items()
    {
        return $this->hasMany('App\Models\CheckListItem');
    }

    public function itemsWithDemands()
    {
        return $this->hasMany('App\Models\CheckListItem')
            ->has('demands');
    }

    public function scopeContractorRepresentativeSearch($query, $value)
    {
        $query
            ->setEagerLoads([])
            ->select(
                'contractor_representative',
                DB::raw('MATCH(`contractor_representative`) AGAINST(?) as `weight`')
            )
            ->distinct();
        $query->whereRaw(DB::raw('MATCH(`contractor_representative`) AGAINST(?)'), [$value, $value]);
        $query->orderBy('weight', 'desc');
        return $query;
    }
    
    public function images()
    {
		$result = [];

		foreach($this->items as $item) {
			$images = $item->getImages();
			if (!empty($images)) {
				$result = array_merge($result, $images);
			}
		}
		
		return $result;
	}

    public function schemaBase64Images()
    {
        $result = [];
        $list = $this->itemsWithDemands->groupBy(function ($item, $key) {
            return $item->image.'||'.$item->image_crop;
        });

        foreach ($list as $key => $markers) {
            list($image, $crop) = explode('||', $key);

            $path = storage_path('app/files/building/'.strtok($image, '?'));
            if (file_exists($path) !== true) {
                continue;
            }

            preg_match('/\?page=(\d+)/', $image, $page);
            $imagick = new \Imagick();

            if (mime_content_type($path) == 'application/pdf') {
                $pdfHelper = new PdfHelper('building', strtok($image, '?'));
                $image = $pdfHelper->image($page[1] ?? 0, $crop);
                $imagick->readImageBlob($image);
            } else {
                $imagick->readImage($path);
                if (empty($crop) == false) {
                    $crop = explode(',', $crop);
                    if (count($crop) == 4) {
                        $x = $crop[0] ?? 0;
                        $y = $crop[1] ?? 0;
                        $width = $crop[2] ?? 0;
                        $height = $crop[3] ?? 0;
                        
                        if ($width != 0 && $height != 0) {
                            $imagick->cropImage($width, $height, $x, $y);
                        }
                    }
                }
            }

            $padding = 13;
            $imgWidth = $imagick->getImageWidth();
            $imgHeight = $imagick->getImageHeight();
            $imagick->setImageBackgroundColor('white');
            $imagick->extentImage(
                $imgWidth + $padding*2,
                $imgHeight + $padding*2,
                -$padding,
                -$padding*2
            );
            
            //scale labels for PDF
            $labelScale = 1 + intval($imgWidth / 1000);
            
            foreach ($markers as $marker) {
                if ($marker->image_size != '') {
                    list($w, $h) = explode(',', $marker->image_size);
                    $scale = $imgWidth / $w;
                } else {
                    $scale = $marker->scale;
                }

                try {
                    $coorArray = explode(',', $marker->coor);
                    $coorX = ($coorArray[0] ?? 0) * $scale + $padding;
                    $coorY = ($coorArray[1] ?? 0) * $scale + $padding * 2;
                    $width = ($coorArray[2] ?? 0) * $scale;
                    $height = ($coorArray[3] ?? 0) * $scale;
                } catch (\Exception $exception) {
                    continue;
                }

                $color = '#000000';
                switch ($marker->status) {
                    case 'red':
                        $color = '#EA2127';
                        break;

                    case 'green':
                        $color = '#005000';
                        break;

                    case 'yellow':
                        $color = '#FFBC00';
                        break;
                }

                if (empty($width) || empty($height)) {
                    $markerImg = new \Imagick();
                    $markerImg->readImage(public_path('images/marker/room_' . $marker->status . '.png'));
                    $markerImg->scaleImage(24 * $labelScale, 24 * $labelScale);
                    $imagick->compositeImage($markerImg, \Imagick::COMPOSITE_ATOP, $coorX - (12 * $labelScale), $coorY - (24 * $labelScale));

                    $circleImage = $this->getCircleWithNumber($marker->id, $color, $labelScale);
                    $imagick->compositeImage(
                        $circleImage,
                        \Imagick::COMPOSITE_ATOP,
                        $coorX + (12 * $labelScale),
                        $coorY - (24 * $labelScale)
                    );
                }
                else {
                    $draw = new \ImagickDraw();
                    $strokeColor = new \ImagickPixel($color);

                    $draw->setStrokeColor($strokeColor);
                    $draw->setStrokeOpacity(1);
                    $draw->setStrokeWidth(2*$labelScale);
                    $draw->setFillOpacity(0);
                    $draw->rectangle($coorX, $coorY, $coorX + $width, $coorY + $height);
                    $imagick->drawImage($draw);

                    $circleImage = $this->getCircleWithNumber($marker->id, $color, $labelScale);
                    $imagick->compositeImage(
                        $circleImage,
                        \Imagick::COMPOSITE_ATOP,
                        $coorX + $width - ($circleImage->getImageWidth() / 2),
                        $coorY - ($circleImage->getImageHeight() / 2)
                    );
                }
            }

            $minimumWidth = $imagick->getImageHeight() * 0.625;
            if ($imagick->getImageWidth() < $minimumWidth) {
                $padding = ($minimumWidth - $imagick->getImageWidth()) / 2;
                $imgWidth = $imagick->getImageWidth();
                $imgHeight = $imagick->getImageHeight();
                $imagick->setImageBackgroundColor('white');
                $imagick->extentImage(
                    $imgWidth + $padding*2,
                    $imgHeight,
                    -$padding,
                    0
                );
            }

            $result[] = 'data:'.$imagick->getImageMimeType().';base64,'.base64_encode($imagick->getImageBlob());
        }

        return $result;
    }

    private function getCircleWithNumber($number, $backgroundColor, $labelScale): \Imagick
    {
        $imagick = new \Imagick();
        $imagick->setResolution(72, 72);

        $drawText = new \ImagickDraw();
        $drawText->setFillOpacity(1);
        $drawText->setStrokeColor(new \ImagickPixel('#ffffff'));
        $drawText->setFillColor(new \ImagickPixel('#ffffff'));
        $drawText->setFontSize(12 * $labelScale);
        $drawText->setStrokeWidth(1);
        $drawText->setStrokeOpacity(1);
        $drawText->setTextAlignment(\Imagick::ALIGN_LEFT);
        $drawText->setFont(storage_path('fonts/PT Astra Serif_Regular.ttf'));

        $metrics = $imagick->queryFontMetrics($drawText, $number);

        $width = $metrics['textWidth'] + (23 * $labelScale);
        $height = (23 * $labelScale);
        $centerX = intval($width / 2);
        $centerY = intval($height / 2);
        $radius = intval($height / 2);

        $imagick->newImage($width, $height, new \ImagickPixel('transparent'));
        $imagick->setImageFormat("jpeg");
        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $imagick->setImageColorspace(\Imagick::COLORSPACE_SRGB);

        $draw = new \ImagickDraw();
        $draw->setFillOpacity(1);
        $draw->setFillColor(new \ImagickPixel($backgroundColor));
        $draw->rectangle($radius, 0, $width-$radius, $height);
        $draw->circle($radius, $centerY, $radius*2, $centerY);
        $draw->circle($width-$radius-2, $centerY, $width-2, $centerY);

        $drawText->annotation(
            ($width / 2) - $metrics['textWidth'] / 2,
            ($height / 2) + $metrics['textHeight'] / 2 + $metrics['descender'],
            $number
        );
        $imagick->drawImage($draw);
        $imagick->drawImage($drawText);
        return $imagick;
    }

    public function createPdf()
    {
        $pdf = PDF::loadView('pdf.prescription', ['prescription' => $this]);
        $filename = Str::uuid().'.pdf';
        $storage = Storage::disk('local');
        $result = $storage->put('files/check-lists/'.$filename, $pdf->output());

        if ($result == true) {
            return $filename;
        }

        return $result;
    }
}
