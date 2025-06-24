<?php
# cGFuZ29saW4=

namespace App\Providers;

use App\Services\AIService;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AIService::class, function ($app) {
            return new AIService();
        });

        // Bind interface if needed in the future
        $this->app->bind('ai', AIService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
