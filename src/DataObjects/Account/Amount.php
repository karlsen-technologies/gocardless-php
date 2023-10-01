<?php

namespace KarlsenTechnologies\GoCardless\DataObjects\Account;

class Amount
{
    public function __construct(
        public float $amount,
        public string $currency,
    ) {
    }

    public static function fromApi(object $data): Amount
    {
        return new Amount(
            $data->amount,
            $data->currency,
        );
    }
}
