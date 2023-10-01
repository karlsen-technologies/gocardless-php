<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\Enums\Account;

enum Usage: string
{
    case PRIVATE = 'PRIV';
    case ORGANISATION = 'ORGA';
}
