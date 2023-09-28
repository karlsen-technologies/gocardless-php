<?php

namespace KarlsenTechnologies\GoCardless;

use KarlsenTechnologies\GoCardless\DataObjects\ApiCredentials;
use KarlsenTechnologies\GoCardless\DataObjects\ApiTokens;
use GuzzleHttp\Client as GuzzleClient;
use KarlsenTechnologies\GoCardless\DataObjects\Bank;

class Client
{

    public ApiCredentials $apiCredentials;

    public ?ApiTokens $apiTokens = null;

    protected GuzzleClient $http;

    public function __construct(ApiCredentials $apiCredentials, ?ApiTokens $authenticationCredentials = null, string $apiEndpoint = 'https://bankaccountdata.gocardless.com/api/v2/')
    {
        $this->apiCredentials = $apiCredentials;
        $this->apiTokens = $authenticationCredentials;

        $this->http = new GuzzleClient([
            'base_uri' => $apiEndpoint
        ]);
    }

    private function authenticate(): void
    {
        $response = $this->http->post('token/new/', [
            'json' => $this->apiCredentials->toArray(),
        ]);

        $data = json_decode($response->getBody()->getContents());

        $this->apiTokens = new ApiTokens(
            $data->access,
            $data->access_expires,
            $data->refresh,
            $data->refresh_expires,
        );
    }

    public function getBanks(?string $country = null)
    {
        // If we do not have any api tokens, we need to authenticate
        if (!$this->apiTokens) {
            $this->authenticate();
        }

        $response = $this->http->get('institutions', [
            'query' => $country ? [
                'country' => $country,
            ] : [],
            'headers' => [
                'Authorization' => "Bearer {$this->apiTokens->access}"
            ]
        ]);

        $data = json_decode($response->getBody()->getContents());

        return array_map(fn ($bank) => new Bank(
            $bank->id,
            $bank->name,
            $bank->bic,
            $bank->transaction_total_days,
            $bank->countries,
            $bank->logo,
        ), $data);
    }
}
