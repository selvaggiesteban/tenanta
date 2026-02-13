<?php

namespace App\Providers;

use App\Services\AI\AIManager;
use App\Services\AI\Tools\ToolExecutor;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AIManager::class, function ($app) {
            return new AIManager();
        });

        $this->app->singleton(ToolExecutor::class, function ($app) {
            return new ToolExecutor();
        });

        $this->app->alias(AIManager::class, 'ai');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
