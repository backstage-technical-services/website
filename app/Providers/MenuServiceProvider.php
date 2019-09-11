<?php

namespace App\Providers;

use App\View\Composers\ContactMenuComposer;
use App\View\Composers\MainMenuComposer;
use App\View\Composers\MemberMenuComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('app.includes.menu', MainMenuComposer::class);
        View::composer('contact.shared', ContactMenuComposer::class);
        View::composer('members.view', MemberMenuComposer::class);
    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
