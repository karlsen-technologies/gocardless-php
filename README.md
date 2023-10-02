# Unofficial GoCardless PHP Library

This is an unofficial PHP library for the [GoCardless Bank Account Data API](https://gocardless.com/bank-account-data/).

It provides the same endpoints and uses the same arguments as the official api, see a full list of endpoints [here](https://developer.gocardless.com/bank-account-data/endpoints).

## Requirements

- **PHP:** 8.2 or above

## Installation

You can install the library using composer:
```bash
composer require karlsen-technologies/gocardless-php
```

## Usage

In order to use this library you first need to setup an application and get your credentials from the [GoCardless Dashboard](https://bankaccountdata.gocardless.com/).

### Creating a client

```php
use KarlsenTechnologies\GoCardless\DataObjects\Api\Credentials;
use KarlsenTechnologies\GoCardless\Client;

$credentials = new Credentials('secret_id', 'secret_key');
$client = new Client($credentials);
```

### Using a client

See the official [API documentation](https://developer.gocardless.com/bank-account-data/endpoints) for a more detailed explanation of the endpoints and their arguments.

```php
use KarlsenTechnologies\GoCardless\Client;

$client = new Client(...);

$client->getBanks();
$client->getBanks('COUNTRY');
$client->getBank('INSTITUTION_ID');

$client->getAgreements(); // Default limit of 100, offset of 0
$client->getAgreements(100, 10);  // Limit of 100, offset of 10
$client->getAgreement('AGREEMENT_ID');
$client->createAgreement('INSTIUTION_ID', 90, 90, ['SCOPES']);
$client->deleteAgreement('AGREEMENT_ID');
$client->acceptAgreement('AGREEMENT_ID', 'GoCardless-PHP', '127.0.0.1');

$client->getRequisitions(); // Default limit of 100, offset of 0
$client->getRequisitions(100, 10);  // Limit of 100, offset of 10
$client->getRequisition('REQUISITION_ID');
$client->createRequisition('http://localhost', 'INSTIUTION_ID', 'AGREEMENT_ID', 'REFRENCE', 'LANGUAGE', 'SSN', true, true);
$client->deleteRequisition('REQUISITION_ID');

$client->getAccount('ACCOUNT_ID');
$client->getAccountBalances('ACCOUNT_ID'); 
$client->getAccountDetails('ACCOUNT_ID');
$client->getAccountTransactions('ACCOUNT_ID', 'FROM_DATE', 'TO_DATE');
```

### Getting and setting the access and refresh tokens

If you want to get and set the authentication tokens you may do so like this.
This is useful if you want to cache the tokens and reuse them so you don't have to authenticate every time you want to use the api.

**NOTE:** At the moment the library automatically authenticates when you try to use the API. There is currently no way to force the library to authenticate without using the API.

```php
use KarlsenTechnologies\GoCardless\DataObjects\Api\Tokens;

$client = new Client(...);

$tokens = $client->getTokens();

$newTokens = new Tokens('access_token', 3600, 'refresh_token', 86400);

$client->setTokens($newTokens);
```

## Testing

Run the tests using:
```bash
composer test
```

## License

This library is licensed under the [MIT License](LICENSE.md).
