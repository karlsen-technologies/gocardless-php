<?php

namespace KarlsenTechnologies\GoCardless\DataObjects;

class Bank
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

    public static function fromApi(object $response): Bank
    {
        return new Bank(
            $response->id,
            $response->name,
            $response->bic,
            $response->transaction_total_days,
            $response->countries,
            $response->logo,
        );
    }
}
