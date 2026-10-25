<?php

namespace Tests\Support\Fakes;

use ArrayObject;
use Illuminate\Support\Str;
use Package\Keycloak\KeycloakClient;
use Package\Keycloak\User;
use Throwable;

/**
 * A fake Keycloak admin client, which keeps its users in memory instead of calling the Keycloak API.
 *
 * KeycloakClient is a readonly class, so this has to be one too. The state lives in ArrayObjects, which can still
 * change.
 */
final readonly class FakeKeycloakClient extends KeycloakClient
{
    /**
     * The users in "Keycloak", keyed by their ID.
     *
     * @var ArrayObject<string, array{username: string, firstName: string, lastName: string, email: string, password: string|null}>
     */
    private ArrayObject $users;

    /**
     * The IDs of the users that have the client access role.
     *
     * @var ArrayObject<int, string>
     */
    private ArrayObject $usersWithAccessRole;

    /**
     * The exceptions to throw, keyed by the username they're for ("*" for every call).
     *
     * @var ArrayObject<string, Throwable>
     */
    private ArrayObject $failures;

    /**
     * This doesn't call the parent constructor, as that gets an access token from Keycloak.
     */
    public function __construct()
    {
        /** @var ArrayObject<string, array{username: string, firstName: string, lastName: string, email: string, password: string|null}> $users */
        $users = new ArrayObject();
        $this->users = $users;
        /** @var ArrayObject<int, string> $usersWithAccessRole */
        $usersWithAccessRole = new ArrayObject();
        $this->usersWithAccessRole = $usersWithAccessRole;
        /** @var ArrayObject<string, Throwable> $failures */
        $failures = new ArrayObject();
        $this->failures = $failures;
    }

    public function findUser(string $username, string $email): ?User
    {
        $this->throwIfFailing($username);

        foreach ([['username', $username], ['email', $email]] as [$field, $value]) {
            foreach ($this->users as $id => $user) {
                if ($user[$field] === $value) {
                    return new User($id);
                }
            }
        }

        return null;
    }

    public function createUser(
        string $username,
        string $firstName,
        string $lastName,
        string $email,
        string $password,
    ): string {
        $this->throwIfFailing($username);

        return $this->addUser($username, $email, $firstName, $lastName, $password);
    }

    public function attachAccessRole(string $userId): void
    {
        $this->throwIfFailing($this->users[$userId]['username'] ?? '');

        $this->usersWithAccessRole->append($userId);
    }

    /**
     * Add a user to "Keycloak", as if they already had an account.
     *
     * @return string the user's Keycloak ID
     */
    public function addUser(
        string $username,
        string $email,
        string $firstName = 'Test',
        string $lastName = 'User',
        ?string $password = null,
    ): string {
        $id = (string) Str::uuid();
        $this->users[$id] = [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password,
        ];

        return $id;
    }

    /**
     * Get the users in "Keycloak", keyed by their ID.
     *
     * @return array<string, array{username: string, firstName: string, lastName: string, email: string, password: string|null}>
     */
    public function users(): array
    {
        return $this->users->getArrayCopy();
    }

    public function hasAccessRole(string $userId): bool
    {
        return in_array($userId, $this->usersWithAccessRole->getArrayCopy(), true);
    }

    /**
     * Throw the given exception from now on, as if Keycloak were down. If a username is given, only calls about that
     * user throw.
     */
    public function failWith(Throwable $exception, string $username = '*'): void
    {
        $this->failures[$username] = $exception;
    }

    private function throwIfFailing(string $username): void
    {
        $failure = $this->failures['*'] ?? ($this->failures[$username] ?? null);
        if ($failure !== null) {
            throw $failure;
        }
    }
}
