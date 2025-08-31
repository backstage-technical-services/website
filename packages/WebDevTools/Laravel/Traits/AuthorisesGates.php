<?php

namespace Package\WebDevTools\Laravel\Traits;

use Illuminate\Auth\Access\AuthorizationException;

trait AuthorisesGates
{
    /**
     * Create a method similar to authorize() for use with Gates.
     *
     * @param $action
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeGate($action)
    {
        $denied = call_user_func_array('Gate::denies', func_get_args());
        if ($denied) {
            throw new AuthorizationException();
        }
    }
}
