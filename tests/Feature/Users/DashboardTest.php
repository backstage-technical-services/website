<?php

namespace Tests\Feature\Users\Dashboard;

use App\Models\Events\Event;
use App\Models\Users\User;
use Database\Factories\Events\EventFactory;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

beforeEach(function () {
    travelTo(Carbon::parse('2026-11-01 12:00'));
});

/**
 * Get the names of the events in a section of the dashboard.
 *
 * @param TestResponse<Response> $response
 *
 * @return list<string>
 */
function eventsIn(TestResponse $response, string $heading): array
{
    $html = (string) $response->getContent();
    $start = strpos($html, "<h2>{$heading}</h2>");
    if ($start === false) {
        return [];
    }
    $section = substr($html, $start + strlen($heading) + 9);
    $end = strpos($section, '<h2>');
    $section = $end === false ? $section : substr($section, 0, $end);
    preg_match_all('/<a href="[^"]*\/events\/\d+[^"]*"[^>]*>(.*?)<\/a>/', $section, $links);

    return $links[1];
}

function eventOn(string $start, string $name, int $type = Event::TYPE_EVENT, ?User $em = null): Event
{
    return EventFactory::new()
        ->ofType($type)
        ->at($start)
        ->createOne(['name' => $name, 'em_id' => $em?->id]);
}

it('should need a login', function () {
    get(route('member.dash'))->assertRedirect(route('auth.login'));
    get(route('member.index'))->assertRedirect(route('auth.login'));
});

it('should be for members', function (string $group) {
    loginAs($group);

    get(route('member.dash'))->assertOk()->assertSeeText("Members' Dashboard");
    get(route('member.index'))->assertOk()->assertSeeText("Members' Dashboard");
})->with(['member', 'associate', 'committee']);

it('should send anyone who isn\'t a member to the diary', function (string $group) {
    loginAs($group);

    get(route('member.dash'))->assertRedirect(route('event.diary'));
})->with(['staff', 'super_admin']);

it('should list the next 5 events, training sessions and socials', function () {
    foreach (range(1, 6) as $day) {
        eventOn("2026-11-0{$day} 19:00", "Event {$day}");
        eventOn("2026-11-0{$day} 10:00", "Training {$day}", Event::TYPE_TRAINING);
        eventOn("2026-11-0{$day} 21:00", "Social {$day}", Event::TYPE_SOCIAL);
    }
    eventOn('2026-10-31 19:00', 'Past Event');
    eventOn('2026-11-02 19:00', 'Meeting', Event::TYPE_MEETING);
    loginAs('member');

    $response = get(route('member.dash'))->assertOk();

    expect(eventsIn($response, 'Upcoming Events'))->toBe(['Event 1', 'Event 2', 'Event 3', 'Event 4', 'Event 5']);
    expect(eventsIn($response, 'Upcoming Training'))->toBe([
        'Training 1',
        'Training 2',
        'Training 3',
        'Training 4',
        'Training 5',
    ]);
    expect(eventsIn($response, 'Upcoming Socials'))->toBe(['Social 1', 'Social 2', 'Social 3', 'Social 4', 'Social 5']);
})->group('mariadb');

it('should include events that started earlier today', function () {
    eventOn('2026-11-01 09:00', 'This Morning');
    loginAs('member');

    expect(eventsIn(get(route('member.dash')), 'Upcoming Events'))->toBe(['This Morning']);
})->group('mariadb');

it('should say when there are no upcoming events', function () {
    loginAs('member');

    get(route('member.dash'))
        ->assertOk()
        ->assertSeeText([
            'There are no upcoming events',
            'There are no upcoming training sessions',
            'There are no upcoming socials',
        ]);
});

it('should only say there is no training or socials when there are no events', function () {
    eventOn('2026-11-02 19:00', 'Gig');
    eventOn('2026-11-02 10:00', 'Training', Event::TYPE_TRAINING);
    loginAs('member');

    get(route('member.dash'))
        ->assertOk()
        ->assertDontSeeText(['There are no upcoming training sessions', 'There are no upcoming socials']);

    eventOn('2026-11-03 10:00', 'More Training', Event::TYPE_TRAINING);
    Event::where('name', 'Gig')->delete();

    get(route('member.dash'))->assertSeeText('There are no upcoming training sessions');
})->group('known-bug', 'bug:dashboard-empty-messages');

it('should list the upcoming events that need a TEM', function () {
    eventOn('2026-11-02 19:00', 'No TEM');
    eventOn('2026-11-03 19:00', 'Has TEM', em: createUser());
    eventOn('2026-10-30 19:00', 'Past, no TEM');
    eventOn('2026-11-04 19:00', 'Social, no TEM', Event::TYPE_SOCIAL);
    loginAs('member');

    expect(eventsIn(get(route('member.dash')), 'Events needing a TEM'))->toBe(['No TEM']);
})->group('mariadb');

describe('outstanding paperwork', function () {
    it('should list the events the member manages that still need paperwork', function () {
        $tem = loginAs('member');
        assert($tem instanceof User);
        eventOn('2026-11-02 19:00', 'Mine', em: $tem);
        eventOn('2026-11-03 19:00', 'Someone else\'s', em: createUser());
        eventOn('2026-10-01 19:00', 'Mine, in the past', em: $tem);

        expect(eventsIn(get(route('member.dash')), 'Your events with outstanding paperwork'))->toBe([
            'Mine',
            'Mine, in the past',
        ]);
    });

    it('should leave out events where only the treasurer\'s finance is outstanding', function () {
        $tem = loginAs('member');
        assert($tem instanceof User);
        $event = eventOn('2026-11-02 19:00', 'Mine', em: $tem);
        foreach (['risk_assessment', 'insurance', 'finance_em', 'event_report'] as $paperwork) {
            $event->setPaperwork($paperwork, true);
        }

        get(route('member.dash'))->assertOk()->assertDontSeeText('Your events with outstanding paperwork');
    });

    it('should miss paperwork that was ticked and then unticked', function () {
        $tem = createUser('member');
        $event = eventOn('2026-11-02 19:00', 'Mine', em: $tem);
        actingAs($tem);
        foreach (['risk_assessment', 'insurance', 'finance_em', 'event_report'] as $paperwork) {
            postAjax(route('event.update', ['id' => $event->id]), [
                'action' => 'update-field',
                'field' => "paperwork.{$paperwork}",
                'value' => '1',
            ])->assertOk();
        }
        postAjax(route('event.update', ['id' => $event->id]), [
            'action' => 'update-field',
            'field' => 'paperwork.insurance',
            'value' => '0',
        ])->assertOk();

        get(route('member.dash'))->assertOk()->assertDontSeeText('Your events with outstanding paperwork');
    })->group('known-bug', 'bug:dashboard-paperwork');

    it('should not list events the member is only crewing', function () {
        loginAs('member');

        get(route('member.dash'))->assertOk()->assertDontSeeText('Your events with outstanding paperwork');
    });
});
