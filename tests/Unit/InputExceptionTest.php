<?php

use KarlsenTechnologies\GoCardless\Exceptions\InputException;

it('can get the summary', function () {
    $exception = new InputException([
        "amount" => [
            "Must be a positive integer."
        ]
    ], 400);

    expect($exception->getSummary())->toBe('Input is missing or malformed.');
});

it('can get the details', function () {
    $exception = new InputException([
        "amount" => [
            "Must be a positive integer."
        ]
    ], 400);

    expect($exception->getDetails())->toBe('amount: Must be a positive integer. ');
});

it('can get the fields', function () {
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

it('can be created from an api response', function () {
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
