<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects;

use KarlsenTechnologies\GoCardless\Enums\Requisition\Status;

class Requisition
{
    public function __construct(
        public string $id,
        public string $created,
        public string $redirect,
        public Status $status,
        public string $institutionId,
        public string $agreement,
        public string $reference,
        public array $accounts,
        public ?string $userLanguage,
        public string $link,
        public ?string $ssn,
        public bool $accountSelection,
        public bool $redirectImmediate,
    ) {
    }

    public static function fromApi(object $response): Requisition
    {
        return new Requisition(
            $response->id,
            $response->created,
            $response->redirect,
            Status::from($response->status),
            $response->institution_id,
            $response->agreement,
            $response->reference,
            $response->accounts,
            $response->user_language ?? null,
            $response->link,
            $response->ssn,
            $response->account_selection,
            $response->redirect_immediate,
        );
    }
}
