<?php

namespace App\Providers;

use App\Services\Html\FormBuilder;

class HtmlServiceProvider extends \Collective\Html\HtmlServiceProvider
{
    /**
     * Register the form builder instance.
     */
    protected function registerFormBuilder()
    {
        $this->app->singleton('form', function ($app) {
            $form = new FormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->token());

            return $form->setSessionStore($app['session.store']);
        });
    }
}
