<?php

namespace Package\WebDevTools\Laravel\Presenters;

class Bootstrap4NavbarRightPresenter extends Bootstrap4NavbarPresenter
{
    /**
     * Get open tag wrapper.
     *
     * @return string
     */
    public function getOpenTagWrapper()
    {
        return PHP_EOL . '<div class="navbar-nav ml-auto navbar-right">' . PHP_EOL;
    }
}
