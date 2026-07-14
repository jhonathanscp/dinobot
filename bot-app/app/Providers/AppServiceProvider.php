<?php

namespace App\Providers;

use App\Interfaces\WhatsAppProviderInterface;
use App\Services\EvolutionProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            WhatsAppProviderInterface::class,
            EvolutionProvider::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
