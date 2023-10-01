<?php

declare(strict_types=1);

namespace KarlsenTechnologies\GoCardless\DataObjects;

use KarlsenTechnologies\GoCardless\DataObjects\Account\Amount;
use KarlsenTechnologies\GoCardless\DataObjects\Account\Balance;

/**
 * {#826
 * +"transactionId": "2023093001723008-1",
 * +"bookingDate": "2023-09-30",
 * +"valueDate": "2023-09-30",
 * +"transactionAmount": {#856
 * +"amount": "-15.00",
 * +"currency": "EUR",
 * },
 * +"remittanceInformationUnstructured": "PAYMENT Alderaan Coffe",
 * +"bankTransactionCode": "PMNT",
 * },
 * {#832
 * +"transactionId": "2023093001723007-1",
 * +"bookingDate": "2023-09-30",
 * +"valueDate": "2023-09-30",
 * +"transactionAmount": {#822
 * +"amount": "45.00",
 * +"currency": "EUR",
 * },
 * +"debtorName": "MON MOTHMA",
 * +"debtorAccount": {#818
 * +"iban": "GL0610500000010500",
 * },
 * +"remittanceInformationUnstructured": "For the support of Restoration of the Republic foundation",
 * +"bankTransactionCode": "PMNT",
 * },
 */
class Transaction
{
    public function __construct(
        public ?string $additionalInformation,
        public ?string $additionalInformationStructured,
        public ?Balance $balanceAfterTransaction,
        public ?string $bankTransactionCode,
        public ?string $bookingDate,
        public ?string $bookingDateTime,
        public ?string $checkId,
        public ?object $creditorAccount,
        public mixed $creditorAgent,
        public ?string $creditorId,
        public ?string $creditorName,
        public ?array $currencyExchange,
        public ?object $debtorAccount,
        public mixed $debtorAgent,
        public ?string $debtorName,
        public ?string $endToEndId,
        public ?string $entryReference,
        public ?string $internalTransactionId,
        public ?string $mandateId,
        public ?string $merchantCategoryCode,
        public ?string $proprietaryBankTransactionCode,
        public mixed $purposeCode,
        public ?string $remittanceInformationStructured,
        public ?array $remittanceInformationStructuredArray,
        public ?string $remittanceInformationUnstructured,
        public ?array $remittanceInformationUnstructuredArray,
        public Amount $transactionAmount,
        public ?string $transactionId,
        public ?string $ultimateCreditor,
        public ?string $ultimateDebtor,
        public ?string $valueDate,
        public ?string $valueDateTime,
    ) {
    }

    public static function fromApi(object $response): Transaction
    {
        return new Transaction(
            $response->additionalInformation ?? null,
            $response->additionalInformationStructured ?? null,
            isset($response->balanceAfterTransaction) ? Balance::fromApi($response->balanceAfterTransaction) : null,
            $response->bankTransactionCode ?? null,
            $response->bookingDate ?? null,
            $response->bookingDateTime ?? null,
            $response->checkId ?? null,
            $response->creditorAccount ?? null,
            $response->creditorAgent ?? null,
            $response->creditorId ?? null,
            $response->creditorName ?? null,
            $response->currencyExchange ?? null,
            $response->debtorAccount ?? null,
            $response->debtorAgent ?? null,
            $response->debtorName ?? null,
            $response->endToEndId ?? null,
            $response->entryReference ?? null,
            $response->internalTransactionId ?? null,
            $response->mandateId ?? null,
            $response->merchantCategoryCode ?? null,
            $response->proprietaryBankTransactionCode ?? null,
            $response->purposeCode ?? null,
            $response->remittanceInformationStructured ?? null,
            $response->remittanceInformationStructuredArray ?? null,
            $response->remittanceInformationUnstructured ?? null,
            $response->remittanceInformationUnstructuredArray ?? null,
            Amount::fromApi($response->transactionAmount),
            $response->transactionId ?? null,
            $response->ultimateCreditor ?? null,
            $response->ultimateDebtor ?? null,
            $response->valueDate ?? null,
            $response->valueDateTime ?? null,
        );
    }
}
