<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use KarlsenTechnologies\GoCardless\Client;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Credentials;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Tokens;
use KarlsenTechnologies\GoCardless\DataObjects\Bank;
use KarlsenTechnologies\GoCardless\Http\TokenClient;

it('can set and get the api client', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokenClient = new TokenClient($credentials, 'https://example.com');

    $client = new Client($credentials, 'https://example.com');

    $client->setApiClient($tokenClient);

    expect($client->getApiClient())->toBe($tokenClient);
});

it('can pass the credentials to the api client', function (): void {
    $credentials = new Credentials('valid', 'credentials');

    $client = new Client($credentials, 'https://example.com');

    expect($client->getApiClient()->getCredentials())->toBe($credentials);
});

it('can set and get the authentication tokens of the TokenClient', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokenClient = new TokenClient($credentials, 'https://example.com');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $client = new Client($credentials, 'https://example.com');

    $client->setApiClient($tokenClient);
    $client->setTokens($tokens);

    expect($tokenClient->getTokens())->toBe($tokens)
        ->and($client->getTokens())->toBe($tokens);
});

it('can get a list of banks from the api without a country', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokenClient = new TokenClient($credentials, 'https://example.com');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'institutions/', [
            'query' => [],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(200, [], '[{"id": "bank_123", "name": "Bank of Test", "bic": "TESTBIC", "transaction_total_days": 5, "countries": ["GB"], "logo": "https://gocardless.com/logo.png"}]'));

    $tokenClient->setClient($guzzle);

    $client = new Client($credentials, 'https://example.com');

    $client->setApiClient($tokenClient);
    $client->setTokens($tokens);

    $banks = $client->getBanks();

    expect($banks)->toBeArray()
        ->and($banks)->toHaveCount(1)
        ->and($banks[0])->toBeInstanceOf(Bank::class)
        ->and($banks[0])->toMatchObject([
            'id' => 'bank_123',
            'name' => 'Bank of Test',
            'bic' => 'TESTBIC',
            'transactionTotalDays' => 5,
            'countries' => ['GB'],
            'logo' => 'https://gocardless.com/logo.png',
        ]);
});

it('can get a list of banks from the api with a country', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokenClient = new TokenClient($credentials, 'https://example.com');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'institutions/', [
            'query' => [
                "country" => "GB",
            ],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(200, [], '[{"id": "bank_123", "name": "Bank of Test", "bic": "TESTBIC", "transaction_total_days": 5, "countries": ["GB"], "logo": "https://gocardless.com/logo.png"}]'));

    $tokenClient->setClient($guzzle);

    $client = new Client($credentials, 'https://example.com');

    $client->setApiClient($tokenClient);
    $client->setTokens($tokens);

    $banks = $client->getBanks('GB');

    expect($banks)->toBeArray()
        ->and($banks)->toHaveCount(1)
        ->and($banks[0])->toBeInstanceOf(Bank::class)
        ->and($banks[0])->toMatchObject([
            'id' => 'bank_123',
            'name' => 'Bank of Test',
            'bic' => 'TESTBIC',
            'transactionTotalDays' => 5,
            'countries' => ['GB'],
            'logo' => 'https://gocardless.com/logo.png',
        ]);
});

it('can get a bank from the api', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokenClient = new TokenClient($credentials, 'https://example.com');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'institutions/bank123/', Mockery::any())
        ->andReturn(new GuzzleResponse(200, [], '{"id": "bank_123", "name": "Bank of Test", "bic": "TESTBIC", "transaction_total_days": 5, "countries": ["GB"], "logo": "https://gocardless.com/logo.png"}'));

    $tokenClient->setClient($guzzle);

    $client = new Client($credentials, 'https://example.com');

    $client->setApiClient($tokenClient);
    $client->setTokens($tokens);

    $banks = $client->getBank('bank123');

    expect($banks)
        ->toBeInstanceOf(Bank::class)
        ->toMatchObject([
            'id' => 'bank_123',
            'name' => 'Bank of Test',
            'bic' => 'TESTBIC',
            'transactionTotalDays' => 5,
            'countries' => ['GB'],
            'logo' => 'https://gocardless.com/logo.png',
        ]);
});
