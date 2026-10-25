<?php

namespace Tests\Feature\Events\AutoClose;

use App\Models\Events\Event;
use Database\Factories\Events\EventFactory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\assertDatabaseHas;

/*
 * crew:auto-close runs every day, and is meant to close the crew lists of events that have finished.
 */

it('should run every day', function () {
    $events = collect(app(Schedule::class)->events())->filter(
        fn($event) => str_contains((string) $event->command, 'crew:auto-close'),
    );

    expect($events)->toHaveCount(1);
    expect($events->first()?->expression)->toBe('0 0 * * *');
});

it('should leave the crew lists of finished events open, as it updates a column that isn\'t fillable', function () {
    $event = EventFactory::new()->past()->createOne();

    expect(Artisan::call('crew:auto-close'))->toBe(0);
    expect(Artisan::output())->toContain('Done.');

    assertDatabaseHas('events', ['id' => $event->id, 'crew_list_status' => Event::CREW_LIST_OPEN]);
})->group('known-bug', 'bug:auto-close');

it('should leave the crew lists of future events open', function () {
    $event = EventFactory::new()->createOne();

    Artisan::call('crew:auto-close');

    assertDatabaseHas('events', ['id' => $event->id, 'crew_list_status' => Event::CREW_LIST_OPEN]);
});
