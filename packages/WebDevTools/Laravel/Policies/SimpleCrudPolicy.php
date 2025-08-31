<?php

namespace Package\WebDevTools\Laravel\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class SimpleCrudPolicy
{
    use HandlesAuthorization;

    /**
     * Test whether the user can view the list of items.
     *
     * @param $user
     *
     * @return bool
     */
    public function index($user)
    {
        return $user->can($this->authorisationPrefix() . '.index');
    }

    /**
     * Test whether the user can create a new item.
     *
     * @param $user
     *
     * @return bool
     */
    public function create($user)
    {
        return $user->can($this->authorisationPrefix() . '.create');
    }

    /**
     * Test whether the user can view an item.
     *
     * @param      $user
     * @param null $model
     *
     * @return bool
     */
    public function view($user, $model = null)
    {
        return $user->can($this->authorisationPrefix() . '.item');
    }

    /**
     * Test whether the user can edit items.
     *
     * @param      $user
     * @param      $model
     *
     * @return bool
     */
    public function edit($user, $model = null)
    {
        return $user->can($this->authorisationPrefix() . '.edit');
    }

    /**
     * Test whether the user can delete items.
     *
     * @param      $user
     * @param      $model
     *
     * @return bool
     */
    public function delete($user, $model = null)
    {
        return $user->can($this->authorisationPrefix() . '.delete');
    }

    /**
     * Get the prefix for the authorisation string.
     *
     * @return string
     */
    protected function authorisationPrefix()
    {
        return isset($this->authorisationPrefix) ? $this->authorisationPrefix : '';
    }
}
