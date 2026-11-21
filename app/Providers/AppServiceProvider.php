<?php

namespace App\Providers;

use App\Http\Controllers\ListingController;
use App\Models\User;
use App\Services\ListingService;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('manage-marketplace', fn (User $user): bool => $user->is_super_admin && $user->suspended_at === null);
    }
}
