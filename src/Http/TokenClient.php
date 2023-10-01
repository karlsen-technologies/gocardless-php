<?php

namespace KarlsenTechnologies\GoCardless\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Credentials;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Response;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Tokens;
use KarlsenTechnologies\GoCardless\Exceptions\ApiException;

class TokenClient
{
    private Credentials $credentials;

    protected ?Tokens $tokens = null;

    protected GuzzleClient $http;

    public function __construct(Credentials $credentials, string $endpoint)
    {
        $this->credentials = $credentials;

        $this->http = new GuzzleClient([
            'base_uri' => $endpoint,
        ]);
    }

    public function getTokens(): ?Tokens
    {
        return $this->tokens;
    }

    public function setTokens(Tokens $tokens): void
    {
        $this->tokens = $tokens;
    }

    protected function getAccessToken(): string
    {
        // If no token pair is set, authenticate and set it
        if ($this->tokens == null) {
            try {
                $response = $this->http->post('token/new/', [
                    'json' => $this->credentials->toArray(),
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);
            } catch (BadResponseException $e) {
                $body = $e->getResponse()->getBody()->getContents();
                $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);

                throw ApiException::fromApi($data);
            }

            $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

            $this->tokens = new Tokens(
                $data->access,
                $data->access_expires,
                $data->refresh,
                $data->refresh_expires,
            );
        }

        // If no access token is set, attempt to refresh it and set it
        if ($this->tokens->access == null) {

            try {
                // If we do not have a refresh token
                $response = $this->http->post('token/refresh/', [
                    'json' => [
                        'refresh' => $this->tokens->refresh,
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);
            } catch (BadResponseException $e) {
                // If we get a 401, we need to authenticate fresh
                if ($e->getCode() == 401) {
                    $this->tokens = null;

                    return $this->getAccessToken();
                }

                $body = $e->getResponse()->getBody()->getContents();
                $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);

                throw ApiException::fromApi($data);
            }

            $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

            $this->tokens->access = $data->access;
            $this->tokens->accessExpiresAt = $data->access_expires;
        }

        return $this->tokens->access;
    }

    protected function request(string $method, string $uri, array $options = []): Response
    {
        $uri = ltrim($uri, '/');
        $uri = rtrim($uri, '/').'/';

        $options['headers'] = [
            'Authorization' => "Bearer {$this->getAccessToken()}",
            'Accept' => 'application/json',
        ];

        try {
            $response = $this->http->request($method, $uri, $options);
        } catch (BadResponseException $e) {
            // If we get a 401, and we had an access token, try to refresh it and try again
            if ($e->getCode() == 401 && $this->tokens->access != null) {
                $this->tokens->access = null;

                return $this->request($method, $uri, $options);
            }

            $body = $e->getResponse()->getBody()->getContents();
            $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);

            throw ApiException::fromApi($data);
        }

        $code = $response->getStatusCode();
        $headers = $response->getHeaders();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, false, 512, JSON_THROW_ON_ERROR);

        return new Response($body, $data, $code, $headers);
    }

    public function get(string $uri, array $options = []): Response
    {
        return $this->request('GET', $uri, $options);
    }

    public function post(string $uri, array $options = []): Response
    {
        return $this->request('POST', $uri, $options);
    }

    public function put(string $uri, array $options = []): Response
    {
        return $this->request('PUT', $uri, $options);
    }

    public function delete(string $uri, array $options = []): Response
    {
        return $this->request('DELETE', $uri, $options);
    }
}
