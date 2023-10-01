<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects;

class EndUserAgreement
{
    public function __construct(
        public string $id,
        public string $created,
        public string $institutionId,
        public int $maxHistoricalDays,
        public int $accessValidForDays,
        public array $accessScopes,
        public ?string $accepted,
    ) {
    }

    public static function fromApi(object $response): EndUserAgreement
    {
        return new EndUserAgreement(
            $response->id,
            $response->created,
            $response->institution_id,
            $response->max_historical_days,
            $response->access_valid_for_days,
            $response->access_scope,
            $response->accepted,
        );
    }
}
