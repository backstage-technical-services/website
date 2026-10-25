<?php

namespace Tests\Feature\Events\Time;

use App\Models\Events\Event;
use App\Models\Events\Time;
use Database\Factories\Events\EventFactory;
use Database\Factories\Events\TimeFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;
use function Pest\Laravel\withHeader;

function timeUrl(Event $event, ?Time $time = null, bool $delete = false): string
{
    return match (true) {
        $time === null => route('event.time.store', ['id' => $event->id]),
        $delete => route('event.time.destroy', ['id' => $event->id, 'timeId' => $time->id]),
        default => route('event.time.update', ['id' => $event->id, 'timeId' => $time->id]),
    };
}

function firstTime(Event $event): Time
{
    $time = $event->times()->first();
    assert($time instanceof Time);

    return $time;
}

/**
 * @return array<string, string>
 */
function timeForm(string $name = 'Get In', string $start = '2026-11-01 09:00', string $end = '2026-11-01 12:00'): array
{
    return ['name' => $name, 'start' => $start, 'end' => $end];
}

describe('adding a time slot', function () {
    it('should only let admins and the event\'s managers add one', function (string $group, int $status) {
        $event = EventFactory::new()->createOne();
        loginAs($group);

        postAjax(timeUrl($event), timeForm())->assertStatus($status);

        assertDatabaseCount('event_times', $status === 200 ? 2 : 1);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should let the TEM add one', function () {
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        actingAs($tem);

        postAjax(timeUrl($event), timeForm())->assertOk();
    });

    it('should only accept AJAX requests', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        post(timeUrl($event), timeForm())->assertNotFound();
    });

    it('should add the time slot', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        postAjax(timeUrl($event), timeForm('Get <b>In</b> & set up'))
            ->assertOk()
            ->assertExactJson(['response' => 'Event time created']);

        assertDatabaseHas('event_times', [
            'event_id' => $event->id,
            'name' => 'Get In &amp; set up',
            'start' => '2026-11-01 09:00:00',
            'end' => '2026-11-01 12:00:00',
        ]);
        expectNotification('success', 'Event time created');
    });

    it('should shift the time by the browser\'s offset', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        withHeader('TZ-OFFSET', '-60')
            ->post(timeUrl($event), timeForm(), [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->assertOk();

        assertDatabaseHas('event_times', ['start' => '2026-11-01 08:00:00', 'end' => '2026-11-01 11:00:00']);
    })->group('known-bug', 'bug:timezones');

    describe('validation', function () {
        it('should require a name, start and end', function (string $field, string $message) {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            postAjax(timeUrl($event), array_merge(timeForm(), [$field => '']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors([$field => $message]);
        })->with([
            ['name', 'Please enter a title for the time'],
            ['start', 'Please enter the start time'],
            ['end', 'Please enter the end time'],
        ]);

        it('should only accept times as YYYY-MM-DD HH:MM', function (string $field) {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            postAjax(timeUrl($event), array_merge(timeForm(), [$field => '01/11/2026 09:00']))
                ->assertUnprocessable()
                ->assertJsonValidationErrors([$field => 'Please enter a valid time']);
        })->with(['start', 'end']);

        it('should require it to end after it starts', function () {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            postAjax(timeUrl($event), timeForm(start: '2026-11-01 12:00', end: '2026-11-01 09:00'))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['end' => "It cannot end before it's begun!"]);
        });

        it('should allow a time slot to run past midnight', function () {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            postAjax(timeUrl($event), timeForm(start: '2026-11-01 22:00', end: '2026-11-02 02:00'))->assertOk();
        });
    });
});

describe('changing a time slot', function () {
    it('should only let admins and the event\'s managers change one', function (string $group, int $status) {
        $event = EventFactory::new()->createOne();
        loginAs($group);

        postAjax(timeUrl($event, firstTime($event)), timeForm('New Name'))->assertStatus($status);

        $status === 200
            ? assertDatabaseHas('event_times', ['event_id' => $event->id, 'name' => 'New Name'])
            : assertDatabaseMissing('event_times', ['name' => 'New Name']);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should change the time slot', function () {
        $event = EventFactory::new()->createOne();
        $time = firstTime($event);
        loginAs('committee');

        postAjax(timeUrl($event, $time), timeForm('Show', '2026-11-02 19:30', '2026-11-02 22:30'))
            ->assertOk()
            ->assertExactJson(['response' => 'Event time updated']);

        assertDatabaseHas('event_times', [
            'id' => $time->id,
            'name' => 'Show',
            'start' => '2026-11-02 19:30:00',
            'end' => '2026-11-02 22:30:00',
        ]);
        expectNotification('success', 'Event time updated');
    });

    it('should give a 404 for a time slot of another event', function () {
        $event = EventFactory::new()->createOne();
        $other = TimeFactory::new()->createOne();
        loginAs('committee');

        postAjax(timeUrl($event, $other), timeForm())->assertNotFound();
    });

    it('should validate the time slot', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        postAjax(timeUrl($event, firstTime($event)), timeForm(name: ''))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    it('should only accept AJAX requests', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        post(timeUrl($event, firstTime($event)), timeForm())->assertNotFound();
    });
});

describe('deleting a time slot', function () {
    it('should only let admins and the event\'s managers delete one', function (string $group, int $status) {
        $event = EventFactory::new()->createOne();
        TimeFactory::new()->for($event)->createOne();
        loginAs($group);

        postAjax(timeUrl($event, firstTime($event), delete: true))->assertStatus($status);

        assertDatabaseCount('event_times', $status === 200 ? 1 : 2);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete the time slot', function () {
        $event = EventFactory::new()->createOne();
        $extra = TimeFactory::new()->for($event)->createOne();
        loginAs('committee');

        postAjax(timeUrl($event, $extra, delete: true))
            ->assertOk()
            ->assertExactJson(['response' => 'Event time deleted']);

        assertDatabaseMissing('event_times', ['id' => $extra->id]);
        expectNotification('success', 'Event time deleted');
    });

    it('should not delete the last time slot', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        postAjax(timeUrl($event, firstTime($event), delete: true))
            ->assertUnprocessable()
            ->assertJson(['error' => 'An event needs at least 1 event time.', '__error' => true]);

        assertDatabaseCount('event_times', 1);
    });

    it('should accept requests that aren\'t AJAX', function () {
        $event = EventFactory::new()->createOne();
        $extra = TimeFactory::new()->for($event)->createOne();
        loginAs('committee');

        post(timeUrl($event, $extra, delete: true))->assertOk();

        assertDatabaseMissing('event_times', ['id' => $extra->id]);
    });
});
