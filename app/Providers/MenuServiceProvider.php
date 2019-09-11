<?php

namespace App\Providers;

use App\View\Composers\MainMenuComposer;
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
        $this->composeContactMenu();
        $this->composeMemberMenus();
    }
    
    /**
     * Make the sub menu for the contact section.
     */
    private function composeContactMenu()
    {
        View::composer(
            'contact.shared',
            function ($view) {
//            $menu = Menu::handler('contactMenu');
//            $menu->add(route('contact.enquiries'), 'General Enquiries')
//                 ->add(route('contact.book'), 'Book Us')->activePattern('\/contact\/book')
//                 ->add(route('contact.feedback'), 'Provide Feedback');
//            $menu->addClass('nav nav-tabs');
//            $view->with('menu', $menu->render());
                $view->with('menu', '');
            }
        );
    }
    
    /**
     * Make the sub menus for the member profile views.
     */
    private function composeMemberMenus()
    {
        view()->composer(
            'members.view',
            function ($view) {
//            $user = $view->getData()['user'];
//            $menu = Menu::handler('profileMenu');
//            if ($user->isActiveUser()) {
//                $menu->add(route('member.profile', ['tab' => 'profile']), 'My Details')
//                     ->add(route('member.profile', ['tab' => 'events']), 'Events')
//                     ->add(route('member.profile', ['tab' => 'training']), 'Training');
//            } else {
//                $menu->add(route('member.view', ['username' => $user->username, 'tab' => 'profile']), 'Details')
//                     ->add(route('member.view', ['username' => $user->username, 'tab' => 'events']), 'Events')
//                     ->add(route('member.view', ['username' => $user->username, 'tab' => 'training']), 'Training');
//            }
//            $menu->addClass('nav nav-tabs');
//            $view->with('menu', $menu->render());
                $view->with('menu', '');
            }
        );
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
