<?php

namespace Tests\Feature\Events\Finance;

use App\Mail\Events\FinanceEmail;
use App\Models\Events\Event;
use Database\Factories\Events\EventFactory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\get;

/*
 * The finance database calls back to /events/{id}/finance-email to email the TEM a reminder: a finance summary (FS) or
 * a budget tracker (FSB).
 */

beforeEach(function () {
    config(['bts.finance_db.key' => 'the-app-key', 'bts.finance_db.em_finance_url' => 'https://finance.example/em/']);
});

/**
 * @param array<string, string> $query
 *
 * @return TestResponse<Response>
 */
function financeCallback(Event $event, array $query = []): TestResponse
{
    return get(
        "/events/{$event->id}/finance-email?" .
            http_build_query(
                $query + ['appKey' => 'the-app-key', 'message' => 'FS', 'subject' => 'Finance summary', 'fsid' => '42'],
            ),
    );
}

it('should need someone to be logged in, so the finance database can\'t call it', function () {
    Mail::fake();
    $event = EventFactory::new()->managedBy(createUser())->createOne();

    financeCallback($event)->assertRedirect(route('auth.login'));

    Mail::assertNothingQueued();
})->group('known-bug', 'bug:finance-callback-needs-login');

it('should work for anyone who is logged in, with the key', function (string $group) {
    Mail::fake();
    $event = EventFactory::new()->managedBy(createUser())->createOne();
    loginAs($group);

    financeCallback($event)
        ->assertOk()
        ->assertExactJson(['code' => 200, 'result' => 'success']);
})->with(['staff', 'member', 'committee', 'super_admin']);

it('should need the app key', function (string $key) {
    Mail::fake();
    $event = EventFactory::new()->managedBy(createUser())->createOne();
    loginAs('member');

    financeCallback($event, ['appKey' => $key])->assertForbidden();

    Mail::assertNothingQueued();
})->with(['wrong-key', '']);

it('should email the TEM a finance summary reminder', function () {
    Mail::fake();
    $tem = createUser('member', ['forename' => 'Tina', 'surname' => 'Tem']);
    $event = EventFactory::new()
        ->managedBy($tem)
        ->createOne(['name' => 'Panto']);
    loginAs('member');

    financeCallback($event)->assertOk();

    Mail::assertQueued(FinanceEmail::class, function (FinanceEmail $mail) use ($tem) {
        $mail->assertTo($tem->email);
        $mail->assertHasReplyTo('treas@bts-crew.com');
        $mail->assertHasSubject('Finance summary (Panto)');
        $mail->assertSeeInText('Hi Tina,');
        $mail->assertSeeInText('Please fill in the finance summary');

        return true;
    });
});

it('should email the TEM a budget tracker reminder', function () {
    Mail::fake();
    $event = EventFactory::new()
        ->managedBy(createUser())
        ->createOne(['name' => 'Panto']);
    loginAs('member');

    financeCallback($event, ['message' => 'FSB', 'subject' => 'Budget tracker'])->assertOk();

    Mail::assertQueued(FinanceEmail::class, function (FinanceEmail $mail) {
        $mail->assertHasSubject('Budget tracker (Panto)');
        $mail->assertSeeInText('online budget tracker');

        return true;
    });
});

it('should link the "EM Finance" button to just the finance DB\'s ID, as it reads the wrong config key', function () {
    Mail::fake();
    $event = EventFactory::new()->managedBy(createUser())->createOne();
    loginAs('member');

    financeCallback($event, ['fsid' => '42'])->assertOk();

    Mail::assertQueued(FinanceEmail::class, function (FinanceEmail $mail) {
        $mail->assertSeeInHtml('href="42"', false);
        $mail->assertDontSeeInHtml('https://finance.example/em/', false);

        return true;
    });
})->group('known-bug', 'bug:finance-email-link');

it('should fail for an event without a TEM', function () {
    Mail::fake();
    $event = EventFactory::new()->createOne();
    loginAs('member');

    financeCallback($event)
        ->assertServerError()
        ->assertExactJson(['code' => '500', 'result' => 'Event not found or no EM assigned']);

    Mail::assertNothingQueued();
});

it('should fail for an unknown event', function () {
    loginAs('member');

    get('/events/999/finance-email?appKey=the-app-key')
        ->assertServerError()
        ->assertExactJson(['code' => '500', 'result' => 'Event not found or no EM assigned']);
});
