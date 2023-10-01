<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Credentials;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Tokens;
use KarlsenTechnologies\GoCardless\Exceptions\ApiException;
use KarlsenTechnologies\GoCardless\Exceptions\InputException;
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

it('can make a GET request with a valid access token', function (): void {
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

it('can make a POST request with a valid access token', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('POST', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($tokens);

    $response = $client->post('test');

    expect($response->data->success)->toBeTrue();
});

it('can make a PUT request with a valid access token', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('PUT', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($tokens);

    $response = $client->put('test');

    expect($response->data->success)->toBeTrue();
});

it('can make a DELETE request with a valid access token', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('DELETE', 'test/', [
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], '{ "success": true }'));

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($tokens);

    $response = $client->delete('test');

    expect($response->data->success)->toBeTrue();
});

it('can refresh an expired access token', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $token = new Tokens('invalid', 0, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);

    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', Mockery::any())
        ->andThrow(
            new GuzzleClientException(
                'Unauthorized',
                new GuzzleRequest('GET', 'test/'),
                new GuzzleResponse(401, [], '{ "summary": "Authentication failed", "detail": "Invalid access token", "status_code": 401 }'),
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
        ->andThrow(
            new GuzzleClientException(
                'Unauthorized',
                new GuzzleRequest('GET', 'test/'),
                new GuzzleResponse(401, [], '{ "summary": "Authentication failed", "detail": "Invalid access token", "status_code": 401 }'),
            )
        );

    $guzzle->shouldReceive('post')->once()
        ->with('token/refresh/', Mockery::any())
        ->andThrow(
            new GuzzleClientException(
                'Invalid refresh token',
                new GuzzleRequest('GET', 'test/'),
                new GuzzleResponse(401, [], '{ "summary": "Invalid refresh token", "detail": "Invalid refresh token", "status_code": 401 }'),
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

it('throws an exception while getting tokens', function (): void {
    $credentials = new Credentials('valid', 'credentials');

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('post')->once()
        ->with('token/new/', Mockery::any())
        ->andThrow(
            new GuzzleClientException(
                'I\'m a teapot',
                new GuzzleRequest('post', 'token/new/'),
                new GuzzleResponse(418, [], '{ "summary": "I\'m a teapot", "detail": "I\'m a teapot", "status_code": 418 }'),
            )
        );

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);

    expect(fn () => $client->get('test'))->toThrow(ApiException::class);
});

it('throws an exception while refreshing access token', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokens = new Tokens(null, null, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('post')->once()
        ->with('token/refresh/', Mockery::any())
        ->andThrow(
            new GuzzleClientException(
                'Missing parameters',
                new GuzzleRequest('post', 'token/refresh/'),
                new GuzzleResponse(400, [], '{ "refresh": ["Field is required"], "status_code": 400 }'),
            )
        );

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($tokens);

    expect(fn () => $client->get('test'))->toThrow(InputException::class);
});

it('throws an exception while performing a request', function (): void {
    $credentials = new Credentials('valid', 'credentials');
    $tokens = new Tokens('access', 10, 'refresh', 42);

    $guzzle = Mockery::mock(\GuzzleHttp\Client::class);
    $guzzle->shouldReceive('request')->once()
        ->with('GET', 'test/', Mockery::any())
        ->andThrow(
            new GuzzleClientException(
                'Server unavailable.',
                new GuzzleRequest('post', 'test/'),
                new GuzzleResponse(500, [], '{ "summary": "Server unavailable.", "details": "The server is unavailable.", "status_code": 500 }'),
            )
        );

    $client = new TokenClient($credentials, '');

    $client->setClient($guzzle);
    $client->setTokens($tokens);

    expect(fn () => $client->get('test'))->toThrow(ApiException::class);
});
