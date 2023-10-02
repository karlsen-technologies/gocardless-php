<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use KarlsenTechnologies\GoCardless\Client;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Amount;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Balance;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Details;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Credentials;
use KarlsenTechnologies\GoCardless\DataObjects\Api\Tokens;
use KarlsenTechnologies\GoCardless\DataObjects\Bank;
use KarlsenTechnologies\GoCardless\DataObjects\EndUserAgreement;
use KarlsenTechnologies\GoCardless\DataObjects\Requisition;
use KarlsenTechnologies\GoCardless\DataObjects\Transaction;
use KarlsenTechnologies\GoCardless\Enums\Account\BalanceType;
use KarlsenTechnologies\GoCardless\Enums\Account\BankStatus;
use KarlsenTechnologies\GoCardless\Enums\Account\Usage;
use KarlsenTechnologies\GoCardless\Enums\Requisition\Status as RequisitionStatus;
use KarlsenTechnologies\GoCardless\Http\TokenClient;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Metadata;
use KarlsenTechnologies\GoCardless\Enums\Account\Status as AccountStatus;

beforeEach(function (): void {
    $this->credentials = new Credentials('valid', 'credentials');
    $this->tokens = new Tokens('access', 10, 'refresh', 42);

    $this->tokenClient = new TokenClient($this->credentials, 'https://example.com');
    $this->client = new Client($this->credentials, 'https://example.com');
    $this->guzzle = Mockery::mock(\GuzzleHttp\Client::class);

    $this->tokenClient->setClient($this->guzzle);
    $this->tokenClient->setTokens($this->tokens);
    $this->client->setApiClient($this->tokenClient);
});

it('can set and get the api client', function (): void {
    $tokenClient = new TokenClient($this->credentials, 'https://test.com');

    $this->client->setApiClient($tokenClient);

    expect($this->client->getApiClient())->toBe($tokenClient);
});

it('can pass the credentials to the api client', function (): void {
    expect($this->client->getApiClient()->getCredentials())->toBe($this->credentials);
});

it('can set and get the authentication tokens of the TokenClient', function (): void {
    $tokens = new Tokens('fresh', 10, 'tokens', 42);

    $this->client->setTokens($tokens);

    expect($this->tokenClient->getTokens())->toBe($tokens)
        ->and($this->client->getTokens())->toBe($tokens);
});

it('can get a list of banks', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'institutions/', [
            'query' => [],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(200, [], '[{"id": "bank_123", "name": "Bank of Test", "bic": "TESTBIC", "transaction_total_days": 5, "countries": ["GB"], "logo": "https://gocardless.com/logo.png"}]'));

    $banks = $this->client->getBanks();

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

it('can get a list of banks limited by country', function (): void {
    $this->guzzle->shouldReceive('request')->once()
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

    $banks = $this->client->getBanks('GB');

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

it('can get a bank', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'institutions/bank123/', Mockery::any())
        ->andReturn(new GuzzleResponse(
            200,
            [],
            '{"id": "bank_123", "name": "Bank of Test", "bic": "TESTBIC", "transaction_total_days": 5, "countries": ["GB"], "logo": "https://gocardless.com/logo.png"}'
        ));

    $banks = $this->client->getBank('bank123');

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

it('can get a list of agreements', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'agreements/enduser/', [
            'query' => [
                'limit' => 10,
                'offset' => 4,
            ],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(
            200,
            [],
            '{"results": [{"id": "agreement_123", "created": "2023-06-07 12:23:00", "institution_id": "inst_123", "max_historical_days": 90, "access_valid_for_days": 90, "access_scope": ["scope_1", "scope_2"], "accepted": "2023-06-08 13:42:00"}]}'
        ));

    $agreements = $this->client->getAgreements(10, 4);

    expect($agreements)->toBeArray()
        ->and($agreements)->toHaveCount(1)
        ->and($agreements[0])->toBeInstanceOf(EndUserAgreement::class)
        ->and($agreements[0])->toMatchObject([
            'id' => 'agreement_123',
            'created' => '2023-06-07 12:23:00',
            'institutionId' => 'inst_123',
            'maxHistoricalDays' => 90,
            'accessValidForDays' => 90,
            'accessScopes' => ['scope_1', 'scope_2'],
            'accepted' => '2023-06-08 13:42:00',
        ]);
});

it('can get an agreement', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'agreements/enduser/agre123/', Mockery::any())
        ->andReturn(new GuzzleResponse(
            200,
            [],
            '{"id": "agreement_123", "created": "2023-06-07 12:23:00", "institution_id": "inst_123", "max_historical_days": 90, "access_valid_for_days": 90, "access_scope": ["scope_1", "scope_2"], "accepted": "2023-06-08 13:42:00"}'
        ));

    $agreements = $this->client->getAgreement('agre123');

    expect($agreements)->toBeInstanceOf(EndUserAgreement::class)
        ->and($agreements)->toMatchObject([
            'id' => 'agreement_123',
            'created' => '2023-06-07 12:23:00',
            'institutionId' => 'inst_123',
            'maxHistoricalDays' => 90,
            'accessValidForDays' => 90,
            'accessScopes' => ['scope_1', 'scope_2'],
            'accepted' => '2023-06-08 13:42:00',
        ]);
});

it('can create an agreement', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('POST', 'agreements/enduser/', [
            'json' => [
                'institution_id' => 'inst_123',
                'max_historical_days' => 30,
                'access_valid_for_days' => 40,
                'access_scope' => [
                    'test',
                ],
            ],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(
            200,
            [],
            '{"id": "agreement_456", "created": "2023-06-07 12:23:00", "institution_id": "inst_123", "max_historical_days": 30, "access_valid_for_days": 40, "access_scope": ["test"], "accepted": "2023-06-08 13:42:00"}'
        ));

    $agreements = $this->client->createAgreement('inst_123', 30, 40, ['test']);

    expect($agreements)->toBeInstanceOf(EndUserAgreement::class)
        ->and($agreements)->toMatchObject([
            'id' => 'agreement_456',
            'created' => '2023-06-07 12:23:00',
            'institutionId' => 'inst_123',
            'maxHistoricalDays' => 30,
            'accessValidForDays' => 40,
            'accessScopes' => ['test'],
            'accepted' => '2023-06-08 13:42:00',
        ]);
});

it('can delete an agreement', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('DELETE', 'agreements/enduser/agre123/', Mockery::any())
        ->andReturn(new GuzzleResponse(200, [], '{}'));

    expect(fn () => $this->client->deleteAgreement('agre123'))->not()->toThrow(Exception::class);
});

it('can accept an agreement', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('PUT', 'agreements/enduser/agreement_123/accept/', [
            'json' => [
                'user_agent' => 'gocardless-php',
                'ip_address' => '127.0.0.1',
            ],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(
            200,
            [],
            '{"id": "agreement_123", "created": "2023-06-07 12:23:00", "institution_id": "inst_123", "max_historical_days": 90, "access_valid_for_days": 90, "access_scope": ["test"], "accepted": "2023-06-08 13:42:00"}'
        ));

    $agreement = $this->client->acceptAgreement('agreement_123', 'gocardless-php', '127.0.0.1');

    expect($agreement)->toBeInstanceOf(EndUserAgreement::class)
        ->and($agreement)->toMatchObject([
            'id' => 'agreement_123',
            'created' => '2023-06-07 12:23:00',
            'institutionId' => 'inst_123',
            'maxHistoricalDays' => 90,
            'accessValidForDays' => 90,
            'accessScopes' => ['test'],
            'accepted' => '2023-06-08 13:42:00',
        ]);
});

it('can get a list of requisitions', function (): void {
    $requisitionData = (object) [
        "id" => "requisition_123",
        "created" => "2023-06-07 12:23:00",
        "redirect" => "http://localhost",
        "status" => "CR",
        "institution_id" => "inst_123",
        "agreement" => "agreement_123",
        "reference" => "ref_123",
        "accounts" => [
            "account_1",
            "account_2",
            "account_3",
        ],
        "user_language" => "EN",
        "link" => "https://gocardless.com",
        "ssn" => "123456789",
        "account_selection" => true,
        "redirect_immediate" => false,
    ];

    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'requisitions/', [
            'query' => [
                'limit' => 10,
                'offset' => 2,
            ],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(200, [], '{"results": [' . json_encode($requisitionData) . ']}'));

    $requisitions = $this->client->getRequisitions(10, 2);

    expect($requisitions)->toBeArray()
        ->and($requisitions)->toHaveCount(1)
        ->and($requisitions[0])->toBeInstanceOf(Requisition::class)
        ->and($requisitions[0])->toMatchObject([
            'id' => 'requisition_123',
            'created' => '2023-06-07 12:23:00',
            'redirect' => 'http://localhost',
            'status' => RequisitionStatus::CREATED,
            'institutionId' => 'inst_123',
            'agreement' => 'agreement_123',
            'reference' => 'ref_123',
            'accounts' => ['account_1', 'account_2', 'account_3'],
            'userLanguage' => 'EN',
            'link' => 'https://gocardless.com',
            'ssn' => '123456789',
            'accountSelection' => true,
            'redirectImmediate' => false,
        ]);
});

it('can get a requisition', function (): void {
    $requisitionData = (object) [
        "id" => "requisition_123",
        "created" => "2023-06-07 12:23:00",
        "redirect" => "http://localhost",
        "status" => "CR",
        "institution_id" => "inst_123",
        "agreement" => "agreement_123",
        "reference" => "ref_123",
        "accounts" => [
            "account_1",
            "account_2",
            "account_3",
        ],
        "user_language" => "EN",
        "link" => "https://gocardless.com",
        "ssn" => "123456789",
        "account_selection" => true,
        "redirect_immediate" => false,
    ];

    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'requisitions/req123/', Mockery::any())
        ->andReturn(new GuzzleResponse(200, [], json_encode($requisitionData)));

    $requisition = $this->client->getRequisition('req123');

    expect($requisition)
        ->toBeInstanceOf(Requisition::class)
        ->toMatchObject([
            'id' => 'requisition_123',
            'created' => '2023-06-07 12:23:00',
            'redirect' => 'http://localhost',
            'status' => RequisitionStatus::CREATED,
            'institutionId' => 'inst_123',
            'agreement' => 'agreement_123',
            'reference' => 'ref_123',
            'accounts' => ['account_1', 'account_2', 'account_3'],
            'userLanguage' => 'EN',
            'link' => 'https://gocardless.com',
            'ssn' => '123456789',
            'accountSelection' => true,
            'redirectImmediate' => false,
        ]);
});

it('can create a requisition', function (): void {
    $requisitionData = (object) [
        "id" => "requisition_123",
        "created" => "2023-06-07 12:23:00",
        "redirect" => "http://localhost",
        "status" => "CR",
        "institution_id" => "inst_123",
        "agreement" => "agreement_123",
        "reference" => "ref_123",
        "accounts" => [
            "account_1",
            "account_2",
            "account_3",
        ],
        "user_language" => "EN",
        "link" => "https://gocardless.com",
        "ssn" => "123456789",
        "account_selection" => false,
        "redirect_immediate" => true,
    ];

    $this->guzzle->shouldReceive('request')->once()
        ->with('POST', 'requisitions/', [
            'json' => [
                'redirect' => 'http://localhost',
                'institution_id' => 'inst_123',
                'agreement' => 'agreement_123',
                'reference' => 'ref_123',
                'user_language' => 'EN',
                'ssn' => '123456789',
                'account_selection' => false,
                'redirect_immediate' => true,
            ],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ],
        ])
        ->andReturn(new GuzzleResponse(200, [], json_encode($requisitionData)));

    $requisition = $this->client->createRequisition('http://localhost', 'inst_123', 'agreement_123', 'ref_123', 'EN', '123456789', false, true);

    expect($requisition)
        ->toBeInstanceOf(Requisition::class)
        ->toMatchObject([
            'id' => 'requisition_123',
            'created' => '2023-06-07 12:23:00',
            'redirect' => 'http://localhost',
            'status' => RequisitionStatus::CREATED,
            'institutionId' => 'inst_123',
            'agreement' => 'agreement_123',
            'reference' => 'ref_123',
            'accounts' => ['account_1', 'account_2', 'account_3'],
            'userLanguage' => 'EN',
            'link' => 'https://gocardless.com',
            'ssn' => '123456789',
            'accountSelection' => false,
            'redirectImmediate' => true,
        ]);
});

it('can delete a requisition', function (): void {
    $this->guzzle->shouldReceive('request')->once()
        ->with('DELETE', 'requisitions/req123/', Mockery::any())
        ->andReturn(new GuzzleResponse(200, [], '{}'));

    expect(fn () => $this->client->deleteRequisition('req123'))->not()->toThrow(Exception::class);
});

it('can get an account', function (): void {
    $accountData = (object) [
        "id" => "account_123",
        "created" => "2023-06-07 12:23:00",
        "last_accessed" => "2023-06-08 13:42:00",
        "iban" => "IBAN123456789",
        "institution_id" => "inst_123",
        "status" => "READY",
        "owner_name" => "Owner Name",
    ];

    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'accounts/account_123/', Mockery::any())
        ->andReturn(new GuzzleResponse(200, [], json_encode($accountData)));

    $account = $this->client->getAccount('account_123');

    expect($account)
        ->toBeInstanceOf(Metadata::class)
        ->toMatchObject([
            'id' => 'account_123',
            'created' => '2023-06-07 12:23:00',
            'lastAccessed' => '2023-06-08 13:42:00',
            'iban' => 'IBAN123456789',
            'institutionId' => 'inst_123',
            'status' => AccountStatus::READY,
            'ownerName' => 'Owner Name',
        ]);
});

it('can get an accounts balances', function (): void {
    $balanceData = (object) [
        "balanceAmount" => (object) [
            "amount" => 12.34,
            "currency" => "EUR",
        ],
        "balanceType" => 'interimBooked',
        "creditLimitIncluded" => true,
        "lastChangeDateTime" => "2023-06-07 12:23:00",
        "lastCommittedTransaction" => "transaction_1",
        "referenceDate" => "2023-06-09 14:23:00",
    ];

    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'accounts/account_123/balances/', Mockery::any())
        ->andReturn(new GuzzleResponse(200, [], '{"balances": [' . json_encode($balanceData) . ']}'));

    $balances = $this->client->getAccountBalances('account_123');

    expect($balances)->toBeArray()
        ->toHaveCount(1)
        ->and($balances[0])->toBeInstanceOf(Balance::class)
        ->toMatchObject(
            [
                'balanceAmount' => new Amount(12.34, 'EUR'),
                'balanceType' => BalanceType::INTERIM_BOOKED,
                'creditLimitIncluded' => true,
                'lastChangeDateTime' => '2023-06-07 12:23:00',
                'lastCommittedTransaction' => 'transaction_1',
                'referenceDate' => '2023-06-09 14:23:00',
            ]
        );
});

it('can get an accounts details', function (): void {
    $detailsData = (object) [
        "resourceId" => "account_123",
        "name" => "my_account",
        "displayName" => "My Account",
        "currency" => "EUR",
        "status" => 'enabled',
        'usage' => 'PRIV',
        'product' => 'account',
        'cashAccountType' => 'cash',
        'iban' => 'IBAN123456789',
        'bban' => 'BBAN123456789',
        'bic' => 'TESTBIC',
        'details' => 'Details',
        'linkedAccounts' => 'linked_accounts',
        'msisdn' => '123456789',
        'ownerName' => 'Owner Name',
        'ownerAddressUnstructured' => 'Owner Address',
    ];

    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'accounts/account_123/details/', Mockery::any())
        ->andReturn(new GuzzleResponse(200, [], '{"account": ' . json_encode($detailsData) . '}'));

    $details = $this->client->getAccountDetails('account_123');

    expect($details)
        ->toBeInstanceOf(Details::class)
        ->toMatchObject(
            [
                'resourceId' => 'account_123',
                'name' => 'my_account',
                'displayName' => 'My Account',
                'currency' => 'EUR',
                'status' => BankStatus::ENABLED,
                'usage' => Usage::PRIVATE,
                'product' => 'account',
                'cashAccountType' => 'cash',
                'iban' => 'IBAN123456789',
                'bban' => 'BBAN123456789',
                'bic' => 'TESTBIC',
                'details' => 'Details',
                'linkedAccounts' => 'linked_accounts',
                'msisdn' => '123456789',
                'ownerName' => 'Owner Name',
                'ownerAddressUnstructured' => 'Owner Address',
            ]
        );
});

it('can get an accounts transactions', function (): void {
    $transactionData = (object) [
        "additionalInformation" => "Additional Information",
        "additionalInformationStructured" => "Additional Information Structured",
        "balanceAfterTransaction" => (object) [
            "balanceAmount" => (object) [
                "amount" => 12.34,
                "currency" => "EUR",
            ],
            "balanceType" => 'interimBooked',
            "creditLimitIncluded" => true,
            "lastChangeDateTime" => "2023-06-07 12:23:00",
            "lastCommittedTransaction" => "transaction_1",
            "referenceDate" => "2023-06-09 14:23:00",
        ],
        "bankTransactionCode" => 'banktransactioncode',
        'bookingDate' => '2023-06-08',
        'bookingDateTime' => '2023-06-08 12:23:00',
        'checkId' => 'check_id',
        'creditorAccount' => (object) [
            'name' => 'Test creditor',
        ],
        'creditorAgent' => 'creditor_agent',
        'creditorId' => 'creditor_id',
        'creditorName' => 'creditor_name',
        'currencyExchange' => [
            'test',
        ],
        'debtorAccount' => (object) [
            'name' => 'Test debtor',
        ],
        'debtorAgent' => 'debtor_agent',
        'debtorName' => 'debtor_name',
        'endToEndId' => 'end_to_end_id',
        'entryReference' => 'entry_reference',
        'internalTransactionId' => 'internal_transaction_id',
        'mandateId' => 'mandate_id',
        'merchantCategoryCode' => 'merchant_category_code',
        'proprietaryBankTransactionCode' => 'proprietary_bank_transaction_code',
        'purposeCode' => 'purpose_code',
        'remittanceInformationStructured' => 'remittance_information_structured',
        'remittanceInformationStructuredArray' => [
            'structured',
        ],
        'remittanceInformationUnstructured' => 'remittance_information_unstructured',
        'remittanceInformationUnstructuredArray' => [
            'unstructured',
        ],
        'transactionAmount' => (object) [
            'amount' => 56.78,
            'currency' => 'EUR',
        ],
        'transactionId' => 'transaction_id',
        'ultimateCreditor' => 'ultimate_creditor',
        'ultimateDebtor' => 'ultimate_debtor',
        'valueDate' => '2023-06-08',
        'valueDateTime' => '2023-06-08 12:23:00',
    ];

    $this->guzzle->shouldReceive('request')->once()
        ->with('GET', 'accounts/account_123/transactions/', [
            'query' => [
                'from_date' => '2023-06-08',
                'to_date' => '2023-06-09',
            ],
            'headers' => [
                'Authorization' => "Bearer access",
                'Accept' => 'application/json',
            ]
        ])
        ->andReturn(new GuzzleResponse(200, [], '{"transactions": {"booked": [' . json_encode($transactionData) . '], "pending": [' . json_encode($transactionData) . ']}}'));

    $transactions = $this->client->getAccountTransactions('account_123', '2023-06-08', '2023-06-09');

    expect($transactions->booked)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($transactions->booked[0])
        ->toBeInstanceOf(Transaction::class)
        ->toMatchObject([
            "additionalInformation" => "Additional Information",
            "additionalInformationStructured" => "Additional Information Structured",
            "balanceAfterTransaction" => new Balance(
                new Amount(12.34, 'EUR'),
                BalanceType::INTERIM_BOOKED,
                true,
                '2023-06-07 12:23:00',
                'transaction_1',
                '2023-06-09 14:23:00'
            ),
            "bankTransactionCode" => 'banktransactioncode',
            'bookingDate' => '2023-06-08',
            'bookingDateTime' => '2023-06-08 12:23:00',
            'checkId' => 'check_id',
            'creditorAccount' => (object)[
                'name' => 'Test creditor',
            ],
            'creditorAgent' => 'creditor_agent',
            'creditorId' => 'creditor_id',
            'creditorName' => 'creditor_name',
            'currencyExchange' => [
                'test',
            ],
            'debtorAccount' => (object)[
                'name' => 'Test debtor',
            ],
            'debtorAgent' => 'debtor_agent',
            'debtorName' => 'debtor_name',
            'endToEndId' => 'end_to_end_id',
            'entryReference' => 'entry_reference',
            'internalTransactionId' => 'internal_transaction_id',
            'mandateId' => 'mandate_id',
            'merchantCategoryCode' => 'merchant_category_code',
            'proprietaryBankTransactionCode' => 'proprietary_bank_transaction_code',
            'purposeCode' => 'purpose_code',
            'remittanceInformationStructured' => 'remittance_information_structured',
            'remittanceInformationStructuredArray' => [
                'structured',
            ],
            'remittanceInformationUnstructured' => 'remittance_information_unstructured',
            'remittanceInformationUnstructuredArray' => [
                'unstructured',
            ],
            'transactionAmount' => new Amount(56.78, 'EUR'),
            'transactionId' => 'transaction_id',
            'ultimateCreditor' => 'ultimate_creditor',
            'ultimateDebtor' => 'ultimate_debtor',
            'valueDate' => '2023-06-08',
            'valueDateTime' => '2023-06-08 12:23:00',
        ])
        ->and($transactions->pending)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($transactions->pending[0])
        ->toBeInstanceOf(Transaction::class)
        ->toMatchObject([
            "additionalInformation" => "Additional Information",
            "additionalInformationStructured" => "Additional Information Structured",
            "balanceAfterTransaction" => new Balance(
                new Amount(12.34, 'EUR'),
                BalanceType::INTERIM_BOOKED,
                true,
                '2023-06-07 12:23:00',
                'transaction_1',
                '2023-06-09 14:23:00'
            ),
            "bankTransactionCode" => 'banktransactioncode',
            'bookingDate' => '2023-06-08',
            'bookingDateTime' => '2023-06-08 12:23:00',
            'checkId' => 'check_id',
            'creditorAccount' => (object)[
                'name' => 'Test creditor',
            ],
            'creditorAgent' => 'creditor_agent',
            'creditorId' => 'creditor_id',
            'creditorName' => 'creditor_name',
            'currencyExchange' => [
                'test',
            ],
            'debtorAccount' => (object)[
                'name' => 'Test debtor',
            ],
            'debtorAgent' => 'debtor_agent',
            'debtorName' => 'debtor_name',
            'endToEndId' => 'end_to_end_id',
            'entryReference' => 'entry_reference',
            'internalTransactionId' => 'internal_transaction_id',
            'mandateId' => 'mandate_id',
            'merchantCategoryCode' => 'merchant_category_code',
            'proprietaryBankTransactionCode' => 'proprietary_bank_transaction_code',
            'purposeCode' => 'purpose_code',
            'remittanceInformationStructured' => 'remittance_information_structured',
            'remittanceInformationStructuredArray' => [
                'structured',
            ],
            'remittanceInformationUnstructured' => 'remittance_information_unstructured',
            'remittanceInformationUnstructuredArray' => [
                'unstructured',
            ],
            'transactionAmount' => new Amount(56.78, 'EUR'),
            'transactionId' => 'transaction_id',
            'ultimateCreditor' => 'ultimate_creditor',
            'ultimateDebtor' => 'ultimate_debtor',
            'valueDate' => '2023-06-08',
            'valueDateTime' => '2023-06-08 12:23:00',
        ]);
});
