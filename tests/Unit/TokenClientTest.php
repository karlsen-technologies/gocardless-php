<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Credentials;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Tokens;
use KarlsenTechnologies\GoCardless\Http\TokenClient;

it('can set and get the GuzzleHttp client', function (): void {
    $credentials = new Credentials('', '');
    $client = new TokenClient($credentials, '');
    $guzzle = new \GuzzleHttp\Client();

    $client->setClient($guzzle);

    expect($client->getClient())->toBe($guzzle);
});

it('can get the authentication credentials', function (): void {
    $credentials = new Credentials('my', 'credentials');
    $client = new TokenClient($credentials, '');

    expect($client->getCredentials())->toBe($credentials);
});

it('can set and get the authentication tokens', function (): void {
    $credentials = new Credentials('', '');
    $client = new TokenClient($credentials, '');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $client->setTokens($tokens);

    expect($client->getTokens())->toBe($tokens);
});

it('can make a request with valid credentials', function (): void {
    $credentials = new Credentials('valid', 'credentials');

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('post')
        ->with('token/new/', [
            'json' => $credentials->toArray(),
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "access": "access", "access_expires": 10, "refresh": "refresh", "refresh_expires": 42 }'));

    $guzzle->shouldReceive('request')
        ->with('GET', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, 'https://api.example.com/');

    $client->setClient($guzzle);

    $response = $client->get('test');

    expect($response->data->success)->toBeTrue();
});
