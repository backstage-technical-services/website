<?php

namespace Package\Keycloak;

use Keycloak\Client\ClientApi;
use Keycloak\User\Entity\Role;
use Keycloak\User\UserApi;

readonly class KeycloakClient
{
    const ACCESS_ROLE = 'client-access';

    /* @var Role[] $clientAccessRoles */
    private array $_clientAccessRoles;
    private string $_clientUuid;

    public ClientApi $clients;
    public UserApi $users;

    public function __construct(string $clientId, string $clientSecret, string $realm, string $url)
    {
        $kcClient = new \Keycloak\KeycloakClient($clientId, $clientSecret, $realm, $url, null, '');

        $this->clients = new ClientApi($kcClient);
        $this->users = new UserApi($kcClient);

        $client = $this->clients->findByClientId($clientId);
        $this->_clientUuid = $client->id;
        $this->_clientAccessRoles = array_map(
            fn($role) => new Role(
                $role->id,
                $role->name,
                $role->description,
                $role->composite,
                $role->clientRole,
                $clientId,
            ),
            array_filter($this->clients->getRoles($client->id), fn($role) => $role->name === self::ACCESS_ROLE),
        );
    }

    public function attachAccessRole(string $userId): void
    {
        $this->users->addClientRoles($userId, $this->_clientUuid, $this->_clientAccessRoles);
    }
}
