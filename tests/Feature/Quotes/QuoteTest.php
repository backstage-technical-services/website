<?php

namespace Tests\Feature\Quotes\Quote;

use App\Models\Quote;
use Database\Factories\QuoteFactory;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;
use function Pest\Laravel\withHeader;

/*
 * The members' quotes board, at /quotes.
 */

beforeEach(function () {
    travelTo(Carbon::parse('2026-11-01 12:00:00'));
});

/**
 * @param array<string, string> $changes
 *
 * @return array<string, string>
 */
function quoteForm(array $changes = []): array
{
    return array_merge(
        ['culprit' => 'Dave', 'date' => '2026-10-31 21:30:00', 'quote' => 'Is it meant to smoke?'],
        $changes,
    );
}

describe('the quotes board', function () {
    it('should only be for members', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('quotes.view')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should list the quotes in the order they were added, newest first, numbered from 1', function () {
        QuoteFactory::new()->createOne(['quote' => 'First added', 'created_at' => now()->subDay(), 'date' => now()]);
        QuoteFactory::new()->createOne(['quote' => 'Second added', 'date' => now()->subYear()]);
        loginAs('member');

        get(route('quotes.view'))
            ->assertOk()
            ->assertSeeInOrder(['#1', 'Second added', '#2', 'First added'], false);
    })->group('known-bug', 'bug:quote-numbers');

    it('should show 15 quotes a page, numbered across the pages', function () {
        foreach (range(1, 16) as $i) {
            QuoteFactory::new()->createOne([
                'quote' => sprintf('Quote %02d', $i),
                'created_at' => now()->subMinutes(20 - $i),
            ]);
        }
        loginAs('member');

        get(route('quotes.view'))->assertOk()->assertSeeText('Quote 16')->assertDontSeeText('Quote 01');
        get(route('quotes.view', ['page' => 2]))
            ->assertOk()
            ->assertSeeInOrder(['#16', 'Quote 01'], false);
    });

    it('should show who said it and when', function () {
        QuoteFactory::new()->createOne(['culprit' => 'Dave', 'date' => now()->subDays(2)]);
        loginAs('member');

        get(route('quotes.view'))->assertOk()->assertSeeText('Said by Dave 2 days ago');
    });

    it('should not show who said it if the user who added it has gone', function () {
        QuoteFactory::new()->createOne(['culprit' => 'Dave', 'added_by' => null]);
        loginAs('member');

        get(route('quotes.view'))->assertOk()->assertDontSeeText('Said by Dave');
    });

    it('should render the quote as Markdown, splitting lines into paragraphs', function () {
        QuoteFactory::new()->createOne(['quote' => "**Bold** line\nNext line"]);
        loginAs('member');

        get(route('quotes.view'))
            ->assertOk()
            ->assertSee('<p><strong>Bold</strong> line</p><p>Next line</p></p><p>', false);
    })->group('known-bug', 'bug:quote-html');

    it('should not render unsafe links', function () {
        QuoteFactory::new()->createOne(['quote' => '[click](javascript:alert(1))']);
        loginAs('member');

        get(route('quotes.view'))->assertOk()->assertDontSee('href="javascript:', false);
    });

    it('should say when there are no quotes', function () {
        loginAs('member');

        get(route('quotes.view'))->assertOk()->assertSee("We don't seem to have any good quotes", false);
    });

    it('should only show the delete button to admins', function (string $group, bool $shown) {
        $quote = QuoteFactory::new()->createOne();
        loginAs($group);

        $response = get(route('quotes.view'))->assertOk();
        $button = route('quotes.delete', $quote->id);
        $shown ? $response->assertSee($button, false) : $response->assertDontSee($button, false);
    })->with([['member', false], ['committee', true]]);
});

describe('adding a quote', function () {
    it('should only let members add quotes', function (string $group, int $status) {
        loginAs($group);

        postAjax(route('quotes.store'), quoteForm())->assertStatus($status);

        assertDatabaseCount('quotes', $status === 200 ? 1 : 0);
    })->with([['guest', 401], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should add the quote', function () {
        $member = loginAs('member');

        postAjax(route('quotes.store'), quoteForm(['quote' => 'Is it <b>meant</b> to smoke & spark?']))
            ->assertOk()
            ->assertJson([
                'culprit' => 'Dave',
                'quote' => 'Is it meant to smoke &amp; spark?',
                'added_by' => $member?->id,
            ]);

        assertDatabaseHas('quotes', [
            'culprit' => 'Dave',
            'quote' => 'Is it meant to smoke &amp; spark?',
            'date' => '2026-10-31 21:30:00',
            'added_by' => $member?->id,
        ]);
        expectNotification('success', 'Quote created');
    });

    it('should shift the date by the browser\'s offset', function () {
        loginAs('member');

        withHeader('TZ-OFFSET', '-60')
            ->post(route('quotes.store'), quoteForm(), [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->assertOk();

        assertDatabaseHas('quotes', ['date' => '2026-10-31 20:30:00']);
    })->group('known-bug', 'bug:timezones');

    it('should validate the quote', function (string $field, string $value, string $message) {
        loginAs('member');

        postAjax(route('quotes.store'), quoteForm([$field => $value]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field => $message]);
    })->with([
        ['culprit', '', 'Please enter the culprit'],
        ['quote', '', 'Please enter what was said'],
        ['date', '', 'Please specify when it was said'],
        ['date', '31/10/2026 21:30', 'Please enter a valid date'],
        ['date', '2026-10-31 21:30', 'Please enter a valid date'],
        ['date', '2026-11-01 12:02:00', 'Try not to predict the future!'],
    ]);

    it('should accept a quote from up to a minute in the future', function () {
        loginAs('member');

        postAjax(route('quotes.store'), quoteForm(['date' => '2026-11-01 12:00:30']))->assertOk();
    });
});

describe('deleting a quote', function () {
    it('should only let admins delete quotes', function (string $group, int $status) {
        $quote = QuoteFactory::new()->createOne();
        loginAs($group);

        postAjax(route('quotes.delete', $quote->id))->assertStatus($status);

        $status === 200 ? assertModelMissing($quote) : assertModelExists($quote);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete the quote', function () {
        $quote = QuoteFactory::new()->createOne();
        loginAs('committee');

        postAjax(route('quotes.delete', $quote->id))
            ->assertOk()
            ->assertExactJson([true]);

        expect(Quote::count())->toBe(0);
        expectNotification('success', 'Quote deleted');
    });

    it('should give a 404 for an unknown quote', function () {
        loginAs('committee');

        postAjax(route('quotes.delete', 999))->assertNotFound();
    });
});
