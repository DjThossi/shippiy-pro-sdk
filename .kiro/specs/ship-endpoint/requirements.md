# Requirements Document

## Introduction

This document specifies the requirements for adding the **`POST /v1/Ship`** endpoint to the existing ShippyPro SDK (PHP). The SDK is a typed, object-oriented PHP library (namespace `DjThossi\ShippyProSdk\`) that already exposes `POST /v1/Ping` and `GET /v2/status`. The Ship endpoint is implemented following the exact same conventions as those endpoints: an envelope-style v1 POST with the body `{"Method": "Ship", "Params": <ShipParams>}` and `Authorization: Bearer <API-Key>`; a per-endpoint `Api` class that orchestrates a Mapper and the shared `Http\Client`; typed Value Objects and domain objects on the public surface with no raw arrays; and errors surfaced through the existing `StatusCode`, `ApiError`, `FieldError` and `ResponseHeaders` infrastructure rather than thrown for documented HTTP error codes.

The authoritative request/response schema is the OpenAPI document #[[file:v1.json]] in the project root. It is the single source of truth for field names, types, enums, `required` constraints and `security`. Content derived from that document has been rephrased for compliance with licensing restrictions.

The primary focus of this iteration is **correct, typed modeling of the customs (CUSTOMS/CN22) block** and **typed access to common DHL and UPS carrier options**, because those are the parts most error-prone when expressed as raw strings. One response field — `LabelURL` — is polymorphic in the schema (empty array on error, a single string for one label, or an array of strings for multiple labels) and MUST be normalized into a single typed collection.

Note on CN23: #[[file:v1.json]] defines no distinct CN23 structure; the only customs line-item container on `ShipParams` is the `CN22Info[]` array, which carries the line items for both low-value (CN22) and higher-value (CN23/CP71) cross-border shipments, with ShippyPro/the carrier deciding the physical customs form from the shipment value, destination and weight.

Three design decisions that were previously open (Requirements 12, 13 and 14) have now been confirmed with their recommended leans and are stated below as firm requirements. Label format conversion controls, previously proposed as a non-goal, are now in scope and are specified in a dedicated label-formatting requirement.

## Glossary

- **SDK**: The existing PHP library under `DjThossi\ShippyProSdk\`.
- **Value_Object**: A `final readonly` object that encapsulates a domain value and validates it in the constructor.
- **Mapper**: A class that translates JSON to and from typed objects at the serialization boundary; the only place where arrays are used internally.
- **Ship_Api**: The new `Api\ShipApi` class that orchestrates the `POST /v1/Ship` request and its mapping.
- **Ship_Mapper**: The new `Mapper\ShipMapper` that encodes a `ShipRequest` into the v1 envelope body and decodes the `ShipResponse` body.
- **Ship_Request**: The typed `Domain\ShipRequest` object (modeling `ShipParams`) passed to `ShipApi::ship()`.
- **Ship_Response**: The typed `Domain\ShipResponse` object returned by `ShipApi::ship()`.
- **ShipParams**: The request parameter object defined by #[[file:v1.json]] for the `Ship` method.
- **Http_Client**: The existing `Http\Client` that encapsulates Guzzle and authentication and contains no domain logic.
- **Api_Error**: The existing `Domain\ApiError` unified error container.
- **Response_Headers**: The existing `Domain\ResponseHeaders` object (including `RateLimit`).
- **Status_Code**: The existing `Domain\StatusCode` Value_Object with HTTP helper methods.
- **Carrier_Option**: A typed name/value pair modeling an entry of the `CarrierOptions[]` array.
- **Cross_Border_Shipment**: A shipment whose `from_address.country` differs from `to_address.country`.
- **EXW/DAP/DDP**: The `Incoterm` enum values defined by #[[file:v1.json]].
- **CN22**: Customs declaration line items modeled from the `CN22Info[]` array.
- **GetCarrierOptions**: The ShippyPro API method that authoritatively returns valid carrier option names at runtime (out of scope for this iteration; referenced for context only).

## Requirements

### Requirement 1: Ship Endpoint Orchestration

**User Story:** As an SDK consumer, I want a typed `ship()` method on a dedicated Ship API class, so that I can create shipments using the v1 Ship endpoint with the same patterns as the existing Ping and Status endpoints.

#### Acceptance Criteria

1. WHEN `ShipApi::ship()` is invoked with a `ShipRequest`, THE Ship_Api SHALL send a Bearer-authenticated POST request to `/v1/Ship` with a JSON body of the form `{"Method": "Ship", "Params": <ShipParams>}`.
2. THE Ship_Api SHALL build the request body exclusively through the Ship_Mapper and SHALL contain no domain logic for request construction.
3. WHEN the Ship request completes, THE Ship_Api SHALL return a `ShipResponse` containing the Status_Code, the Response_Headers, a typed response body and an optional Api_Error.
4. THE SDK_Factory SHALL provide `createShipApi(): ShipApi` and SHALL wire up the Http_Client, the Ship_Mapper, the `HeaderMapper` and the `ErrorMapper` internally, consistent with `createPingApi()`.
5. THE Ship_Api SHALL send the request through the existing `Http\Client::postV1` method and SHALL NOT introduce a new transport method.

### Requirement 2: Request Base Model

**User Story:** As an SDK consumer, I want the Ship request top-level fields modeled as typed objects, so that I can construct a valid `ShipParams` payload without assembling raw arrays.

#### Acceptance Criteria

1. THE Ship_Request SHALL model the `ShipParams` required fields `to_address`, `from_address`, `parcels`, `TransactionID`, `ContentDescription`, `CarrierName`, `CarrierService` and `CarrierID` as typed, mandatory members.
2. THE Ship_Request SHALL model a shipping address as a typed `Address` object exposing `name`, `company`, `street1`, `street2`, `city`, `state`, `zip`, `country`, `phone` and `email`.
3. THE `Address` SHALL model `country` as an ISO 3166-1 alpha-2 country code.
4. IF an `Address` is constructed with a `country` value that is not a two-letter ISO 3166-1 alpha-2 code, THEN THE SDK SHALL throw a dedicated validation exception from `src/Exception/`.
5. THE Ship_Request SHALL accept the parcels as a typed parcel collection rather than a raw array.
6. THE Ship_Request SHALL expose `TransactionID` and `ContentDescription` as typed string-bearing members.

### Requirement 3: Parcels

**User Story:** As an SDK consumer, I want parcels modeled as typed objects with explicit units, so that dimensions and weights are unambiguous.

#### Acceptance Criteria

1. THE SDK SHALL model each parcel as a typed `Parcel` object exposing `length`, `width`, `height`, `weight`, `dimension_unit`, `weight_unit`, `package` and `Attributes`.
2. THE `Parcel` SHALL model `dimension_unit` as an enum with the values `CM` and `IN`, defaulting to `CM` when not supplied.
3. THE `Parcel` SHALL model `weight_unit` as an enum with the values `KG`, `LB` and `OZ`, defaulting to `KG` when not supplied.
4. THE `Parcel` SHALL model `package` as an optional carrier-predefined package identifier of integer type.
5. THE `Parcel` SHALL model `Attributes` as a typed object exposing `parcelID` and `parcelType`.
6. THE SDK SHALL model a collection of parcels as a typed `ParcelCollection` and SHALL NOT expose a raw array of parcels in the public API.

### Requirement 4: Monetary Values

**User Story:** As an SDK consumer, I want monetary fields modeled with explicit currency, so that values and currencies are kept together and formatted as the API expects.

#### Acceptance Criteria

1. THE SDK SHALL model `TotalValue` as a typed value expressing an amount and an ISO currency code, serialized as the string form `"<amount> <currency>"` (for example `"123 EUR"`).
2. WHERE `TotalValue` is provided, THE SDK SHALL permit only the `.` character as the decimal separator in the amount and SHALL reject values containing a `,` as the decimal separator with a dedicated validation exception.
3. THE SDK SHALL model `Insurance` together with `InsuranceCurrency`, defaulting `InsuranceCurrency` to `EUR` when an insurance amount is provided without a currency.
4. THE SDK SHALL model `CashOnDelivery` together with `CashOnDeliveryCurrency` and `CashOnDeliveryType`.
5. THE SDK SHALL model `CashOnDeliveryType` as an enum with the values `0` (Cash), `1` (Cashier's check), `2` (Check) and `3` (All), defaulting to `0` (Cash).

### Requirement 5: Carrier Selection

**User Story:** As an SDK consumer, I want carrier selection fields modeled as typed members, so that I can target a specific carrier, service and rate.

#### Acceptance Criteria

1. THE Ship_Request SHALL model `CarrierName`, `CarrierService` and `CarrierID` as mandatory typed members.
2. THE Ship_Request SHALL model `RateID` and `OrderID` as optional typed members.
3. THE Ship_Mapper SHALL serialize the carrier selection members into the `ShipParams` payload using the exact field names defined in #[[file:v1.json]].

### Requirement 6: Customs and CN22 (Primary Focus)

**User Story:** As an SDK consumer shipping cross-border, I want the customs block modeled as typed objects with correct enums, so that I can declare customs data without constructing error-prone raw structures.

#### Acceptance Criteria

1. THE SDK SHALL model the customs block as a typed `Customs` object attachable to the Ship_Request.
2. THE `Customs` SHALL model `Incoterm` as an enum with the values `DAP`, `DDP` and `EXW`, defaulting to `DAP` for a Cross_Border_Shipment.
3. THE `Customs` SHALL model `BillAccountNumber` as an optional typed member representing a third-party billing account.
4. THE `Customs` SHALL model `CustomInfoContentsType` as an enum with the values `documents`, `gift`, `merchandise`, `returned_goods`, `sample` and `other`.
5. THE SDK SHALL model each customs line item as a typed `CN22Item` exposing `Description`, `Weight`, `Quantity`, `UnitValue`, `OriginCountry`, `Currency`, `HSCode`, `EAN`, `SKU`, `MPN` and `VOC`.
6. THE `CN22Item` SHALL model `VOC` as a typed numeric member representing volatile organic compound content in grams.
7. THE SDK SHALL model the customs line items as a typed `Cn22ItemCollection` and SHALL NOT expose a raw array of CN22 items in the public API.
8. THE `Customs` SHALL model `non_delivery_option` as an enum with the values `return` and `abandon` for dutiable shipments.
9. THE SDK SHALL expose the IOSS identifier through the parcel `Attributes` member `sp_ioss` as a typed managed attribute.
10. THE SDK SHALL document that `SKU` on a `CN22Item` is required for EU B2C shipments valued at or below 150 EUR as of 2026-11-01, as stated in #[[file:v1.json]].
11. THE SDK SHALL model customs line items via a single `CN22Item` / `Cn22ItemCollection` type mapping to the `CN22Info[]` field and SHALL NOT provide a separate CN23 model, because #[[file:v1.json]] defines no distinct CN23 structure.
12. THE SDK SHALL document that the `CN22Item` / `Cn22ItemCollection` container carries the customs line items for both low-value (CN22) and higher-value (CN23/CP71) cross-border shipments, and that ShippyPro and the carrier determine the physical customs form from the shipment value, destination and weight.

### Requirement 7: DHL and UPS Carrier Options (Primary Focus)

**User Story:** As an SDK consumer using DHL or UPS, I want typed access to carrier options with documented named helpers, so that I can set common options without hardcoding magic strings while still being able to pass any option name.

#### Acceptance Criteria

1. THE SDK SHALL model each carrier option as a typed `CarrierOption` exposing a `name` and a `value`.
2. THE SDK SHALL model a collection of carrier options as a typed `CarrierOptionCollection` and SHALL serialize it into the `CarrierOptions[]` array of the `ShipParams` payload.
3. THE SDK SHALL provide documented named helper constructors or constants for the common DHL and UPS customs and service options (for example `mydhlsaturday_delivery` and `upsdangerousgoods`) so that callers obtain typed access instead of raw strings.
4. THE SDK SHALL allow construction of a `CarrierOption` with an arbitrary option name, because the authoritative set of valid option names is returned by the GetCarrierOptions method at runtime.
5. THE SDK SHALL document that valid carrier option names are defined per carrier by the GetCarrierOptions method and that the provided named helpers are a convenience subset.

### Requirement 8: Return Labels and Return Address Override

**User Story:** As an SDK consumer, I want to request return labels and override the return address, so that I can create combined outbound-plus-return shipments and plain returns.

#### Acceptance Criteria

1. THE Ship_Request SHALL model the return-label combination members `ReturnCarrierName`, `ReturnCarrierService`, `ReturnCarrierID`, `ReturnInsurance`, `ReturnInsuranceCurrency` and `ReturnCarrierOptions` as optional typed members.
2. THE Ship_Request SHALL model `ReturnCarrierOptions` as a typed `CarrierOptionCollection` consistent with Requirement 7.
3. THE Ship_Request SHALL model `return_to` as an optional typed `Address` for overriding the return address.
4. THE Ship_Request SHALL model `IsReturn` as an optional typed boolean flag for plain return shipments.
5. WHERE `ReturnInsurance` is provided without a currency, THE SDK SHALL default `ReturnInsuranceCurrency` to `EUR`.

### Requirement 9: Response Mapping

**User Story:** As an SDK consumer, I want the Ship response mapped into typed objects, so that I can read the result, tracking data and labels without parsing arrays, including the polymorphic label field.

#### Acceptance Criteria

1. THE Ship_Response SHALL expose the response fields `Result`, `NewOrderID`, `TrackingCarrier`, `TrackingNumber`, `TrackingExternalLink`, `DeliveryCarrier`, `Status`, `ErrorMessage` and `ReturnErrorMessage` as typed members.
2. THE Ship_Response SHALL model `Status` as an enum or typed value distinguishing `"1"` (ok) from `"2"` (error).
3. THE Ship_Response SHALL model `DeliveryCarrier` as a nullable typed member.
4. WHEN the `LabelURL` field is an empty array, THE Ship_Mapper SHALL map it to an empty typed label collection.
5. WHEN the `LabelURL` field is a single string, THE Ship_Mapper SHALL map it to a typed label collection containing exactly one label.
6. WHEN the `LabelURL` field is an array of strings, THE Ship_Mapper SHALL map it to a typed label collection containing one label per string in order.
7. THE Ship_Response SHALL expose `PDF`, `ZPL` and `AdditionalTrackingNumbers` as typed collections rather than raw arrays.
8. THE Ship_Response SHALL model `ErrorType` as a typed object exposing `code`, `name` and `typeDescription`, and `ErrorSuggestion` as a typed object exposing `errorDescription` and `solution`.
9. THE Ship_Response SHALL model the per-parcel breakdown `Parcels` and `ReturnParcels` as typed collections of a `ShipParcel` object carrying per-parcel tracking and label data, each nullable where the schema permits.
10. THE Ship_Response SHALL model the return-variant response fields analogously to their outbound counterparts.

### Requirement 10: Error Handling

**User Story:** As an SDK consumer, I want Ship errors surfaced through the existing error infrastructure, so that error handling is consistent with the Ping and Status endpoints.

#### Acceptance Criteria

1. IF the API returns a documented HTTP error code (`400`, `401`, `403`, `404`, `429` or `500`), THEN THE Ship_Api SHALL expose the code through the Status_Code and SHALL NOT throw for that code.
2. WHEN the response body carries a v1 error payload, THE Ship_Api SHALL normalize it into an Api_Error via the existing `ErrorMapper`.
3. THE Ship_Api SHALL expose the Response_Headers (including `RateLimit` and the request identifier) on the `ShipResponse` via the existing `HeaderMapper`.
4. IF a required field is missing while mapping the response body, THEN THE Ship_Mapper SHALL throw a `MissingRequiredFieldsException` naming the missing fields.
5. WHEN the response reports `Status` `"2"` or a non-null `ErrorMessage`, THE Ship_Response SHALL make the business-level error readable through its typed `ErrorMessage`, `ErrorType` and `ErrorSuggestion` members alongside the Status_Code.

### Requirement 11: Separation of Responsibilities and No-Arrays Principle

**User Story:** As an SDK maintainer, I want the Ship endpoint to follow the established separation of responsibilities, so that the SDK stays typed and consistent.

#### Acceptance Criteria

1. THE Ship_Mapper SHALL be the only class that reads or writes arrays for the Ship request and response at the serialization boundary.
2. THE SDK SHALL return only typed objects from `ShipApi::ship()` and SHALL never return a raw array.
3. THE Ship_Request SHALL accept only Value Objects or typed objects and SHALL NOT expose associative arrays in its public constructor or accessors.
4. THE Http_Client SHALL remain free of Ship-specific domain logic.

### Requirement 12: Customs Validation Depth

**User Story:** As an SDK consumer, I want the clearly-documented customs invariants validated before the request is sent, so that I get a fast, expressive failure instead of a remote API rejection, while carrier- and country-specific rules remain the API's responsibility.

#### Acceptance Criteria

1. IF `Incoterm` is `EXW` and no `BillAccountNumber` is provided, THEN THE SDK SHALL reject the request at build time with a dedicated validation exception.
2. IF a `BillAccountNumber` is provided and `Incoterm` is not `EXW`, THEN THE SDK SHALL reject the request at build time with a dedicated validation exception.
3. IF the Ship_Request describes a Cross_Border_Shipment and no `TotalValue` is provided, THEN THE SDK SHALL reject the request at build time with a dedicated validation exception.
4. THE SDK SHALL leave carrier-specific and country-matrix customs rules (beyond the invariants in criteria 1 through 3) to the API and SHALL NOT enforce them client-side.
5. THE SDK SHALL enforce exactly the clearly-documented `EXW`↔`BillAccountNumber` and Cross_Border_Shipment↔`TotalValue` invariants client-side and SHALL type the remaining customs fields without additional client-side rules, passing them through to the API for validation.

### Requirement 13: DHL/UPS Modeling Approach

**User Story:** As an SDK maintainer, I want DHL and UPS options modeled at the right depth, so that the implementation balances typed convenience against the fact that option names are only authoritatively known at runtime.

#### Acceptance Criteria

1. THE SDK SHALL implement a generic typed `CarrierOption` plus documented named helper constants for common DHL and UPS options per Requirement 7, rather than an exhaustive per-carrier strict model, because valid option names are authoritatively returned only by GetCarrierOptions at runtime.
2. THE SDK SHALL NOT fail construction of a `CarrierOption` whose name is absent from the documented helper set.

### Requirement 14: First-Iteration Non-Goals

**User Story:** As a stakeholder, I want the scope of the first Ship iteration bounded, so that the team delivers a focused, correct Ship endpoint.

#### Acceptance Criteria

1. THE SDK SHALL NOT implement Progressive/Dynamic Multiparcel (`IsDynamicMultiparcel`) in this iteration.
2. THE SDK SHALL NOT implement detailed pricing (`detailed_pricing`) in this iteration.
3. THE SDK SHALL NOT implement asynchronous label retrieval in this iteration.

### Requirement 15: Examples

**User Story:** As an SDK consumer, I want a runnable Ship example, so that I can see how to construct a cross-border shipment with customs data end-to-end.

#### Acceptance Criteria

1. THE SDK SHALL provide `examples/ship.php` demonstrating a Ship call end-to-end using a configured `ApiKey`, typed `Address` objects, a `ParcelCollection`, a `Customs` block with `CN22` items, and a DHL or UPS `CarrierOption`.
2. THE SDK SHALL make the Ship example runnable via its own Makefile target (`make examples-ship`).

### Requirement 16: Tests

**User Story:** As an SDK maintainer, I want comprehensive strict tests for the Ship endpoint, so that its behavior is verified and regressions are caught.

#### Acceptance Criteria

1. THE SDK SHALL provide 100% unit test coverage for the new Ship classes in `tests/Unit`, measured via the PHPUnit coverage report.
2. THE Ship_Api test SHALL mock the Http_Client and verify the endpoint path, the Bearer envelope body, the Status_Code handling and the response mapping.
3. THE Ship_Mapper test SHALL cover complete, minimal and malformed JSON inputs, including the `MissingRequiredFieldsException` path.
4. THE Ship_Mapper test SHALL cover all three `LabelURL` shapes (empty array, single string, array of strings) and assert the resulting typed label collection.
5. WHEN a valid Ship_Request is serialized and then parsed back, THE Ship_Mapper SHALL produce an equivalent `ShipParams` payload (round-trip property).
6. THE Value Object tests SHALL cover valid and invalid inputs for `Address` country codes, `TotalValue` formatting, the customs enums, and the customs invariants of Requirement 12, including the respective exceptions.
7. THE SDK SHALL run the new tests under the existing strict PHPUnit configuration (`failOnWarning`, `failOnRisky`, `failOnDeprecation`, `failOnNotice`).
8. THE Value Object tests SHALL cover valid and invalid inputs for the `LabelType` enum, and the Ship_Mapper test SHALL assert that the optional label-formatting members (`LabelType`, `LabelWidth`, `LabelHeight`, `Rotate`, `PageNumber`, `SplitPages`) are serialized under their exact v1.json field names when provided and omitted when absent.

### Requirement 17: Label Formatting

**User Story:** As an SDK consumer, I want typed control over the label format, size, rotation, splitting and selection, so that I can request the exact label output I need without constructing raw fields.

#### Acceptance Criteria

1. THE SDK SHALL model `LabelType` as an optional enum with the values `PDF` and `ZPL`, where format conversion happens automatically on the API side.
2. THE SDK SHALL model `LabelWidth` and `LabelHeight` as optional typed members expressed in CENTIMETRES, exactly as #[[file:v1.json]] defines them, and SHALL apply to the `PDF` label format.
3. THE SDK SHALL NOT provide an inches-based convenience type for label dimensions, and THE SDK SHALL document that a caller wanting a 4x8 inch label must express it in centimetres (approximately `10.16` x `20.32` cm).
4. THE SDK SHALL model `Rotate` as an optional typed member expressing label rotation in degrees, applicable only when a format conversion is performed.
5. THE SDK SHALL model `PageNumber` as an optional typed member expressing the index of the label to retrieve.
6. THE SDK SHALL model `SplitPages` as an optional typed boolean member controlling whether the labels are split.
7. THE SDK SHALL model all label-formatting members in criteria 1 through 6 as optional, and THE Ship_Mapper SHALL serialize each provided member into the `ShipParams` payload using the exact field names defined in #[[file:v1.json]] and SHALL omit each member that is not provided.
8. THE SDK SHALL treat the label-formatting members as controls over label FORMAT, type, size, rotation, splitting and selection only, which are distinct from asynchronous label retrieval, and asynchronous label retrieval SHALL remain a non-goal per Requirement 14.
