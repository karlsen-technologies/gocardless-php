<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\Enums\Account;

enum BankStatus: string
{
    case ENABLED = 'enabled';
    case DELETED = 'deleted';
    case BLOCKED = 'blocked';
}
