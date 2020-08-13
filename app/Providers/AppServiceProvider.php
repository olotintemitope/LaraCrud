<?php

namespace App\Providers;

use App\Contracts\BuilderServiceInterface;
use App\Contracts\ModelServiceInterface;
use app\Services\MigrationServiceBuilder;
use App\Services\ModelServiceBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //$this->app->bind(BuilderServiceInterface::class, ModelServiceBuilder::class);
        //$this->app->bind(BuilderServiceInterface::class, MigrationServiceBuilder::class);
    }
}
