<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\ShippyProFactory;
use DjThossi\ShippyProSdk\SimpleShippyProConfiguration;

// GET /v2/status is a public endpoint and needs no authentication. The SDK
// still requires an ApiKey to be configured, but it is never sent for this
// call, so any non-empty placeholder works here.
$configuration = new SimpleShippyProConfiguration(new ApiKey('not-required-for-status'));
$factory = new ShippyProFactory($configuration);
$api = $factory->createStatusApi();

try {
    $response = $api->status();

    echo "Status request completed.\n";
    echo "Response Status Code: {$response->statusCode->value}\n";

    if ($response->headers->getRequestId() !== null) {
        echo "Request ID: {$response->headers->getRequestId()}\n";
    }

    if ($response->hasError()) {
        echo "Error: {$response->error->detail}\n";
    } elseif ($response->statusCode->isOk() && $response->responseBody !== null) {
        echo "Status: {$response->responseBody->status}\n";
        echo "Version: {$response->responseBody->version}\n";
        echo "Success! The v2 API is healthy.\n";
    } else {
        echo "Unexpected status code: {$response->statusCode->value}\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
