<?php

namespace Tests\Feature\Events\EventList;

use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EventFactory;
use Database\Factories\Events\TimeFactory;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The admin list of events (/events), searching events for the resources "link to event" picker, and deleting events.
 *
 * The list and the search use SELECT DISTINCT with an ORDER BY on event_times, which isn't in the SELECT list. MariaDB
 * allows that, so those tests are in the mariadb group.
 */

describe('the list of events', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('event.index')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list every type of event, newest first', function () {
        EventFactory::new()
            ->at('2026-01-10 19:00')
            ->createOne(['name' => 'Older Show']);
        EventFactory::new()
            ->social()
            ->at('2026-03-10 19:00')
            ->createOne(['name' => 'Newer Social']);
        EventFactory::new()
            ->hidden()
            ->at('2026-02-10 19:00')
            ->createOne(['name' => 'Middle Info']);

        loginAs('committee');

        get(route('event.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Newer Social', 'Middle Info', 'Older Show']);
    })->group('mariadb');

    it('should show the dates, venue, EM and size of the crew', function () {
        $em = createUser('member', ['forename' => 'Emma', 'surname' => 'Manager']);
        $event = EventFactory::new()
            ->managedBy($em)
            ->at('2026-05-01 19:00')
            ->createOne(['name' => 'The Show', 'venue' => 'Founders Hall']);
        TimeFactory::new()
            ->for($event)
            ->createOne(['start' => '2026-05-02 19:00', 'end' => '2026-05-02 23:00']);
        CrewFactory::new()->for($event)->count(3)->create();

        loginAs('committee');

        get(route('event.index'))
            ->assertOk()
            ->assertSeeText(['The Show', 'Founders Hall', 'Emma Manager'])
            ->assertSee('01/05/2026 &mdash; 02/05/2026', false)
            ->assertSeeHtml('<td col="crew">3</td>');
    })->group('mariadb');

    it('should list each event once, however many time slots it has', function () {
        $event = EventFactory::new()->createOne(['name' => 'Many Slots']);
        TimeFactory::new()->for($event)->count(3)->create();

        loginAs('committee');

        $response = get(route('event.index'))->assertOk();
        expect(substr_count((string) $response->getContent(), 'Many Slots'))->toBe(1);
    })->group('mariadb');

    it('should search by name or venue', function () {
        EventFactory::new()->createOne(['name' => 'Panto', 'venue' => 'Arts Theatre']);
        EventFactory::new()->createOne(['name' => 'Ball', 'venue' => 'Panto Hall']);
        EventFactory::new()->createOne(['name' => 'Gig', 'venue' => 'Plug']);

        loginAs('committee');

        get(route('event.index', ['search' => 'panto']))
            ->assertOk()
            ->assertSeeText(['Panto', 'Ball'])
            ->assertDontSeeText('Gig');
    })->group('mariadb');

    it('should show 20 events a page', function () {
        foreach (range(1, 21) as $day) {
            EventFactory::new()
                ->at(sprintf('2026-01-%02d 19:00', $day))
                ->createOne(['name' => sprintf('Event %02d', $day)]);
        }

        loginAs('committee');

        get(route('event.index'))->assertOk()->assertSeeText('Event 21')->assertDontSeeText('Event 01');
        get(route('event.index', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('Event 01')
            ->assertDontSeeText('Event 21');
    })->group('mariadb');

    it('should redirect to the first page if the page is empty', function () {
        EventFactory::new()->createOne();

        loginAs('committee');

        get(route('event.index', ['page' => 5]))->assertRedirect(route('event.index', ['page' => 1]));
    })->group('mariadb');

    it('should say when there are no events', function () {
        loginAs('committee');

        get(route('event.index'))->assertOk()->assertSee("We don't have any events :/", false);
    });
});

describe('deleting an event', function () {
    it('should only be for admins', function (string $group, int $status) {
        $event = EventFactory::new()->createOne();
        loginAs($group);

        postAjax(route('event.destroy', ['id' => $event->id]))->assertStatus($status);

        $status === 200 ? assertModelMissing($event) : assertModelExists($event);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should not let the TEM delete it', function () {
        $tem = loginAs('member');
        $event = EventFactory::new()
            ->managedBy($tem ?? createUser())
            ->createOne();

        postAjax(route('event.destroy', ['id' => $event->id]))->assertForbidden();

        assertModelExists($event);
    });

    it('should only accept AJAX requests', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        post(route('event.destroy', ['id' => $event->id]))->assertNotFound();

        assertModelExists($event);
    });

    it('should delete the event with its times, crew and emails', function () {
        $event = EventFactory::new()->createOne();
        CrewFactory::new()->for($event)->createOne();
        loginAs('committee');

        postAjax(route('event.destroy', ['id' => $event->id]))
            ->assertOk()
            ->assertExactJson(['response' => 'Event deleted.']);

        assertModelMissing($event);
        assertDatabaseMissing('event_times', ['event_id' => $event->id]);
        assertDatabaseMissing('event_crew', ['event_id' => $event->id]);
        expectNotification('success', 'Event deleted.');
    });

    it('should log the deletion', function () {
        $event = EventFactory::new()->createOne();
        $admin = loginAs('committee');

        postAjax(route('event.destroy', ['id' => $event->id]))->assertOk();

        expect(\App\Models\Log::where('action', 'event.delete')->where('user_id', $admin?->id)->exists())->toBeTrue();
    });

    it('should give a 404 for an unknown event', function () {
        loginAs('committee');

        postAjax(route('event.destroy', ['id' => 999]))->assertNotFound();
    });
});

describe('searching events', function () {
    it('should only be for members', function (string $group, int $status) {
        EventFactory::new()->createOne();
        loginAs($group);

        postAjax(route('event.search'))->assertStatus($status);
    })->with([
        ['guest', 401],
        ['staff', 403],
        ['member', 200],
        ['associate', 200],
        ['committee', 200],
        ['super_admin', 403],
    ]);

    it('should only accept AJAX requests', function () {
        loginAs('member');

        post(route('event.search'))->assertNotFound();
    });

    it('should list matching events with the month they end, newest first', function () {
        $older = EventFactory::new()
            ->at('2026-01-10 19:00')
            ->createOne(['name' => 'Spring Show']);
        $newer = EventFactory::new()
            ->at('2026-03-10 19:00')
            ->createOne(['name' => 'Summer Show']);
        EventFactory::new()->createOne(['name' => 'Ball']);
        loginAs('member');

        postAjax(route('event.search'), ['name' => 'show'])
            ->assertOk()
            ->assertExactJson([
                'response' => [
                    ['id' => $newer->id, 'name' => 'Summer Show', 'date' => 'Mar 2026'],
                    ['id' => $older->id, 'name' => 'Spring Show', 'date' => 'Jan 2026'],
                ],
            ]);
    })->group('mariadb');

    it('should include events of every type', function () {
        EventFactory::new()
            ->hidden()
            ->createOne(['name' => 'Committee Meeting']);
        loginAs('member');

        postAjax(route('event.search'), ['name' => 'meeting'])
            ->assertOk()
            ->assertJsonCount(1, 'response');
    })->group('mariadb');

    it('should list every event without a name', function () {
        EventFactory::new()->count(3)->create();
        loginAs('member');

        postAjax(route('event.search'))->assertOk()->assertJsonCount(3, 'response');
    })->group('mariadb');

    it('should leave out events without any time slots', function () {
        EventFactory::new()
            ->withoutTimes()
            ->createOne(['name' => 'No Times']);
        loginAs('member');

        postAjax(route('event.search'), ['name' => 'No Times'])
            ->assertOk()
            ->assertJsonCount(0, 'response');
    });

    it('should list each event once, however many time slots it has', function () {
        $event = EventFactory::new()->createOne(['name' => 'Two Slots']);
        TimeFactory::new()->for($event)->createOne();
        loginAs('member');

        postAjax(route('event.search'), ['name' => 'Two Slots'])
            ->assertOk()
            ->assertJsonCount(1, 'response');
    })->group('mariadb');
});
