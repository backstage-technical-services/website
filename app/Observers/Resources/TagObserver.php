<?php

namespace App\Observers\Resources;

use App\Logger;
use App\Models\Resources\Tag;
use App\Observers\ModelObserver;

class TagObserver extends ModelObserver
{
    public function created(Tag $tag)
    {
        Logger::log('resource-tag.create', true, $this->getCreatedAttributes($tag));
    }

    public function updated(Tag $tag)
    {
        Logger::log('resource-tag.edit', true, $this->getUpdatedAttributes($tag));
    }

    public function deleted(Tag $tag)
    {
        Logger::log('resource-tag.delete', true, $this->getDeletedAttributes($tag));
    }
}
