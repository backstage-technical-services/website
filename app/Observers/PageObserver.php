<?php

namespace App\Observers;

use App\Logger;
use App\Models\Page;

class PageObserver extends ModelObserver
{
    public function created(Page $page)
    {
        Logger::log('page.create', true, $this->getCreatedAttributes($page));
    }

    public function updated(Page $page)
    {
        Logger::log('page.edit', true, $this->getUpdatedAttributes($page));
    }

    public function deleted(Page $page)
    {
        Logger::log('page.delete', true, $this->getDeletedAttributes($page));
    }
}