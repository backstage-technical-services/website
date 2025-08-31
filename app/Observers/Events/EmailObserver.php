<?php

namespace App\Observers\Events;

use App\Logger;
use App\Models\Events\Email;
use App\Observers\ModelObserver;

class EmailObserver extends ModelObserver
{
    public function created(Email $email)
    {
        Logger::log('event.email', true, $this->getCreatedAttributes($email));
    }
}
