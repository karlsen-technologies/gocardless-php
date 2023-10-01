<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects\Api;

class Tokens
{
    public function __construct(
        public ?string $access,
        public ?int $accessExpiresAt,
        public string $refresh,
        public int $refreshExpiresAt,
    ) {
    }
}
