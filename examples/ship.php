<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\Domain\CarrierOption;
use DjThossi\ShippyProSdk\Domain\CarrierOptionCollection;
use DjThossi\ShippyProSdk\Domain\CarrierOptionName;
use DjThossi\ShippyProSdk\Domain\Cn22Item;
use DjThossi\ShippyProSdk\Domain\Cn22ItemCollection;
use DjThossi\ShippyProSdk\Domain\CountryCode;
use DjThossi\ShippyProSdk\Domain\CustomInfoContentsType;
use DjThossi\ShippyProSdk\Domain\Customs;
use DjThossi\ShippyProSdk\Domain\Incoterm;
use DjThossi\ShippyProSdk\Domain\MoneyAmount;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelCollection;
use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\ShippyProFactory;
use DjThossi\ShippyProSdk\SimpleShippyProConfiguration;

// A cross-border shipment (IT -> US) with customs (CN22) data and a UPS carrier option.
$configuration = new SimpleShippyProConfiguration(new ApiKey('YOUR_API_KEY'));
$factory = new ShippyProFactory($configuration);
$shipApi = $factory->createShipApi();

$from = new Address('Mario Rossi', 'ShippyPro', 'Via Napoli 1', '', 'Roma', 'RM', '00100', new CountryCode('IT'), '+390612345678', 'sender@example.com');
$to = new Address('John Doe', '', '123 Main St', 'Suite 4', 'Park City', 'UT', '84060', new CountryCode('US'), '+15551231234', 'recipient@example.com');

$customs = new Customs(
    Incoterm::Dap,
    null,
    CustomInfoContentsType::Merchandise,
    new Cn22ItemCollection(
        new Cn22Item('T-Shirt', 0.3, 2, 19.99, new CountryCode('IT'), 'EUR', '610910', null, 'TSHIRT-RED-M', 'TS-1000-M'),
    ),
);

$request = new ShipRequest(
    toAddress: $to,
    fromAddress: $from,
    parcels: new ParcelCollection(new Parcel(20.0, 15.0, 10.0, 0.6)),
    transactionId: 'ORDER2365',
    contentDescription: 'Clothing',
    carrierName: 'UPS',
    carrierService: 'EXPRESS',
    carrierId: 23,
    totalValue: new MoneyAmount('39.98', 'EUR'),
    customs: $customs,
    carrierOptions: new CarrierOptionCollection(new CarrierOption(CarrierOptionName::UPS_DANGEROUS_GOODS, '0')),
);

try {
    $response = $shipApi->ship($request);

    echo "Ship request completed.\n";
    echo "Response Status Code: {$response->statusCode->value}\n";

    if ($response->headers->getRequestId() !== null) {
        echo "Request ID: {$response->headers->getRequestId()}\n";
    }

    if ($response->hasError()) {
        echo "Error: {$response->error->detail}\n";
    } elseif ($response->responseBody !== null && !$response->responseBody->hasBusinessError()) {
        echo "Success! New order ID: {$response->responseBody->newOrderId}\n";
        foreach ($response->responseBody->labelUrls->all() as $url) {
            echo "Label: {$url}\n";
        }
    } elseif ($response->responseBody !== null) {
        echo "Business error: {$response->responseBody->errorMessage}\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
