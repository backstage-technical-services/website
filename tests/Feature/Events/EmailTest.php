<?php

namespace Tests\Feature\Events\Email;

use App\Mail\Events\CrewEmail;
use App\Models\Events\Event;
use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EventFactory;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

/**
 * @param array<string, string> $changes
 *
 * @return array<string, string>
 */
function emailForm(array $changes = []): array
{
    return array_merge(['header' => 'Get-in time', 'body' => 'Please arrive at **9am**', 'crew' => 'all'], $changes);
}

function emailUrl(Event $event): string
{
    return route('event.email.store', ['id' => $event->id]);
}

it('should only let admins and the event\'s managers email the crew', function (string $group, int $status) {
    Mail::fake();
    $event = EventFactory::new()->createOne();
    CrewFactory::new()->for($event)->createOne();
    loginAs($group);

    postAjax(emailUrl($event), emailForm())->assertStatus($status);

    assertDatabaseCount('event_emails', $status === 200 ? 1 : 0);
})->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

it('should let the TEM email the crew', function () {
    Mail::fake();
    $tem = createUser('member');
    $event = EventFactory::new()->managedBy($tem)->createOne();
    CrewFactory::new()->for($event)->createOne();
    actingAs($tem);

    postAjax(emailUrl($event), emailForm())->assertOk();
});

it('should not let the rest of the crew email the crew', function () {
    $event = EventFactory::new()->createOne();
    $member = createUser('member');
    CrewFactory::new()->for($event)->for($member)->createOne();
    actingAs($member);

    postAjax(emailUrl($event), emailForm())->assertForbidden();
});

it('should only accept AJAX requests', function () {
    $event = EventFactory::new()->createOne();
    loginAs('committee');

    post(emailUrl($event), emailForm())->assertNotFound();
});

it('should store the email and send one email to all of the crew, from the sender', function () {
    Mail::fake();
    $event = EventFactory::new()->createOne(['name' => 'Panto']);
    $core = createUser('member');
    CrewFactory::new()->for($event)->for($core)->role('Sound')->createOne();
    $general = createUser('member');
    CrewFactory::new()->for($event)->for($general)->createOne();
    $sender = loginAs('committee', ['forename' => 'Sue', 'surname' => 'Sender']);
    assert($sender !== null);

    postAjax(emailUrl($event), emailForm(['header' => 'Times & <b>dates</b>']))
        ->assertOk()
        ->assertExactJson(['response' => 'Email sent']);

    assertDatabaseHas('event_emails', [
        'event_id' => $event->id,
        'sender_id' => $sender->id,
        'header' => 'Times &amp; dates',
        'body' => 'Please arrive at **9am**',
    ]);
    Mail::assertSentCount(1);
    Mail::assertNothingQueued();
    Mail::assertSent(CrewEmail::class, function (CrewEmail $mail) use ($core, $general, $sender) {
        $mail->assertTo($core->email);
        $mail->assertTo($general->email);
        $mail->assertFrom($sender->email, 'Sue Sender');
        $mail->assertHasCc($sender->email, 'Sue Sender');
        $mail->assertHasSubject('[Backstage Website] [Panto] Times &amp; dates');
        $mail->assertSeeInHtml('Please arrive at <strong', false);

        return true;
    });
    expectNotification('success', 'Email sent');
});

it('should only send to the core crew', function () {
    Mail::fake();
    $event = EventFactory::new()->createOne();
    $core = createUser('member');
    CrewFactory::new()->for($event)->for($core)->role('Sound')->createOne();
    $general = createUser('member');
    CrewFactory::new()->for($event)->for($general)->createOne();
    loginAs('committee');

    postAjax(emailUrl($event), emailForm(['crew' => 'core']))->assertOk();

    Mail::assertSent(CrewEmail::class, function (CrewEmail $mail) use ($core, $general) {
        return $mail->hasTo($core->email) && !$mail->hasTo($general->email);
    });
});

it('should not include the TEM unless they are on the crew', function () {
    Mail::fake();
    $tem = createUser('member');
    $event = EventFactory::new()->managedBy($tem)->createOne();
    CrewFactory::new()->for($event)->createOne();
    loginAs('committee');

    postAjax(emailUrl($event), emailForm())->assertOk();

    Mail::assertSent(CrewEmail::class, fn(CrewEmail $mail) => !$mail->hasTo($tem->email));
});

it('should only list a member with several roles once', function () {
    Mail::fake();
    $event = EventFactory::new()->createOne();
    $member = createUser('member');
    CrewFactory::new()->for($event)->for($member)->role('Sound')->createOne();
    CrewFactory::new()->for($event)->for($member)->role('Lighting')->createOne();
    loginAs('committee');

    postAjax(emailUrl($event), emailForm())->assertOk();

    Mail::assertSentCount(1);
    Mail::assertSent(
        CrewEmail::class,
        fn(CrewEmail $mail) => $mail->to === [['name' => $member->name, 'address' => $member->email]],
    );
});

it('should fail when there is no crew, after storing the email', function () {
    Mail::fake();
    $event = EventFactory::new()->createOne();
    loginAs('committee');

    postAjax(emailUrl($event), emailForm())
        ->assertUnprocessable()
        ->assertJson(['error' => 'No crew to send email to', '__error' => true]);

    assertDatabaseCount('event_emails', 1);
    Mail::assertNothingSent();
});

it('should fail when emailing all of a social\'s crew if it has guests, after storing the email', function () {
    Mail::fake();
    $event = EventFactory::new()->social()->createOne();
    CrewFactory::new()->for($event)->createOne();
    CrewFactory::new()->for($event)->guest()->createOne();
    loginAs('committee');

    postAjax(emailUrl($event), emailForm())->assertServerError();

    assertDatabaseCount('event_emails', 1);
    Mail::assertNothingSent();
})->group('known-bug', 'bug:email-crew-with-guests');

it('should email the members of a social when only emailing the core crew', function () {
    Mail::fake();
    $event = EventFactory::new()->social()->createOne();
    CrewFactory::new()->for($event)->role('Organiser')->createOne();
    CrewFactory::new()->for($event)->guest()->createOne();
    loginAs('committee');

    postAjax(emailUrl($event), emailForm(['crew' => 'core']))->assertOk();

    Mail::assertSentCount(1);
});

it('should validate the email', function (string $field, string $value, string $message) {
    $event = EventFactory::new()->createOne();
    CrewFactory::new()->for($event)->createOne();
    loginAs('committee');

    postAjax(emailUrl($event), emailForm([$field => $value]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$field => $message]);

    assertDatabaseCount('event_emails', 0);
})->with([
    ['header', '', 'Please enter the email subject'],
    ['body', '', 'Please enter the email message'],
    ['crew', '', 'Please select who to send the email to'],
    ['crew', 'everyone', 'Please select who to send the email to'],
]);
