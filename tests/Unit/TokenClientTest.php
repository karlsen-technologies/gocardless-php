<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
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

it('can get an access token with valid credentials', function (): void {
    $credentials = new Credentials('valid', 'credentials');

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('post')->once()
        ->with('token/new/', [
            'json' => $credentials->toArray(),
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "access": "access", "access_expires": 10, "refresh": "refresh", "refresh_expires": 42 }'));

    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);

    $client->get('test');

    expect($client->getTokens()->access)->toBeString('access');
});

it('can make a request with a valid access token', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($tokens);

    $response = $client->get('test');

    expect($response->data->success)->toBeTrue();
});

it('can make a request with valid credentials', function (): void {
    $credentials = new Credentials('valid', 'credentials');

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('post')->once()
        ->with('token/new/', [
            'json' => $credentials->toArray(),
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "access": "access", "access_expires": 10, "refresh": "refresh", "refresh_expires": 42 }'));

    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);

    $response = $client->get('test');

    expect($response->data->success)->toBeTrue();
});

it('can refresh an access token', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $token = new Tokens('invalid', 0, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);

    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', Mockery::any())
        ->andThrow(new GuzzleClientException(
                'Unauthorized',
                new GuzzleRequest('GET', 'test/'),
                new GuzzleResponse(401, [], '{ "summary: "Authentication failed", "detail": "Invalid access token", "status_code": 401 }'),
            )
        );

    $guzzle->shouldReceive('post')->once()
        ->with('token/refresh/', [
            'json' => [
                'refresh' => 'refresh',
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "access": "access", "access_expires": 10, "refresh": "refresh", "refresh_expires": 42 }'));

    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($token);

    $response = $client->get('test');

    expect($response->data->success)->toBeTrue();
});

it('can get new tokens when both are invalid', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $token = new Tokens('invalid', 0, 'noway', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);

    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', Mockery::any())
        ->andThrow(new GuzzleClientException(
                'Unauthorized',
                new GuzzleRequest('GET', 'test/'),
                new GuzzleResponse(401, [], '{ "summary: "Authentication failed", "detail": "Invalid access token", "status_code": 401 }'),
            )
        );

    $guzzle->shouldReceive('post')->once()
        ->with('token/refresh/', Mockery::any())
        ->andThrow(new GuzzleClientException(
                'Invalid refresh token',
                new GuzzleRequest('GET', 'test/'),
                new GuzzleResponse(401, [], '{ "summary: "Invalid refresh token", "detail": "Invalid refresh token", "status_code": 401 }'),
            )
        );

    $guzzle->shouldReceive('post')->once()
        ->with('token/new/', [
            'json' => $credentials->toArray(),
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "access": "access", "access_expires": 10, "refresh": "refresh", "refresh_expires": 42 }'));

    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($token);

    $response = $client->get('test');

    expect($response->data->success)->toBeTrue();
});
