<?php

namespace App\Providers;

use App\Interface\Weather;
use App\Service\OpenMeteoWeatherService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        $this->app->scoped(Weather::class, function () {
            return new OpenMeteoWeatherService(
                weatherUrl: config('services.weather.url'),
                geoUrl: config('services.weather.geo'),
            );
        });
    }
}
