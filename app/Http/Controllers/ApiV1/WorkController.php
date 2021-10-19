<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Models\Work;

class WorkController extends Controller
{
    public function all()
    {
        $works = Work::all();
        return $works;
    }
}
