<?php

namespace Package\WebDevTools\Laravel\Traits;

trait AllowsAdminOverride
{
    /**
     * Create a filter to allow admins to perform all actions.
     *
     * @param $user
     * @param $ability
     *
     * @return bool
     */
    public function before($user, $ability)
    {
        if ($user->can($this->adminPermissionName())) {
            return true;
        }
    }

    /**
     * Get the name of the overriding admin permission.
     *
     * @return string
     */
    protected function adminPermissionName()
    {
        return isset($this->adminPermissionName) ? $this->adminPermissionName : 'admin';
    }
}
