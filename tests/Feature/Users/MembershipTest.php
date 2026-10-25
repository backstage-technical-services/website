<?php

namespace Tests\Feature\Users\Membership;

use function Pest\Laravel\get;

/*
 * The membership directory, at /membership.
 */

it('should be open to anyone who is logged in', function (string $group, string|int $access) {
    loginAs($group);

    expectAccess(get(route('membership.view')), $access);
})->with([['guest', 'login'], ['staff', 200], ['member', 200], ['committee', 200], ['super_admin', 200]]);

it('should list the active members, by surname', function () {
    createUser('member', ['forename' => 'Zoe', 'surname' => 'Adams']);
    createUser('associate', ['forename' => 'Amy', 'surname' => 'Brown']);
    createUser('committee', ['forename' => 'Bob', 'surname' => 'Clark']);
    createUser('archived', ['forename' => 'Gone', 'surname' => 'Away']);
    createUser('staff', ['forename' => 'Su', 'surname' => 'Staff']);
    createUser('super_admin', ['forename' => 'Webby', 'surname' => 'Master']);
    loginAs('staff', ['forename' => 'Viewing', 'surname' => 'Staff']);

    get(route('membership.view'))
        ->assertOk()
        ->assertSeeTextInOrder(['Zoe Adams', 'Amy Brown', 'Bob Clark'])
        ->assertDontSeeText(['Gone Away', 'Su Staff', 'Webby Master']);
});

it('should only show the contact details each member has shared', function () {
    createUser('member', [
        'email' => 'shared@example.com',
        'phone' => '07700900111',
        'show_email' => true,
        'show_phone' => false,
    ]);
    createUser('member', [
        'email' => 'hidden@example.com',
        'phone' => '07700900222',
        'show_email' => false,
        'show_phone' => true,
    ]);
    loginAs('member');

    get(route('membership.view'))
        ->assertOk()
        ->assertSee('shared@example.com')
        ->assertDontSee('07700900111')
        ->assertDontSee('hidden@example.com')
        ->assertSee('07700900222');
});

it('should show nicknames and tool colours', function () {
    createUser('member', ['forename' => 'Pat', 'nickname' => 'Patty', 'tool_colours' => 'Red']);
    loginAs('member');

    get(route('membership.view'))
        ->assertOk()
        ->assertSee('<span class="nickname">(Patty)</span>', false)
        ->assertSee('<span class="tool-colours" title="Red">', false);
});

it('should search by name, nickname, username and email', function (string $search) {
    createUser('member', [
        'forename' => 'Sam',
        'surname' => 'Searchable',
        'nickname' => 'Sammy',
        'username' => 'ss123',
        'email' => 'sam@example.com',
    ]);
    createUser('member', ['forename' => 'Other', 'surname' => 'Person']);
    loginAs('member');

    get(route('membership.view', ['search' => $search]))
        ->assertOk()
        ->assertSeeText('Sam Searchable')
        ->assertDontSeeText('Other Person');
})->with(['Searchable', 'sammy', 'ss123', 'sam@example', 'Sam Search']);

it('should only show the admin buttons to admins', function (string $group, bool $shown) {
    loginAs($group);

    $response = get(route('membership.view'))->assertOk();
    $shown
        ? $response->assertSeeText(['Add more users', 'View all users'])
        : $response->assertDontSeeText(['Add more users', 'View all users']);
})->with([['member', false], ['committee', true], ['super_admin', true]]);
