<?php

namespace App\Observers;

use App\Logger;
use App\Models\Quote;

class QuoteObserver extends ModelObserver
{
    public function created(Quote $quote)
    {
        Logger::log('quote.create', true, $this->getCreatedAttributes($quote));
    }

    public function updated(Quote $quote)
    {
        Logger::log('quote.edit', true, $this->getUpdatedAttributes($quote));
    }

    public function deleted(Quote $quote)
    {
        Logger::log('quote.delete', true, $this->getDeletedAttributes($quote));
    }
}
