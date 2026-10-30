<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use DjThossi\ShippyProSdk\Domain\ApiKey;
use DjThossi\ShippyProSdk\ShippyProFactory;
use DjThossi\ShippyProSdk\SimpleShippyProConfiguration;

// Initialize the ShippyPro SDK with YOUR API key. The base URL
// (https://api.shippypro.com) and request timeout (10 seconds) default.
$configuration = new SimpleShippyProConfiguration(new ApiKey('YOUR_API_KEY'));
$factory = new ShippyProFactory($configuration);
$api = $factory->createPingApi();

try {
    $response = $api->ping();

    echo "Ping request completed.\n";
    echo "Response Status Code: {$response->statusCode->value}\n";

    if ($response->hasError()) {
        echo "Error: {$response->error->detail}\n";
    }

    if ($response->statusCode->isOk() && $response->responseBody->result === true) {
        echo "Result: true\n";
        echo "Success! v1 connectivity and authentication are working (200 OK)\n";
    } elseif ($response->statusCode->isOk() && $response->hasError()) {
        echo 'You have an error: ' . $response->error->detail;
    } elseif ($response->statusCode->isOk() && $response->responseBody->result === null) {
        // ShippyPro answers a failed Ping with HTTP 200 and an {"Error": ...}
        // body that carries no Result field, so result is null here.
        echo "The API returned 200 but no Ping result — check your API key.\n";
    } elseif ($response->statusCode->isForbidden()) {
        echo "Forbidden (403) — check your API key permissions\n";
    } elseif ($response->statusCode->isTooManyRequests()) {
        echo "Too Many Requests (429) — rate limit of 20 calls / 10 seconds exceeded\n";
    } elseif ($response->statusCode->isServerError()) {
        echo "Server Error (500)\n";
    } else {
        echo "Unexpected status code: {$response->statusCode->value}\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
