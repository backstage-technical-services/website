<?php

namespace Package\Keycloak;

use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

readonly class KeycloakClient
{
    private const ACCESS_ROLE = 'client-access';

    private Client $_http;
    /* @var Role[] $_clientAccessRoles */
    private array $_clientAccessRoles;
    private string $_clientUuid;

    public function __construct(string $clientId, string $clientSecret, string $realm, string $url)
    {
        $baseUrl = rtrim($url, '/');

        $this->_http = new Client([
            'base_uri' => "{$baseUrl}/admin/realms/{$realm}/",
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken($baseUrl, $realm, $clientId, $clientSecret),
            ],
        ]);

        $this->_clientUuid = $this->findClientUuid($clientId);
        $this->_clientAccessRoles = $this->findAccessRoles($this->_clientUuid);
    }

    public function findUser(string $username, string $email): ?User
    {
        foreach ([['username' => $username], ['email' => $email]] as $query) {
            $user = $this->get('users', $query + ['exact' => true])[0] ?? null;
            if ($user !== null) {
                return User::fromArray($user);
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
        $response = $this->_http->post('users', [
            'json' => [
                'username' => $username,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'enabled' => true,
                'credentials' => [
                    [
                        'type' => 'password',
                        'value' => $password,
                        'temporary' => true,
                    ],
                ],
            ],
        ]);

        $location = $response->getHeaderLine('Location');
        if ($location === '') {
            throw new RuntimeException('Keycloak did not return a Location header when creating a user');
        }

        return basename($location);
    }

    public function attachAccessRole(string $userId): void
    {
        if (count($this->_clientAccessRoles) === 0) {
            return;
        }

        Log::debug("Attaching access role to Keycloak user {$userId}");
        $this->_http->post("users/{$userId}/role-mappings/clients/{$this->_clientUuid}", [
            'json' => array_map(static fn(Role $role) => $role->toArray(), $this->_clientAccessRoles),
        ]);
    }

    private function getAccessToken(string $baseUrl, string $realm, string $clientId, string $clientSecret): string
    {
        $response = new Client()->post("{$baseUrl}/realms/{$realm}/protocol/openid-connect/token", [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);

        $token = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR)['access_token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new RuntimeException('Failed to obtain a Keycloak access token');
        }

        return $token;
    }

    private function findClientUuid(string $clientId): string
    {
        $uuid = $this->get('clients', ['clientId' => $clientId])[0]['id'] ?? null;
        if (!is_string($uuid) || $uuid === '') {
            throw new RuntimeException("Keycloak client \"{$clientId}\" was not found");
        }

        return $uuid;
    }

    /**
     * @return Role[]
     */
    private function findAccessRoles(string $clientUuid): array
    {
        return array_values(
            array_map(
                Role::fromArray(...),
                array_filter(
                    $this->get("clients/{$clientUuid}/roles"),
                    static fn(array $role) => ($role['name'] ?? null) === self::ACCESS_ROLE,
                ),
            ),
        );
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        $response = $this->_http->get($path, ['query' => $query]);

        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
