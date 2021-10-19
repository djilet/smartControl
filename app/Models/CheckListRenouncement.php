<?php

namespace App\Models;

use App\Traits\LoggingTraits;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckListRenouncement extends Model
{
    use LoggingTraits;

    protected $fillable = [
        'user_id',
        'check_list_id',
        'pdf_filename',
    ];

    protected $with = ['prescription', 'items'];

    public function prescription()
    {
        return $this->belongsTo(CheckList::class, 'check_list_id');
    }

    public function items()
    {
        return $this->belongsToMany(
            CheckList::class,
            'check_list_renouncement_items',
            'renouncement_id',
            'check_list_id',
            'id',
            'id'
        );
    }

    public function createPdf()
    {
        $pdf = PDF::loadView('pdf.renouncement', ['renouncement' => $this]);
        $filename = Str::uuid().'.pdf';
        $storage = Storage::disk('local');
        $result = $storage->put('files/renouncements/'.$filename, $pdf->output());

        if ($result == true) {
            return $filename;
        }

        return $result;
    }
}
