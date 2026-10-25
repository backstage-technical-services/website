<?php

namespace Tests\Feature\Contact\SafetyReports;

use App\Mail\Contact\AccidentReport;
use App\Mail\Contact\AccidentReportReceipt;
use App\Mail\Contact\NearMissReport;
use App\Models\Log;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The accident and near-miss reports need a login. They only send emails: nothing is stored.
 */

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function accidentForm(array $changes = []): array
{
    return array_merge(
        [
            'location' => 'Founders Hall',
            'date' => '2026-10-20',
            'time' => '21:15',
            'severity' => '0',
            'absence_details' => '',
            'details' => 'Tripped over a cable',
            'injured_name' => 'Ian Injured',
            'contact_name' => 'Cath Contact',
            'contact_email' => 'cath@example.com',
            'contact_phone' => '07700900123',
            'person_type' => '11',
            'person_type_other' => '',
        ],
        $changes,
    );
}

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function nearMissForm(array $changes = []): array
{
    return array_merge(
        [
            'location' => 'Store',
            'date' => '2026-10-20',
            'time' => '14:00',
            'details' => 'A box fell off a shelf',
            'safety_recommendations' => 'Strap the boxes down',
            'user_name' => '',
            'user_email' => '',
        ],
        $changes,
    );
}

describe('the accident form', function () {
    it('should need a login', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('contact.accident')), $access);
    })->with([['guest', 'login'], ['staff', 200], ['member', 200], ['super_admin', 200]]);

    it('should offer severities with a gap between 3 and 4 days', function () {
        loginAs('member');

        get(route('contact.accident'))
            ->assertOk()
            ->assertSeeInOrder(['Absent for less than 3 days', 'Absent for more than 4 days']);
    })->group('known-bug', 'bug:accident-severities');

    it('should email the safety team and send the contact a receipt', function () {
        Mail::fake();
        loginAs('member');
        $logs = Log::count();

        post(route('contact.accident.do'), accidentForm())->assertRedirect(route('home'));

        Mail::assertQueued(AccidentReport::class, function (AccidentReport $mail) {
            foreach (
                [
                    'committee@bts-crew.com',
                    'safety@bts-crew.com',
                    'P.Hawker@bath.ac.uk',
                    'su-healthandsafety@bath.ac.uk',
                ]
                as $address
            ) {
                $mail->assertTo($address);
            }
            $mail->assertHasReplyTo('cath@example.com', 'Cath Contact');
            $mail->assertHasSubject('[Backstage Website] ** BTS Accident Report **');
            $mail->assertSeeInText('Tripped over a cable');
            $mail->assertSeeInText('Undergraduate');
            $mail->assertSeeInText('No absence');

            return true;
        });
        Mail::assertQueued(AccidentReportReceipt::class, function (AccidentReportReceipt $mail) {
            $mail->assertTo('cath@example.com');
            $mail->assertHasReplyTo('safety@bts-crew.com');
            $mail->assertHasSubject('[Backstage Website] BTS Accident Report Receipt');

            return true;
        });
        expectNotification('success', 'Thank you for reporting the accident');
        assertDatabaseCount('logs', $logs);
    });

    it('should use the other employment category that was entered', function () {
        Mail::fake();
        loginAs('member');

        post(
            route('contact.accident.do'),
            accidentForm(['person_type' => 'other', 'person_type_other' => 'Visitor']),
        )->assertRedirect();

        Mail::assertQueued(AccidentReport::class, function (AccidentReport $mail) {
            $mail->assertSeeInText('Visitor');

            return true;
        });
    });

    it('should validate the report', function (string $field, string $value, string $message) {
        Mail::fake();
        loginAs('member');

        post(route('contact.accident.do'), accidentForm([$field => $value]))->assertSessionHasErrors([
            $field => $message,
        ]);

        Mail::assertNothingQueued();
    })->with([
        ['location', '', 'Please provide the accident location'],
        ['date', '20/10/2026', "Please provide the date in the form 'dd/mm/YYYY'"],
        ['time', '9pm', "Please provide the time in the form 'hh:mm'"],
        ['severity', '5', 'Please select one of the provided severities'],
        ['details', '', 'Please provide the accident details'],
        ['injured_name', '', 'Please provide the name of the injured party'],
        ['contact_name', '', 'Please provide the name of the contact person'],
        ['contact_email', 'cath', 'Please provide a valid email address'],
        ['contact_phone', '', 'Please provide a phone number for the contact'],
        ['person_type', '12', 'Please select one of the provided employment categories'],
    ]);

    it('should need details of the absence for absences', function (string $severity) {
        loginAs('member');

        post(route('contact.accident.do'), accidentForm(['severity' => $severity]))->assertSessionHasErrors([
            'absence_details' => 'Please enter any details of their absence from employment or studies',
        ]);
    })->with(['1', '2', '3']);

    it('should need the other employment category', function () {
        loginAs('member');

        post(route('contact.accident.do'), accidentForm(['person_type' => 'other']))->assertSessionHasErrors([
            'person_type_other' => 'Please enter an alternative employment category',
        ]);
    });

    it('should send a duplicate report each time it is submitted', function () {
        Mail::fake();
        loginAs('member');

        post(route('contact.accident.do'), accidentForm())->assertRedirect();
        post(route('contact.accident.do'), accidentForm())->assertRedirect();

        Mail::assertQueued(AccidentReport::class, 2);
    })->group('known-bug', 'bug:double-submit');
});

describe('the near-miss form', function () {
    it('should need a login', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('contact.near-miss')), $access);
    })->with([['guest', 'login'], ['staff', 200], ['member', 200], ['super_admin', 200]]);

    it('should fill in the user\'s name and email', function () {
        $user = loginAs('member', ['forename' => 'Nat', 'surname' => 'Nearmiss']);

        get(route('contact.near-miss'))
            ->assertOk()
            ->assertSee('value="Nat Nearmiss"', false)
            ->assertSee('value="' . $user?->email . '"', false);
    });

    it('should email the safety team, without a receipt', function () {
        Mail::fake();
        loginAs('member');

        post(
            route('contact.near-miss.send'),
            nearMissForm(['user_name' => 'Nat Nearmiss', 'user_email' => 'nat@example.com']),
        )->assertRedirect(route('home'));

        Mail::assertQueuedCount(1);
        Mail::assertQueued(NearMissReport::class, function (NearMissReport $mail) {
            foreach (['committee@bts-crew.com', 'safety@bts-crew.com', 'su-healthandsafety@bath.ac.uk'] as $address) {
                $mail->assertTo($address);
            }
            $mail->assertHasSubject('[Backstage Website] Near miss reported');
            $mail->assertSeeInText('A box fell off a shelf');
            $mail->assertSeeInText('Strap the boxes down');
            $mail->assertSeeInText('Tue 20th Oct 2026');
            $mail->assertSeeInText('2:00pm');
            $mail->assertSeeInText('Nat Nearmiss');

            return true;
        });
        expectNotification('success', 'Thank you for reporting the near miss');
    });

    it('should let the report be anonymous', function () {
        Mail::fake();
        loginAs('member');

        post(route('contact.near-miss.send'), nearMissForm())->assertRedirect(route('home'));

        Mail::assertQueued(NearMissReport::class);
    });

    it('should validate the report', function (string $field, string $value, string $message) {
        loginAs('member');

        post(route('contact.near-miss.send'), nearMissForm([$field => $value]))->assertSessionHasErrors([
            $field => $message,
        ]);
    })->with([
        ['location', '', 'Please provide the location'],
        ['date', 'yesterday', 'Please provide a valid date'],
        ['time', '2pm', 'Please provide a valid time'],
        ['details', '', 'Please provide details of the Near Miss'],
        ['user_email', 'nat', 'Please provide a valid email'],
    ]);

    it('should need a name with an email', function () {
        loginAs('member');

        post(
            route('contact.near-miss.send'),
            nearMissForm(['user_email' => 'nat@example.com']),
        )->assertSessionHasErrors([
            'user_name' => 'If you are providing your email address, please also provide your name',
        ]);
    });
});
