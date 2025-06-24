<?php

namespace App\Providers;

use App\Services\ConversationService;
use App\Services\WeatherService;
use Illuminate\Support\ServiceProvider;

class ConversationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ConversationService::class, function ($app) {
            return new ConversationService(
                $app->make(WeatherService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
