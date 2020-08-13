<?php

namespace App\Providers;

use App\Contracts\MigrationServiceInterface;
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
        $this->app->bind(ModelServiceInterface::class, ModelServiceBuilder::class);
        $this->app->bind(MigrationServiceInterface::class, MigrationServiceBuilder::class);
    }
}
