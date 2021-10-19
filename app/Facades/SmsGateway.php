<?php

namespace App\Facades;

use App\Services\Contracts\SmsServiceContract;
use Illuminate\Support\Facades\Facade;

class SmsGateway extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SmsServiceContract::class;
    }
}