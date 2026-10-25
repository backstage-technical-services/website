<?php

namespace Tests\Feature\Users\Auth;

use App\Models\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as KeycloakUser;
use Mockery;
use SocialiteProviders\Keycloak\Provider;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\withSession;

/*
 * Logging in goes through Keycloak with Socialite. These tests fake Socialite's Keycloak provider, so that the
 * callback gets the given user from "Keycloak".
 */

beforeEach(function () {
    config([
        'services.keycloak.base_url' => 'https://auth.example.com',
        'services.keycloak.realms' => 'bts',
        'services.keycloak.client_id' => 'website-v4',
        'services.keycloak.client_secret' => 'secret',
    ]);
});

/**
 * Make the Keycloak callback return the given user.
 */
function keycloakReturns(
    string $id = 'keycloak-id',
    ?string $email = 'abc123@bath.ac.uk',
    ?string $username = 'abc123',
    bool $emailVerified = true,
): void {
    $user = new KeycloakUser()
        ->setRaw(['sub' => $id, 'email_verified' => $emailVerified])
        ->map(['id' => $id, 'email' => $email, 'nickname' => $username]);

    Socialite::shouldReceive('driver')
        ->with('keycloak')
        ->andReturn(Mockery::mock(Provider::class, ['user' => $user]));
}

function sessionKey(): string
{
    return auth()->guard()->getName();
}

describe('starting to log in', function () {
    it('should send guests to Keycloak', function () {
        $response = get(route('auth.login'));

        $location = (string) $response->headers->get('Location');
        expect($response->status())->toBe(302);
        expect($location)->toStartWith('https://auth.example.com/realms/bts/protocol/openid-connect/auth?');
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        expect($query)->toMatchArray([
            'client_id' => 'website-v4',
            'redirect_uri' => url('/auth/callback'),
            'scope' => 'openid',
            'response_type' => 'code',
        ]);
        expect($query)->toHaveKey('state');
        expect($query)->not->toHaveKey('code_challenge');
    });

    it('should send users who are already logged in to the members\' area', function () {
        loginAs('member');

        get(route('auth.login'))->assertRedirect('/members');
    });
});

describe('the login callback', function () {
    it('should log in the user with that Keycloak ID', function () {
        $user = createUser('member', ['keycloak_user_id' => 'keycloak-id', 'username' => 'abc123']);
        keycloakReturns();

        get(route('auth.callback'))->assertRedirect('/members');

        assertAuthenticatedAs($user);
        expectNotification('success', 'Logged in');
        expect(Log::where('action', 'auth.login')->where('user_id', $user->id)->exists())->toBeTrue();
    });

    it('should log in the user with that email on their first login, and link their Keycloak ID', function () {
        $user = createUser('member', ['email' => 'abc123@bath.ac.uk']);
        keycloakReturns();

        get(route('auth.callback'))->assertRedirect('/members');

        assertAuthenticatedAs($user);
        assertDatabaseHas('users', ['id' => $user->id, 'keycloak_user_id' => 'keycloak-id']);
    });

    it('should match the email whatever its case', function () {
        $user = createUser('member', ['email' => 'ABC123@bath.ac.uk']);
        keycloakReturns();

        get(route('auth.callback'))->assertRedirect('/members');

        assertAuthenticatedAs($user);
    })->group('mariadb');

    it('should copy the username and email from Keycloak', function () {
        $user = createUser('member', [
            'keycloak_user_id' => 'keycloak-id',
            'username' => 'old',
            'email' => 'old@bath.ac.uk',
        ]);
        keycloakReturns(email: 'new@bath.ac.uk', username: 'new');

        get(route('auth.callback'))->assertRedirect('/members');

        assertDatabaseHas('users', ['id' => $user->id, 'username' => 'new', 'email' => 'new@bath.ac.uk']);
    });

    it('should keep the username if Keycloak doesn\'t have one', function () {
        $user = createUser('member', ['keycloak_user_id' => 'keycloak-id', 'username' => 'abc123']);
        keycloakReturns(username: null);

        get(route('auth.callback'))->assertRedirect('/members');

        assertDatabaseHas('users', ['id' => $user->id, 'username' => 'abc123']);
    });

    it('should let in users of every group', function (string $group) {
        createUser($group, ['keycloak_user_id' => 'keycloak-id']);
        keycloakReturns();

        get(route('auth.callback'))->assertRedirect('/members');

        assertAuthenticated();
    })->with(['member', 'associate', 'committee', 'staff', 'super_admin']);

    it('should refuse archived users', function () {
        createUser('archived', ['keycloak_user_id' => 'keycloak-id']);
        keycloakReturns();

        get(route('auth.callback'))->assertRedirect('/');

        assertGuest();
        expectNotification('error', 'Log in failed - your account is disabled');
    });

    it('should refuse users who aren\'t on the website', function () {
        keycloakReturns();

        get(route('auth.callback'))->assertRedirect('/');

        assertGuest();
        expectNotification('error', 'Log in failed');
    });

    it('should refuse users whose email isn\'t verified', function () {
        createUser('member', ['keycloak_user_id' => 'keycloak-id']);
        keycloakReturns(emailVerified: false);

        get(route('auth.callback'))->assertRedirect('/');

        assertGuest();
        expectNotification('error', 'Log in failed - your email address not is verified');
    });

    it('should refuse users without an email', function () {
        createUser('member', ['keycloak_user_id' => 'keycloak-id']);
        keycloakReturns(email: null);

        get(route('auth.callback'))->assertRedirect('/');

        assertGuest();
        expectNotification('error', 'Log in failed');
    });

    it('should not go back to the page that was asked for', function () {
        $user = createUser('member', ['keycloak_user_id' => 'keycloak-id']);
        keycloakReturns();

        get(route('event.index'))->assertRedirect(route('auth.login'));
        get(route('auth.callback'))->assertRedirect('/members');

        assertAuthenticatedAs($user);
    });
});

describe('logging out', function () {
    it('should log the user out', function () {
        $user = loginAs('member');

        get(route('auth.logout'))->assertRedirect('/');

        assertGuest();
        expectNotification('success', 'Logged out');
        expect(Log::where('action', 'auth.logout')->where('user_id', $user?->id)->exists())->toBeTrue();
    });

    it('should do nothing for guests', function () {
        get(route('auth.logout'))->assertRedirect('/');

        expect(notifications())->toBeEmpty();
    });
});

describe('the logged-in session', function () {
    it('should keep the user logged in on later requests', function () {
        $user = createUser('member');

        withSession([sessionKey() => $user->id])
            ->get(route('member.dash'))
            ->assertOk();

        assertAuthenticatedAs($user);
    });

    it('should log out users who have been archived, on their next request', function () {
        $user = createUser('archived');

        withSession([sessionKey() => $user->id])
            ->get(route('member.dash'))
            ->assertRedirect(route('auth.login'));

        assertGuest();
        expectNotification(
            'error',
            'Please [contact the secretary](mailto:sec@bts-crew.com) to have your account re-enabled.',
            'Account disabled',
        );
    });
});
