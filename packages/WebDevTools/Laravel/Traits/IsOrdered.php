<?php

namespace Package\WebDevTools\Laravel\Traits;

trait IsOrdered
{
    /**
     * Register callbacks for the updating and deleted events.
     *
     * @return void
     */
    public static function boot()
    {
        /**
         * Hook into the created event to fix the order.
         */
        static::created(function ($model) {
            $model->insertIntoOrder();
        });

        /**
         * Hook into the updating event to re-order the list.
         */
        static::updated(function ($model) {
            $currentOrder = $model->original[static::orderAttributeName()];
            $newOrder     = $model->{static::orderAttributeName()};

            if ($newOrder != $currentOrder) {
                if ($newOrder > $currentOrder) {
                    static::whereBetween(static::orderAttributeName(), [$currentOrder + 1, $newOrder])
                          ->where($model->getKeyName(), '!=', $model->getKey())
                          ->decrement(static::orderAttributeName());
                } else {
                    static::whereBetween(static::orderAttributeName(), [$newOrder, $currentOrder - 1])
                          ->where($model->getKeyName(), '!=', $model->getKey())
                          ->increment(static::orderAttributeName());
                }
            }
        });

        /**
         * Hook into the deleted event to move any later items down.
         */
        static::deleted(function ($model) {
            static::where(static::orderAttributeName(), '>', $model->{static::orderAttributeName()})
                  ->decrement(static::orderAttributeName());
        });

        /**
         * Hook into the restore event to restore the correct order.
         */
        static::restored(function ($model) {
            $model->insertIntoOrder();
        });

        parent::boot();
    }

    /**
     * Get the attribute to use for ordering.
     *
     * @return string
     */
    protected static function orderAttributeName()
    {
        return isset(static::$orderAttributeName) ? static::$orderAttributeName : 'order';
    }


    /**
     * Add a scope to order the list.
     *
     * @param $query
     *
     * @return void
     */
    public function scopeOrdered($query)
    {
        $this->scopeOrderedAsc($query);
    }

    /**
     * Add a scope to order the list ascending.
     *
     * @param $query
     *
     * @return void
     */
    public function scopeOrderedAsc($query)
    {
        $query->orderBy(static::orderAttributeName(), 'ASC');
    }

    /**
     * Add a scope to order the list descending.
     *
     * @param $query
     *
     * @return void
     */
    public function scopeOrderedDesc($query)
    {
        $query->orderBy(static::orderAttributeName(), 'DESC');
    }

    /**
     * "Insert" the model in the order - this bumps everything that should be after by 1.
     *
     * @return void
     */
    public function insertIntoOrder()
    {
        if ($this->exists() && static::where(static::orderAttributeName(), $this->{static::orderAttributeName()})->count() > 1) {
            static::where(static::orderAttributeName(), '>=', $this->{static::orderAttributeName()})
                  ->where($this->getKeyName(), '!=', $this->getKey())
                  ->increment(static::orderAttributeName());
        }
    }

    /**
     * Move a driver status to a new position in the order.
     *
     * @param $newOrder
     *
     * @return bool
     */
    public function moveTo($newOrder)
    {
        if ($this->exists()) {
            $newOrder = (int)$newOrder;
            if ($newOrder < 1 || $newOrder > static::count()) {
                return false;
            }

            return $this->update([
                static::orderAttributeName() => $newOrder,
            ]);
        }
    }
}
