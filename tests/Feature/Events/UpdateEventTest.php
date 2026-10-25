<?php

namespace Tests\Feature\Events\UpdateEvent;

use App\Models\Events\Event;
use App\Models\Users\User;
use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EventFactory;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/**
 * The event settings form, as the browser sends it, with any changes.
 *
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function settingsForm(Event $event, array $changes = []): array
{
    return array_merge(
        [
            'action' => 'update',
            'name' => $event->name,
            'em_id' => (string) $event->em_id,
            'type' => (string) $event->type,
            'client_type' => (string) ($event->client_type ?: 1),
            'venue_type' => (string) ($event->venue_type ?: 1),
            'venue' => $event->venue,
            'production_charge' => '',
            'description' => $event->description,
            'crew_list_status' => (string) $event->crew_list_status,
        ],
        $changes,
    );
}

/**
 * @param array<string, mixed> $data
 *
 * @return TestResponse<Response>
 */
function updateEvent(Event $event, array $data): TestResponse
{
    return post(route('event.update', ['id' => $event->id]), $data);
}

function settingsTab(Event $event): string
{
    return route('event.view', ['id' => $event->id, 'tab' => 'settings']);
}

function crewTab(Event $event): string
{
    return route('event.view', ['id' => $event->id, 'tab' => 'crew']);
}

function withEmRole(Event $event): User
{
    $member = createUser('member');
    CrewFactory::new()->for($event)->for($member)->eventManager()->createOne();

    return $member;
}

describe('who can change an event', function () {
    it('should only let admins and the event\'s managers change it', function (string $group, string|int $access) {
        $event = EventFactory::new()->createOne(['name' => 'Old Name']);
        loginAs($group);

        expectAccess(updateEvent($event, settingsForm($event, ['name' => 'New Name'])), $access);

        assertDatabaseHas('events', ['id' => $event->id, 'name' => $access === 302 ? 'New Name' : 'Old Name']);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should let the TEM change it', function () {
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        actingAs($tem);

        updateEvent($event, settingsForm($event, ['name' => 'New Name']))->assertRedirect(settingsTab($event));

        assertDatabaseHas('events', ['id' => $event->id, 'name' => 'New Name']);
    });

    it('should let members with an EM role change it', function () {
        $event = EventFactory::new()->createOne();
        actingAs(withEmRole($event));

        updateEvent($event, settingsForm($event, ['name' => 'New Name']))->assertRedirect(settingsTab($event));

        assertDatabaseHas('events', ['id' => $event->id, 'name' => 'New Name']);
    });

    it('should not let the rest of the crew change it', function () {
        $event = EventFactory::new()->createOne();
        $member = createUser('member');
        CrewFactory::new()->for($event)->for($member)->role('Sound')->createOne();
        actingAs($member);

        updateEvent($event, settingsForm($event, ['name' => 'New Name']))->assertForbidden();
    });

    it('should give a 404 for an unknown event', function () {
        loginAs('committee');

        post(route('event.update', ['id' => 999]), ['action' => 'update'])->assertNotFound();
    });

    it('should go to the settings for an unknown action', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        updateEvent($event, ['action' => 'something-else'])->assertRedirect(settingsTab($event));
    });
});

describe('changing the details', function () {
    it('should update the details', function () {
        $event = EventFactory::new()->createOne();
        $em = createUser('member');
        loginAs('committee');

        updateEvent(
            $event,
            settingsForm($event, [
                'name' => 'Christmas Ball',
                'em_id' => (string) $em->id,
                'type' => (string) Event::TYPE_EVENT,
                'client_type' => '3',
                'venue_type' => '2',
                'venue' => 'Assembly Rooms',
                'production_charge' => '250.50',
                'description' => 'A ball',
                'crew_list_status' => (string) Event::CREW_LIST_CLOSED,
            ]),
        )->assertRedirect(settingsTab($event));

        assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Christmas Ball',
            'em_id' => $em->id,
            'client_type' => 3,
            'venue_type' => 2,
            'venue' => 'Assembly Rooms',
            'production_charge' => 25050,
            'description' => 'A ball',
            'crew_list_status' => Event::CREW_LIST_CLOSED,
        ]);
        expectNotification('success', 'Event updated');
    });

    it('should remove the EM if none is chosen', function () {
        $event = EventFactory::new()->managedBy(createUser())->createOne();
        loginAs('committee');

        updateEvent($event, settingsForm($event, ['em_id' => '']))->assertRedirect(settingsTab($event));

        assertDatabaseHas('events', ['id' => $event->id, 'em_id' => null]);
    });

    it('should not let the TEM change the EM', function () {
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        actingAs($tem);

        updateEvent($event, settingsForm($event, ['em_id' => (string) createUser()->id]))->assertRedirect();

        assertDatabaseHas('events', ['id' => $event->id, 'em_id' => $tem->id]);
    });

    it('should let members with an EM role change the EM', function () {
        $event = EventFactory::new()->managedBy(createUser())->createOne();
        $member = withEmRole($event);
        actingAs($member);

        updateEvent($event, settingsForm($event, ['em_id' => (string) $member->id]))->assertRedirect();

        assertDatabaseHas('events', ['id' => $event->id, 'em_id' => $member->id]);
    });

    it('should only let admins change the client, venue type and production charge', function () {
        $tem = createUser('member');
        $event = EventFactory::new()
            ->managedBy($tem)
            ->createOne(['production_charge' => 100]);
        actingAs($tem);

        updateEvent(
            $event,
            settingsForm($event, ['client_type' => '3', 'venue_type' => '2', 'production_charge' => '5']),
        )->assertRedirect();

        assertDatabaseHas('events', [
            'id' => $event->id,
            'client_type' => 1,
            'venue_type' => 1,
            'production_charge' => 10000,
        ]);
    });

    it('should let managers change the type and crew list status', function () {
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        actingAs($tem);

        updateEvent(
            $event,
            settingsForm($event, [
                'type' => (string) Event::TYPE_SOCIAL,
                'crew_list_status' => (string) Event::CREW_LIST_HIDDEN,
            ]),
        )->assertRedirect();

        assertDatabaseHas('events', [
            'id' => $event->id,
            'type' => Event::TYPE_SOCIAL,
            'crew_list_status' => Event::CREW_LIST_HIDDEN,
        ]);
    });

    it('should remove the guests when an event stops being a social', function () {
        $event = EventFactory::new()->social()->createOne();
        CrewFactory::new()->for($event)->guest()->createOne();
        $member = CrewFactory::new()->for($event)->createOne();
        loginAs('committee');

        updateEvent($event, settingsForm($event, ['type' => (string) Event::TYPE_EVENT]))->assertRedirect();

        assertDatabaseCount('event_crew', 1);
        assertDatabaseHas('event_crew', ['id' => $member->id]);
    });

    it('should keep the guests when the event stays a social', function () {
        $event = EventFactory::new()->social()->createOne();
        CrewFactory::new()->for($event)->guest()->createOne();
        loginAs('committee');

        updateEvent($event, settingsForm($event, ['name' => 'Renamed social']))->assertRedirect();

        assertDatabaseCount('event_crew', 1);
    });

    it('should store the text with HTML tags removed and HTML characters escaped', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        updateEvent($event, settingsForm($event, ['name' => 'Fish & <i>Chips</i>']))->assertRedirect();

        assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Fish &amp; Chips']);
    })->group('known-bug', 'bug:html-escaped-text');

    it('should not change the times', function () {
        $event = EventFactory::new()->at('2026-11-01 19:00', '2026-11-01 23:00')->createOne();
        loginAs('committee');

        updateEvent($event, settingsForm($event, ['name' => 'New Name']))->assertRedirect();

        assertDatabaseHas('event_times', ['event_id' => $event->id, 'start' => '2026-11-01 19:00:00']);
    });

    describe('validation', function () {
        it('should require the details', function (string $field, string $message) {
            $event = EventFactory::new()->createOne(['name' => 'Old Name']);
            loginAs('committee');

            updateEvent($event, settingsForm($event, [$field => '', 'name' => 'New Name']))
                ->assertRedirect(settingsTab($event))
                ->assertSessionHasErrors([$field => $message]);

            assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Old Name']);
        })->with([
            ['description', 'Please enter the event description'],
            ['venue', 'Please enter the venue'],
            ['type', 'Please select an event type'],
            ['venue_type', 'Please select the venue type'],
            ['client_type', 'Please select a client type'],
        ]);

        it('should require a name', function () {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            updateEvent($event, settingsForm($event, ['name' => '']))->assertSessionHasErrors([
                'name' => "Please enter the event's name",
            ]);
        });

        it('should require admins to send the client and venue types, even for other types of event', function () {
            $event = EventFactory::new()->social()->createOne();
            loginAs('committee');

            updateEvent(
                $event,
                settingsForm($event, ['client_type' => '', 'venue_type' => '']),
            )->assertSessionHasErrors(['client_type', 'venue_type']);
        });

        it('should not require the TEM to send the client and venue types', function () {
            $tem = createUser('member');
            $event = EventFactory::new()->managedBy($tem)->createOne();
            actingAs($tem);

            updateEvent(
                $event,
                settingsForm($event, ['client_type' => '', 'venue_type' => '']),
            )->assertSessionHasNoErrors();
        });

        it('should only accept the known crew list statuses', function () {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            updateEvent($event, settingsForm($event, ['crew_list_status' => '2']))->assertSessionHasErrors([
                'crew_list_status' => 'Please select a status for the crew list',
            ]);
        });

        it('should require an EM who is a user', function () {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            updateEvent($event, settingsForm($event, ['em_id' => '999']))->assertSessionHasErrors([
                'em_id' => 'Please select a valid user',
            ]);
        });

        it('should keep what was entered', function () {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            updateEvent($event, settingsForm($event, ['name' => '', 'venue' => 'New Venue']))->assertSessionHasInput(
                'venue',
                'New Venue',
            );
        });
    });

    describe('when the TEM is archived', function () {
        it('should leave them out of the list of EMs in the settings', function () {
            $tem = createUser('archived', ['forename' => 'Gone', 'surname' => 'Away']);
            $event = EventFactory::new()->managedBy($tem)->createOne();
            loginAs('committee');

            get(settingsTab($event))
                ->assertOk()
                ->assertDontSee('<option value="' . $tem->id . '"', false);
        })->group('known-bug', 'bug:inactive-tem-removed');

        it('should remove them when the settings are saved, as the form sends no EM', function () {
            $tem = createUser('archived');
            $event = EventFactory::new()->managedBy($tem)->createOne();
            loginAs('committee');

            updateEvent($event, settingsForm($event, ['em_id' => '', 'name' => 'Just a rename']))->assertRedirect();

            assertDatabaseHas('events', ['id' => $event->id, 'em_id' => null]);
        })->group('known-bug', 'bug:inactive-tem-removed');
    });
});

describe('clearing the crew list', function () {
    function eventWithCrew(): Event
    {
        $event = EventFactory::new()->social()->createOne();
        CrewFactory::new()->for($event)->role('Sound')->createOne();
        CrewFactory::new()->for($event)->createOne();
        CrewFactory::new()->for($event)->guest()->createOne();

        return $event;
    }

    it('should only let admins and the event\'s managers clear it', function (string $group, string|int $access) {
        $event = eventWithCrew();
        loginAs($group);

        expectAccess(updateEvent($event, ['action' => 'clear-crew:all']), $access);

        assertDatabaseCount('event_crew', $access === 302 ? 0 : 3);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should clear the crew', function (string $mode, int $left, string $message) {
        $event = eventWithCrew();
        loginAs('committee');

        updateEvent($event, ['action' => "clear-crew:{$mode}"])->assertRedirect(crewTab($event));

        assertDatabaseCount('event_crew', $left);
        expectNotification('success', $message);
    })->with([
        ['all', 0, 'Crew list cleared'],
        ['core', 2, 'Core crew cleared'],
        ['general', 2, 'General crew cleared'],
        ['guests', 2, 'Guests cleared'],
    ]);

    it('should only clear the guests of a social', function () {
        $event = EventFactory::new()->createOne();
        CrewFactory::new()->for($event)->guest()->createOne();
        loginAs('committee');

        updateEvent($event, ['action' => 'clear-crew:guests'])->assertRedirect(crewTab($event));

        assertDatabaseCount('event_crew', 1);
        expect(notifications())->toBeEmpty();
    });

    it('should do nothing for an unknown mode', function () {
        $event = eventWithCrew();
        loginAs('committee');

        updateEvent($event, ['action' => 'clear-crew:everyone'])->assertRedirect(crewTab($event));

        assertDatabaseCount('event_crew', 3);
    });
});

describe('ticking off the paperwork', function () {
    it('should only let admins and the event\'s managers tick it off', function (string $group, int $status) {
        $event = EventFactory::new()->createOne();
        loginAs($group);

        postAjax(route('event.update', ['id' => $event->id]), [
            'action' => 'update-field',
            'field' => 'paperwork.insurance',
            'value' => '1',
        ])->assertStatus($status);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should store the value that the page sends', function (string $value) {
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        actingAs($tem);

        postAjax(route('event.update', ['id' => $event->id]), [
            'action' => 'update-field',
            'field' => 'paperwork.risk_assessment',
            'value' => $value,
        ])
            ->assertOk()
            ->assertExactJson(['response' => 'Paperwork status updated']);

        expect($event->fresh()?->paperwork)->toMatchArray(['risk_assessment' => $value, 'insurance' => false]);
    })->with(['1', '0']);

    it('should reject unknown paperwork', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        postAjax(route('event.update', ['id' => $event->id]), [
            'action' => 'update-field',
            'field' => 'paperwork.secret',
            'value' => '1',
        ])
            ->assertNotFound()
            ->assertJson(['error' => 'Unknown paperwork', 'error_code' => 0, '__error' => true]);
    });

    it('should only accept AJAX requests', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        updateEvent($event, [
            'action' => 'update-field',
            'field' => 'paperwork.insurance',
            'value' => '1',
        ])->assertRedirect(settingsTab($event));

        expect($event->fresh()?->paperwork['insurance'] ?? null)->toBeFalse();
    });
});
