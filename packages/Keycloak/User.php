<?php

namespace Package\Keycloak;

readonly class User
{
    public function __construct(
        public string $id,
    ) {}

    /**
     * @param array<string, mixed> $user
     */
    public static function fromArray(array $user): self
    {
        return new self(id: $user['id']);
    }
}
