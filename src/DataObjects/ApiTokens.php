<?php

namespace KarlsenTechnologies\GoCardless\DataObjects;

class ApiTokens
{
    public function __construct(
        public ?string $access = null,
        public ?int $accessExpiresAt = null,
        public ?string $refresh = null,
        public ?int $refreshExpiresAt = null,
    ) {
    }
}
