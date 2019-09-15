<?php


namespace App\View\Composers;


use Illuminate\View\View;

interface ViewComposer
{
    public function compose(View $view);
}