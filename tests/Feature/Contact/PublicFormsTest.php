<?php

namespace Tests\Feature\Contact\PublicForms;

use App\Mail\Contact\Booking;
use App\Mail\Contact\BookingReceipt;
use App\Mail\Contact\Enquiry;
use App\Mail\Contact\EnquiryReceipt;
use App\Mail\Contact\Feedback;
use Illuminate\Support\Facades\Mail;
use Tests\Support\Fakes\FakeCaptcha;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The enquiry, booking and feedback forms are open to everyone and protected by reCAPTCHA. They only send emails:
 * nothing is stored.
 */

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function enquiryForm(array $changes = []): array
{
    return array_merge(
        [
            'name' => 'Erin Enquirer',
            'email' => 'erin@example.com',
            'phone' => '07700900123',
            'message' => 'Can you light our show?',
            'g-recaptcha-response' => FakeCaptcha::PASS,
        ],
        $changes,
    );
}

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function bookingForm(array $changes = []): array
{
    return array_merge(
        [
            'event_name' => 'Summer Ball',
            'event_venue' => 'Founders Hall',
            'event_dates' => '1st June 2027',
            'event_access' => ['morning', 'evening'],
            'event_club' => 'Ballroom Society',
            'event_description' => 'A ball',
            'contact_name' => 'Bea Booker',
            'contact_email' => 'bea@example.com',
            'contact_phone' => '07700900123',
            'terms' => '1',
            'g-recaptcha-response' => FakeCaptcha::PASS,
        ],
        $changes,
    );
}

describe('the forms', function () {
    it('should be open to everyone', function (string $route, string $group) {
        loginAs($group);

        get(route($route))->assertOk();
    })
        ->with(['contact.enquiries', 'contact.book', 'contact.book.alt', 'contact.feedback'])
        ->with(['guest', 'staff', 'member', 'super_admin']);

    it('should show the booking terms in a modal for AJAX requests', function () {
        getAjax(route('contact.book.terms'))
            ->assertOk()
            ->assertDontSee('<html', false)
            ->assertSeeText('Terms and Conditions for the Provision of Services');
    });
});

describe('the enquiry form', function () {
    it('should email the committee and send the enquirer a receipt', function () {
        Mail::fake();

        post(route('contact.enquiries.do'), enquiryForm())->assertRedirect(route('home'));

        Mail::assertQueued(Enquiry::class, function (Enquiry $mail) {
            $mail->assertTo('committee@bts-crew.com');
            $mail->assertHasReplyTo('erin@example.com', 'Erin Enquirer');
            $mail->assertHasSubject('[Backstage Website] General enquiry');
            $mail->assertSeeInText('Can you light our show?');

            return true;
        });
        Mail::assertQueued(EnquiryReceipt::class, function (EnquiryReceipt $mail) {
            $mail->assertTo('erin@example.com');
            $mail->assertHasReplyTo('committee@bts-crew.com');
            $mail->assertHasSubject('[Backstage Website] Your enquiry to BTS');

            return true;
        });
        expectNotification('success', 'Enquiry sent. You should receive a receipt soon.');
        assertDatabaseCount('logs', 0);
    });

    it('should send the receipt to whatever address is entered', function () {
        Mail::fake();

        post(route('contact.enquiries.do'), enquiryForm(['email' => 'someone-else@example.com']))->assertRedirect();

        Mail::assertQueued(EnquiryReceipt::class, fn(EnquiryReceipt $mail) => $mail->hasTo('someone-else@example.com'));
    });

    it('should validate the enquiry', function (string $field, string $value, string $message) {
        Mail::fake();

        from(route('contact.enquiries'))
            ->post(route('contact.enquiries.do'), enquiryForm([$field => $value]))
            ->assertRedirect(route('contact.enquiries'))
            ->assertSessionHasErrors([$field => $message]);

        Mail::assertNothingQueued();
    })->with([
        ['name', '', 'Please enter your name'],
        ['name', 'Erin', 'Please enter both your forename and surname, using letters only'],
        ['email', 'not-an-email', 'Please enter a valid email address'],
        ['phone', '123', 'Please enter a valid full phone number'],
        ['message', '', 'Please enter your enquiry'],
        ['g-recaptcha-response', '', 'Please fill in the CAPTCHA'],
    ]);

    it('should reject names that aren\'t two words of plain letters', function (string $name) {
        Mail::fake();

        post(route('contact.enquiries.do'), enquiryForm(['name' => $name]))->assertSessionHasErrors([
            'name' => 'Please enter both your forename and surname, using letters only',
        ]);

        Mail::assertNothingQueued();
    })
        ->with(['Cher', 'José García'])
        ->group('known-bug', 'bug:name-check');

    it('should reject phone numbers from outside the UK', function () {
        Mail::fake();

        post(route('contact.enquiries.do'), enquiryForm(['phone' => '+1 555 123 4567']))->assertSessionHasErrors([
            'phone' => 'Please enter a valid full phone number',
        ]);

        Mail::assertNothingQueued();
    })->group('known-bug', 'bug:uk-only-phones');

    it('should show the untranslated message when the captcha fails', function () {
        Mail::fake();

        post(route('contact.enquiries.do'), enquiryForm(['g-recaptcha-response' => 'wrong']))->assertSessionHasErrors([
            'g-recaptcha-response' => 'validation.captcha',
        ]);

        Mail::assertNothingQueued();
    })->group('known-bug', 'bug:captcha-message');

    it('should accept any name with two plain letters either side of a space, as the check isn\'t anchored', function (
        string $name,
    ) {
        Mail::fake();

        post(route('contact.enquiries.do'), enquiryForm(['name' => $name]))->assertSessionHasNoErrors();
    })
        ->with(['Ana María López', 'Siân Jones', 'Mary-Jane Watson'])
        ->group('known-bug', 'bug:name-check');

    it('should check the captcha with Google', function () {
        Mail::fake();

        post(route('contact.enquiries.do'), enquiryForm())->assertRedirect();

        expect(fakeCaptcha()->verifications)->toHaveCount(1);
        expect(fakeCaptcha()->verifications[0])->toMatchArray([
            'secret' => 'fake-secret',
            'response' => FakeCaptcha::PASS,
            'remoteip' => '127.0.0.1',
        ]);
    });
});

describe('the booking form', function () {
    it('should email the committee and send the contact a receipt', function (string $route) {
        Mail::fake();

        post(route($route), bookingForm())->assertRedirect(route('home'));

        Mail::assertQueued(Booking::class, function (Booking $mail) {
            $mail->assertTo('committee@bts-crew.com');
            $mail->assertHasReplyTo('bea@example.com', 'Bea Booker');
            $mail->assertHasSubject('[Backstage Website] Booking Request - Summer Ball (1st June 2027)');
            $mail->assertSeeInText('Founders Hall');

            return true;
        });
        Mail::assertQueued(BookingReceipt::class, function (BookingReceipt $mail) {
            $mail->assertTo('bea@example.com');
            $mail->assertHasReplyTo('pm@bts-crew.com');
            $mail->assertHasSubject('[Backstage Website] Booking Request Receipt - Summer Ball (1st June 2027)');
            $mail->assertSeeInText('Bea');

            return true;
        });
        expectNotification('success', 'Thank you for your booking. You should receive a receipt soon.');
        assertDatabaseCount('events', 0);
    })->with(['contact.book.do', 'contact.book.alt.do']);

    it('should validate the booking', function (string $field, mixed $value, string $message) {
        Mail::fake();

        post(route('contact.book.do'), bookingForm([$field => $value]))->assertSessionHasErrors([$field => $message]);

        Mail::assertNothingQueued();
    })->with([
        ['event_name', '', 'Please enter the event name'],
        ['event_venue', '', 'Please enter the event venue'],
        ['event_dates', '', 'Please provide the event date(s)'],
        ['event_access', '', 'Please specify when we can access the venue'],
        [
            'event_access',
            'morning',
            'Please select a combination of morning, afternoon and evening for the venue access time',
        ],
        ['event_club', '', 'Please enter the name of the club/organisation'],
        ['contact_name', 'Bea', 'Please enter both their forename and surname, using letters only'],
        ['contact_name', 'Zoë Booker', 'Please enter both their forename and surname, using letters only'],
        ['contact_email', 'bea', 'Please enter a valid email address'],
        ['contact_phone', '123', 'Please enter a valid phone number'],
        ['contact_phone', '+33 1 23 45 67 89', 'Please enter a valid phone number'],
        ['terms', '', 'The terms must be accepted.'],
        ['g-recaptcha-response', '', 'Please fill in the CAPTCHA'],
    ]);
});

describe('the feedback form', function () {
    it('should email the committee anonymously', function () {
        Mail::fake();

        post(route('contact.feedback.do'), [
            'event' => 'Panto',
            'feedback' => 'Great lighting',
            'g-recaptcha-response' => FakeCaptcha::PASS,
        ])->assertRedirect(route('home'));

        Mail::assertQueuedCount(1);
        Mail::assertQueued(Feedback::class, function (Feedback $mail) {
            $mail->assertTo('committee@bts-crew.com');
            $mail->assertHasSubject('[Backstage Website] Feedback received');
            $mail->assertSeeInText('Great lighting');

            return true;
        });
        expectNotification('success', 'Thank you for providing feedback');
    });

    it('should validate the feedback', function (string $field, string $message) {
        post(route('contact.feedback.do'), [
            'event' => 'Panto',
            'feedback' => 'Good',
            'g-recaptcha-response' => FakeCaptcha::PASS,
            $field => '',
        ])->assertSessionHasErrors([$field => $message]);
    })->with([
        ['event', 'Please enter the event name'],
        ['feedback', 'Please enter your feedback'],
        ['g-recaptcha-response', 'Please fill in the CAPTCHA'],
    ]);
});
