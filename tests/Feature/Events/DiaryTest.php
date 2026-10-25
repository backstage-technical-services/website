<?php

namespace Tests\Feature\Events\Diary;

use App\Models\Users\User;
use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EventFactory;
use Database\Factories\Events\TimeFactory;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;
use function Pest\Laravel\withHeader;

/**
 * @return TestResponse<Response>
 */
function diary(int $year = 2026, int $month = 11): TestResponse
{
    return get(route('event.diary', ['year' => $year, 'month' => $month]));
}

/**
 * Get the events listed on a day of the diary, keyed by name, with whether each is shown.
 *
 * @param TestResponse<Response> $response
 *
 * @return array<string, bool>
 */
function eventsOnDay(TestResponse $response, int $day): array
{
    $html = (string) $response->getContent();
    preg_match_all(
        '/<div class="cell day[^"]*">\s*<span class="date">(\d+)<\/span>(.*?)<\/div>\s*<\/div>\s*(?=<div class="cell|<span class="cell|<\/div>)/s',
        $html,
        $days,
        PREG_SET_ORDER,
    );

    foreach ($days as [, $date, $contents]) {
        if ((int) $date === $day) {
            preg_match_all(
                '/<div class="event"[^>]*?(style=display:none;)?>.*?<a[^>]*>(.*?)<\/a>/s',
                $contents,
                $events,
                PREG_SET_ORDER,
            );

            return collect($events)->mapWithKeys(fn(array $event) => [$event[2] => $event[1] === ''])->all();
        }
    }

    return [];
}

/**
 * @param array<string, string> $query
 *
 * @return TestResponse<Response>
 */
function export(array $query = []): TestResponse
{
    return get(route('event.export', $query));
}

function memberWithToken(string $token = 'secret-token'): User
{
    return createUser('member', ['export_token' => $token]);
}

describe('the diary', function () {
    it('should be open to everyone', function (string $group) {
        loginAs($group);

        diary()->assertOk()->assertSeeText('November 2026');
    })->with(['guest', 'staff', 'member', 'committee', 'super_admin']);

    it('should show the current month by default', function () {
        travelTo(Carbon::parse('2026-03-15 12:00'));

        get(route('event.diary'))->assertOk()->assertSeeText('March 2026');
    });

    it('should show the current month if only the year is given', function () {
        travelTo(Carbon::parse('2026-03-15 12:00'));

        get(route('event.diary', ['year' => 2025]))
            ->assertOk()
            ->assertSeeText('March 2026');
    });

    it('should link to the months before and after', function () {
        diary(2026, 1)
            ->assertOk()
            ->assertSee('href="' . route('event.diary', ['year' => 2025, 'month' => 12]) . '"', false)
            ->assertSee('href="' . route('event.diary', ['year' => 2026, 'month' => 2]) . '"', false);
    });

    it('should roll an invalid month over into the next year', function () {
        diary(2026, 13)->assertOk()->assertSeeText('January 2027');
    });

    it('should give a 404 for a badly formed date', function () {
        get('/events/diary/26/1')->assertNotFound();
    });

    it('should list the events on each day', function () {
        EventFactory::new()
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Gig']);

        $response = diary()->assertOk();

        expect(eventsOnDay($response, 5))->toBe(['Gig' => true]);
        expect(eventsOnDay($response, 6))->toBe([]);
    });

    it('should list an event on every day it has a time slot', function () {
        $event = EventFactory::new()
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Festival']);
        TimeFactory::new()
            ->for($event)
            ->createOne(['start' => '2026-11-07 19:00', 'end' => '2026-11-07 23:00']);

        $response = diary()->assertOk();

        expect(eventsOnDay($response, 5))->toBe(['Festival' => true]);
        expect(eventsOnDay($response, 6))->toBe([]);
        expect(eventsOnDay($response, 7))->toBe(['Festival' => true]);
    });

    it('should list events that run past midnight on both days, in UTC', function () {
        EventFactory::new()
            ->at('2026-11-05 23:00', '2026-11-06 01:00')
            ->createOne(['name' => 'Late Gig']);

        $response = diary()->assertOk();

        expect(eventsOnDay($response, 5))->toBe(['Late Gig' => true]);
        expect(eventsOnDay($response, 6))->toBe(['Late Gig' => true]);
    });

    it('should only list events for anyone who isn\'t a member', function (string $group) {
        EventFactory::new()
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Gig']);
        EventFactory::new()
            ->social()
            ->at('2026-11-05 20:00')
            ->createOne(['name' => 'Social']);
        EventFactory::new()
            ->hidden()
            ->at('2026-11-05 10:00')
            ->createOne(['name' => 'Info']);
        loginAs($group);

        expect(eventsOnDay(diary()->assertOk(), 5))->toBe(['Gig' => true]);
    })->with(['guest', 'staff', 'super_admin']);

    it('should list every type of event for members, in time order', function () {
        EventFactory::new()
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Gig']);
        EventFactory::new()
            ->social()
            ->at('2026-11-05 20:00')
            ->createOne(['name' => 'Social']);
        EventFactory::new()
            ->hidden()
            ->at('2026-11-05 10:00')
            ->createOne(['name' => 'Info']);
        loginAs('member');

        expect(eventsOnDay(diary()->assertOk(), 5))->toBe(['Info' => true, 'Gig' => true, 'Social' => true]);
    });

    it('should hide the types of event that the member has turned off', function () {
        EventFactory::new()
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Gig']);
        EventFactory::new()
            ->social()
            ->at('2026-11-05 20:00')
            ->createOne(['name' => 'Social']);
        loginAs('member', ['diary_preferences' => ['event_types' => ['social'], 'crewing' => '*']]);

        expect(eventsOnDay(diary()->assertOk(), 5))->toBe(['Gig' => false, 'Social' => true]);
    });

    it('should only show the events the member is crewing, if they choose', function (string $crewing, array $shown) {
        $member = loginAs('member', [
            'diary_preferences' => ['event_types' => ['event'], 'crewing' => $crewing],
        ]);
        $crewed = EventFactory::new()
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Crewed']);
        CrewFactory::new()
            ->for($crewed)
            ->for($member ?? createUser())
            ->createOne();
        EventFactory::new()
            ->at('2026-11-05 20:00')
            ->createOne(['name' => 'Not Crewed']);

        expect(eventsOnDay(diary()->assertOk(), 5))->toBe($shown);
    })->with([
        ['*', ['Crewed' => true, 'Not Crewed' => true]],
        ['true', ['Crewed' => true, 'Not Crewed' => false]],
        ['false', ['Crewed' => false, 'Not Crewed' => true]],
    ]);

    it('should count the TEM as crewing', function () {
        $tem = loginAs('member', ['diary_preferences' => ['event_types' => ['event'], 'crewing' => 'true']]);
        EventFactory::new()
            ->managedBy($tem ?? createUser())
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Managed']);

        expect(eventsOnDay(diary()->assertOk(), 5))->toBe(['Managed' => true]);
    });

    it('should hide everything from a member without any preferences', function () {
        EventFactory::new()
            ->at('2026-11-05 19:00')
            ->createOne(['name' => 'Gig']);
        loginAs('member', ['diary_preferences' => []]);

        expect(eventsOnDay(diary()->assertOk(), 5))->toBe(['Gig' => false]);
    });

    it('should only give members the tools to customise and export the diary', function (string $group, bool $tools) {
        loginAs($group);

        $response = diary()->assertOk();
        $tools
            ? $response->assertSee('id="DiaryPreferences-edit"', false)
            : $response->assertDontSee('id="DiaryPreferences-edit"', false);
    })->with([['guest', false], ['staff', false], ['member', true], ['super_admin', false]]);

    it('should give the page the member\'s export token, hashed', function () {
        $member = memberWithToken();
        actingAs($member);

        diary()
            ->assertOk()
            ->assertSee(
                '<input name="member-event-token" type="hidden" value="' . hash('sha256', 'secret-token') . '">',
                false,
            )
            ->assertSee('<input name="member-id" type="hidden" value="' . $member->username . '">', false);
    });
});

describe('the iCal feed', function () {
    it('should be open to everyone, as a download', function () {
        export()
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="bts_diary.ics"');
    });

    it('should include every time slot of every event, labelled as UTC', function () {
        $event = EventFactory::new()
            ->at('2026-11-05 19:00', '2026-11-05 23:00')
            ->createOne(['name' => 'Gig', 'venue' => 'Plug']);
        TimeFactory::new()
            ->for($event)
            ->createOne(['name' => 'Get In', 'start' => '2026-11-05 09:00', 'end' => '2026-11-05 12:00']);

        $ical = (string) export()->assertOk()->getContent();

        expect($ical)
            ->toContain("X-WR-CALNAME:Backstage Diary\r\n")
            ->toContain("DTSTART:20261105T190000Z\r\n")
            ->toContain("DTEND:20261105T230000Z\r\n")
            ->toContain("SUMMARY:Gig - Gig\r\n")
            ->toContain("DTSTART:20261105T090000Z\r\n")
            ->toContain("SUMMARY:Gig - Get In\r\n")
            ->toContain("LOCATION:Plug\r\n");
        expect(substr_count($ical, 'BEGIN:VEVENT'))->toBe(2);
    });

    it('should label the times as UTC after shifting them to UK time', function () {
        EventFactory::new()->at('2026-07-01 18:00', '2026-07-01 22:00')->createOne();

        $ical = (string) withHeader('TZ-OFFSET', '-60')->get(route('event.export'))->assertOk()->getContent();

        expect($ical)->toContain("DTSTART:20260701T190000Z\r\n");
    })->group('known-bug', 'bug:timezones');

    it('should include events from the past', function () {
        EventFactory::new()
            ->at('2019-01-01 19:00')
            ->createOne(['name' => 'Old Gig']);

        expect((string) export()->getContent())->toContain('SUMMARY:Old Gig - Old Gig');
    });

    it('should only include events in the public feed', function () {
        EventFactory::new()->createOne(['name' => 'Gig']);
        EventFactory::new()
            ->social()
            ->createOne(['name' => 'Social']);

        $ical = (string) export()->getContent();

        expect($ical)->toContain('SUMMARY:Gig');
        expect($ical)->not->toContain('SUMMARY:Social');
    });

    it('should give each event a new UID every time', function () {
        EventFactory::new()->createOne();

        preg_match('/UID:(\S+)/', (string) export()->getContent(), $first);
        preg_match('/UID:(\S+)/', (string) export()->getContent(), $second);

        expect($first[1])->not->toBe($second[1]);
    });

    describe('personal feeds', function () {
        it('should include the chosen types for a member with a token', function () {
            $member = memberWithToken();
            EventFactory::new()->createOne(['name' => 'Gig']);
            EventFactory::new()
                ->social()
                ->createOne(['name' => 'Social']);
            EventFactory::new()
                ->meeting()
                ->createOne(['name' => 'Meeting']);

            $ical = (string) export([
                'types' => 'social,meeting',
                'user' => $member->username,
                'token' => hash('sha256', 'secret-token'),
            ])
                ->assertOk()
                ->getContent();

            expect($ical)->toContain('SUMMARY:Social')->toContain('SUMMARY:Meeting');
            expect($ical)->not->toContain('SUMMARY:Gig');
        });

        it('should only include the events the member is crewing or managing', function () {
            $member = memberWithToken();
            $crewed = EventFactory::new()->createOne(['name' => 'Crewed']);
            CrewFactory::new()->for($crewed)->for($member)->createOne();
            $managed = EventFactory::new()
                ->managedBy($member)
                ->createOne(['name' => 'Managed']);
            CrewFactory::new()->for($managed)->createOne();
            EventFactory::new()->createOne(['name' => 'Other']);

            $ical = (string) export([
                'crewing' => 'true',
                'user' => $member->username,
                'token' => hash('sha256', 'secret-token'),
            ])->getContent();

            expect($ical)->toContain('SUMMARY:Crewed')->toContain('SUMMARY:Managed');
            expect($ical)->not->toContain('SUMMARY:Other');
        });

        it('should leave out events the member manages that have no crew', function () {
            $member = memberWithToken();
            EventFactory::new()
                ->managedBy($member)
                ->createOne(['name' => 'Managed']);

            $ical = (string) export([
                'crewing' => 'true',
                'user' => $member->username,
                'token' => hash('sha256', 'secret-token'),
            ])->getContent();

            expect($ical)->not->toContain('SUMMARY:Managed');
        });

        it('should include every event when crewing is false', function () {
            $member = memberWithToken();
            $crewed = EventFactory::new()->createOne(['name' => 'Crewed']);
            CrewFactory::new()->for($crewed)->for($member)->createOne();
            EventFactory::new()->createOne(['name' => 'Other']);

            $ical = (string) export([
                'crewing' => 'false',
                'user' => $member->username,
                'token' => hash('sha256', 'secret-token'),
            ])->getContent();

            expect($ical)->toContain('SUMMARY:Crewed')->toContain('SUMMARY:Other');
        });

        it('should need a valid token', function (array $query) {
            $member = memberWithToken();

            export(
                ['types' => 'social'] +
                    array_map(fn($value) => str_replace('{user}', $member->username, $value), $query),
            )->assertStatus(419);
        })->with([
            'no user' => [['token' => hash('sha256', 'secret-token')]],
            'unknown user' => [['user' => 'nobody', 'token' => hash('sha256', 'secret-token')]],
            'no token' => [['user' => '{user}']],
            'wrong token' => [['user' => '{user}', 'token' => hash('sha256', 'wrong')]],
            'unhashed token' => [['user' => '{user}', 'token' => 'secret-token']],
        ]);

        it('should need the user to be a member', function (string $group) {
            $user = createUser($group, ['export_token' => 'secret-token']);

            export([
                'types' => 'social',
                'user' => $user->username,
                'token' => hash('sha256', 'secret-token'),
            ])->assertStatus(419);
        })->with(['staff', 'super_admin']);

        it('should still work for an archived member', function () {
            $user = createUser('archived', ['export_token' => 'secret-token']);

            export([
                'types' => 'social',
                'user' => $user->username,
                'token' => hash('sha256', 'secret-token'),
            ])->assertOk();
        });

        it('should need a token for the public types if crewing is chosen', function () {
            export(['crewing' => 'true'])->assertStatus(419);
        });
    });
});
