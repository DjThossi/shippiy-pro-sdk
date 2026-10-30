# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial project structure analogous to `ergosoft-sdk` / `shipcloud-sdk`.
- Support for the `POST /v1/Ping` endpoint (Bearer-authenticated connection/auth check).
- Support for the `GET /v2/status` endpoint (public, unauthenticated health check).
- Typed access to HTTP response headers via `ResponseHeaders` (`getRequestId()`, `getRateLimit()`, generic `get()`/`has()`/`all()`) and a `RateLimit` value object that reads both the legacy `X-RateLimit-*` headers and the IETF-draft `RateLimit` / `RateLimit-Policy` headers.
- A unified `ApiError` error container (with `FieldError` for field-level details) that normalizes v1 body errors (`{"Error": ...}` and `{"Result":"NOK","ErrorMessage":...}`) and v2 RFC 9457 `ProblemDetail` responses into one shape.
- `PingResponse` and `StatusResponse` now expose `headers` (always) and `error` (nullable, with `hasError()`), so callers can reach the request id, rate-limit state and any API error regardless of endpoint version.

### Fixed
- `PingApi::ping()` no longer throws `MissingRequiredFieldsException` when the API answers with HTTP 200 but no `Result` field (for example `{"Error":"Authentication Failed"}` on a failed authentication). Per `v1.json` the `Result` field is optional, so an absent value now maps to `PingResponseBody::$result === null` instead of raising an exception.

### Changed
- `PingResponseBody::$result` is now nullable (`?bool`). It is `null` when the API omits the `Result` field; callers check `$result === true` for a successful Ping.
- `PingResponse` and `StatusResponse` constructors now take `ResponseHeaders` as the second argument and an optional `ApiError` as the last argument. `StatusResponse::$responseBody` is now nullable (`null` on error / non-200 responses).
- `PingApi` and `StatusApi` constructors now additionally require a `HeaderMapper` and an `ErrorMapper` (wired automatically by `ShippyProFactory`).
- Dev dependencies updated to their latest versions: `phpunit/phpunit` to `^13.4` (from `^11.5`), `friendsofphp/php-cs-fixer` to `^3.95`, and `giorgiosironi/eris` to `^1.3`. The `phpunit.xml.dist` schema was migrated to 13.4, and the Api tests now use test stubs (instead of mocks without expectations) to satisfy PHPUnit 13's stricter mock checks.

### Removed

### Breaking Changes

[unreleased]: https://github.com/DjThossi/shippy-pro-sdk/compare/HEAD
