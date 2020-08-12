<?php

namespace App\Providers;

use App\Contracts\ModelServiceInterface;
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
    public function register()
    {
        $this->app->bind(ModelServiceInterface::class, ModelService::class);
    }
}
