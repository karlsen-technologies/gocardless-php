<?php

namespace KarlsenTechnologies\GoCardless\DataObjects\Account;

use KarlsenTechnologies\GoCardless\DataObjects\Transaction;

class Transactions
{
    public function __construct(
        public array $booked,
        public array $pending,
    ) {
    }

    public static function fromApi(object $response): Transactions
    {
        $booked = array_map(fn ($transaction) => Transaction::fromApi($transaction), $response->transactions->booked);
        $pending = array_map(fn ($transaction) => Transaction::fromApi($transaction), $response->transactions->pending);

        return new Transactions(
            $booked,
            $pending,
        );
    }
}
