<?php

namespace Tests\Feature\Events\CreateEvent;

use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\Events\AcceptedExternal;
use App\Models\Events\Event;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withHeader;
use function Pest\Laravel\withoutMiddleware;

dataset('non-event types', [
    'training' => fn() => Event::TYPE_TRAINING,
    'social' => fn() => Event::TYPE_SOCIAL,
    'meeting' => fn() => Event::TYPE_MEETING,
    'hidden' => fn() => Event::TYPE_HIDDEN,
]);

/**
 * The form data for a new one-day event, with any changes. A change to null leaves the field out, as browsers do for
 * unticked checkboxes.
 *
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function eventForm(array $changes = []): array
{
    $form = array_merge(
        [
            'name' => 'Freshers Gig',
            'em_id' => '',
            'type' => (string) Event::TYPE_EVENT,
            'description' => 'A gig for the freshers',
            'venue' => 'Plug',
            'venue_type' => '1',
            'client_type' => '1',
            'date_start' => '2026-11-01',
            'date_end' => '2026-11-01',
            'one_day' => '1',
            'time_start' => '19:00',
            'time_end' => '23:00',
            'production_charge' => '',
            'action' => 'create',
        ],
        $changes,
    );

    return array_filter($form, fn($value) => $value !== null);
}

/**
 * v4 only sends events to the finance database in production. The CSRF check is skipped only in testing, so it has to
 * be turned off as well.
 */
function inProduction(): void
{
    app()->detectEnvironment(fn() => 'prod');
    withoutMiddleware(VerifyCsrfToken::class);
}

function createdEvent(): Event
{
    $event = Event::latest('id')->first();
    expect($event)->not->toBeNull();
    assert($event instanceof Event);

    return $event;
}

describe('the form to create an event', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('event.create')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list the active members as possible EMs', function () {
        createUser('member', ['forename' => 'Active', 'surname' => 'Member']);
        createUser('associate', ['forename' => 'An', 'surname' => 'Associate']);
        createUser('archived', ['forename' => 'Archived', 'surname' => 'Member']);
        createUser('staff', ['forename' => 'Staff', 'surname' => 'Member']);
        createUser('super_admin', ['forename' => 'Super', 'surname' => 'Admin']);
        loginAs('committee');

        get(route('event.create'))
            ->assertOk()
            ->assertSeeText(['Active Member', 'An Associate'])
            ->assertDontSeeText(['Archived Member', 'Staff Member', 'Super Admin']);
    });
});

describe('creating an event', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(post(route('event.store'), eventForm()), $access);

        assertDatabaseCount('events', in_array($access, [302], true) ? 1 : 0);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should create the event and show it', function () {
        $em = createUser('member');
        loginAs('committee');

        $response = post(route('event.store'), eventForm(['em_id' => (string) $em->id]));

        $event = createdEvent();
        $response->assertRedirect(route('event.view', ['id' => $event->id]));
        assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Freshers Gig',
            'venue' => 'Plug',
            'em_id' => $em->id,
            'description' => 'A gig for the freshers',
            'type' => Event::TYPE_EVENT,
            'crew_list_status' => Event::CREW_LIST_OPEN,
            'client_type' => 1,
            'venue_type' => 1,
            'production_charge' => null,
        ]);
        expect($event->paperwork)->toBe([
            'risk_assessment' => false,
            'insurance' => false,
            'finance_em' => false,
            'finance_treas' => false,
            'event_report' => false,
        ]);
        expectNotification('success', 'Event created');
    });

    it('should go back to the form to create another', function () {
        loginAs('committee');

        from(route('event.create'))
            ->post(route('event.store'), eventForm(['action' => 'create-another']))
            ->assertRedirect(route('event.create'));

        assertDatabaseCount('events', 1);
    });

    it('should create a time slot named after the event', function () {
        loginAs('committee');

        post(route('event.store'), eventForm())->assertRedirect();

        $event = createdEvent();
        assertDatabaseCount('event_times', 1);
        assertDatabaseHas('event_times', [
            'event_id' => $event->id,
            'name' => 'Freshers Gig',
            'start' => '2026-11-01 19:00:00',
            'end' => '2026-11-01 23:00:00',
        ]);
    });

    it('should create a time slot for each day', function () {
        loginAs('committee');

        post(
            route('event.store'),
            eventForm(['one_day' => null, 'date_start' => '2026-11-01', 'date_end' => '2026-11-03']),
        )->assertRedirect();

        $event = createdEvent();
        assertDatabaseCount('event_times', 3);
        foreach (['2026-11-01', '2026-11-02', '2026-11-03'] as $date) {
            assertDatabaseHas('event_times', [
                'event_id' => $event->id,
                'start' => "{$date} 19:00:00",
                'end' => "{$date} 23:00:00",
            ]);
        }
    });

    it('should ignore the end date for a one-day event', function () {
        loginAs('committee');

        post(route('event.store'), eventForm(['date_end' => '2026-11-05']))->assertRedirect();

        assertDatabaseCount('event_times', 1);
    });

    it('should not create an event without an EM', function () {
        loginAs('committee');

        post(route('event.store'), eventForm(['em_id' => '']))->assertRedirect();

        assertDatabaseHas('events', ['name' => 'Freshers Gig', 'em_id' => null]);
    });

    it('should store the production charge in pence', function (string $charge, int $pence) {
        loginAs('committee');

        post(route('event.store'), eventForm(['production_charge' => $charge]))->assertRedirect();

        assertDatabaseHas('events', ['name' => 'Freshers Gig', 'production_charge' => $pence]);
    })->with([['150', 15000], ['12.5', 1250], ['-10.99', -1099], ['0', 0]]);

    it('should store the client and venue types as 0 for anything but an event', function (int $type) {
        loginAs('committee');

        post(
            route('event.store'),
            eventForm(['type' => (string) $type, 'client_type' => '3', 'venue_type' => '2']),
        )->assertRedirect();

        assertDatabaseHas('events', ['name' => 'Freshers Gig', 'type' => $type, 'client_type' => 0, 'venue_type' => 0]);
    })
        ->with('non-event types')
        ->group('mariadb');

    it('should store the text with HTML tags removed and HTML characters escaped', function () {
        loginAs('committee');

        post(
            route('event.store'),
            eventForm([
                'name' => 'Rock & Roll <b>Night</b>',
                'venue' => 'Bar & Grill',
                'description' => '**Bold** & <script>alert(1)</script>',
            ]),
        )->assertRedirect();

        assertDatabaseHas('events', [
            'name' => 'Rock &amp; Roll Night',
            'venue' => 'Bar &amp; Grill',
            'description' => '**Bold** &amp; ',
        ]);
    })->group('known-bug', 'bug:html-escaped-text');

    it('should store the times shifted by the browser\'s offset, which is -60 in the summer', function () {
        loginAs('committee');

        withHeader('TZ-OFFSET', '-60')
            ->post(route('event.store'), eventForm(['date_start' => '2026-07-01']))
            ->assertRedirect();

        assertDatabaseHas('event_times', ['start' => '2026-07-01 18:00:00', 'end' => '2026-07-01 22:00:00']);
    });

    it('should shift the times by the offset when saving, not the offset on the day of the event', function () {
        loginAs('committee');

        withHeader('TZ-OFFSET', '-60')
            ->post(route('event.store'), eventForm(['date_start' => '2026-12-01']))
            ->assertRedirect();

        assertDatabaseHas('event_times', ['start' => '2026-12-01 18:00:00']);
    })->group('known-bug', 'bug:timezones');

    describe('validation', function () {
        it('should require the details', function (string $field, string $message) {
            loginAs('committee');

            from(route('event.create'))
                ->post(route('event.store'), eventForm([$field => '']))
                ->assertRedirect(route('event.create'))
                ->assertSessionHasErrors([$field => $message]);

            assertDatabaseCount('events', 0);
        })->with([
            ['name', "Please enter the event's name"],
            ['type', 'Please select an event type'],
            ['description', 'Please enter the event description'],
            ['venue', 'Please enter the venue'],
            ['venue_type', 'Please select the venue type'],
            ['client_type', 'Please select a client type'],
            ['date_start', 'Please enter when this event starts'],
            ['time_start', 'Please enter the start time'],
            ['time_end', 'Please enter the end time'],
        ]);

        it('should not require the venue or client type for anything but an event', function () {
            loginAs('committee');

            post(
                route('event.store'),
                eventForm(['type' => (string) Event::TYPE_SOCIAL, 'venue_type' => '', 'client_type' => '']),
            )->assertSessionHasNoErrors();

            assertDatabaseCount('events', 1);
        });

        it('should only accept the known types', function (string $field, string $value, string $message) {
            loginAs('committee');

            post(route('event.store'), eventForm([$field => $value]))->assertSessionHasErrors([$field => $message]);
        })->with([
            ['type', '6', 'Please select a valid event type'],
            ['type', '0', 'Please select a valid event type'],
            ['venue_type', '3', 'Please select a valid venue type'],
            ['client_type', '4', 'Please select a valid client type'],
        ]);

        it('should require an EM who is a user', function () {
            loginAs('committee');

            post(route('event.store'), eventForm(['em_id' => '999']))->assertSessionHasErrors([
                'em_id' => 'Please select a valid user',
            ]);
        });

        it('should accept any user as the EM, even if they are archived or not a member', function (string $group) {
            $em = createUser($group);
            loginAs('committee');

            post(route('event.store'), eventForm(['em_id' => (string) $em->id]))->assertSessionHasNoErrors();

            assertDatabaseHas('events', ['em_id' => $em->id]);
        })->with(['archived', 'staff', 'super_admin']);

        it('should only accept dates as YYYY-MM-DD', function (string $date) {
            loginAs('committee');

            post(route('event.store'), eventForm(['date_start' => $date]))->assertSessionHasErrors([
                'date_start' => 'Please enter a valid date',
            ]);
        })->with(['01/11/2026', '2026-11-1', '2026-11-01 19:00', 'tomorrow']);

        it('should require the end date unless it is a one-day event', function () {
            loginAs('committee');

            post(route('event.store'), eventForm(['one_day' => null, 'date_end' => '']))->assertSessionHasErrors([
                'date_end' => 'Please enter when this event ends',
            ]);
        });

        it('should require the end date to be after the start date', function (string $end) {
            loginAs('committee');

            post(route('event.store'), eventForm(['one_day' => null, 'date_end' => $end]))->assertSessionHasErrors([
                'date_end' => 'This must be after the start date',
            ]);
        })->with(['2026-11-01', '2026-10-31']);

        it('should only accept times as HH:MM', function (string $field, string $time) {
            loginAs('committee');

            post(route('event.store'), eventForm([$field => $time]))->assertSessionHasErrors([
                $field => 'Please enter a valid time',
            ]);
        })->with([['time_start', '7pm'], ['time_start', '19:00:00'], ['time_end', '25:00']]);

        it('should require the end time to be after the start time, so events can\'t run past midnight', function () {
            loginAs('committee');

            post(
                route('event.store'),
                eventForm(['time_start' => '22:00', 'time_end' => '02:00']),
            )->assertSessionHasErrors(['time_end' => 'This must be after the start time']);
        });

        it('should only accept a production charge in pounds and pence', function (string $charge, string $message) {
            loginAs('committee');

            post(route('event.store'), eventForm(['production_charge' => $charge]))->assertSessionHasErrors([
                'production_charge' => $message,
            ]);
        })->with([
            ['ten', 'Please enter a valid number'],
            ['1000000', 'Charge is out of bounds'],
            ['10.999', 'Charge may only have 2 decimal places and no spaces'],
            ['1e3', 'Charge may only have 2 decimal places and no spaces'],
        ]);
    });

    describe('telling the SU about external off-campus events', function () {
        it('should email the SU\'s technical coordinator', function (string $clientType) {
            Mail::fake();
            $em = createUser('member', ['forename' => 'Emma', 'surname' => 'Manager']);
            loginAs('committee');

            post(
                route('event.store'),
                eventForm(['client_type' => $clientType, 'venue_type' => '2', 'em_id' => (string) $em->id]),
            )->assertRedirect();

            Mail::assertQueuedCount(1);
            Mail::assertQueued(AcceptedExternal::class, function (AcceptedExternal $mail) {
                $mail->assertTo('cjl25@bath.ac.uk');
                $mail->assertHasCc('committee@bts-crew.com');
                $mail->assertFrom('pm@bts-crew.com');
                $mail->assertHasReplyTo('committee@bts-crew.com');
                $mail->assertHasSubject('[Backstage Website] Backstage External Off-Campus Event');
                $mail->assertSeeInText('Freshers Gig');
                $mail->assertSeeInText('01/11/2026');
                $mail->assertSeeInText('Emma Manager');
                $mail->assertSeeInText('Off-campus');

                return true;
            });
        })->with(['2', '3']);

        it('should not email about events for the SU or on campus', function (string $clientType, string $venueType) {
            Mail::fake();
            loginAs('committee');

            post(
                route('event.store'),
                eventForm(['client_type' => $clientType, 'venue_type' => $venueType]),
            )->assertRedirect();

            Mail::assertNothingQueued();
            Mail::assertNothingSent();
        })->with([['1', '2'], ['2', '1'], ['3', '1']]);

        it('should say when there is no EM yet', function () {
            Mail::fake();
            loginAs('committee');

            post(route('event.store'), eventForm(['client_type' => '3', 'venue_type' => '2']))->assertRedirect();

            Mail::assertQueued(AcceptedExternal::class, function (AcceptedExternal $mail) {
                $mail->assertSeeInText('none yet');

                return true;
            });
        });
    });

    describe('adding it to the finance database', function () {
        it('should not add it outside production', function () {
            loginAs('committee');

            post(route('event.store'), eventForm())->assertRedirect();

            expect(fakeFinanceDb()->events)->toBeEmpty();
        });

        it('should add events in production', function () {
            inProduction();
            loginAs('committee');

            post(
                route('event.store'),
                eventForm(['one_day' => null, 'date_start' => '2026-11-01', 'date_end' => '2026-11-03']),
            )->assertRedirect();

            expect(fakeFinanceDb()->events)->toBe([
                [
                    'data[Event][event_name]' => 'Freshers Gig',
                    'data[Event][start_date]' => '01-11-2026',
                    'data[Event][end_date]' => '03-11-2026',
                    'data[Event][verified]' => 0,
                    'data[Event][bts_crew_id]' => createdEvent()->id,
                ],
            ]);
        });

        it('should only add events, not the other types', function (int $type) {
            inProduction();
            loginAs('committee');

            post(route('event.store'), eventForm(['type' => (string) $type]))->assertRedirect();

            expect(fakeFinanceDb()->events)->toBeEmpty();
        })->with('non-event types');
    });
});
