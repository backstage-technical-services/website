<?php

namespace Tests\Support\Fakes;

use App\Services\FinanceDb;

/**
 * Records the events that would have been added to the finance database.
 */
class FakeFinanceDb extends FinanceDb
{
    /**
     * The fields sent for each event, in order.
     *
     * @var list<array<string, mixed>>
     */
    public array $events = [];

    public function addEvent(array $fields): void
    {
        $this->events[] = $fields;
    }
}
