<?php

declare(strict_types=1);

use KarlsenTechnologies\GoCardless\Exceptions\ApiException;

it('can get the summary', function (): void {
    $exception = new ApiException('summary', 'details', 418);

    expect($exception->getSummary())->toBe('summary');
});

it('can get the details', function (): void {
    $exception = new ApiException('summary', 'details', 418);

    expect($exception->getDetails())->toBe('details');
});

it('can be created from an api response', function (): void {
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
