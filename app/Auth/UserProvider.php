<?php

namespace App\Auth;

use bnjns\LaravelNotifications\Facades\Notify;
use Illuminate\Auth\EloquentUserProvider;

class UserProvider extends EloquentUserProvider
{
    /**
     * Track whether the error message has been created or not.
     *
     * @var bool
     */
    private static $MessageCreated = false;

    /**
     * Override the Eloquent method to check the account is enabled.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->checkUserStatus(parent::retrieveByToken($identifier, $token));
    }

    /**
     * Override the Eloquent method to check the account is enabled.
     *
     * @param array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->checkUserStatus(parent::retrieveByCredentials($credentials));
    }

    /**
     * Override the Eloquent method to check the account is enabled.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($id)
    {
        return $this->checkUserStatus(parent::retrieveById($id));
    }

    /**
     * Method to check the status of the user account and create
     * the error message if the user's account is disabled.
     *
     * @param  $user
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private function checkUserStatus($user)
    {
        if ($user && !$user->status) {
            session()->flush();
            session()->regenerate();

            if (!self::$MessageCreated) {
                Notify::error(
                    'Please [contact the secretary](mailto:sec@bts-crew.com) to have your account re-enabled.',
                    'Account disabled'
                );
                self::$MessageCreated = true;
            }

            return null;
        }

        return $user;
    }
}