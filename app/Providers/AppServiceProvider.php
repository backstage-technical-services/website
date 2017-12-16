<?php

namespace App\Providers;

use App\Models\Resources\Resource;
use App\Observers\Resources\ResourceObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerObservers();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);
    }

    /**
     * List any observers that should be registered.
     */
    public function registerObservers()
    {
        Resource::observe(ResourceObserver::class);
    }
}
