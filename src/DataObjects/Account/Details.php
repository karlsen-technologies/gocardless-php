<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects\Account;

use KarlsenTechnologies\GoCardless\Enums\Account\BankStatus;
use KarlsenTechnologies\GoCardless\Enums\Account\Usage;

class Details
{
    public function __construct(
        public ?string $resourceId,
        public ?string $name,
        public ?string $displayName,
        public string $currency,
        public ?BankStatus $status,
        public ?Usage $usage,
        public ?string $product,
        public ?string $cashAccountType,
        public ?string $iban,
        public ?string $bban,
        public ?string $bic,
        public ?string $details,
        public ?string $linkedAccounts,
        public ?string $msisdn,
        public ?string $ownerName,
        public ?string $ownerAddressUnstructured,
    ) {
    }

    public static function fromApi(object $response): Details
    {
        return new Details(
            $response->resourceId ?? null,
            $response->name ?? null,
            $response->displayName ?? null,
            $response->currency,
            BankStatus::tryFrom($response->status ?? ''),
            Usage::tryFrom($response->usage ?? ''),
            $response->product ?? null,
            $response->cashAccountType ?? null,
            $response->iban ?? null,
            $response->bban ?? null,
            $response->bic ?? null,
            $response->details ?? null,
            $response->linkedAccounts ?? null,
            $response->msisdn ?? null,
            $response->ownerName ?? null,
            $response->ownerAddressUnstructured ?? null,
        );
    }
}
