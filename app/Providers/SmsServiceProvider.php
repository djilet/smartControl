<?php

namespace App\Providers;

use App\Services\Contracts\SmsServiceContract;
use App\Services\SigmaSmsService;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(SMSServiceContract::class, function ($app) {
            if (config('sms.sms_service') == 'SigmaSms') {
                return new SigmaSmsService();
            }
        });
    }
}
