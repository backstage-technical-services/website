<?php

namespace App\Observers\Resources;

use App\Logger;
use App\Models\Resources\Category;
use App\Observers\ModelObserver;

class CategoryObserver extends ModelObserver
{
    public function created(Category $category)
    {
        Logger::log('resource-category.create', true, $this->getCreatedAttributes($category));
    }

    public function updated(Category $category)
    {
        Logger::log('resource-category.edit', true, $this->getUpdatedAttributes($category));
    }

    public function deleted(Category $category)
    {
        Logger::log('resource-category.delete', true, $this->getDeletedAttributes($category));
    }
}