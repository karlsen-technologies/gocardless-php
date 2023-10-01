<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects\Api;

class Tokens
{
    public function __construct(
        public ?string $access = null,
        public ?int $accessExpiresAt = null,
        public ?string $refresh = null,
        public ?int $refreshExpiresAt = null,
    ) {
    }
}
