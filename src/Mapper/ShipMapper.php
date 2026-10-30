<?php

declare(strict_types=1);

namespace DjThossi\ShippyProSdk\Mapper;

use function array_key_exists;
use function array_map;
use function array_values;

use DjThossi\ShippyProSdk\Domain\Address;
use DjThossi\ShippyProSdk\Domain\CarrierOptionCollection;
use DjThossi\ShippyProSdk\Domain\Customs;
use DjThossi\ShippyProSdk\Domain\ErrorSuggestion;
use DjThossi\ShippyProSdk\Domain\ErrorType;
use DjThossi\ShippyProSdk\Domain\LabelFormatting;
use DjThossi\ShippyProSdk\Domain\LabelUrlCollection;
use DjThossi\ShippyProSdk\Domain\Parcel;
use DjThossi\ShippyProSdk\Domain\ParcelAttributes;
use DjThossi\ShippyProSdk\Domain\ShipParcel;
use DjThossi\ShippyProSdk\Domain\ShipParcelCollection;
use DjThossi\ShippyProSdk\Domain\ShipRequest;
use DjThossi\ShippyProSdk\Domain\ShipResponseBody;
use DjThossi\ShippyProSdk\Domain\ShipStatus;
use DjThossi\ShippyProSdk\Exception\MissingRequiredFieldsException;

use function is_array;
use function is_string;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final readonly class ShipMapper
{
    public const string METHOD = 'Ship';
    public const string FIELD_METHOD = 'Method';
    public const string FIELD_PARAMS = 'Params';

    // Request field names (exact v1.json names).
    public const string FIELD_TO_ADDRESS = 'to_address';
    public const string FIELD_FROM_ADDRESS = 'from_address';
    public const string FIELD_PARCELS = 'parcels';
    public const string FIELD_TRANSACTION_ID = 'TransactionID';
    public const string FIELD_CONTENT_DESCRIPTION = 'ContentDescription';
    public const string FIELD_CARRIER_NAME = 'CarrierName';
    public const string FIELD_CARRIER_SERVICE = 'CarrierService';
    public const string FIELD_CARRIER_ID = 'CarrierID';
    public const string FIELD_RATE_ID = 'RateID';
    public const string FIELD_ORDER_ID = 'OrderID';
    public const string FIELD_TOTAL_VALUE = 'TotalValue';
    public const string FIELD_INSURANCE = 'Insurance';
    public const string FIELD_INSURANCE_CURRENCY = 'InsuranceCurrency';
    public const string FIELD_COD = 'CashOnDelivery';
    public const string FIELD_COD_CURRENCY = 'CashOnDeliveryCurrency';
    public const string FIELD_COD_TYPE = 'CashOnDeliveryType';
    public const string FIELD_NOTE = 'Note';
    public const string FIELD_CARRIER_NOTE = 'CarrierNote';
    public const string FIELD_USER_NOTE = 'UserNote';
    public const string FIELD_PAYMENT_METHOD = 'PaymentMethod';
    public const string FIELD_INCOTERM = 'Incoterm';
    public const string FIELD_BILL_ACCOUNT_NUMBER = 'BillAccountNumber';
    public const string FIELD_CUSTOM_INFO_CONTENTS_TYPE = 'CustomInfoContentsType';
    public const string FIELD_CARRIER_OPTIONS = 'CarrierOptions';
    public const string FIELD_CN22_INFO = 'CN22Info';
    public const string FIELD_NON_DELIVERY_OPTION = 'non_delivery_option';
    public const string FIELD_IS_RETURN = 'IsReturn';
    public const string FIELD_RETURN_CARRIER_NAME = 'ReturnCarrierName';
    public const string FIELD_RETURN_CARRIER_SERVICE = 'ReturnCarrierService';
    public const string FIELD_RETURN_CARRIER_ID = 'ReturnCarrierID';
    public const string FIELD_RETURN_INSURANCE = 'ReturnInsurance';
    public const string FIELD_RETURN_INSURANCE_CURRENCY = 'ReturnInsuranceCurrency';
    public const string FIELD_RETURN_CARRIER_OPTIONS = 'ReturnCarrierOptions';
    public const string FIELD_RETURN_TO = 'return_to';
    public const string FIELD_ATTRIBUTES = 'Attributes';
    public const string FIELD_LABEL_TYPE = 'LabelType';
    public const string FIELD_LABEL_WIDTH = 'LabelWidth';
    public const string FIELD_LABEL_HEIGHT = 'LabelHeight';
    public const string FIELD_ROTATE = 'Rotate';
    public const string FIELD_PAGE_NUMBER = 'PageNumber';
    public const string FIELD_SPLIT_PAGES = 'SplitPages';

    // Response field names (exact v1.json names).
    public const string FIELD_RESULT = 'Result';
    public const string FIELD_NEW_ORDER_ID = 'NewOrderID';
    public const string FIELD_LABEL_URL = 'LabelURL';
    public const string FIELD_PDF = 'PDF';
    public const string FIELD_ZPL = 'ZPL';
    public const string FIELD_TRACKING_CARRIER = 'TrackingCarrier';
    public const string FIELD_TRACKING_NUMBER = 'TrackingNumber';
    public const string FIELD_TRACKING_EXTERNAL_LINK = 'TrackingExternalLink';
    public const string FIELD_ADDITIONAL_TRACKING_NUMBERS = 'AdditionalTrackingNumbers';
    public const string FIELD_DELIVERY_CARRIER = 'DeliveryCarrier';
    public const string FIELD_STATUS = 'Status';
    public const string FIELD_ERROR_MESSAGE = 'ErrorMessage';
    public const string FIELD_RETURN_ERROR_MESSAGE = 'ReturnErrorMessage';
    public const string FIELD_ERROR_TYPE = 'ErrorType';
    public const string FIELD_ERROR_SUGGESTION = 'ErrorSuggestion';
    public const string FIELD_PARCELS_OUT = 'Parcels';
    public const string FIELD_RETURN_PARCELS = 'ReturnParcels';

    public const array REQUIRED_RESPONSE_FIELDS = [
        self::FIELD_RESULT,
        self::FIELD_STATUS,
    ];

    public function encodeRequest(ShipRequest $request): string
    {
        return json_encode([
            self::FIELD_METHOD => self::METHOD,
            self::FIELD_PARAMS => (object) $this->encodeParams($request),
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function encodeParams(ShipRequest $request): array
    {
        $params = [
            self::FIELD_TO_ADDRESS => $this->encodeAddress($request->toAddress),
            self::FIELD_FROM_ADDRESS => $this->encodeAddress($request->fromAddress),
            self::FIELD_PARCELS => array_map(
                fn (Parcel $parcel): array => $this->encodeParcel($parcel),
                $request->parcels->all(),
            ),
            self::FIELD_TRANSACTION_ID => $request->transactionId,
            self::FIELD_CONTENT_DESCRIPTION => $request->contentDescription,
            self::FIELD_CARRIER_NAME => $request->carrierName,
            self::FIELD_CARRIER_SERVICE => $request->carrierService,
            self::FIELD_CARRIER_ID => $request->carrierId,
        ];

        if ($request->rateId !== null) {
            $params[self::FIELD_RATE_ID] = $request->rateId;
        }
        if ($request->orderId !== null) {
            $params[self::FIELD_ORDER_ID] = $request->orderId;
        }
        if ($request->totalValue !== null) {
            $params[self::FIELD_TOTAL_VALUE] = $request->totalValue->toApiString();
        }
        if ($request->insurance !== null) {
            $params[self::FIELD_INSURANCE] = $request->insurance->amount;
            $params[self::FIELD_INSURANCE_CURRENCY] = $request->insurance->currency;
        }
        if ($request->cashOnDelivery !== null) {
            $params[self::FIELD_COD] = $request->cashOnDelivery->amount;
            $params[self::FIELD_COD_CURRENCY] = $request->cashOnDelivery->currency;
            $params[self::FIELD_COD_TYPE] = $request->cashOnDelivery->type->value;
        }
        if ($request->note !== null) {
            $params[self::FIELD_NOTE] = $request->note;
        }
        if ($request->carrierNote !== null) {
            $params[self::FIELD_CARRIER_NOTE] = $request->carrierNote;
        }
        if ($request->userNote !== null) {
            $params[self::FIELD_USER_NOTE] = $request->userNote;
        }
        if ($request->paymentMethod !== null) {
            $params[self::FIELD_PAYMENT_METHOD] = $request->paymentMethod;
        }
        if ($request->customs !== null) {
            $params = [...$params, ...$this->encodeCustoms($request->customs)];
        }
        if ($request->carrierOptions !== null) {
            $params[self::FIELD_CARRIER_OPTIONS] = $this->encodeCarrierOptions($request->carrierOptions);
        }
        if ($request->isReturn !== null) {
            $params[self::FIELD_IS_RETURN] = $request->isReturn;
        }
        if ($request->returnCarrierName !== null) {
            $params[self::FIELD_RETURN_CARRIER_NAME] = $request->returnCarrierName;
        }
        if ($request->returnCarrierService !== null) {
            $params[self::FIELD_RETURN_CARRIER_SERVICE] = $request->returnCarrierService;
        }
        if ($request->returnCarrierId !== null) {
            $params[self::FIELD_RETURN_CARRIER_ID] = $request->returnCarrierId;
        }
        if ($request->returnInsurance !== null) {
            $params[self::FIELD_RETURN_INSURANCE] = $request->returnInsurance->amount;
            $params[self::FIELD_RETURN_INSURANCE_CURRENCY] = $request->returnInsurance->currency;
        }
        if ($request->returnCarrierOptions !== null) {
            $params[self::FIELD_RETURN_CARRIER_OPTIONS] = $this->encodeCarrierOptions($request->returnCarrierOptions);
        }
        if ($request->returnTo !== null) {
            $params[self::FIELD_RETURN_TO] = $this->encodeAddress($request->returnTo);
        }
        if ($request->attributes !== null && !$request->attributes->isEmpty()) {
            $params[self::FIELD_ATTRIBUTES] = $this->encodeAttributes($request->attributes);
        }
        if ($request->labelFormatting !== null) {
            $params = [...$params, ...$this->encodeLabelFormatting($request->labelFormatting)];
        }

        return $params;
    }

    public function mapLabelUrl(array $data): LabelUrlCollection
    {
        $raw = $data[self::FIELD_LABEL_URL] ?? null;

        if (is_string($raw)) {
            return new LabelUrlCollection($raw);
        }

        if (is_array($raw)) {
            $urls = array_values(array_map(static fn (mixed $v): string => (string) $v, $raw));

            return new LabelUrlCollection(...$urls);
        }

        return new LabelUrlCollection();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function mapResponseBody(array $data): ShipResponseBody
    {
        $this->ensureRequiredFields($data);

        return new ShipResponseBody(
            (string) $data[self::FIELD_RESULT],
            ShipStatus::from((string) $data[self::FIELD_STATUS]),
            $this->mapLabelUrl($data),
            $this->nullableString($data, self::FIELD_NEW_ORDER_ID),
            $this->nullableString($data, self::FIELD_TRACKING_CARRIER),
            $this->nullableString($data, self::FIELD_TRACKING_NUMBER),
            $this->nullableString($data, self::FIELD_TRACKING_EXTERNAL_LINK),
            $this->nullableString($data, self::FIELD_DELIVERY_CARRIER),
            $this->nullableString($data, self::FIELD_ERROR_MESSAGE),
            $this->nullableString($data, self::FIELD_RETURN_ERROR_MESSAGE),
            $this->mapErrorType($data),
            $this->mapErrorSuggestion($data),
            $this->stringList($data, self::FIELD_PDF),
            $this->stringList($data, self::FIELD_ZPL),
            $this->arrayList($data, self::FIELD_ADDITIONAL_TRACKING_NUMBERS),
            $this->mapShipParcels($data, self::FIELD_PARCELS_OUT),
            $this->mapShipParcels($data, self::FIELD_RETURN_PARCELS),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function encodeAddress(Address $address): array
    {
        return [
            'name' => $address->name,
            'company' => $address->company,
            'street1' => $address->street1,
            'street2' => $address->street2,
            'city' => $address->city,
            'state' => $address->state,
            'zip' => $address->zip,
            'country' => $address->country->value,
            'phone' => $address->phone,
            'email' => $address->email,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function encodeParcel(Parcel $parcel): array
    {
        $encoded = [
            'length' => $parcel->length,
            'width' => $parcel->width,
            'height' => $parcel->height,
            'weight' => $parcel->weight,
            'dimension_unit' => $parcel->dimensionUnit->value,
            'weight_unit' => $parcel->weightUnit->value,
        ];

        if ($parcel->package !== null) {
            $encoded['package'] = $parcel->package;
        }
        if ($parcel->attributes !== null && !$parcel->attributes->isEmpty()) {
            $encoded['Attributes'] = $this->encodeAttributes($parcel->attributes);
        }

        return $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function encodeAttributes(ParcelAttributes $attributes): array
    {
        $encoded = [];
        if ($attributes->parcelId !== null) {
            $encoded['parcelID'] = $attributes->parcelId;
        }
        if ($attributes->parcelType !== null) {
            $encoded['parcelType'] = $attributes->parcelType;
        }
        if ($attributes->spIoss !== null) {
            $encoded['sp_ioss'] = $attributes->spIoss;
        }

        return $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function encodeCustoms(Customs $customs): array
    {
        $encoded = [self::FIELD_INCOTERM => $customs->incoterm->value];

        if ($customs->billAccountNumber !== null) {
            $encoded[self::FIELD_BILL_ACCOUNT_NUMBER] = $customs->billAccountNumber;
        }
        if ($customs->contentsType !== null) {
            $encoded[self::FIELD_CUSTOM_INFO_CONTENTS_TYPE] = $customs->contentsType->value;
        }
        if ($customs->nonDeliveryOption !== null) {
            $encoded[self::FIELD_NON_DELIVERY_OPTION] = $customs->nonDeliveryOption->value;
        }
        if ($customs->items !== null && !$customs->items->isEmpty()) {
            $encoded[self::FIELD_CN22_INFO] = array_map(
                static fn ($item): array => [
                    'Description' => $item->description,
                    'Weight' => $item->weight,
                    'Quantity' => $item->quantity,
                    'UnitValue' => $item->unitValue,
                    'OriginCountry' => $item->originCountry->value,
                    'Currency' => $item->currency,
                    ...($item->hsCode !== null ? ['HSCode' => $item->hsCode] : []),
                    ...($item->ean !== null ? ['EAN' => $item->ean] : []),
                    ...($item->sku !== null ? ['SKU' => $item->sku] : []),
                    ...($item->mpn !== null ? ['MPN' => $item->mpn] : []),
                    ...($item->voc !== null ? ['VOC' => $item->voc] : []),
                ],
                $customs->items->all(),
            );
        }

        return $encoded;
    }

    /**
     * @return array<int, array{name: string, value: string}>
     */
    private function encodeCarrierOptions(CarrierOptionCollection $options): array
    {
        return array_map(
            static fn ($option): array => ['name' => $option->name, 'value' => $option->value],
            $options->all(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function encodeLabelFormatting(LabelFormatting $formatting): array
    {
        $encoded = [];
        if ($formatting->labelType !== null) {
            $encoded[self::FIELD_LABEL_TYPE] = $formatting->labelType->value;
        }
        if ($formatting->labelWidthCm !== null) {
            $encoded[self::FIELD_LABEL_WIDTH] = $formatting->labelWidthCm;
        }
        if ($formatting->labelHeightCm !== null) {
            $encoded[self::FIELD_LABEL_HEIGHT] = $formatting->labelHeightCm;
        }
        if ($formatting->rotate !== null) {
            $encoded[self::FIELD_ROTATE] = $formatting->rotate;
        }
        if ($formatting->pageNumber !== null) {
            $encoded[self::FIELD_PAGE_NUMBER] = $formatting->pageNumber;
        }
        if ($formatting->splitPages !== null) {
            $encoded[self::FIELD_SPLIT_PAGES] = $formatting->splitPages;
        }

        return $encoded;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function ensureRequiredFields(array $data): void
    {
        $missing = [];
        foreach (self::REQUIRED_RESPONSE_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            throw new MissingRequiredFieldsException($missing);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullableString(array $data, string $field): ?string
    {
        return isset($data[$field]) ? (string) $data[$field] : null;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return string[]
     */
    private function stringList(array $data, string $field): array
    {
        if (!isset($data[$field]) || !is_array($data[$field])) {
            return [];
        }

        return array_values(array_map(static fn (mixed $v): string => (string) $v, $data[$field]));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, array<mixed>>
     */
    private function arrayList(array $data, string $field): array
    {
        if (!isset($data[$field]) || !is_array($data[$field])) {
            return [];
        }

        return array_values(array_filter($data[$field], static fn (mixed $v): bool => is_array($v)));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapErrorType(array $data): ?ErrorType
    {
        if (!isset($data[self::FIELD_ERROR_TYPE]) || !is_array($data[self::FIELD_ERROR_TYPE])) {
            return null;
        }

        $e = $data[self::FIELD_ERROR_TYPE];

        return new ErrorType(
            isset($e['code']) ? (string) $e['code'] : null,
            isset($e['name']) ? (string) $e['name'] : null,
            isset($e['typeDescription']) ? (string) $e['typeDescription'] : null,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapErrorSuggestion(array $data): ?ErrorSuggestion
    {
        if (!isset($data[self::FIELD_ERROR_SUGGESTION]) || !is_array($data[self::FIELD_ERROR_SUGGESTION])) {
            return null;
        }

        $e = $data[self::FIELD_ERROR_SUGGESTION];

        return new ErrorSuggestion(
            isset($e['errorDescription']) ? (string) $e['errorDescription'] : null,
            isset($e['solution']) ? (string) $e['solution'] : null,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapShipParcels(array $data, string $field): ?ShipParcelCollection
    {
        if (!isset($data[$field]) || !is_array($data[$field])) {
            return null;
        }

        $parcels = [];
        foreach ($data[$field] as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $parcels[] = new ShipParcel(
                isset($raw['TrackingNumber']) ? (string) $raw['TrackingNumber'] : null,
                isset($raw['PDF']) ? (string) $raw['PDF'] : null,
                isset($raw['ZPL']) ? (string) $raw['ZPL'] : null,
            );
        }

        return new ShipParcelCollection(...$parcels);
    }
}
