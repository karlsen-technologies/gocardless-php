<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\Enums\Account;

enum BalanceType: string
{
    case CLOSING_AVAILABLE = 'closingAvailable';
    case CLOSING_BOOKED = 'closingBooked';
    case EXPECTED = 'expected';
    case FORWARD_AVAILABLE = 'forwardAvailable';
    case INTERIM_AVAILABLE = 'interimAvailable';
    case INFORMATION = 'information';
    case INTERIM_BOOKED = 'interimBooked';
    case NON_INVOICED = 'nonInvoiced';
    case OPENING_BOOKED = 'openingBooked';
    case OPENING_AVAILABLE = 'openingAvailable';
    case PREVIOUSLY_CLOSED_BOOKED = 'previouslyClosedBooked';
}
