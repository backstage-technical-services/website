<?php

namespace App\Observers\Resources;

use App\Logger;
use App\Models\Resources\Resource;
use App\Observers\ModelObserver;
use bnjns\WebDevTools\Traits\DeletesDirectory;

class ResourceObserver extends ModelObserver
{
    use DeletesDirectory;

    public function created(Resource $resource)
    {
        Logger::log('resource.create', true, $this->getCreatedAttributes($resource));
    }

    public function updated(Resource $resource)
    {
        Logger::log('resource.edit', true, $this->getUpdatedAttributes($resource));
    }

    /**
     * Listen to the Resource deleted event.
     *
     * @param Resource $resource
     *
     * @return bool
     */
    public function deleted(Resource $resource)
    {
        Logger::log('resource.delete', true, $this->getDeletedAttributes($resource));

        // If a file, delete the directory
        if ($resource->isFile()) {
            $this->rmdir($resource->getPath());
        }
    }
}