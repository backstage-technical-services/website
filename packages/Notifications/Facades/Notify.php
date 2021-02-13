<?php

namespace Package\Notifications\Facades;

use Illuminate\Support\Facades\Facade;
use Package\Notifications\NotificationHandler;

class Notify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NotificationHandler::class;
    }
}
