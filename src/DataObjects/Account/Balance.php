<?php

namespace KarlsenTechnologies\GoCardless\DataObjects\Account;

use KarlsenTechnologies\GoCardless\Enums\Account\BalanceType;

class Balance
{
    public function __construct(
        public Amount $balanceAmount,
        public BalanceType $balanceType,
        public ?bool $creditLimitIncluded,
        public ?string $lastChangeDateTime,
        public ?string $lastCommittedTransaction,
        public ?string $referenceDate,
    ) {
    }

    public static function fromApi(object $response): Balance
    {
        return new Balance(
            Amount::fromApi($response->balanceAmount),
            BalanceType::from($response->balanceType),
            $response->creditLimitIncluded ?? null,
            $response->lastChangeDateTime ?? null,
            $response->lastCommittedTransaction ?? null,
            $response->referenceDate ?? null,
        );
    }
}
