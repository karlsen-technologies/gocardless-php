<?php

use KarlsenTechnologies\GoCardless\Exceptions\ApiException;

it('can get the summary', function () {
    $exception = new ApiException('summary', 'details', 418);

    expect($exception->getSummary())->toBe('summary');
});

it('can get the details', function () {
    $exception = new ApiException('summary', 'details', 418);

    expect($exception->getDetails())->toBe('details');
});

it('can be created from an api response', function () {
    $data = (object) [
        'summary' => 'summary',
        'detail' => 'details',
        'status_code' => 418,
    ];

    $exception = ApiException::fromApi($data);

    expect($exception->getSummary())->toBe('summary')
        ->and($exception->getDetails())->toBe('details')
        ->and($exception->getCode())->toBe(418);
});
