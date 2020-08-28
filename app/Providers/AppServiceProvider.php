<?php

namespace Laztopaz\Providers;

use Illuminate\Support\ServiceProvider;
use Laztopaz\Builders\ModelServiceBuilder;
use Laztopaz\Services\ModelFileWriterService;

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
        //$this->app->bind(ModelFileWriterService::class, ModelServiceBuilder::class);
    }
}
