<?php

namespace Package\Keycloak;

readonly class Role
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description = null,
        public bool $composite = false,
        public bool $clientRole = true,
        public ?string $containerId = null,
    ) {}

    /**
     * @param array<string, mixed> $role
     */
    public static function fromArray(array $role): self
    {
        return new self(
            id: $role['id'],
            name: $role['name'],
            description: $role['description'] ?? null,
            composite: (bool) ($role['composite'] ?? false),
            clientRole: (bool) ($role['clientRole'] ?? true),
            containerId: $role['containerId'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'composite' => $this->composite,
            'clientRole' => $this->clientRole,
            'containerId' => $this->containerId,
        ];
    }
}
