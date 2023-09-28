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
}
