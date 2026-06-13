<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\CarAvailabilityServiceInterface;
use App\Services\CarAvailabilityService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CarAvailabilityServiceInterface::class, CarAvailabilityService::class);
    }

    public function boot(): void
    {
        //
    }
}