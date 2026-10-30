<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Mapper;

use function array_key_exists;

use DjThossi\ShippyProSdk\Domain\ApiError;
use DjThossi\ShippyProSdk\Domain\FieldError;

use function is_array;
use function is_string;
use function trim;

final readonly class ErrorMapper
{
    // v1 envelope error fields.
    public const string FIELD_ERROR = 'Error';
    public const string FIELD_RESULT = 'Result';
    public const string FIELD_ERROR_MESSAGE = 'ErrorMessage';
    public const string RESULT_NOK = 'NOK';

    // v2 RFC 9457 ProblemDetail fields.
    public const string FIELD_TYPE = 'type';
    public const string FIELD_TITLE = 'title';
    public const string FIELD_STATUS = 'status';
    public const string FIELD_DETAIL = 'detail';
    public const string FIELD_INSTANCE = 'instance';
    public const string FIELD_ERRORS = 'errors';

    /**
     * Normalizes a decoded JSON body into an ApiError, or returns null when the
     * body carries no error indicator.
     *
     * @param array<string, mixed> $data
     */
    public function mapFromArray(array $data): ?ApiError
    {
        if ($this->isProblemDetail($data)) {
            return $this->mapProblemDetail($data);
        }

        return $this->mapV1Error($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function isProblemDetail(array $data): bool
    {
        return array_key_exists(self::FIELD_TITLE, $data)
            || array_key_exists(self::FIELD_TYPE, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapProblemDetail(array $data): ApiError
    {
        $fieldErrors = [];
        if (isset($data[self::FIELD_ERRORS]) && is_array($data[self::FIELD_ERRORS])) {
            foreach ($data[self::FIELD_ERRORS] as $error) {
                if (is_array($error) && isset($error['detail'])) {
                    $fieldErrors[] = new FieldError(
                        (string) $error['detail'],
                        isset($error['pointer']) ? (string) $error['pointer'] : null,
                        isset($error['parameter']) ? (string) $error['parameter'] : null,
                        isset($error['code']) ? (string) $error['code'] : null,
                    );
                }
            }
        }

        $detail = isset($data[self::FIELD_DETAIL]) ? (string) $data[self::FIELD_DETAIL] : '';
        if ($detail === '' && isset($data[self::FIELD_TITLE])) {
            $detail = (string) $data[self::FIELD_TITLE];
        }

        return new ApiError(
            $detail,
            isset($data[self::FIELD_TYPE]) ? (string) $data[self::FIELD_TYPE] : null,
            isset($data[self::FIELD_TITLE]) ? (string) $data[self::FIELD_TITLE] : null,
            isset($data[self::FIELD_STATUS]) ? (int) $data[self::FIELD_STATUS] : null,
            isset($data[self::FIELD_INSTANCE]) ? (string) $data[self::FIELD_INSTANCE] : null,
            $fieldErrors,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapV1Error(array $data): ?ApiError
    {
        // A direct {"Error": "..."} body.
        if (isset($data[self::FIELD_ERROR]) && is_string($data[self::FIELD_ERROR]) && trim($data[self::FIELD_ERROR]) !== '') {
            return new ApiError($data[self::FIELD_ERROR]);
        }

        // A {"Result": "NOK", "ErrorMessage": "..."} envelope.
        $result = $data[self::FIELD_RESULT] ?? null;
        if (is_string($result) && $result === self::RESULT_NOK) {
            $message = isset($data[self::FIELD_ERROR_MESSAGE]) ? (string) $data[self::FIELD_ERROR_MESSAGE] : self::RESULT_NOK;

            return new ApiError($message);
        }

        return null;
    }
}
