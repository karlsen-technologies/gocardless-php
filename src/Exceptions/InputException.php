<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\Exceptions;

class InputException extends ApiException
{
    public array $fields = [];

    public function __construct(array $fields, int $code)
    {
        $this->fields = $fields;

        $details = '';

        foreach ($fields as $field => $errors) {
            $details .= $field.': '.implode(', ', (array) $errors).' ';
        }

        parent::__construct('Input is missing or malformed.', $details, $code);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public static function fromApi(object $data): InputException
    {
        $fields = [];

        foreach ($data as $key => $value) {
            if (! is_int($value)) {
                $fields[$key] = $value;
            }
        }

        return new InputException(
            $fields,
            $data->status_code
        );
    }
}
