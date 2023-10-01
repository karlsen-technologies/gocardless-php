<?php

namespace KarlsenTechnologies\GoCardless\Enums\Account;

enum BankStatus: string
{
    case ENABLED = 'enabled';
    case DELETED = 'deleted';
    case BLOCKED = 'blocked';
}
