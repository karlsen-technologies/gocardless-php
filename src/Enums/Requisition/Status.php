<?php

namespace KarlsenTechnologies\GoCardless\Enums\Requisition;

enum Status: string
{
    case CREATED = 'CR';
    case GIVING_CONSENT = 'GC';
    case UNDERGOING_AUTHENTICATION = 'UA';
    case REJECTED = 'RJ';
    case SELECTING_ACCOUNTS = 'SA';
    case GRANTING_ACCESS = 'GA';
    case LINKED = 'LN';
    case SUSPENDED = 'SU';
    case EXPIRED = 'EX';
}
