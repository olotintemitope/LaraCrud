<?php

namespace App\Providers;

use app\Contracts\BuilderServiceTrait;
use App\Contracts\MigrationServiceInterface;
use App\Contracts\ModelServiceInterface;
use app\Services\MigrationService;
use App\Services\ModelService;
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
        $this->app->bind(ModelServiceInterface::class, ModelService::class);
        $this->app->bind(MigrationServiceInterface::class, MigrationService::class);
    }
}
