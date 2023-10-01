<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects\Api;

class Credentials
{
    public function __construct(
        public string $secretId,
        public string $secretKey,
    ) {
    }

    public function toArray(): array
    {
        return [
            'secret_id' => $this->secretId,
            'secret_key' => $this->secretKey,
        ];
    }
}
