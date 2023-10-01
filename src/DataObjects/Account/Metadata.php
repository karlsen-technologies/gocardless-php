<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects\Account;

use KarlsenTechnologies\GoCardless\Enums\Account\Status;

class Metadata
{
    public function __construct(
        public string $id,
        public string $created,
        public ?string $lastAccessed,
        public string $iban,
        public string $institutionId,
        public Status $status,
        public string $ownerName,
    ) {
    }

    public static function fromApi(object $response): Metadata
    {
        return new Metadata(
            $response->id,
            $response->created,
            $response->last_accessed,
            $response->iban,
            $response->institution_id,
            Status::from($response->status),
            $response->owner_name,
        );
    }
}
