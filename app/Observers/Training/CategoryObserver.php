<?php

namespace App\Observers\Training;

use App\Logger;
use App\Models\Training\Category;
use App\Observers\ModelObserver;

class CategoryObserver extends ModelObserver
{
    public function created(Category $category)
    {
        Logger::log('training-category.create', true, $this->getCreatedAttributes($category));
    }

    public function updated(Category $category)
    {
        Logger::log('training-category.edit', true, $this->getUpdatedAttributes($category));
    }

    public function deleted(Category $category)
    {
        Logger::log('training-category.delete', true, $this->getDeletedAttributes($category));
    }
}
