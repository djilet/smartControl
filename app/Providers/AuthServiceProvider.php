<?php

namespace App\Providers;

use App\Models\Building;
use App\Models\CheckList;
use App\Models\Contractor;
use App\Policies\BuildingPolicy;
use App\Policies\CheckListPolicy;
use App\Policies\ContractorPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
         Building::class => BuildingPolicy::class,
         Contractor::class => ContractorPolicy::class,
         CheckList::class => CheckListPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
