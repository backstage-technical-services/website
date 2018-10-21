<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

abstract class ModelObserver
{
    protected function getCreatedAttributes(Model $model)
    {
        return $model->getAttributes();
    }

    protected function getUpdatedAttributes(Model $model)
    {
        return ['id' => $model->id] + $model->getDirty();
    }

    protected function getDeletedAttributes(Model $model)
    {
        return $model->getAttributes();
    }

    protected function cleanForSaving(Model $model, array $attributes)
    {
        $guarded = array_merge($model->getGuarded(), $model->getHidden());
        $cleaned = $attributes;

        foreach ($cleaned as $name => $value) {
            if (in_array($name, $guarded)) {
                unset($cleaned[$name]);
            }
        }

        return $cleaned;
    }
}