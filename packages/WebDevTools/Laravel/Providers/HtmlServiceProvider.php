<?php

namespace Package\WebDevTools\Laravel\Providers;

use Collective\Html\HtmlServiceProvider as CollectiveHtmlProvider;
use Package\WebDevTools\Laravel\Html\FormBuilder;

class HtmlServiceProvider extends CollectiveHtmlProvider
{
    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->singleton('form', function ($app) {
            $form = new FormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->token());

            return $form->setSessionStore($app['session.store']);
        });
    }
}
