<?php

namespace App\Providers;

use App\Http\Controllers\ListingController;
use App\Services\ListingService;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when([ListingController::class, ListingService::class])
            ->needs(FilesystemAdapter::class)
            ->give(fn ($app) => $app->make(Factory::class)->disk('local'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
