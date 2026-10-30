<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Mapper;

use function array_key_exists;

use DjThossi\ShippyProSdk\Domain\PingResponseBody;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final readonly class PingMapper
{
    public const string METHOD = 'Ping';
    public const string FIELD_METHOD = 'Method';
    public const string FIELD_PARAMS = 'Params';
    public const string FIELD_RESULT = 'Result';

    public function encodeRequest(): string
    {
        return json_encode([
            self::FIELD_METHOD => self::METHOD,
            self::FIELD_PARAMS => (object) [],
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * The `Result` field is optional per v1.json, so a missing field maps to
     * a null result rather than an error. ShippyPro reports a failed Ping with
     * HTTP 200 and an `{"Error": ...}` body that carries no `Result`.
     *
     * @param array<string, mixed> $data
     */
    public function mapResponseBody(array $data): PingResponseBody
    {
        if (!array_key_exists(self::FIELD_RESULT, $data)) {
            return new PingResponseBody(null);
        }

        return new PingResponseBody((bool) $data[self::FIELD_RESULT]);
    }
}
