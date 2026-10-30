<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Mapper;

use function array_key_exists;

use DjThossi\ShippyProSdk\Domain\StatusResponseBody;
use DjThossi\ShippyProSdk\Exception\MissingRequiredFieldsException;

final readonly class StatusMapper
{
    public const string FIELD_STATUS = 'status';
    public const string FIELD_VERSION = 'version';

    public const array REQUIRED_FIELDS = [
        self::FIELD_STATUS,
        self::FIELD_VERSION,
    ];

    /**
     * @param array<string, mixed> $data
     */
    public function mapResponseBody(array $data): StatusResponseBody
    {
        $this->ensureRequiredFields($data);

        return new StatusResponseBody(
            (string) $data[self::FIELD_STATUS],
            (string) $data[self::FIELD_VERSION],
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function ensureRequiredFields(array $data): void
    {
        $missingFields = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new MissingRequiredFieldsException($missingFields);
        }
    }
}
