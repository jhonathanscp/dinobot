<?php

namespace App\Providers;

use App\Interfaces\WhatsappProviderInterface;
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
            \App\Interfaces\WhatsAppProviderInterface::class,
            \App\Services\EvolutionProvider::class
        );

        $this->app->bind(
            WhatsappProviderInterface::class,
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
