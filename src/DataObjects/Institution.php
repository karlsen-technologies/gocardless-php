<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects;

class Institution
{
    public function __construct(
        public string $id,
        public string $name,
        public string $bic,
        public int $transactionTotalDays,
        public array $countries,
        public string $logo,
    ) {
    }

    public static function fromApi(object $response): Institution
    {
        return new Institution(
            $response->id,
            $response->name,
            $response->bic,
            $response->transaction_total_days,
            $response->countries,
            $response->logo,
        );
    }
}
