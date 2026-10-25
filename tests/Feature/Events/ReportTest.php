<?php

namespace Tests\Feature\Events\Report;

use Database\Factories\Events\CrewFactory;
use Database\Factories\Events\EventFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/*
 * The event report is a Google Form, embedded in an iframe and pre-filled with the event's details.
 */

beforeEach(function () {
    config(['bts.links.event_report' => 'https://docs.google.com/forms/report']);
});

it('should let members open a blank report', function (string $group, string|int $access) {
    loginAs($group);

    $response = expectAccess(get(route('event.report')), $access);

    if ($access === 200) {
        $response->assertSee('src="https://docs.google.com/forms/report?"', false);
    }
})->with([['guest', 'login'], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

it('should only let the event\'s managers open a pre-filled report', function (string $group, string|int $access) {
    $event = EventFactory::new()->createOne();
    loginAs($group);

    expectAccess(get(route('event.report', ['id' => $event->id])), $access);
})->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

it('should pre-fill the report with the event\'s details', function () {
    $tem = createUser('member', ['forename' => 'Tina', 'surname' => 'Tem']);
    $event = EventFactory::new()
        ->managedBy($tem)
        ->createOne(['name' => 'Panto', 'venue' => 'Arts Theatre']);
    CrewFactory::new()
        ->for($event)
        ->for(createUser('member', ['forename' => 'Larry', 'surname' => 'Lights']))
        ->role('Lighting')
        ->createOne();
    CrewFactory::new()->for($event)->createOne();
    actingAs($tem);

    $query = http_build_query([
        'entry.806898027' => 'Panto',
        'entry.194415760' => 'Tina Tem',
        'entry.1260407120' => $tem->email,
        'entry.3626157' => 'Arts Theatre',
        'entry.2079484309' => 2,
        'entry.1054743347' => 'Lighting: Larry Lights',
    ]);

    get(route('event.report', ['id' => $event->id]))
        ->assertOk()
        ->assertSeeText('Panto')
        ->assertSee('src="' . e("https://docs.google.com/forms/report?{$query}") . '"', false)
        ->assertSee(route('event.view', ['id' => $event->id]), false);
});

it('should give a 404 for anything but an event', function () {
    $event = EventFactory::new()->social()->createOne();
    loginAs('committee');

    get(route('event.report', ['id' => $event->id]))->assertNotFound();
});

it('should give a 404 for an unknown event', function () {
    loginAs('committee');

    get(route('event.report', ['id' => 999]))->assertNotFound();
});
