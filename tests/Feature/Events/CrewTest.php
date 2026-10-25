<?php

namespace Tests\Feature\Events\Crew;

use App\Models\Events\Event;
use App\Models\Log;
use App\Models\Users\User;
use App\Notifications\Events\HasBeenVolunteered;
use App\Notifications\Events\UserHasVolunteered;
use App\Notifications\Events\VolunteeredToCrew;
use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EventFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;

function crewUrl(Event $event, ?int $crewId = null, string $suffix = ''): string
{
    return $crewId === null
        ? route('event.crew.store', ['id' => $event->id])
        : route($suffix === 'delete' ? 'event.crew.destroy' : 'event.crew.update', [
            'id' => $event->id,
            'crewId' => $crewId,
        ]);
}

function lastLog(string $action): ?Log
{
    return Log::where('action', $action)->latest('id')->first();
}

describe('adding a member to the crew', function () {
    it('should only let admins and the event\'s managers add crew', function (string $group, int $status) {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = createUser('member');
        loginAs($group);

        postAjax(crewUrl($event), ['user_id' => $member->id])->assertStatus($status);

        assertDatabaseCount('event_crew', $status === 200 ? 1 : 0);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should let the TEM add crew', function () {
        Notification::fake();
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        actingAs($tem);

        postAjax(crewUrl($event), ['user_id' => createUser()->id])->assertOk();
    });

    it('should only accept AJAX requests', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        post(crewUrl($event), ['user_id' => createUser()->id])->assertNotFound();
    });

    it('should add them to the general crew and email them', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = createUser('member');
        loginAs('committee');

        postAjax(crewUrl($event), ['user_id' => $member->id])
            ->assertOk()
            ->assertExactJson(['response' => 'Member added to crew']);

        assertDatabaseHas('event_crew', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'name' => null,
            'em' => false,
            'confirmed' => false,
            'guest_name' => '',
        ]);
        Notification::assertSentTo($member, HasBeenVolunteered::class);
        expectNotification('success', 'Member added to crew');
    });

    it('should add them to the core crew with a role', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = createUser('member');
        loginAs('committee');

        postAjax(crewUrl($event), [
            'user_id' => $member->id,
            'core' => '1',
            'name' => 'Lighting <b>Designer</b>',
            'em' => '1',
        ])->assertOk();

        assertDatabaseHas('event_crew', [
            'user_id' => $member->id,
            'name' => 'Lighting Designer',
            'em' => true,
        ]);
    });

    it('should ignore the role unless it is core crew', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event), ['user_id' => createUser()->id, 'name' => 'Sound', 'em' => '1'])->assertOk();

        assertDatabaseHas('event_crew', ['name' => null, 'em' => false]);
    });

    it('should only track attendance for training and socials', function (string $type, bool $confirmed) {
        Notification::fake();
        $event = EventFactory::new()->ofType((int) array_search($type, Event::$TypesShort))->createOne();
        loginAs('committee');

        postAjax(crewUrl($event), ['user_id' => createUser()->id, 'confirmed' => '1'])->assertOk();

        assertDatabaseHas('event_crew', ['event_id' => $event->id, 'confirmed' => $confirmed]);
    })->with([['event', false], ['training', true], ['social', true]]);

    it('should not add the same role twice', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = createUser('member', ['forename' => 'Ann']);
        CrewFactory::new()->for($event)->for($member)->role('Sound')->createOne();
        loginAs('committee');

        postAjax(crewUrl($event), ['user_id' => $member->id, 'core' => '1', 'name' => 'Sound'])
            ->assertUnprocessable()
            ->assertJson(['error' => 'Ann already has that crew role.', '__error' => true]);

        assertDatabaseCount('event_crew', 1);
        Notification::assertNothingSent();
    });

    it('should let a member have several roles', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = createUser('member');
        CrewFactory::new()->for($event)->for($member)->role('Sound')->createOne();
        loginAs('committee');

        postAjax(crewUrl($event), ['user_id' => $member->id, 'core' => '1', 'name' => 'Lighting'])->assertOk();

        assertDatabaseCount('event_crew', 2);
    });

    it('should let anyone be added, even if they are archived or not a member', function (string $group) {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $user = createUser($group);
        loginAs('committee');

        postAjax(crewUrl($event), ['user_id' => $user->id])->assertOk();

        assertDatabaseHas('event_crew', ['user_id' => $user->id]);
    })->with(['archived', 'staff', 'super_admin']);

    it('should log the new crew role', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = createUser('member');
        loginAs('committee');

        postAjax(crewUrl($event), ['user_id' => $member->id])->assertOk();

        expect(lastLog('event-crew.create')?->payload)->toMatchArray([
            'event_id' => $event->id,
            'user_id' => (string) $member->id,
        ]);
    });

    describe('validation', function () {
        it('should require a member', function (mixed $userId, string $message) {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            postAjax(crewUrl($event), ['user_id' => $userId])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['user_id' => $message]);
        })->with([['', 'Please select a member'], ['999', 'Please select a member']]);

        it('should require a role for core crew', function () {
            $event = EventFactory::new()->createOne();
            loginAs('committee');

            postAjax(crewUrl($event), ['user_id' => createUser()->id, 'core' => '1', 'name' => ''])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['name' => 'Please enter a role title']);
        });
    });

    describe('the email', function () {
        it('should come from the TEM', function () {
            Notification::fake();
            $tem = createUser('member', ['forename' => 'Tina', 'surname' => 'Tem']);
            $event = EventFactory::new()
                ->managedBy($tem)
                ->createOne(['name' => 'Panto']);
            $member = createUser('member', ['forename' => 'Max']);
            loginAs('committee');

            postAjax(crewUrl($event), ['user_id' => $member->id])->assertOk();

            Notification::assertSentTo($member, function (HasBeenVolunteered $notification) use (
                $member,
                $tem,
                $event,
            ) {
                $mail = $notification->toMail($member);

                expect($mail->subject)->toBe("[Backstage Website] Volunteered to crew event 'Panto'");
                expect($mail->greeting)->toBe('Hi Max,');
                expect($mail->replyTo)->toBe([[$tem->email, 'Tina Tem']]);
                expect($mail->actionUrl)->toBe(route('event.view', ['id' => $event->id]));
                expect(implode(' ', $mail->introLines))->toContain('The Event Manager, Tina Tem');

                return true;
            });
        });

        it('should come from the Production Manager if there is no TEM', function () {
            Notification::fake();
            $event = EventFactory::new()->createOne();
            $member = createUser('member');
            loginAs('committee');

            postAjax(crewUrl($event), ['user_id' => $member->id])->assertOk();

            Notification::assertSentTo($member, function (HasBeenVolunteered $notification) use ($member) {
                $mail = $notification->toMail($member);

                expect($mail->replyTo)->toBe([['pm@bts-crew.com', null]]);
                expect(implode(' ', $mail->outroLines))->toContain('Production Manager');

                return true;
            });
        });
    });
});

describe('adding a guest to a social', function () {
    it('should add a guest', function () {
        $event = EventFactory::new()->social()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event), ['guest' => '1', 'guest_name' => 'Plus One', 'confirmed' => '1'])
            ->assertOk()
            ->assertExactJson(['response' => 'Guest added']);

        assertDatabaseHas('event_crew', [
            'event_id' => $event->id,
            'user_id' => null,
            'name' => null,
            'em' => false,
            'confirmed' => true,
            'guest_name' => 'Plus One',
        ]);
        expectNotification('success', 'Guest added');
    });

    it('should require the guest\'s name', function () {
        $event = EventFactory::new()->social()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event), ['guest' => '1', 'guest_name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['guest_name' => "Please enter the guest's name"]);
    });

    it('should treat a guest on anything but a social as a member', function () {
        $event = EventFactory::new()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event), ['guest' => '1', 'guest_name' => 'Plus One'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id']);
    });
});

describe('changing a crew role', function () {
    it('should only let admins and the event\'s managers change it', function (string $group, int $status) {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->role('Sound')->createOne();
        loginAs($group);

        postAjax(crewUrl($event, $crew->id), ['core' => '1', 'name' => 'Lighting'])->assertStatus($status);

        assertDatabaseHas('event_crew', ['id' => $crew->id, 'name' => $status === 200 ? 'Lighting' : 'Sound']);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should change the role', function () {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id), ['core' => '1', 'name' => 'Lighting', 'em' => '1'])
            ->assertOk()
            ->assertExactJson(['response' => 'Crew role updated']);

        assertDatabaseHas('event_crew', ['id' => $crew->id, 'name' => 'Lighting', 'em' => true]);
        expectNotification('success', 'Crew role updated');
    });

    it('should remove the EM flag and attendance unless they are sent again', function () {
        $event = EventFactory::new()->training()->createOne();
        $crew = CrewFactory::new()->for($event)->eventManager('Instructor')->confirmed()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id), ['core' => '1', 'name' => 'Instructor'])->assertOk();

        assertDatabaseHas('event_crew', ['id' => $crew->id, 'em' => false, 'confirmed' => false]);
    })->group('known-bug', 'bug:crew-edit-strips-flags');

    it('should move them to the general crew without the core flag', function () {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->role('Sound')->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id), ['name' => 'Sound'])->assertOk();

        assertDatabaseHas('event_crew', ['id' => $crew->id, 'name' => null]);
    });

    it('should toggle whether a crew member attended or paid', function (string $value, bool $confirmed) {
        $event = EventFactory::new()->training()->createOne();
        $crew = CrewFactory::new()
            ->for($event)
            ->createOne(['confirmed' => !$confirmed]);
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id), ['action' => 'update-field', 'field' => 'confirmed', 'value' => $value])
            ->assertOk()
            ->assertExactJson(['response' => 'Field updated']);

        assertDatabaseHas('event_crew', ['id' => $crew->id, 'confirmed' => $confirmed]);
    })->with([['1', true], ['0', false]]);

    it('should only toggle whether they attended', function () {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id), ['action' => 'update-field', 'field' => 'em', 'value' => '1'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['field' => 'No valid field specified']);
    });

    it('should rename a guest on a social', function () {
        $event = EventFactory::new()->social()->createOne();
        $crew = CrewFactory::new()->for($event)->guest('Old Name')->confirmed()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id), ['guest' => '1', 'guest_name' => 'New Name'])
            ->assertOk()
            ->assertExactJson(['response' => 'Guest updated']);

        assertDatabaseHas('event_crew', ['id' => $crew->id, 'guest_name' => 'New Name', 'confirmed' => false]);
    });

    it('should give a 404 for crew of another event', function () {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id), ['name' => 'Sound'])->assertNotFound();
    });

    it('should only accept AJAX requests', function () {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->createOne();
        loginAs('committee');

        post(crewUrl($event, $crew->id), ['core' => '1', 'name' => 'Sound'])->assertNotFound();
    });
});

describe('removing a crew role', function () {
    it('should only let admins and the event\'s managers remove it', function (string $group, int $status) {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->createOne();
        loginAs($group);

        postAjax(crewUrl($event, $crew->id, 'delete'))->assertStatus($status);

        assertDatabaseCount('event_crew', $status === 200 ? 0 : 1);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should remove a member', function () {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id, 'delete'))
            ->assertOk()
            ->assertExactJson(['response' => 'Crew role deleted']);

        assertDatabaseMissing('event_crew', ['id' => $crew->id]);
        expectNotification('success', 'Crew role deleted');
    });

    it('should remove a guest', function () {
        $event = EventFactory::new()->social()->createOne();
        $crew = CrewFactory::new()->for($event)->guest()->createOne();
        loginAs('committee');

        postAjax(crewUrl($event, $crew->id, 'delete'))
            ->assertOk()
            ->assertExactJson(['response' => 'Guest removed']);
    });

    it('should accept requests that aren\'t AJAX', function () {
        $event = EventFactory::new()->createOne();
        $crew = CrewFactory::new()->for($event)->createOne();
        loginAs('committee');

        post(crewUrl($event, $crew->id, 'delete'))->assertOk();

        assertDatabaseCount('event_crew', 0);
    });
});

describe('volunteering', function () {
    /**
     * @return TestResponse<Response>
     */
    function volunteer(Event $event): TestResponse
    {
        return postAjax(route('event.volunteer', ['id' => $event->id]));
    }

    it('should only let members volunteer', function (string $group, int $status) {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        loginAs($group);

        volunteer($event)->assertStatus($status);

        assertDatabaseCount('event_crew', $status === 200 ? 1 : 0);
    })->with([
        ['guest', 401],
        ['staff', 403],
        ['member', 200],
        ['associate', 200],
        ['committee', 200],
        ['super_admin', 403],
    ]);

    it('should only let members volunteer while the crew list is open', function (int $status) {
        $event = EventFactory::new()->crewList($status)->createOne();
        loginAs('committee');

        volunteer($event)->assertForbidden();
    })->with(['closed' => fn() => Event::CREW_LIST_CLOSED, 'hidden' => fn() => Event::CREW_LIST_HIDDEN]);

    it('should not let the TEM volunteer', function () {
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        actingAs($tem);

        volunteer($event)->assertForbidden();
    });

    it('should only accept AJAX requests', function () {
        $event = EventFactory::new()->createOne();
        loginAs('member');

        post(route('event.volunteer', ['id' => $event->id]))->assertNotFound();
    });

    it('should add them to the general crew and email them and the TEM', function () {
        Notification::fake();
        $tem = createUser('member');
        $event = EventFactory::new()->managedBy($tem)->createOne();
        $member = loginAs('member');
        assert($member instanceof User);

        volunteer($event)
            ->assertOk()
            ->assertExactJson(['response' => 'Volunteered']);

        assertDatabaseHas('event_crew', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'name' => null,
            'em' => false,
            'confirmed' => false,
            'guest_name' => '',
        ]);
        Notification::assertSentTo($member, VolunteeredToCrew::class);
        Notification::assertSentTo($tem, UserHasVolunteered::class);
        expectNotification('success', 'You have volunteered');
    });

    it('should rely on MariaDB filling in the crew row\'s other columns', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        loginAs('member');

        volunteer($event)->assertOk();

        assertDatabaseHas('event_crew', ['event_id' => $event->id, 'em' => 0, 'confirmed' => 0, 'guest_name' => '']);
    })->group('mariadb');

    it('should only email the volunteer if there is no TEM', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = loginAs('member');

        volunteer($event)->assertOk();

        Notification::assertSentTimes(VolunteeredToCrew::class, 1);
        Notification::assertNotSentTo($member ?? createUser(), UserHasVolunteered::class);
        Notification::assertSentTimes(UserHasVolunteered::class, 0);
    });

    it('should tell the TEM who volunteered', function () {
        Notification::fake();
        $tem = createUser('member', ['forename' => 'Tina']);
        $event = EventFactory::new()
            ->managedBy($tem)
            ->createOne(['name' => 'Panto']);
        $member = loginAs('member', ['forename' => 'Val', 'surname' => 'Volunteer']);
        assert($member instanceof User);

        volunteer($event)->assertOk();

        Notification::assertSentTo($tem, function (UserHasVolunteered $notification) use ($tem, $member, $event) {
            $mail = $notification->toMail($tem);

            expect($mail->subject)->toBe("[Backstage Website] Crew volunteered for 'Panto'");
            expect($mail->greeting)->toBe('Hi Tina,');
            expect($mail->replyTo)->toBe([[$member->email, 'Val Volunteer']]);
            expect(implode(' ', $mail->introLines))
                ->toContain('**Val Volunteer** has volunteered to crew your event **Panto**')
                ->toContain(route('event.view', ['id' => $event->id, 'tab' => 'emails']));

            return true;
        });
    });

    it('should thank the volunteer', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne(['name' => 'Panto']);
        $member = loginAs('member', ['forename' => 'Val']);
        assert($member instanceof User);

        volunteer($event)->assertOk();

        Notification::assertSentTo($member, function (VolunteeredToCrew $notification) use ($member) {
            $mail = $notification->toMail($member);

            expect($mail->subject)->toBe("[Backstage Website] Volunteered to crew event 'Panto'");
            expect($mail->introLines[0])->toBe('Thank you for volunteering to crew the event **Panto**.');

            return true;
        });
    });

    it('should let members volunteer for events that have finished, as the crew lists never close', function () {
        Notification::fake();
        $event = EventFactory::new()->past()->createOne();
        Artisan::call('crew:auto-close');
        loginAs('member');

        volunteer($event)->assertOk();

        assertDatabaseHas('events', ['id' => $event->id, 'crew_list_status' => Event::CREW_LIST_OPEN]);
    })->group('known-bug', 'bug:auto-close');

    it('should log the volunteering', function () {
        Notification::fake();
        $event = EventFactory::new()->createOne();
        $member = loginAs('member');

        volunteer($event)->assertOk();

        expect(lastLog('event.volunteer')?->payload)->toMatchArray([
            'event_id' => $event->id,
            'user_id' => $member?->id,
        ]);
    });
});

describe('unvolunteering', function () {
    it('should remove all of the member\'s roles', function () {
        $event = EventFactory::new()->createOne();
        $member = loginAs('member');
        assert($member instanceof User);
        CrewFactory::new()->for($event)->for($member)->createOne();
        CrewFactory::new()->for($event)->for($member)->role('Sound')->createOne();
        $other = CrewFactory::new()->for($event)->createOne();

        postAjax(route('event.volunteer', ['id' => $event->id]))
            ->assertOk()
            ->assertExactJson(['response' => 'Unvolunteered']);

        assertDatabaseCount('event_crew', 1);
        assertDatabaseHas('event_crew', ['id' => $other->id]);
        expectNotification('success', 'You have unvolunteered');
    });

    it('should not let members unvolunteer from a social', function () {
        $event = EventFactory::new()->social()->createOne();
        $member = loginAs('member');
        CrewFactory::new()
            ->for($event)
            ->for($member ?? createUser())
            ->createOne();

        postAjax(route('event.volunteer', ['id' => $event->id]))
            ->assertUnauthorized()
            ->assertJson(['error' => "You can't unvolunteer from a social.", '__error' => true]);

        assertDatabaseCount('event_crew', 1);
    });

    it('should only let members unvolunteer while the crew list is open', function () {
        $event = EventFactory::new()->crewList(Event::CREW_LIST_CLOSED)->createOne();
        $member = loginAs('member');
        CrewFactory::new()
            ->for($event)
            ->for($member ?? createUser())
            ->createOne();

        postAjax(route('event.volunteer', ['id' => $event->id]))->assertForbidden();

        assertDatabaseCount('event_crew', 1);
    });

    it('should log the user ID as a value rather than a key', function () {
        $event = EventFactory::new()->createOne();
        $member = loginAs('member');
        CrewFactory::new()
            ->for($event)
            ->for($member ?? createUser())
            ->createOne();

        postAjax(route('event.volunteer', ['id' => $event->id]))->assertOk();

        expect(lastLog('event.unvolunteer')?->payload)->toBe([
            'event_id' => $event->id,
            0 => 'user_id',
            1 => $member?->id,
        ]);
    })->group('known-bug', 'bug:unvolunteer-log');
});
