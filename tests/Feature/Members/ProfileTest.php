<?php

namespace Tests\Feature\Members;

use App\Models\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * Create a member who hasn't shared any of their personal details.
 */
function createPrivateMember(): User
{
    return createUser('member', [
        'email' => 'private-email@example.com',
        'phone' => '07700900123',
        'address' => '1 Private Road',
        'dob' => '2000-01-02',
        'show_email' => false,
        'show_phone' => false,
        'show_address' => false,
        'show_age' => false,
    ]);
}

it('should require users to log in to view a profile', function () {
    $member = createPrivateMember();

    get(route('member.view', ['username' => $member->username]))->assertRedirect(route('auth.login'));
});

it('should not show the edit forms or private details to other members', function (string $group) {
    $member = createPrivateMember();

    actingAs(createUser($group));
    get(route('member.view', ['username' => $member->username]))
        ->assertOk()
        ->assertDontSee('data-type="modal-template"', false)
        ->assertDontSee('private-email@example.com')
        ->assertDontSee('07700900123')
        ->assertDontSee('1 Private Road')
        ->assertDontSee('2000-01-02');
})->with(['member', 'committee']);

it('should show the edit forms on your own profile', function () {
    $member = createPrivateMember();

    actingAs($member);
    get(route('member.view', ['username' => $member->username]))
        ->assertOk()
        ->assertSee('data-id="contact"', false)
        ->assertSee('07700900123');
});
