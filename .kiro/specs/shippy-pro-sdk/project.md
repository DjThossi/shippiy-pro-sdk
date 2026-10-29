# Requirements Document

## Introduction

Dieses Dokument beschreibt die Anforderungen an das ShippyPro SDK (PHP). Die
nachfolgenden nummerierten Abschnitte (1–12) bilden den fachlichen Kern; die
funktionalen Anforderungen sind als FR-Nummern in den Abschnitten 5–10 gepflegt. Der
Startumfang umfasst genau zwei Endpunkte: `POST /v1/Ping` und `GET /v2/status`. Alle
Schemata sind über die OpenAPI-Dokumente `v1.json` und `v2.json` im Projekt-Root
verbindlich festgelegt.

## Glossary

- **SDK**: Die hier spezifizierte PHP-Bibliothek, die die ShippyPro-API als getippte,
  objektorientierte PHP-API zugänglich macht.
- **Value Object**: Ein `final readonly` Objekt, das einen primitiven Fachwert
  kapselt und im Konstruktor validiert (z. B. `ApiKey`, `BaseUrl`, `RequestTimeout`,
  `StatusCode`).
- **Mapper**: Klasse, die JSON ↔ Domain-Objekte an der Serialisierungsgrenze
  übersetzt; einziger Ort, an dem intern Arrays erlaubt sind.
- **Bearer-Auth**: Authentifizierung über den Header `Authorization: Bearer <API-Key>`.
- **OpenAPI-Dokumente**: Die Dateien `v1.json` und `v2.json` im Projekt-Root, die als
  verbindliche Quelle für Request-/Response-Schemata und `security` dienen.

## Requirements

Die funktionalen Anforderungen (FR-1 bis FR-29) sind in den Abschnitten 5 bis 10
dieses Dokuments definiert.

## 1. Zweck und Scope

Ein neues PHP-SDK für die [ShippyPro API](https://api.shippypro.com/docs), das die
ShippyPro-Endpunkte als getippte, objektorientierte PHP-API zugänglich macht.

Das SDK orientiert sich strukturell und konventionell an drei bestehenden Vorlagen
im Workspace:

- `ergosoft-sdk` (wichtig) – liefert die Value-Object-/Domain-Konventionen und den
  "alles als Objekt, keine Arrays nach außen"-Stil, den dieses SDK übernehmen soll.
- `shipcloud-sdk` (sehr wichtig) – liefert das Factory-/Api-/Http-Client-Muster sowie
  die API-Key-basierte Authentifizierung.
- `teppana/aws-sdk` (für die Beispiele/Docker-Struktur).

### Startumfang (erste Iteration)

Um klein zu starten, implementiert die erste Iteration genau zwei Endpunkte:

- `POST /v1/Ping` – Verbindungs-/Auth-Check der v1-API.
- `GET /v2/status` – Status-Check der v2-API.

Weitere Endpunkte werden in späteren Iterationen ergänzt.

### API-Versions-Strategie

- Wo eine **v2** eines Endpunkts existiert, wird **v2** umgesetzt.
- Für alles andere bleibt es bei **v1**.

> **Geklärt:** Die exakten Request-/Response-Schemata von `POST /v1/Ping` und
> `GET /v2/status` (Feldnamen, Typen, HTTP-Methoden, Authentifizierung) sind durch
> die bereitgestellten OpenAPI-Dokumente `v1.json` und `v2.json` im Projekt-Root
> verbindlich festgelegt. Frühere vorläufige Annahmen wurden ersetzt.

## 2. Technische Rahmenbedingungen

| Thema | Anforderung |
| --- | --- |
| PHP-Version | PHP 8.5 (Minimum `>=8.5`) |
| PHP-Patch-Version | Mindestens PHP 8.5.12 (Security-Release); das Docker-Image muss Composer und Xdebug (für Coverage) mitbringen |
| HTTP-Client | Guzzle (`guzzlehttp/guzzle`) |
| Autoloading | PSR-4, Namespace `DjThossi\ShippyProSdk\` |
| Code-Stil | PHP-CS-Fixer mit `@Symfony` + `@Symfony:risky`, `declare(strict_types=1)` überall |
| Tests | PHPUnit, **100 % Unit-Test-Abdeckung** |
| Coverage-Report | HTML-Report nach `html-coverage/` (wie Vorlagen) |
| Container | Docker / `docker-compose.yml` mit dem Image `ronisaha/php-8-debian:cli-8.5.12` (PHP 8.5.12, Composer und Xdebug vorinstalliert) |
| Build/Tooling | `Makefile` mit Targets für composer, test, test-fast, cs-fix und pro Beispiel |
| Lizenz | MIT |

> **Plattform-Hinweis:** Die veröffentlichten Tags von
> `ronisaha/php-8-debian:cli-8.5.12` decken derzeit nur `linux/amd64` ab; auf
> `arm64` (Apple Silicon) läuft das Image per Emulation.

### Projektstruktur (Zielbild, analog `ergosoft-sdk`)

```
shippy-pro-sdk/
├── composer.json
├── docker-compose.yml
├── Makefile
├── phpunit.xml.dist
├── .php-cs-fixer.php
├── .gitignore
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE
├── examples/
│   ├── ping.php
│   └── status.php
├── src/
│   ├── ShippyProFactory.php
│   ├── ShippyProConfiguration.php          (Interface)
│   ├── SimpleShippyProConfiguration.php
│   ├── Api/
│   │   ├── PingApi.php
│   │   └── StatusApi.php
│   ├── Domain/
│   │   ├── ApiKey.php
│   │   ├── BaseUrl.php
│   │   ├── RequestTimeout.php
│   │   ├── StatusCode.php
│   │   ├── PingResponse.php
│   │   ├── PingResponseBody.php
│   │   ├── StatusResponse.php
│   │   └── StatusResponseBody.php
│   ├── Exception/
│   │   ├── InvalidApiKeyException.php
│   │   ├── InvalidBaseUrlException.php
│   │   ├── InvalidRequestTimeoutException.php
│   │   └── MissingRequiredFieldsException.php
│   ├── Http/
│   │   └── Client.php
│   └── Mapper/
│       ├── PingMapper.php
│       └── StatusMapper.php
└── tests/
    ├── Unit/
    │   ├── Api/
    │   ├── Domain/
    │   ├── Http/
    │   └── Mapper/
    └── Integration/
```

## 3. Design-Prinzipien

### 3.1 Keine Arrays in der öffentlichen API (Kernwunsch)

- Nach außen hin gibt das SDK **ausschließlich getippte Objekte** zurück, niemals
  rohe Arrays.
- Eingaben (Request-Parameter) werden ebenfalls als Value Objects / typisierte
  Objekte modelliert, nicht als assoziative Arrays.
- Arrays sind nur intern erlaubt, nämlich an der JSON-(De-)Serialisierungs-Grenze
  innerhalb der `Mapper`-Klassen. Sobald die Daten diese Grenze verlassen, sind sie
  in Objekte gewandelt.
- Primitive Werte mit Fachbedeutung werden als Value Objects gekapselt
  (z. B. `ApiKey`, `BaseUrl`, `RequestTimeout`, `StatusCode`), analog zu
  `ergosoft-sdk` (`JobGuid`, `JobName`, …).

### 3.2 Immutability

- Value Objects und Response-Objekte sind `final readonly` bzw. `readonly`.
- Validierung findet im Konstruktor des jeweiligen Value Objects statt; ungültige
  Werte werfen eine spezifische Exception aus `src/Exception/`.

### 3.3 Trennung der Verantwortlichkeiten

- `Http\Client` kapselt Guzzle und die Authentifizierung; kennt keine Fachlogik.
- `Api\*Api`-Klassen bilden je einen Endpunkt ab, orchestrieren Request + Mapping.
- `Mapper\*Mapper`-Klassen übersetzen JSON ↔ Domain-Objekte.
- `ShippyProFactory` verdrahtet Client, Mapper und Api-Klassen.

## 4. Authentifizierung

Verbindlich aus den `securitySchemes` und der Pfad-`security` der bereitgestellten
OpenAPI-Dokumente `v1.json`/`v2.json` (Inhalt für Compliance umformuliert):

- **v1**: Bearer-Token-Authentifizierung, Header `Authorization: Bearer <API-Key>`.
  Alle v1-Calls sind **POST** + JSON mit der Body-Form
  `{"Method": "<MethodName>", "Params": <ParamsObjekt>}`.
- **v2**: ebenfalls Bearer-Token für geschützte Endpunkte, ABER `GET /v2/status`
  benötigt **KEINE** Authentifizierung (`security: []`, "No authentication
  required") und wird ohne `Authorization`-Header aufgerufen.
- **Base-URL** für beide Versionen: `https://api.shippypro.com`; die Version ist Teil
  des Pfads (`/v1/...`, `/v2/...`).
- Optionaler Header `Referer: <partner-name>` (konsistenter, kleingeschriebener
  Identifier) zur Partner-Identifikation.
- Rate-Limit: max. **20 Calls / 10 Sekunden**, sonst `429 Too Many Requests`.

Das SDK verwaltet den API-Key als `ApiKey`-Value-Object und verwendet
Bearer-Authentifizierung; für nicht authentifizierte Endpunkte wie `GET /v2/status`
wird **KEIN** `Authorization`-Header gesendet.

> Quelle (für Compliance umformuliert): bereitgestellte OpenAPI-Dokumente `v1.json`
> und `v2.json` (`securitySchemes` + Pfad-`security`).

## 5. Funktionale Anforderungen

### 5.1 Konfiguration & Factory

- **FR-1**: `ShippyProConfiguration` (Interface) stellt mindestens bereit:
  - `getApiKey(): ApiKey`
  - `getBaseUrl(): BaseUrl` (Default: `https://api.shippypro.com`)
  - `getRequestTimeout(): RequestTimeout` (Default: 10 Sekunden)
- **FR-2**: `SimpleShippyProConfiguration` ist eine `final readonly` Implementierung
  mit sinnvollen Defaults (nur der `ApiKey` ist Pflicht).
- **FR-3**: `ShippyProFactory` erzeugt die Api-Klassen über `createPingApi()` und
  `createStatusApi()` und verdrahtet Client + Mapper intern.

### 5.2 Endpunkt `POST /v1/Ping`

- **FR-4**: `PingApi::ping(): PingResponse` sendet einen mit Bearer-Auth
  authentifizierten **POST**-Request an `/v1/Ping` mit dem JSON-Body
  `{"Method": "Ping", "Params": {}}` (`PingParams` = leeres Objekt,
  `additionalProperties: false`).
- **FR-5**: `PingResponse` enthält den `StatusCode` und den getippten
  `PingResponseBody`.
- **FR-6**: `PingResponseBody` bietet ein getipptes boolesches Feld `result`
  (JSON-Feld `Result`, boolean, Beispielwert `true`). Die 200-Antwort enthält keine
  weiteren Felder.
- **FR-6a**: Mögliche Fehlercodes `400`, `401`, `403`, `404`, `429`, `500`, zugänglich
  über den `StatusCode`.

### 5.3 Endpunkt `/v2/status`

- **FR-7**: `StatusApi::status(): StatusResponse` sendet einen **GET**-Request an
  `/v2/status` **OHNE** Authentifizierung (kein `Authorization`-Header).
- **FR-8**: `StatusResponse` enthält den `StatusCode` und den getippten
  `StatusResponseBody`.
- **FR-9**: `StatusResponseBody` bietet zwei getippte, erforderliche Felder:
  `status` (string, enum/const mit ausschließlich dem Wert `HEALTHY`) und `version`
  (string, z. B. `"2.0.0"`).

### 5.4 Value Objects

- **FR-10**: `ApiKey` validiert, dass der Key nicht leer ist, sonst
  `InvalidApiKeyException`.
- **FR-11**: `BaseUrl` validiert eine gültige URL (wie `ergosoft-sdk`), sonst
  `InvalidBaseUrlException`.
- **FR-12**: `RequestTimeout` validiert einen positiven Timeout in Sekunden, sonst
  `InvalidRequestTimeoutException`.
- **FR-13**: `StatusCode` kapselt den HTTP-Statuscode mit Hilfsmethoden
  (`isSuccessful`, `isOk`, `isBadRequest`, `isForbidden`, `isNotFound`,
  `isTooManyRequests`, `isServerError`, …).

## 6. Fehlerbehandlung

- **FR-14**: Validierungsfehler von Value Objects werfen dedizierte, sprechende
  Exceptions aus `src/Exception/`.
- **FR-15**: Fehlende Pflichtfelder beim Mapping werfen eine
  `MissingRequiredFieldsException`, die die fehlenden Feldnamen benennt (analog
  `ergosoft-sdk`).
- **FR-16**: HTTP-Fehler der API (z. B. `401`, `403`, `429`, `5xx`) werden über den
  `StatusCode` im Response-Objekt zugänglich gemacht; das SDK unterdrückt
  Guzzle-Exceptions nicht stillschweigend (Verhalten final bei Implementierung
  festzulegen, konsistent zu den Vorlagen).

## 7. Symfony-Integration

- **FR-17**: Das SDK ist Framework-agnostisch, lässt sich aber leicht in Symfony per
  Autowiring einbinden (analog zur `README.md` von `ergosoft-sdk`).
- **FR-18**: Die `README.md` dokumentiert die `services.yaml`-Konfiguration
  (Parameter für API-Key/Base-URL, Autowiring der `Http\`- und `Api\`-Namespaces).

## 8. Beispiele (examples/)

- **FR-19**: `examples/ping.php` demonstriert einen Ping-Call end-to-end.
- **FR-20**: `examples/status.php` demonstriert den v2-Status-Call **OHNE** API-Key
  (der Endpunkt ist öffentlich; nur der Ping-Call benötigt den API-Key).
- **FR-21**: Jedes Beispiel ist über ein eigenes `Makefile`-Target ausführbar
  (`make examples-ping`, `make examples-status`), analog zu den Vorlagen.

## 9. Tests

- **FR-22**: 100 % Unit-Test-Abdeckung für `src/` (`tests/Unit`), gemessen über den
  PHPUnit-Coverage-Report.
- **FR-23**: Api-Tests mocken `Http\Client` und prüfen Endpoint-Pfad, Auth-Verhalten,
  Status-Code-Handling und Mapping (analog `TestCommunicationsApiTest`).
- **FR-24**: Value-Object-Tests decken gültige und ungültige Eingaben inkl. der
  jeweiligen Exceptions ab.
- **FR-25**: Mapper-Tests decken vollständige, minimale und fehlerhafte
  JSON-Eingaben ab.
- **FR-26**: PHPUnit läuft strikt (wie Vorlage: `failOnWarning`, `failOnRisky`,
  `failOnDeprecation`, `failOnNotice` etc.).

## 10. Qualität & Tooling

- **FR-27**: `.php-cs-fixer.php` entspricht dem Regelsatz der Vorlagen
  (`@Symfony`, `@Symfony:risky`, `declare_strict_types`, `global_namespace_import`,
  etc.).
- **FR-28**: `Makefile` bietet mindestens: `setup`, `phpLatest-composer-update`,
  `phpLatest-test`, `phpLatest-test-fast`, `php-cs-fix` sowie die `examples-*`-Targets.
- **FR-29**: `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`, `LICENSE` werden analog
  zu den Vorlagen angelegt.

## 11. Nicht-Ziele (für diese Iteration)

- Keine weiteren Endpunkte außer `/v1/Ping` und `/v2/status`.
- Kein eigenes Symfony-Bundle (nur Autowiring-Doku).
- Keine asynchronen/paralleln Requests, kein eingebautes Rate-Limiting (nur
  dokumentiert, dass das API-Limit bei 20/10s liegt).

## 12. Geklärte Punkte

Alle zuvor offenen Punkte sind geklärt:

1. ✅ `POST /v1/Ping` 200-Antwort: genau ein Feld `Result` (boolean). Siehe FR-6.
2. ✅ `/v2/status`: HTTP-Methode `GET`, Felder `status` (const `HEALTHY`) und
   `version` (string). Siehe FR-7 bis FR-9.
3. ✅ Base-URL `https://api.shippypro.com`, Version im Pfad (`/v1/...`, `/v2/...`).
   Siehe Abschnitt 4.
4. ✅ Mögliche HTTP-Fehlercodes für Ping: `400`, `401`, `403`, `404`, `429`, `500`;
   zugänglich über den `StatusCode`. Siehe FR-6a und FR-16.
5. ✅ PHP-Docker-Image: `ronisaha/php-8-debian:cli-8.5.12` (PHP 8.5.12, Composer +
   Xdebug vorinstalliert); `sineverba/php8xc:latest` ist zu alt.

Aktuell verbleiben keine offenen Punkte.
