<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless;

use KarlsenTechnologies\GoCardless\DataObjects\Account\Balance;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Details;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Metadata;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Transactions;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Credentials;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Tokens;
use KarlsenTechnologies\GoCardless\DataObjects\Bank;
use KarlsenTechnologies\GoCardless\DataObjects\EndUserAgreement;
use KarlsenTechnologies\GoCardless\DataObjects\Requisition;
use KarlsenTechnologies\GoCardless\Http\TokenClient;

class Client
{
    public const DEFAULT_API_ENDPOINT = 'https://bankaccountdata.gocardless.com/api/v2/';

    protected TokenClient $api;

    public function __construct(Credentials $credentials, string $endpoint = self::DEFAULT_API_ENDPOINT)
    {
        $this->api = new TokenClient($credentials, $endpoint);
    }

    public function setTokens(Tokens $tokens): void
    {
        $this->api->setTokens($tokens);
    }

    public function getTokens(): ?Tokens
    {
        return $this->api->getTokens();
    }

    public function getBanks(string $country = null): array
    {
        $response = $this->api->get('institutions', [
            'query' => $country ? [
                'country' => $country,
            ] : [],
        ]);

        return array_map(fn ($bank) => Bank::fromApi($bank), $response->data);
    }

    public function getBank(string $id): Bank
    {
        $response = $this->api->get('institutions/'.$id);

        return Bank::fromApi($response->data);
    }

    public function getAgreements(int $limit = 100, int $offset = 0): array
    {
        $response = $this->api->get('agreements/enduser', [
            'query' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);

        return array_map(fn ($agreement) => EndUserAgreement::fromApi($agreement), $response->data->results);
    }

    public function getAgreement(string $id): EndUserAgreement
    {
        $response = $this->api->get('agreements/enduser/'.$id);

        return EndUserAgreement::fromApi($response->data);
    }

    public function createAgreement(string $institutionId, int $maxHistoricalDays = 90, int $accessValidForDays = 90, array $accessScopes = ['balances', 'details', 'transactions']): EndUserAgreement
    {
        $response = $this->api->post('agreements/enduser', [
            'json' => [
                'institution_id' => $institutionId,
                'max_historical_days' => $maxHistoricalDays,
                'access_valid_for_days' => $accessValidForDays,
                'access_scope' => $accessScopes,
            ],
        ]);

        return EndUserAgreement::fromApi($response->data);
    }

    public function deleteAgreement(string $id): void
    {
        $this->api->delete('agreements/enduser/'.$id);
    }

    public function acceptAgreement(string $id, string $userAgent, string $ipAddress): EndUserAgreement
    {
        $response = $this->api->put("agreements/enduser/{$id}/accept", [
            'json' => [
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
            ],
        ]);

        return EndUserAgreement::fromApi($response->data);
    }

    public function getRequisitions(int $limit = 100, int $offset = 0): array
    {
        $response = $this->api->get('requisitions', [
            'query' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);

        return array_map(fn ($requisition) => Requisition::fromApi($requisition), $response->data->results);
    }

    public function getRequisition(string $id): Requisition
    {
        $response = $this->api->get('requisitions/'.$id);

        return Requisition::fromApi($response->data);
    }

    public function createRequisition(string $redirectUrl, string $institutionId, string $agreementId, string $reference, string $userLanguage = '', string $ssn = '', bool $accountSelection = false, bool $redirectImmediate = false): Requisition
    {
        $response = $this->api->post('requisitions', [
            'json' => [
                'redirect' => $redirectUrl,
                'institution_id' => $institutionId,
                'agreement' => $agreementId,
                'reference' => $reference,
                'user_language' => $userLanguage,
                'ssn' => $ssn,
                'account_selection' => $accountSelection,
                'redirect_immediate' => $redirectImmediate,
            ],
        ]);

        return Requisition::fromApi($response->data);
    }

    public function deleteRequisition(string $id): void
    {
        $this->api->delete('requisitions/'.$id);
    }

    public function getAccount(string $id): Metadata
    {
        $response = $this->api->get('accounts/'.$id);

        return Metadata::fromApi($response->data);
    }

    public function getAccountBalances(string $id): array
    {
        $response = $this->api->get('accounts/'.$id.'/balances');

        return array_map(fn ($balance) => Balance::fromApi($balance), $response->data->balances);
    }

    public function getAccountDetails(string $id): Details
    {
        $response = $this->api->get('accounts/'.$id.'/details');

        return Details::fromApi($response->data->account);
    }

    public function getAccountTransactions(string $id, string $fromDate = null, string $toDate = null): Transactions
    {
        $response = $this->api->get(
            'accounts/'.$id.'/transactions',
            [
                'query' => [
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                ],
            ]
        );

        return Transactions::fromApi($response->data);
    }
}
