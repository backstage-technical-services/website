<?php

namespace Tests\Feature\Events\ViewEvent;

use App\Models\Events\Event;
use App\Models\Users\User;
use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EmailFactory;
use Database\Factories\Events\EventFactory;
use Database\Factories\Events\TimeFactory;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * View an event's page, on the given tab.
 *
 * @return TestResponse<Response>
 */
function viewEvent(Event $event, ?string $tab = null): TestResponse
{
    return get(route('event.view', array_filter(['id' => $event->id, 'tab' => $tab])));
}

/**
 * Check which of the event page's tabs are shown, by the link to each.
 *
 * @param TestResponse<Response> $response
 * @param array<mixed> $tabs
 */
function expectTabs(TestResponse $response, Event $event, array $tabs): void
{
    foreach (['crew', 'times', 'emails', 'paperwork', 'settings'] as $tab) {
        $link = 'href="' . e(route('event.view', ['id' => $event->id, 'tab' => $tab])) . '"';
        in_array($tab, $tabs) ? $response->assertSee($link, false) : $response->assertDontSee($link, false);
    }
}

function managedEvent(User $tem): Event
{
    return EventFactory::new()->managedBy($tem)->createOne();
}

describe('who can see an event', function () {
    it('should show events to everyone', function (string $group) {
        $event = EventFactory::new()->createOne(['name' => 'Public Show']);
        loginAs($group);

        viewEvent($event)->assertOk()->assertSeeText('Public Show');
    })->with(['guest', 'staff', 'member', 'associate', 'committee', 'super_admin']);

    it('should only show the other types to members', function (string $type, string $group, int $status) {
        $event = EventFactory::new()->ofType((int) array_search($type, Event::$TypesShort))->createOne();
        loginAs($group);

        viewEvent($event)->assertStatus($status);
    })
        ->with(['training', 'social', 'meeting', 'hidden'])
        ->with([
            ['guest', 403],
            ['staff', 403],
            ['member', 200],
            ['associate', 200],
            ['committee', 200],
            ['super_admin', 403],
        ]);

    it('should give a 404 for an unknown event', function () {
        get(route('event.view', ['id' => 999]))->assertNotFound();
    });

    it('should fail for an event without any time slots', function () {
        $event = EventFactory::new()->withoutTimes()->createOne();

        viewEvent($event)->assertServerError();
    })->group('known-bug', 'bug:events-without-times');
});

describe('the details', function () {
    it('should show the details', function () {
        $em = createUser('member', ['forename' => 'Emma', 'surname' => 'Manager']);
        $event = EventFactory::new()
            ->managedBy($em)
            ->at('2026-11-01 19:00')
            ->createOne(['name' => 'Panto', 'venue' => 'Arts Theatre', 'client_type' => 2]);

        viewEvent($event)
            ->assertOk()
            ->assertSeeText(['Panto', '1st Nov 2026', 'Emma Manager', 'Event', 'University', 'Arts Theatre']);
    });

    it('should show the date range of events over several days', function () {
        $event = EventFactory::new()->at('2026-11-01 19:00')->createOne();
        TimeFactory::new()
            ->for($event)
            ->createOne(['start' => '2026-11-03 19:00', 'end' => '2026-11-03 23:00']);

        viewEvent($event)->assertOk()->assertSee('1st Nov 2026', false)->assertSee('&mdash; 3rd Nov 2026', false);
    });

    it('should say when there is no EM', function () {
        $event = EventFactory::new()->createOne();

        viewEvent($event)->assertOk()->assertSee('not yet assigned', false);
    });

    it('should only show the client for events', function () {
        loginAs('member');
        $social = EventFactory::new()->social()->createOne();

        viewEvent($social)->assertOk()->assertDontSeeText('Client:');
    });

    it('should render the description as Markdown', function () {
        $event = EventFactory::new()->createOne(['description' => "Some **bold** text\n\n- a list"]);

        viewEvent($event)->assertOk()->assertSee('<strong>bold</strong>', false)->assertSee('<li>a list</li>', false);
    });

    it('should not render unsafe links in the description', function () {
        $event = EventFactory::new()->createOne(['description' => '[Click me](javascript:alert(1))']);

        viewEvent($event)->assertOk()->assertDontSee('href="javascript:', false);
    });

    it('should show text with HTML characters escaped twice', function () {
        $event = EventFactory::new()->createOne(['name' => 'Rock &amp; Roll']);

        viewEvent($event)->assertOk()->assertSee('<h1>Rock &amp;amp; Roll</h1>', false);
    })->group('known-bug', 'bug:html-escaped-text');

    it('should only show the production charge to the event\'s managers', function (string $group, bool $shown) {
        $event = EventFactory::new()->createOne(['production_charge' => 150]);
        loginAs($group);

        $response = viewEvent($event)->assertOk();
        $shown ? $response->assertSee('£ 150', false) : $response->assertDontSeeText('Production Charge');
    })->with([['guest', false], ['member', false], ['committee', true], ['super_admin', true]]);

    it('should show the production charge to the TEM', function () {
        $tem = loginAs('member');
        $event = EventFactory::new()
            ->managedBy($tem ?? createUser())
            ->createOne(['production_charge' => 12.5]);

        viewEvent($event)->assertOk()->assertSee('£ 12.50', false);
    });
});

describe('the tabs', function () {
    it('should show the tabs for each group', function (string $group, array $tabs) {
        $event = EventFactory::new()->createOne();
        loginAs($group);

        expectTabs(viewEvent($event)->assertOk(), $event, $tabs);
    })->with([
        ['guest', ['crew', 'times']],
        ['staff', ['crew', 'times']],
        ['member', ['crew', 'times', 'paperwork']],
        ['committee', ['crew', 'times', 'paperwork', 'settings']],
        ['super_admin', ['crew', 'times', 'settings']],
    ]);

    it('should show the emails tab to the crew', function () {
        $member = loginAs('member');
        $event = EventFactory::new()->createOne();
        CrewFactory::new()
            ->for($event)
            ->for($member ?? createUser())
            ->createOne();

        expectTabs(viewEvent($event)->assertOk(), $event, ['crew', 'times', 'emails', 'paperwork']);
    });

    it('should show the emails and settings tabs to the TEM', function () {
        $tem = loginAs('member');
        $event = managedEvent($tem ?? createUser());

        expectTabs(viewEvent($event)->assertOk(), $event, ['crew', 'times', 'emails', 'paperwork', 'settings']);
    });

    it('should show the emails and settings tabs to members with an EM role', function () {
        $member = loginAs('member');
        $event = EventFactory::new()->createOne();
        CrewFactory::new()
            ->for($event)
            ->for($member ?? createUser())
            ->eventManager()
            ->createOne();

        expectTabs(viewEvent($event)->assertOk(), $event, ['crew', 'times', 'emails', 'paperwork', 'settings']);
    });

    it('should open the tab given in the URL', function () {
        $event = EventFactory::new()->createOne();

        viewEvent($event, 'times')
            ->assertOk()
            ->assertSee('<div class="tab-pane active" id="event_times">', false)
            ->assertSee('<div class="tab-pane" id="event_details">', false);
    });

    it('should count the paperwork that is still to do', function () {
        loginAs('member');
        $event = EventFactory::new()->createOne();
        $event->setPaperwork('insurance', true);

        viewEvent($event)->assertOk()->assertSee('<span class="badge">4</span>', false);
    });
});

describe('the crew list', function () {
    it('should list the TEM and crew to everyone, grouped by role', function () {
        $event = EventFactory::new()
            ->managedBy(createUser('member', ['forename' => 'Tina', 'surname' => 'Tem']))
            ->createOne();
        CrewFactory::new()
            ->for($event)
            ->for(createUser('member', ['forename' => 'Larry', 'surname' => 'Lights']))
            ->role('Lighting')
            ->createOne();
        CrewFactory::new()
            ->for($event)
            ->for(createUser('member', ['forename' => 'Gina', 'surname' => 'General']))
            ->createOne();

        viewEvent($event, 'crew')
            ->assertOk()
            ->assertSeeText('1 core crew and 1 general crew')
            ->assertSeeTextInOrder(['TEM:', 'Tina Tem', 'Lighting:', 'Larry Lights', 'General Crew:', 'Gina General']);
    });

    it('should say when no one is crewing', function () {
        $event = EventFactory::new()->createOne();

        viewEvent($event, 'crew')->assertOk()->assertSeeText('No one is crewing this event yet');
    });

    it('should count the members and guests of a social, and how many have paid', function () {
        loginAs('member');
        $event = EventFactory::new()->social()->createOne();
        CrewFactory::new()->for($event)->confirmed()->createOne();
        CrewFactory::new()->for($event)->guest('Guest One')->confirmed()->createOne();
        CrewFactory::new()->for($event)->guest('Guest Two')->createOne();

        viewEvent($event, 'crew')
            ->assertOk()
            ->assertSeeText('1 members and 2 guests (2 paid)')
            ->assertSeeTextInOrder(['Guests:', 'Guest One', 'Guest Two']);
    });

    it('should count the instructors and attendees of a training session', function () {
        loginAs('member');
        $event = EventFactory::new()->training()->createOne();
        CrewFactory::new()->for($event)->eventManager('Instructor')->createOne();
        CrewFactory::new()->for($event)->confirmed()->createOne();
        CrewFactory::new()->for($event)->confirmed()->createOne();

        viewEvent($event, 'crew')->assertOk()->assertSeeText('1 instructors and 2 attendees');
    });

    it('should always say that no one attended a training session', function () {
        loginAs('member');
        $event = EventFactory::new()->training()->createOne();
        CrewFactory::new()->for($event)->confirmed()->createOne();

        viewEvent($event, 'crew')->assertOk()->assertSeeText('(0 attended)');
    })->group('known-bug', 'bug:training-attended-count');

    it('should only show a hidden crew list to admins', function (string $group, bool $shown) {
        $event = EventFactory::new()->crewList(Event::CREW_LIST_HIDDEN)->createOne();
        CrewFactory::new()
            ->for($event)
            ->for(createUser('member', ['forename' => 'Secret', 'surname' => 'Crew']))
            ->createOne();
        loginAs($group);

        $response = viewEvent($event, 'crew')->assertOk();
        $shown
            ? $response->assertSee('<div class="tab-pane active" id="event_crew">', false)->assertSeeText('Secret Crew')
            : $response
                ->assertDontSee('<div class="tab-pane active" id="event_crew">', false)
                ->assertDontSeeText('Secret Crew');
    })->with([['guest', false], ['member', false], ['committee', true], ['super_admin', true]]);

    it('should hide a hidden crew list from the event\'s managers too', function () {
        $tem = loginAs('member');
        $event = EventFactory::new()
            ->managedBy($tem ?? createUser())
            ->crewList(Event::CREW_LIST_HIDDEN)
            ->createOne();
        CrewFactory::new()
            ->for($event)
            ->for(createUser('member', ['forename' => 'Secret', 'surname' => 'Crew']))
            ->createOne();

        viewEvent($event, 'crew')->assertOk()->assertDontSee('<div class="tab-pane active" id="event_crew">', false);
    })->group('known-bug', 'bug:hidden-crew-list');

    it('should offer to volunteer on an open list', function (string $group, bool $offered) {
        $event = EventFactory::new()->createOne();
        loginAs($group);

        $response = viewEvent($event, 'crew')->assertOk();
        $button = 'data-submit-ajax="' . route('event.volunteer', ['id' => $event->id]) . '"';
        $offered ? $response->assertSee($button, false) : $response->assertDontSee($button, false);
    })->with([['guest', false], ['staff', false], ['member', true], ['committee', true], ['super_admin', false]]);

    it('should offer to unvolunteer to the crew, except on a social', function (string $type, bool $offered) {
        $member = loginAs('member');
        $event = EventFactory::new()->ofType((int) array_search($type, Event::$TypesShort))->createOne();
        CrewFactory::new()
            ->for($event)
            ->for($member ?? createUser())
            ->createOne();

        $response = viewEvent($event, 'crew')->assertOk();
        $offered ? $response->assertSeeText('Unvolunteer') : $response->assertDontSeeText('Unvolunteer');
    })->with([['event', true], ['training', true], ['social', false]]);
});

describe('the times', function () {
    it('should list the time slots in order', function () {
        $event = EventFactory::new()->withoutTimes()->createOne();
        TimeFactory::new()
            ->for($event)
            ->createOne(['name' => 'Show', 'start' => '2026-11-01 19:30', 'end' => '2026-11-01 22:00']);
        TimeFactory::new()
            ->for($event)
            ->createOne(['name' => 'Get In', 'start' => '2026-11-01 09:00', 'end' => '2026-11-01 12:00']);

        viewEvent($event, 'times')
            ->assertOk()
            ->assertSeeTextInOrder(['09:00', '12:00', 'Get In', '19:30', '22:00', 'Show']);
    });
});

describe('the emails', function () {
    it('should list the emails sent to the crew, newest first', function () {
        $tem = createUser('member');
        $event = managedEvent($tem);
        $sender = createUser('member', ['forename' => 'Sam', 'surname' => 'Sender']);
        EmailFactory::new()
            ->for($event)
            ->for($sender, 'sender')
            ->createOne(['header' => 'First email', 'body' => 'Hello **crew**', 'created_at' => '2026-10-01 10:00']);
        EmailFactory::new()
            ->for($event)
            ->for($sender, 'sender')
            ->createOne(['header' => 'Second email', 'created_at' => '2026-10-02 10:00']);
        actingAs($tem);

        viewEvent($event, 'emails')
            ->assertOk()
            ->assertSeeTextInOrder(['Second email', 'First email'])
            ->assertSeeText("Sam Sender ({$sender->username})")
            ->assertSee('Hello <strong>crew</strong>', false);
    });

    it('should say when there are no emails', function () {
        $tem = loginAs('member');
        $event = managedEvent($tem ?? createUser());

        viewEvent($event, 'emails')->assertOk()->assertSeeText('There are no crew emails for this event.');
    });

    it('should give the managers a mailto link to the crew', function () {
        $tem = loginAs('member');
        $event = managedEvent($tem ?? createUser());
        $crew = createUser('member');
        CrewFactory::new()->for($event)->for($crew)->createOne();

        viewEvent($event, 'emails')
            ->assertOk()
            ->assertSee(
                'href="mailto:' . $crew->email . '?subject=[Backstage Website] ' . e($event->name) . '"',
                false,
            );
    });
});

describe('the paperwork', function () {
    it('should show what has been done', function () {
        loginAs('member');
        $event = EventFactory::new()->createOne();
        $event->setPaperwork('risk_assessment', true);

        viewEvent($event, 'paperwork')
            ->assertOk()
            ->assertSeeInOrder(['fa-check', 'Risk assessment', 'fa-remove', 'Insurance'], false);
    });

    it('should only link the managers to the forms', function (string $group, bool $linked) {
        loginAs($group);
        $event = EventFactory::new()->createOne();

        $response = viewEvent($event, 'paperwork')->assertOk();
        $linked
            ? $response->assertSeeText(['Risk assessment form', 'Event report form'])
            : $response->assertDontSeeText(['Risk assessment form', 'Event report form']);
    })->with([['member', false], ['committee', true]]);
});
