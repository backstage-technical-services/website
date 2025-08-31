<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class ContactMenuComposer implements ViewComposer
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
        $menu = $this->menu->make('contactMenu', function (Builder $menu) {
            $menu->add('General Enquiries', route('contact.enquiries'));
            $menu->add('Book Us', route('contact.book'))->active('contact/book');
            $menu->add('Provide Feedback', route('contact.feedback'));
        });

        $view->with('menu', $menu->asUl(['class' => 'nav nav-tabs']));
    }
}
