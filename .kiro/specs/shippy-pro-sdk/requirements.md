# Requirements Document

## Introduction

This document specifies the requirements for the ShippyPro SDK (PHP), a typed, object-oriented PHP library that exposes the [ShippyPro API](https://api.shippypro.com/docs) as native PHP objects. The initial scope covers exactly two endpoints: `POST /v1/Ping` (connection/auth check of the v1 API) and `GET /v2/status` (status check of the v2 API). Additional endpoints will be added in later iterations.

Structurally and conventionally, the SDK follows three existing templates in the workspace: `ergosoft-sdk` (Value Object / domain conventions and the "everything as an object, no arrays to the outside" style), `shipcloud-sdk` (Factory/Api/Http client pattern and API-key-based authentication), and `teppana/aws-sdk` (examples / Docker structure). The API versioning strategy is: where a v2 of an endpoint exists, v2 is implemented; for everything else, v1 remains in use.

The exact request/response schemas of `POST /v1/Ping` and `GET /v2/status` (field names, types, HTTP methods, authentication) are bindingly defined by the authoritative OpenAPI documents `v1.json` and `v2.json` in the project root. These documents are the single source of truth for schemas and `security`; earlier preliminary assumptions have been superseded. Content derived from those documents has been rephrased for compliance.

## Glossary

- **SDK**: The PHP library specified here, which exposes the ShippyPro API as a typed, object-oriented PHP API.
- **Value_Object**: A `final readonly` object that encapsulates a primitive domain value and validates it in the constructor (e.g. `ApiKey`, `BaseUrl`, `RequestTimeout`, `StatusCode`).
- **Mapper**: A class that translates JSON to and from domain objects at the serialization boundary; the only place where arrays are allowed internally.
- **Bearer_Auth**: Authentication via the header `Authorization: Bearer <API-Key>`.
- **OpenAPI_Documents**: The files `v1.json` and `v2.json` in the project root, which serve as the binding source for request/response schemas and `security`.
- **Http_Client**: The `Http\Client` class that encapsulates Guzzle and authentication and contains no domain logic.
- **Ping_Api**: The `Api\PingApi` class that orchestrates the `POST /v1/Ping` request and its mapping.
- **Status_Api**: The `Api\StatusApi` class that orchestrates the `GET /v2/status` request and its mapping.
- **SDK_Factory**: The `ShippyProFactory` class that wires up the client, mappers and Api classes.
- **Configuration**: The `ShippyProConfiguration` interface and its implementation `SimpleShippyProConfiguration`, which provide the API key, base URL and request timeout.

## Requirements

### Requirement 1: Technical Constraints

**User Story:** As an SDK maintainer, I want the project to use a fixed set of technical constraints and a defined structure, so that the SDK is reproducible, consistent with the existing templates, and runs on supported PHP and container tooling.

#### Acceptance Criteria

1. THE SDK SHALL require PHP with a minimum version constraint of `>=8.5` and a minimum patch level of PHP 8.5.12.
2. THE SDK SHALL use Guzzle (`guzzlehttp/guzzle`) as the HTTP client.
3. THE SDK SHALL use PSR-4 autoloading under the namespace `DjThossi\ShippyProSdk\`.
4. THE SDK SHALL declare `declare(strict_types=1)` in every PHP source file.
5. THE SDK SHALL be licensed under the MIT license.
6. THE SDK SHALL provide a `docker-compose.yml` using the image `ronisaha/php-8-debian:cli-8.5.12` with Composer and Xdebug preinstalled.
7. THE SDK SHALL use a project structure analogous to `ergosoft-sdk`, including an `examples/` directory, a `src/` directory with `Api/`, `Domain/`, `Exception/`, `Http/` and `Mapper/` subdirectories, and a `tests/` directory with `Unit/` and `Integration/` subdirectories.
8. THE SDK SHALL document that the published tags of `ronisaha/php-8-debian:cli-8.5.12` currently cover only `linux/amd64` and that on `arm64` (Apple Silicon) the image runs via emulation.

### Requirement 2: Authentication

**User Story:** As an SDK consumer, I want authentication handled correctly per API version, so that authenticated and public endpoints behave as defined by the OpenAPI documents.

#### Acceptance Criteria

1. THE SDK SHALL model the API key as an `ApiKey` Value_Object.
2. WHEN the SDK calls a v1 endpoint, THE SDK SHALL set the header `Authorization: Bearer <API-Key>` and send a POST request with a JSON body of the form `{"Method": "<MethodName>", "Params": <ParamsObject>}`.
3. WHEN the SDK calls `GET /v2/status`, THE SDK SHALL send no `Authorization` header, consistent with `security: []` in the OpenAPI_Documents.
4. THE SDK SHALL use the base URL `https://api.shippypro.com` with the version as part of the path (`/v1/...`, `/v2/...`).
5. WHERE a partner identifier is configured, THE SDK SHALL send an optional header `Referer: <partner-name>` as a consistent lowercase identifier.
6. IF the API responds with `429 Too Many Requests` because the rate limit of 20 calls per 10 seconds is exceeded, THEN THE SDK SHALL expose the `429` status through the `StatusCode`.

### Requirement 3: Configuration and Factory

**User Story:** As an SDK consumer, I want a configuration interface and a factory, so that I can create API clients with sensible defaults while only providing the mandatory API key.

#### Acceptance Criteria

1. THE Configuration SHALL define a `ShippyProConfiguration` interface providing `getApiKey(): ApiKey`, `getBaseUrl(): BaseUrl` with default `https://api.shippypro.com`, and `getRequestTimeout(): RequestTimeout` with default 10 seconds.
2. THE Configuration SHALL provide `SimpleShippyProConfiguration` as a `final readonly` implementation in which only the `ApiKey` is mandatory.
3. THE SDK_Factory SHALL provide `createPingApi(): PingApi` and `createStatusApi(): StatusApi` and SHALL wire up the client and mappers internally.

### Requirement 4: Endpoint POST /v1/Ping

**User Story:** As an SDK consumer, I want a typed Ping endpoint, so that I can verify v1 connectivity and authentication and receive a typed result.

#### Acceptance Criteria

1. WHEN `PingApi::ping()` is invoked, THE Ping_Api SHALL send a Bearer-authenticated POST request to `/v1/Ping` with the JSON body `{"Method": "Ping", "Params": {}}`, where `PingParams` is an empty object with `additionalProperties: false`.
2. WHEN the Ping request completes, THE Ping_Api SHALL return a `PingResponse` containing the `StatusCode` and a typed `PingResponseBody`.
3. THE `PingResponseBody` SHALL provide a typed boolean field `result` mapped from the JSON field `Result`.
4. WHEN the API returns a `200` response, THE `PingResponseBody` SHALL contain no fields other than `result`.
5. IF the API returns an error code of `400`, `401`, `403`, `404`, `429` or `500`, THEN THE Ping_Api SHALL make the error code accessible through the `StatusCode`.

### Requirement 5: Endpoint GET /v2/status

**User Story:** As an SDK consumer, I want a typed status endpoint, so that I can check the v2 API health without authentication.

#### Acceptance Criteria

1. WHEN `StatusApi::status()` is invoked, THE Status_Api SHALL send a GET request to `/v2/status` without an `Authorization` header.
2. WHEN the status request completes, THE Status_Api SHALL return a `StatusResponse` containing the `StatusCode` and a typed `StatusResponseBody`.
3. THE `StatusResponseBody` SHALL provide a required typed field `status` of type string with the constant value `HEALTHY` and a required typed field `version` of type string (for example `"2.0.0"`).

### Requirement 6: Value Objects

**User Story:** As an SDK maintainer, I want domain values encapsulated as validated Value Objects, so that invalid values are rejected early and HTTP status codes are easy to inspect.

#### Acceptance Criteria

1. IF an `ApiKey` is constructed with an empty value, THEN THE SDK SHALL throw an `InvalidApiKeyException`.
2. IF a `BaseUrl` is constructed with a value that is not a valid URL (validated like `ergosoft-sdk`), THEN THE SDK SHALL throw an `InvalidBaseUrlException`.
3. IF a `RequestTimeout` is constructed with a value that is not a positive number of seconds, THEN THE SDK SHALL throw an `InvalidRequestTimeoutException`.
4. THE `StatusCode` SHALL wrap an HTTP status code and provide the helper methods `isSuccessful`, `isOk`, `isBadRequest`, `isForbidden`, `isNotFound`, `isTooManyRequests` and `isServerError`.
5. THE SDK SHALL implement all Value Objects as `final readonly` with validation in the constructor.

### Requirement 7: Error Handling

**User Story:** As an SDK consumer, I want clear and expressive error handling, so that I can diagnose validation failures, missing fields and HTTP errors.

#### Acceptance Criteria

1. IF a Value_Object validation fails, THEN THE SDK SHALL throw a dedicated expressive exception from `src/Exception/`.
2. IF a required field is missing during mapping, THEN THE Mapper SHALL throw a `MissingRequiredFieldsException` naming the missing fields (analogous to `ergosoft-sdk`).
3. IF the API returns an HTTP error such as `401`, `403`, `429` or a `5xx` code, THEN THE SDK SHALL expose the error through the `StatusCode` in the response object.

### Requirement 8: Separation of Responsibilities and No-Arrays Principle

**User Story:** As an SDK maintainer, I want strict separation of responsibilities and no arrays in the public API, so that the SDK stays typed, maintainable and consistent with `ergosoft-sdk`.

#### Acceptance Criteria

1. THE Http_Client SHALL encapsulate Guzzle and authentication and SHALL contain no domain logic.
2. THE SDK SHALL provide one Api class per endpoint that orchestrates the request and the mapping.
3. THE Mapper SHALL translate JSON to and from domain objects and SHALL be the only place where arrays are used internally at the serialization boundary.
4. THE SDK SHALL return only typed objects externally and SHALL never return raw arrays.
5. THE SDK SHALL model inputs as Value Objects or typed objects and SHALL not expose associative arrays in the public API.

### Requirement 9: Symfony Integration

**User Story:** As a Symfony developer, I want the SDK to integrate via autowiring, so that I can use it in a Symfony application without a dedicated bundle.

#### Acceptance Criteria

1. THE SDK SHALL be framework-agnostic and integrable into Symfony via autowiring (analogous to the `README.md` of `ergosoft-sdk`).
2. THE SDK SHALL provide a `README.md` documenting the `services.yaml` configuration, including parameters for API key and base URL and autowiring of the `Http\` and `Api\` namespaces.

### Requirement 10: Examples

**User Story:** As an SDK consumer, I want runnable examples, so that I can see how to call each endpoint end-to-end.

#### Acceptance Criteria

1. THE SDK SHALL provide `examples/ping.php` that demonstrates a Ping call end-to-end.
2. THE SDK SHALL provide `examples/status.php` that demonstrates the v2 status call without an API key, because the endpoint is public.
3. THE SDK SHALL make each example runnable via its own Makefile target (`make examples-ping`, `make examples-status`).

### Requirement 11: Tests

**User Story:** As an SDK maintainer, I want comprehensive strict tests, so that the SDK behavior is verified and regressions are caught.

#### Acceptance Criteria

1. THE SDK SHALL provide 100% unit test coverage for `src/` in `tests/Unit` measured via the PHPUnit coverage report.
2. THE SDK SHALL generate an HTML coverage report into `html-coverage/`.
3. THE Api tests SHALL mock the Http_Client and verify the endpoint path, authentication behavior, status code handling and mapping (analogous to `TestCommunicationsApiTest`).
4. THE Value Object tests SHALL cover valid and invalid inputs including the respective exceptions.
5. THE Mapper tests SHALL cover complete, minimal and malformed JSON inputs.
6. WHEN a valid domain object is serialized and then parsed, THE Mapper SHALL produce an equivalent object (round-trip property).
7. THE SDK SHALL configure PHPUnit to run strictly with `failOnWarning`, `failOnRisky`, `failOnDeprecation` and `failOnNotice`.

### Requirement 12: Quality and Tooling

**User Story:** As an SDK maintainer, I want quality and tooling configuration, so that code style and developer workflows are consistent with the templates.

#### Acceptance Criteria

1. THE SDK SHALL provide a `.php-cs-fixer.php` matching the template rule set (`@Symfony`, `@Symfony:risky`, `declare_strict_types`, `global_namespace_import` and related rules).
2. THE SDK SHALL provide a `Makefile` with at least the targets `setup`, `phpLatest-composer-update`, `phpLatest-test`, `phpLatest-test-fast`, `php-cs-fix` and the `examples-*` targets.
3. THE SDK SHALL provide `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md` and `LICENSE` analogous to the templates.

### Requirement 13: Non-Goals for This Iteration

**User Story:** As a stakeholder, I want the scope of this iteration bounded, so that the team delivers a focused first version.

#### Acceptance Criteria

1. THE SDK SHALL implement only the `/v1/Ping` and `/v2/status` endpoints and no other endpoints in this iteration.
2. THE SDK SHALL NOT provide a dedicated Symfony bundle and SHALL instead document autowiring only.
3. THE SDK SHALL NOT provide asynchronous or parallel requests in this iteration.
4. THE SDK SHALL NOT provide built-in rate limiting and SHALL instead document the limit of 20 calls per 10 seconds.
