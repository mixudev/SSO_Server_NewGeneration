# PRD / SRS — Laravel `ssoclient` Package

Status: Proposed, not implemented
Owner: Mixu SSO ecosystem

## 1. Product definition

`ssoclient` adalah package Laravel yang membuat aplikasi pihak ketiga dapat login ke Mixu SSO melalui OAuth 2.0/OIDC Authorization Code + PKCE tanpa mengulang implementasi state, nonce, discovery, token exchange, JWKS, session, logout, dan error handling secara tidak aman.

Package ini adalah relying-party/client package, bukan OAuth/OIDC provider dan bukan pengganti `laravel/passport` pada server SSO.

## 2. Goals

- Satu API Laravel untuk login OIDC.
- Discovery-driven configuration.
- Secure state, nonce, dan PKCE.
- Authorization-code callback handling.
- ID Token/JWKS verification.
- UserInfo retrieval berdasarkan scope.
- Local user provisioning/linking hooks.
- Refresh-token rotation handling untuk confidential clients.
- Local logout dan provider logout.
- Revocation support.
- Redacted diagnostics dan audit hooks.
- Test doubles untuk provider responses.

## 3. Non-goals

- Menjadi identity provider.
- Password grant.
- Menyimpan client secret di frontend.
- Native client support sebelum provider native policy selesai.
- SAML.
- Device Authorization Grant pada release pertama.
- Automatic account linking berbasis email tanpa policy.
- Menampilkan token/secret di exception atau log.

## 4. Supported release profile

Release 1:

- Laravel 12/13 compatibility matrix yang benar-benar diuji.
- PHP 8.3+.
- Confidential web client.
- OIDC Authorization Code.
- PKCE `S256`.
- Discovery, JWKS, ID Token, UserInfo, RP-Initiated Logout, revocation.
- Session-backed state binding.

Release 1 public SPA support hanya boleh diaktifkan setelah browser/XSS/token-storage acceptance test tersedia.

## 5. Configuration contract

```php
return [
    'issuer' => env('SSO_ISSUER'),
    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'redirect_uri' => env('SSO_REDIRECT_URI'),
    'scopes' => ['openid', 'profile', 'email'],
    'http' => [
        'timeout' => 10,
        'connect_timeout' => 3,
        'retry' => 2,
    ],
    'clock_skew' => 60,
    'state_ttl' => 600,
    'jwks_cache_ttl' => 3600,
    'user_provisioning' => [
        'enabled' => false,
        'require_verified_email' => true,
    ],
];
```

Requirements:

- issuer, client ID, and redirect URI required;
- production issuer/redirect must be HTTPS;
- secret may be configured only server-side;
- config cache must not serialize one-time authorization state;
- `scopes` must be allowlisted and deduplicated;
- timeout and retry bounded;
- no arbitrary endpoint override from request input.

## 6. Package architecture

Suggested structure:

```text
src/
├── Contracts/
│   ├── OidcClient.php
│   ├── TokenStore.php
│   ├── UserResolver.php
│   └── AuditLogger.php
├── Data/
│   ├── DiscoveryDocument.php
│   ├── AuthorizationContext.php
│   ├── TokenSet.php
│   └── IdentityClaims.php
├── Exceptions/
├── Http/
│   ├── DiscoveryClient.php
│   ├── TokenClient.php
│   ├── UserInfoClient.php
│   └── RevocationClient.php
├── Security/
│   ├── StateManager.php
│   ├── PkceGenerator.php
│   ├── NonceManager.php
│   ├── IdTokenVerifier.php
│   └── JwksCache.php
├── Services/
│   ├── AuthorizationUrlBuilder.php
│   ├── CallbackExchange.php
│   ├── IdentityProvisioner.php
│   └── LogoutService.php
├── Http/Controllers/
│   └── SsoController.php
├── Http/Middleware/
├── LaravelSsoClientServiceProvider.php
└── config/ssoclient.php
```

Controllers remain thin. Cryptographic verification belongs under `Security`; HTTP transport belongs under `Http`; local user decisions belong under `Services`/application callbacks.

## 7. Public API proposal

```php
Route::get('/login/sso', [SsoController::class, 'redirect'])
    ->name('sso.login');

Route::get('/login/sso/callback', [SsoController::class, 'callback'])
    ->name('sso.callback');

Route::post('/logout/sso', [SsoController::class, 'logout'])
    ->name('sso.logout');
```

```php
$url = $sso->authorizationUrl();
$identity = $sso->handleCallback($request);
$sso->logout($request);
```

The package must not expose raw access tokens from Blade by default. Advanced token access requires an explicit server-only contract.

## 8. Provider flow

### 8.1 Start

1. Resolve discovery using configured issuer.
2. Validate discovery issuer equals configured issuer.
3. Generate state, verifier, and nonce.
4. Store a one-time encrypted/hash-bound transaction with expiry.
5. Build authorization URL using discovery authorization endpoint.
6. Redirect.

### 8.2 Callback

1. Validate provider error response safely.
2. Consume state transaction atomically.
3. Compare state using constant-time comparison.
4. Validate callback has one code and expected context.
5. POST form-encoded token exchange over HTTPS.
6. Validate token response shape and token types.
7. Verify ID Token signature and claims.
8. Optionally call UserInfo using access token.
9. Resolve/provision local user through explicit application callback.
10. Regenerate local session ID.
11. Store only required local session/token data.
12. Redirect to an allowlisted intended URL.

### 8.3 Logout

1. Revoke local token set if configured.
2. Invalidate local session and regenerate CSRF token.
3. Build provider end-session URL only from discovery and registered client context.
4. Preserve an opaque local state binding.
5. Never accept arbitrary post-logout redirect from request input.

## 9. User provisioning contract

```php
interface UserResolver
{
    public function resolve(IdentityClaims $claims): Authenticatable;
}
```

Default package must not silently create users. Application chooses:

- lookup by `(issuer, sub)`;
- verified email linking policy;
- domain/organization restrictions;
- inactive account behavior;
- role mapping;
- audit event.

## 10. Storage contract

Required transient storage:

- state hash;
- PKCE verifier encrypted or server-side only;
- nonce hash;
- issuer/client/redirect binding;
- expiry;
- consumed timestamp.

Required persistent token storage only for confidential server-side clients:

- encrypted token values or secure token reference;
- expiry;
- scope;
- provider subject;
- issuer;
- generation metadata.

Never store raw state, authorization code, client secret, private key, or bearer token in logs/audit metadata.

## 11. Error and event contract

Package exceptions:

- `DiscoveryException`
- `StateMismatchException`
- `PkceException`
- `TokenExchangeException`
- `IdTokenValidationException`
- `UserInfoException`
- `ProviderLogoutException`
- `ConfigurationException`

Events may include:

- `SsoLoginStarted`
- `SsoLoginSucceeded`
- `SsoLoginFailed`
- `SsoLogoutCompleted`
- `SsoTokenRefreshFailed`

Event payloads must contain correlation ID, issuer host, client ID hash, and error category only. Never include tokens, authorization code, verifier, nonce, or secret.

## 12. Security requirements

- HTTPS and certificate verification mandatory in production.
- SSRF protection: issuer is configuration, not request input; reject private/loopback metadata endpoints in production unless explicit local mode.
- Exact issuer and redirect validation.
- `state` required and one-time.
- PKCE `S256` required.
- OIDC `nonce` required.
- Algorithm allowlist `RS256` only for current provider.
- JWKS cache must refresh once on unknown `kid`, then fail closed.
- Bounded clock skew.
- Session ID regeneration after successful login.
- CSRF on client-owned browser mutation routes.
- SameSite/Secure/HttpOnly cookie policy.
- No open redirect after login/logout.
- No token in URLs, logs, exception messages, or HTML.
- Refresh token single-flight and replay-safe rotation.
- Constant-time comparisons for state, nonce, and hashes.
- Dependency and TLS failure must fail closed.

## 13. Test requirements

Unit:

- PKCE generation and challenge.
- state/nonce binding.
- discovery issuer validation.
- JWKS selection and unknown `kid` refresh.
- JWT algorithm confusion.
- expiry and clock skew.
- audience/issuer/nonce validation.

Feature:

- login redirect;
- callback success;
- missing/mismatched/expired state;
- duplicate callback;
- provider error;
- invalid code;
- invalid token response;
- invalid signature;
- wrong issuer/audience/nonce;
- UserInfo scope behavior;
- logout cleanup;
- open redirect attempts;
- inactive local account;
- account linking policy.

Integration:

- real Mixu SSO discovery/JWKS/token endpoints in isolated HTTPS environment;
- key rotation overlap;
- refresh rotation/replay;
- timeout and provider outage;
- Laravel session/cache drivers.

## 14. Definition of done

Package client tidak boleh dirilis sebelum:

- contract API terdokumentasi;
- Laravel/PHP matrix diuji;
- security tests lulus;
- no-secret log test lulus;
- external HTTPS smoke test lulus;
- provider key rotation test lulus;
- package install/config cache test lulus;
- migration and rollback behavior verified;
- signed release and changelog tersedia.
