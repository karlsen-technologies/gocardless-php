<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects\Api;

class Response
{
    public function __construct(
        public string $body,
        public mixed $data,
        public int $statusCode,
        public array $headers,
    ) {
    }
}
