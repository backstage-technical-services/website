<?php

namespace App\Auth;

use App\Helpers\Redact;
use App\Logger;
use App\Models\Users\User;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\Factory;
use Package\Notifications\NotificationHandler;
use SocialiteProviders\Keycloak\Provider;

readonly class AuthService
{
    private Provider $keycloak;

    public function __construct(
        private AuthFactory $auth,
        private readonly ConfigRepository $config,
        private NotificationHandler $notifications,
        private LogManager $logger,
        Factory $socialite,
    ) {
        $this->keycloak = $socialite->driver('keycloak');
    }

    /**
     * This is responsible for handling the authentication response from Keycloak. It verifies that the user is properly
     * configured before searching for the user entry in the database (first by the keycloak subject, falling back to
     * their email address).
     *
     * If the user is found and their account is active they are logged in. Their email and username are automatically
     * synced with their Keycloak profile.
     *
     * @return User|null
     */
    function login(): ?User
    {
        $kcUser = $this->keycloak->user();

        $authError = $this->verifyKeycloakUser($kcUser);
        if ($authError !== null) {
            return $this->handledFailedAuth($kcUser, $authError);
        }

        $user = $this->findUser($kcUser);
        if ($user === null) {
            return $this->handledFailedAuth($kcUser, AuthError::UserNotFound);
        }

        if (!$user->status) {
            return $this->handledFailedAuth($kcUser, AuthError::AccountDisabled);
        }

        $this->syncUser($user, $kcUser);

        // Log the user into the app
        $this->auth->guard()->login($user);
        $this->logger->info("User {$user->id} logged in");
        Logger::log('auth.login');
        $this->notifications->success('Logged in');

        return $user;
    }

    /**
     * This is responsible for logging the user out of both the application and Keycloak. If the user is not logged in,
     * we just skip this and redirect them to the home page.
     *
     * @param Request $request
     * @return bool
     */
    function logout(Request $request): bool
    {
        if (!$this->auth->guard()->check()) {
            return false;
        }

        Log::info("User {$request->user()->id} logged out");
        Logger::log('auth.logout');

        // Log out of the app
        $this->auth->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $this->notifications->success('Logged out');

        return true;
    }

    function getKeycloakLogoutUrl(): string
    {
        return $this->keycloak->getLogoutUrl(
            $this->config->get('app.url'),
            $this->config->get('services.keycloak.client_id'),
        );
    }

    /**
     * Verifies that the user in Keycloak is configured correctly.
     *
     * @param \Laravel\Socialite\Contracts\User $user
     * @return AuthError|null
     */
    private function verifyKeycloakUser(\Laravel\Socialite\Contracts\User $user): ?AuthError
    {
        if ($user->getEmail() === null) {
            return AuthError::MissingEmail;
        }

        if (!($user->user['email_verified'] ?? false)) {
            return AuthError::EmailNotVerified;
        }

        return null;
    }

    /**
     * Finds the user associated with the given Keycloak user. This will try their Keycloak ID (subject) first, before
     * falling back to their email address.
     *
     * @param \Laravel\Socialite\Contracts\User $kcUser
     * @return User|null
     */
    private function findUser(\Laravel\Socialite\Contracts\User $kcUser): ?User
    {
        $this->logger->debug("Searching for user by subject {$kcUser->getId()}");
        $user = User::where('keycloak_user_id', $kcUser->getId())->first();
        if ($user !== null) {
            $this->logger->debug("Found user {$user->id} for keycloak ID {$kcUser->getId()}");
            return $user;
        }

        $this->logger->debug('Searching for user by email ' . Redact::email($kcUser->getEmail()));
        $user = User::where('email', $kcUser->getEmail())->first();
        if ($user !== null) {
            $this->logger->debug("Found user {$user->id} for email " . Redact::email($kcUser->getEmail()));
            return $user;
        }

        return null;
    }

    /**
     *  Synchronises the user's record with their Keycloak profile. As most of these can be edited via the website, we
     *  only sync the essentials in order to prevent a tug of war. Ideally we would make people manage their profile in
     *  the Keycloak account dashboard and just sync them here.
     *
     * @param User $user
     * @param \Laravel\Socialite\Contracts\User $kcUser
     * @return void
     */
    private function syncUser(User $user, \Laravel\Socialite\Contracts\User $kcUser): void
    {
        $user->update([
            'username' => $kcUser->getNickname() ?? $user->username,
            'email' => $kcUser->getEmail() ?? $user->email,
            'keycloak_user_id' => $kcUser->getId(),
        ]);
        $this->logger->debug("User {$user->id} has been synced with their Keycloak profile");
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $kcUser
     * @param AuthError $error
     * @return null
     */
    private function handledFailedAuth(\Laravel\Socialite\Contracts\User $kcUser, AuthError $error): null
    {
        $this->logger->warning("Failed log in attempt for user {$kcUser->getId()}: {$error->getLogMessage()}");
        $this->notifications->error(
            implode(' - ', array_filter(['Log in failed', $error->getNotificationDetails() ?? ''])),
        );
        return null;
    }
}
