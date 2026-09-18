# Architecture — Mixu SSO / Identity Platform

## 1. Architectural goal

Arsitektur harus memungkinkan pertumbuhan berikut tanpa memindahkan business logic dari satu layer ke layer lain:

```text
OAuth2 → OIDC → SAML → Federation → SCIM → Advanced Policy
```

Prinsip paling penting adalah **protocol independence**.

Core identity tidak boleh berkata:

```php
Passport::...
Saml::...
Socialite::...
```

Sebaliknya core berbicara melalui port:

```php
$tokenIssuer->issue(...);
$identity->authenticate(...);
$claims = $claimsResolver->resolve(...);
```

Vendor menjadi adapter.

## 2. Recommended stack

### Core

- Laravel 13.x
- Eloquent / Database
- Laravel Cache
- Laravel Queue
- Laravel Notifications
- Laravel Scheduler
- Laravel HTTP Client untuk controlled outbound requests

### Authentication

`mixudev/laravel-authentication` sebagai authentication provider. Package tersebut dipublikasikan sebagai authentication package untuk Laravel 11/12/13 dan PHP 8.2–8.5; project ini memperlakukannya sebagai dependency boundary, bukan sebagai domain SSO. citeturn110952search2

### OAuth2

Laravel Passport sebagai OAuth2 authorization server implementation. Passport dibangun di atas `league/oauth2-server` dan menyediakan authorization/token infrastructure, scopes, PKCE, client credentials, dan route authorization. citeturn300680search0

### OIDC

Custom application layer di atas Passport:

- discovery;
- ID Token issuance;
- UserInfo;
- nonce validation;
- claims mapping;
- OIDC metadata;
- protocol-specific error/response rules.

### Dashboard UI

- Custom modular dashboard shell through Blade components and the Laravel/Vite asset pipeline.
- Blade is the primary rendering layer.
- Bootstrap Icons come through the self-hosted `bootstrap-icons` npm package; styling is handled by Tailwind CSS v4.
- Vanilla JavaScript is the default interaction layer. Alpine.js/Livewire may be introduced selectively.
- Vue is explicitly not part of the dashboard baseline.

Bootstrap Icons documentation: https://icons.getbootstrap.com/

### Optional Laravel packages

- `laravel/sanctum`: first-party dashboard/API authentication bila dibutuhkan; jangan mencampurnya dengan OAuth2 token issuance.
- `laravel/socialite`: upstream social/federated login bila SSO Server nanti menjadi client terhadap Google/GitHub/Microsoft atau provider lain. Socialite menyediakan driver untuk beberapa provider OAuth/social. citeturn110952search0
- `spatie/laravel-permission:^8.3`: internal RBAC for the control plane. Package handles role/permission persistence and integrates with Laravel Gate; application domain/policies remain project-owned. citeturn722739search1turn722739search4
- `spatie/laravel-backup:^10`: application/database backup, cleanup and health monitoring. Production backup destination should be external to the primary host. citeturn230300search1turn230300search3
- `laravel/horizon`: queue operations.
- `laravel/telescope`: development/staging diagnostics; production exposure harus dikunci.
- `laravel/pulse`: optional application metrics.

Fortify tidak diperlukan untuk core login bila `mixudev/laravel-authentication` sudah menjadi auth provider utama; mengaktifkannya tanpa use case akan membuat dua auth boundary yang bertumpang tindih.

## 2.1 Verified dependency baseline (2026-09-17)

| Package | Role | Policy | Verified baseline |
|---|---|---|---|
| `mixudev/laravel-authentication` | user authentication boundary | required | `v1.7.5` |
| `laravel/passport` | OAuth2 server engine | required for OAuth2/OIDC implementation | `v13.8.0` |
| `laravel/sanctum` | optional first-party SPA/API auth | only for dashboard/internal API when needed | `v4.3.3` |
| `laravel/socialite` | optional upstream social IdP client | federation feature only | current compatible release selected by Composer |
| `laravel/horizon` | queue operations | recommended when Redis queues are used | `v5.49.0` |
| `laravel/pulse` | application performance telemetry | optional | `v1.8.1` |

At verification time, Passport `v13.8.0` was the latest Packagist release and supports Laravel 13. A 2026 security advisory affected Passport versions `>=13.0.0 <13.7.1`; therefore the project must not pin an affected version. citeturn796571search0turn300680search5

Laravel Sanctum is intended for SPA/simple API authentication, so it must not replace Passport as the OAuth2 authorization server. citeturn796571search3

Laravel Horizon is the queue dashboard/operations layer and supports Laravel 13. citeturn796571search1

There is no Laravel-maintained SAML IdP package in the official dependency set used by this design. SAML therefore remains behind a dedicated adapter and may use a mature external SAML implementation. SimpleSAMLphp is one reference implementation that supports SAML 2.0 as both SP and IdP. citeturn701336search8

## 3. Layer model

```text
┌─────────────────────────────────────────────┐
│ Presentation                               │
│ Dashboard / API / Protocol Controllers      │
├─────────────────────────────────────────────┤
│ Application                                 │
│ Use Cases / Commands / Queries / DTOs        │
├─────────────────────────────────────────────┤
│ Domain                                      │
│ Identity / Application / Authorization       │
│ Session / Claims / Keys / Audit / Policy     │
├─────────────────────────────────────────────┤
│ Ports                                       │
│ Authentication / Token / Signing / SAML      │
│ Persistence / Audit / Events / Clock          │
├─────────────────────────────────────────────┤
│ Infrastructure                              │
│ Eloquent / Passport / Redis / Queue / Mail   │
│ OIDC implementation / SAML engine / HTTP     │
└─────────────────────────────────────────────┘
```

Dependency rule:

```text
Presentation → Application → Domain
Infrastructure → Ports
Application/Domain may depend on interfaces, never concrete vendor implementation.
```

## 4. Suggested Laravel structure

```text
app/
├── Domain/
│   ├── Identity/
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   ├── Contracts/
│   │   └── Services/
│   ├── Applications/
│   ├── Authorization/
│   ├── Roles/
│   ├── Permissions/
│   ├── Sessions/
│   ├── Claims/
│   ├── Credentials/
│   ├── Keys/
│   ├── Audit/
│   ├── Policies/
│   └── Shared/
│
├── Application/
│   ├── Commands/
│   ├── Queries/
│   ├── DTOs/
│   ├── Handlers/
│   └── Services/
│
├── Protocols/
│   ├── Oidc/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   ├── Responses/
│   │   └── Mappers/
│   ├── OAuth2/
│   │   └── ...
│   └── Saml/
│       ├── Controllers/
│       ├── Metadata/
│       ├── Assertions/
│       └── Mappers/
│
├── Infrastructure/
│   ├── Auth/
│   │   └── MixuAuthentication/
│   ├── Passport/
│   ├── Persistence/
│   ├── Security/
│   ├── Keys/
│   ├── Cache/
│   ├── Queue/
│   ├── Http/
│   └── Sso/
│
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   ├── Middleware/
│   └── Resources/
│
├── Policies/
├── Providers/
└── Support/

resources/
├── views/
│   ├── auth/
│   ├── consent/
│   ├── dashboard/
│   └── errors/
└── js/                         # optional if using SPA/Inertia

database/
├── migrations/
├── factories/
└── seeders/

tests/
├── Unit/
├── Feature/
├── Integration/
├── Protocol/
├── Security/
└── Contract/
```

## 5. Bounded contexts

### Identity

Canonical user identity and authentication context.

### Application

Registered relying party/client/service provider.

### Authorization

Authorization request, scopes, consent, grants, and policy decisions.

### Token

OAuth token lifecycle. In implementation, Passport adapter handles vendor mechanics.

### Claims

Canonical claims → OIDC/SAML-specific claims.

### Session

SSO browser session, device/session metadata, session revocation.

### Keys

Signing key lifecycle and public key publication.

### Audit

Security and administrative evidence.

### Federation

External identity provider relationships in future.

## 6. Core contracts

### AuthenticationProvider

```php
interface AuthenticationProvider
{
    public function currentUser(): ?AuthenticatedIdentity;

    public function beginAuthentication(AuthenticationContext $context): AuthenticationResult;

    public function requiresReauthentication(AuthenticatingParty $party): bool;
}
```

The exact names may be changed during implementation, tetapi contract concept harus dipertahankan.

### AuthorizationRequestRepository

```php
interface AuthorizationRequestRepository
{
    public function create(CreateAuthorizationRequest $input): AuthorizationTransaction;
    public function findActive(string $transactionId): ?AuthorizationTransaction;
    public function consume(string $transactionId): AuthorizationTransaction;
}
```

### TokenIssuer

```php
interface TokenIssuer
{
    public function issueAuthorizationCodeTokenSet(TokenContext $context): TokenSet;
    public function issueClientCredentialsTokenSet(TokenContext $context): TokenSet;
    public function revoke(TokenIdentifier $token): void;
}
```

### IdTokenIssuer

```php
interface IdTokenIssuer
{
    public function issue(IdTokenContext $context): SignedIdToken;
}
```

### ClaimsResolver

```php
interface ClaimsResolver
{
    public function resolve(ClaimsContext $context): CanonicalClaims;
}
```

### SigningKeyProvider

```php
interface SigningKeyProvider
{
    public function activeSigningKey(): SigningKey;
    public function verificationKeys(): iterable;
}
```

### AuditRecorder

```php
interface AuditRecorder
{
    public function record(SecurityEvent $event): void;
}
```

## 7. Protocol adapters

```text
                         ┌───────────────┐
                         │ Identity Core │
                         └──────┬────────┘
                                │
          ┌─────────────────────┼─────────────────────┐
          ▼                     ▼                     ▼
    OAuth2 Adapter         OIDC Adapter          SAML Adapter
          │                     │                     │
      Passport            Custom OIDC layer      SAML engine
```

Protocol adapter boleh membaca request dan mengubahnya menjadi application command. Adapter tidak boleh mengandung business policy yang panjang.

## 8. OIDC flow

```text
Client
  │
  │ GET /oauth/authorize
  ▼
OIDC Request Validator
  │
  ├─ validate client
  ├─ exact redirect match
  ├─ validate response type
  ├─ validate scope
  ├─ validate PKCE
  └─ validate nonce/state context
  │
  ▼
Authorization Transaction
  │
  ▼
SSO Authentication Boundary
  │
  ▼
Consent / Policy
  │
  ▼
Authorization Code
  │
  ▼
Token Endpoint
  │
  ├─ redeem code
  ├─ verify PKCE
  ├─ issue access/refresh token
  └─ issue ID Token when OIDC
```

OpenID Connect Authorization Code flow secara konseptual memang mengarahkan user ke Authorization Endpoint, melakukan authentication/consent, mengembalikan authorization code, kemudian code ditukar di Token Endpoint menjadi ID Token + Access Token. citeturn600504search0

## 9. SAML flow

```text
SP
 │
 │ AuthnRequest
 ▼
SAML Adapter
 │
 │ validate SP
 │ validate request/binding
 ▼
SSO Authentication Boundary
 │
 │ identity + policy
 ▼
Assertion Builder
 │
 │ sign assertion
 ▼
HTTP POST/Redirect
 │
 ▼
SP ACS
```

SAML implementation harus diisolasi karena XML signature, canonicalization, bindings, metadata, dan assertion validation memiliki kompleksitas tersendiri. SimpleSAMLphp membuktikan bahwa SAML IdP adalah domain terpisah dengan metadata IdP/SP, authentication sources, attribute filters, dan key material; dokumentasinya dapat dijadikan referensi interoperability bila engine eksternal dipilih. citeturn701336search0turn701336search5

## 10. Why Passport is not the OIDC core

Passport menyelesaikan OAuth2 server behavior. OIDC menambahkan identity layer: ID Token, claims, nonce, discovery, UserInfo, issuer consistency, dan OIDC-specific validation. Karena itu architecture harus tetap memisahkan:

```text
OAuth2 token engine != OIDC identity layer
```

Dengan demikian Passport dapat di-upgrade/replaced tanpa mengubah domain claims/policy.

## 11. Key architecture

Gunakan asymmetric signing key untuk ID Token.

```text
Private Key
   │
   ▼
Token Signer
   │
   └── kid=key_2026_01

Public Keys
   │
   ▼
/.well-known/jwks.json
```

Rotasi:

```text
K1 ACTIVE
K2 GENERATED

↓ activate K2

K1 VERIFY-ONLY
K2 ACTIVE

↓ retention expires

K1 DESTROYED
K2 ACTIVE
```

## 12. Cache strategy

Cache candidates:

- OIDC discovery metadata;
- JWKS public set;
- application configuration hot-path;
- scope definitions;
- protocol capability metadata.

Jangan cache authorization decisions tanpa explicit invalidation strategy.

## 13. Queue strategy

Async jobs untuk:

- audit export;
- email/notification;
- webhook;
- security aggregation;
- cleanup expired transactions/tokens;
- key lifecycle housekeeping.

Authorization decision path tidak boleh bergantung pada asynchronous queue.

## 14. Multi-tenant readiness

Schema dari hari pertama mendukung:

```text
organization
 ├── applications
 ├── users/memberships
 ├── scopes/policies (where scoped)
 ├── SAML providers/configuration
 └── audit scope
```

Tidak semua global resource harus memiliki organization_id. Key platform-wide dapat tetap global; key tenant-specific adalah extension point.

## 15. Error isolation

Vendor exceptions harus diterjemahkan:

```text
Vendor Exception
      ↓
Infrastructure Adapter
      ↓
Domain/Application Error
      ↓
Protocol Error Response
```

Jangan biarkan stack trace, SQL error, client secret, atau library-specific exception name keluar ke public protocol response.

## 16. Dependency inversion rules

DILARANG:

```text
Controller → Eloquent Model → Passport → custom logic
```

DIREKOMENDASIKAN:

```text
Controller
  → Command/Query
    → Domain contract
      → Infrastructure adapter
```

Eloquent models boleh berada di Infrastructure/Persistence bila domain entity perlu dipisahkan.

## 17. Future extension points

- `AuthenticationProvider`
- `TokenIssuer`
- `IdTokenIssuer`
- `ClaimsResolver`
- `ProtocolHandler`
- `SigningKeyProvider`
- `AuditRecorder`
- `PolicyEvaluator`
- `ConsentStore`
- `AuthorizationTransactionStore`
- `FederatedIdentityProvider`
- `ProvisioningProvider`

Semua extension point harus berupa interface/contract kecil yang focused.


## 18. Presentation architecture

The UI layer SHALL be treated as a replaceable adapter around the application layer.

```text
resources/views/
├── layouts/
│   ├── admin.blade.php
│   ├── auth.blade.php
│   └── guest.blade.php
├── partials/
│   ├── admin/
│   └── shared/
├── components/
│   ├── ui/
│   ├── form/
│   ├── navigation/
│   ├── security/
│   └── protocol/
└── pages/admin/
    ├── dashboard/
    ├── applications/
    ├── organizations/
    ├── users/
    ├── sessions/
    ├── oauth/
    ├── oidc/
    ├── saml/
    ├── scopes/
    ├── claims/
    ├── keys/
    ├── audit/
    ├── security/
    ├── developer/
    └── settings/
```

Page views compose data; reusable components render UI. Business logic never belongs in Blade.

## 19. Route architecture

```text
routes/
├── web.php          # composition only
├── admin.php        # admin control plane
├── consent.php      # end-user consent
├── oidc.php         # OIDC owned endpoints
├── oauth.php        # project-owned OAuth behavior
├── saml.php         # SAML protocol adapter endpoints
├── api.php          # dashboard/SDK APIs when required
└── health.php       # minimal health endpoints
```

Vendor-registered Passport routes remain vendor-managed; project code MUST NOT duplicate them merely to keep URLs visually uniform.

## 20. Infrastructure dependency direction

```text
Blade/HTTP/Protocol controllers
        ↓
Application use cases / Queries
        ↓
Domain contracts
        ↑
Infrastructure adapters
        ├── Mixu Authentication
        ├── Passport
        ├── SAML engine
        ├── cache/queue/storage
        └── HTTP clients
```

No controller may orchestrate persistence + token signing + audit + notification in one method.


## 21. Test architecture

Security-sensitive systems should not put every test into `tests/Unit`. Test location communicates the scope of the invariant being verified.

Recommended structure:

```text
tests/
├── Unit/
│   ├── Domain/
│   │   ├── Authorization/
│   │   ├── Claims/
│   │   ├── Identity/
│   │   ├── Keys/
│   │   ├── Policies/
│   │   └── Sessions/
│   └── Protocol/
│       ├── Oidc/
│       ├── OAuth2/
│       └── Saml/
├── Feature/
│   ├── Admin/
│   ├── Consent/
│   ├── Oidc/
│   ├── OAuth2/
│   └── Saml/
├── Integration/
│   ├── Authentication/
│   ├── Passport/
│   ├── Keys/
│   ├── Queue/
│   └── Storage/
├── Security/
│   ├── Authorization/
│   ├── Replay/
│   ├── TenantIsolation/
│   ├── Redirects/
│   ├── Tokens/
│   ├── KeyRotation/
│   ├── SsrF/
│   └── Saml/
├── Contract/
│   ├── Oidc/
│   ├── OAuth2/
│   └── Saml/
└── Browser/
    ├── Dashboard/
    └── Consent/
```

Laravel provides built-in unit/feature testing support; browser tests can use Laravel Dusk for real browser behavior. Sources: https://laravel.com/docs/13.x/testing and https://laravel.com/docs/dusk

## 22. Route registration in modern Laravel

Laravel's recent application structure configures routing in `bootstrap/app.php`. The project SHOULD keep the standard framework entry point and use it as the composition layer for route files.

Conceptual example:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            require base_path('routes/oidc.php');
            require base_path('routes/oauth.php');
            require base_path('routes/saml.php');
            require base_path('routes/health.php');
        },
    );
```

This is a conceptual composition pattern; the final middleware/group registration must be implemented deliberately per protocol. Do not put token endpoints behind browser middleware only because they live under an OAuth URL.

Reference framework documentation: https://laravel.com/docs/13.x/routing
