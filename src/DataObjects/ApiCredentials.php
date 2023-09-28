<?php

namespace KarlsenTechnologies\GoCardless\DataObjects;

class ApiCredentials
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
