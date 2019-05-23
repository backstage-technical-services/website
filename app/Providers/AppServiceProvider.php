<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::macro('tzUser', function(){
            return $this->setTimezone(config('bts.user_tz'));
        });
        Carbon::macro('tzServer', function(){
            return $this->setTimezone(config('bts.server_tz'));
        });
        Carbon::macro('createFromUser', function($timestamp, $format='Y-m-d H:i') {
            return Carbon::createFromFormat($format, $timestamp, config('bts.user_tz'))->tzServer();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
