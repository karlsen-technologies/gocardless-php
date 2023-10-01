<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct(
        protected string $summary,
        protected string $details,
        int $code
    ) {
        parent::__construct($details, $code, null);
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public static function fromApi(object $data): ApiException
    {
        ray($data);

        if (! isset($data->summary) || ! isset($data->detail)) {
            return InputException::fromApi($data);
        }

        return new ApiException(
            $data->summary,
            $data->detail,
            $data->status_code
        );
    }
}
