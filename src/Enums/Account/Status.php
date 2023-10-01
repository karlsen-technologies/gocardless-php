<?php

namespace KarlsenTechnologies\GoCardless\Enums\Account;

enum Status: string
{
    case DISCOVERED = 'DISCOVERED';
    case ERROR = 'ERROR';
    case EXPIRED = 'EXPIRED';
    case PROCESSING = 'PROCESSING';
    case READY = 'READY';
    case SUSPENDED = 'SUSPENDED';

}
