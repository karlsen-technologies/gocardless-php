<?php

declare(strict_types=1);

use KarlsenTechnologies\GoCardless\Exceptions\InputException;

it('can get the summary', function (): void {
    $exception = new InputException([
        "amount" => [
            "Must be a positive integer."
        ]
    ], 400);

    expect($exception->getSummary())->toBe('Input is missing or malformed.');
});

it('can get the details', function (): void {
    $exception = new InputException([
        "amount" => [
            "Must be a positive integer."
        ]
    ], 400);

    expect($exception->getDetails())->toBe('amount: Must be a positive integer. ');
});

it('can get the fields', function (): void {
    $exception = new InputException([
        "amount" => [
            "Must be a positive integer."
        ]
    ], 400);

    expect($exception->getFields())->toBe([
        "amount" => [
            "Must be a positive integer."
        ]
    ]);
});

it('can be created from an api response', function (): void {
    $data = (object) [
        'amount' => [
            'Must be a positive integer.',
        ],
        'status_code' => 400,
    ];

    $exception = InputException::fromApi($data);

    expect($exception->getSummary())->toBe('Input is missing or malformed.')
        ->and($exception->getDetails())->toBe('amount: Must be a positive integer. ')
        ->and($exception->getFields())->toBe([
            'amount' => [
                'Must be a positive integer.',
            ],
        ])
        ->and($exception->getCode())->toBe(400);
});
