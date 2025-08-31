<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SearchToolsServiceProvider extends ServiceProvider
{
    private string $packageDir;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->packageDir = base_path('packages/SearchTools');
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Published views
        $this->loadViewsFrom($this->packageDir . 'resources/views', 'search-tools');
        $this->publishes(
            [
                $this->packageDir . '/resources/views' => resource_path('views/vendor/search-tools'),
            ],
            'views',
        );

        // Published assets
        $this->publishes(
            [
                $this->packageDir . '/resources/assets/css' => public_path('css/vendors/search_tools'),
            ],
            'styles',
        );
        $this->publishes(
            [
                $this->packageDir . '/resources/assets/js/search_tools.min.js' => public_path(
                    'js/vendors/search_tools/search_tools.js',
                ),
            ],
            'scripts',
        );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SearchTools::class, function () {
            return new SearchTools($this->app['request'], $this->app['router']);
        });
    }
}
