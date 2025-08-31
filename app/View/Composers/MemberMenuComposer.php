<?php

namespace App\View\Composers;

use App\Models\Users\User;
use Illuminate\View\View;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class MemberMenuComposer implements ViewComposer
{
    /**
     * @var Menu
     */
    private $menu;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    public function compose(View $view)
    {
        /** @var User $user */
        $user = $view->getData()['user'];

        $menu = $this->menu->make('membersMenu', function (Builder $menu) use ($user) {
            if ($user->isActiveUser()) {
                $menu->add('My Details', route('member.profile', ['tab' => 'profile']));
                $menu->add('Events', route('member.profile', ['tab' => 'events']));
                $menu->add('Training', route('member.profile', ['tab' => 'training']));
            } else {
                $menu->add('Details', route('member.view', ['username' => $user->username, 'tab' => 'profile']));
                $menu->add('Events', route('member.view', ['username' => $user->username, 'tab' => 'events']));
                $menu->add('Training', route('member.view', ['username' => $user->username, 'tab' => 'training']));
            }
        });

        $view->with('menu', $menu->asUl(['class' => 'nav nav-tabs']));
    }
}
