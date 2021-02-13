<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Package\Notifications\NotificationHandler;

class NotificationServiceProvider extends ServiceProvider
{
    private string $packageDir;
    
    public function __construct($app)
    {
        parent::__construct($app);
        
        $this->packageDir = base_path('packages/Notifications');
    }
    
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Config
        $this->publishes([
            $this->packageDir . '/config/notifications.php' => config_path('notifications.php'),
        ], 'config');

        // Published views
        $this->loadViewsFrom($this->packageDir . 'resources/views', 'laravel-notifications');
        $this->publishes([
            $this->packageDir . '/resources/views' => resource_path('views/vendor/laravel-notifications'),
        ], 'views');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(NotificationHandler::class, function () {
            return new NotificationHandler($this->app['session.store']);
        });
        $this->app->alias(NotificationHandler::class, 'notify');
    }
}
