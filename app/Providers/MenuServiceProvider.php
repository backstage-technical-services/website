<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Menu\Items\Contents\Link;
use Menu\Menu;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->composeMainMenu();
        $this->composeContactMenu();
        $this->composeMemberMenus();
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

    /**
     * Make the main menu.
     */
    private function composeMainMenu()
    {
        View::composer('app.includes.menu', function ($view) {
            // Set up some permissions
            $user         = Auth::user();
            $isRegistered = Auth::check();
            $isMember     = $isRegistered && $user->isMember();
            $isAdmin      = $isRegistered && $user->isAdmin();

            // Create the parent menu
            $menu = Menu::handler('mainMenu');

            // Parent menu
            $menu->add(route('home'), 'Home');
            $menu->add(route('page.show', ['slug' => 'about']), 'About Us');
            $menu->add('#', 'Media', Menu::items('media'))->activePattern('^\/media');
            $menu->add(route('committee.view'), 'The Committee');
            $menu->add(route('event.diary'), 'Events Diary', Menu::items('events'))->activePattern('^\/events');
            $menu->add(route('auth.login'), 'Members\' Area', Menu::items('members'))->activePattern('^\/members');
            $menu->add(route('resource.search'), 'Resources', Menu::items('resources'))->activePattern('^\/resources');
            $menu->add(route('contact.book'), 'Enquiries & Book Us')->activePattern('^\/contact\/book');

            // Media sub-menu
            $media = $menu->find('media');
            $media->add(route('media.image.index'), 'Image Gallery')->activePattern('^\/media\/images')
                  ->add(route('media.videos.index'), 'Videos')->activePattern('^\/media\/videos');

            if ($isRegistered) {
                // Events sub-menu
                if ($isMember) {
                    $menu->find('events')
                         ->add(route('event.report'), 'Submit event report')->activePattern('^\/events\/report');
                }
                if ($isAdmin) {
                    $menu->find('events')
                         ->add(route('event.index'), 'View all events')
                         ->add(route('event.create'), 'Create a new event');
                }

                // Members' area sub-menu
                $menu->find('members')
                     ->add(route('member.profile'), 'My Profile', Menu::items('members.profile'), [], ['class' => 'profile']);
                if ($isMember) {
                    $menu->find('members')
                         ->add(route('membership.view'), 'The Membership', Menu::items('members.users'), [], ['class' => 'admin-users'])
                         ->add(route('quotes.index'), 'Quotes Board')
                         ->add('#', 'Equipment', Menu::items('members.equipment'), [], ['class' => 'equipment'])
                         ->add('#', 'Training', Menu::items('members.training'), [], ['class' => 'training'])
                         ->add('#', 'Other', Menu::items('members.misc'), [], ['class' => 'misc'])
                         ->raw('', null, ['class' => 'divider'])
                         ->add(route('contact.near-miss'), 'Report a Near Miss')
                         ->add(route('contact.accident'), 'Report an Accident')
                         ->raw('', null, ['class' => 'divider']);
                }
                $menu->find('members')
                     ->add(route('auth.logout'), 'Log out');

                // Profile sub-menu
                if ($isMember) {
                    $menu->find('members.profile')
                         ->add(route('member.profile', ['tab' => 'events']), 'My events')
                         ->add(route('member.profile', ['tab' => 'training']), 'My training');
                }

                // Users sub-menu
                if ($isAdmin) {
                    $menu->find('members.users')
                         ->add(route('user.index'), 'View all users')
                         ->add(route('user.create'), 'Create a new user');
                }

                // Equipment sub-menu
                if ($isMember) {
                    $menu->find('members.equipment')
                         ->add(route('equipment.assets'), 'Asset register')
                         ->add(route('equipment.repairs.index'), 'View repairs db')
                         ->add(route('equipment.repairs.create'), 'Report broken kit');
                }

                // Training sub-menu
                if ($isMember) {
                    $menu->find('members.training')
                         ->add(route('training.skill.index'), 'View skills')->activePattern('^\/training\/skills');
                }
                if ($isAdmin) {
                    $menu->find('members.training')
                         ->add(route('training.category.index'), 'View categories')
                         ->add(route('training.skill.proposal.index'), 'Review applications')->activePattern('^\/training\/proposals')
                         ->add(route('training.skill.log'), 'Skills log');
                }

                // Other sub-menu
                if ($isMember) {
                    $menu->find('members.misc')
                         ->add(route('election.index'), 'Committee elections')->activePattern('^\/elections')
                         ->add(route('award.season.index'), 'Awards')->activePattern('^\/awards');
                }
                if ($isAdmin) {
                    $menu->find('members.misc')
                         ->add(route('backup.index'), 'Backups')
                         ->add(route('page.index'), 'Webpages');
                }
            }

            // Resources sub-menu
            $resources = $menu->find('resources');
            if ($isRegistered) {
                $resources->add(route('resource.search', ['category' => 'event-reports']), 'Event Reports')
                          ->add(route('resource.search', ['category' => 'event-risk-assessments']), 'Event Risk Assessments')
                          ->add(route('resource.search', ['category' => 'meeting-minutes']), 'Meeting Minutes')
                          ->add(route('resource.search', ['category' => 'meeting-agendas']), 'Meeting Agendas');
            }
            $resources->add(route('page.show', ['slug' => 'links']), 'Links')
                      ->add(route('page.show', ['slug' => 'faq']), 'FAQ');

            // Add the necessary classes
            $menu->addClass('nav navbar-nav')
                 ->getItemsByContentType(Link::class)
                 ->map(function ($item) {
                     if ($item->hasChildren()) {
                         $item->addClass('dropdown');
                         $item->getChildren()->getAllItems()->map(function ($childItem) use ($item) {
                             if ($childItem->isActive()) {
                                 $item->addClass('active');
                             }
                         });
                     }
                 });
            $menu->getAllItemLists()
                 ->map(function ($itemList) {
                     if ($itemList->hasChildren()) {
                         $itemList->addClass('dropdown-menu');
                     }
                 });

            // Render
            $view->with('mainMenu', $menu->render());
        });
    }

    /**
     * Make the sub menu for the contact section.
     */
    private function composeContactMenu()
    {
        View::composer('contact.shared', function ($view) {
            $menu = Menu::handler('contactMenu');
            $menu->add(route('contact.enquiries'), 'General Enquiries')
                 ->add(route('contact.book'), 'Book Us')->activePattern('\/contact\/book')
                 ->add(route('contact.feedback'), 'Provide Feedback');
            $menu->addClass('nav nav-tabs');
            $view->with('menu', $menu->render());
        });
    }

    /**
     * Make the sub menus for the member profile views.
     */
    private function composeMemberMenus()
    {
        view()->composer('members.view', function ($view) {
            $user = $view->getData()['user'];
            $menu = Menu::handler('profileMenu');
            if ($user->isActiveUser()) {
                $menu->add(route('member.profile', ['tab' => 'profile']), 'My Details')
                     ->add(route('member.profile', ['tab' => 'events']), 'Events')
                     ->add(route('member.profile', ['tab' => 'training']), 'Training');
            } else {
                $menu->add(route('member.view', ['username' => $user->username, 'tab' => 'profile']), 'Details')
                     ->add(route('member.view', ['username' => $user->username, 'tab' => 'events']), 'Events')
                     ->add(route('member.view', ['username' => $user->username, 'tab' => 'training']), 'Training');
            }
            $menu->addClass('nav nav-tabs');
            $view->with('menu', $menu->render());
        });
    }
}
