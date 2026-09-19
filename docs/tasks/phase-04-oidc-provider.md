# Phase 4 — OpenID Connect Provider

## Scope

Add OIDC endpoints on top of the tested OAuth 2.0 infrastructure, using active signing keys and application claim policies.

## Tasks

### 4.1 Discovery
- Paths: `routes/web.php`, `app/Http/Controllers/Oidc/DiscoveryController.php`, `tests/Feature/OidcDiscoveryTest.php`.
- Return issuer, authorization endpoint, token endpoint, userinfo endpoint, JWKS URI, supported response types, scopes, claims, and signing algorithms from active configuration.
- Verify: `php artisan test tests/Feature/OidcDiscoveryTest.php --compact`.

### 4.2 JWKS
- Paths: `app/Http/Controllers/Oidc/JwksController.php`, `tests/Feature/OidcJwksTest.php`.
- Expose public JWK values only; include active and still-valid rollover keys, never private material.
- Verify: `php artisan test tests/Feature/OidcJwksTest.php --compact`.

### 4.3 ID token builder
- Builder slice implemented at `app/Domain/Oidc/Services/IdTokenBuilder.php` with native OpenSSL RS256 signing, active `kid`, UUID `sub`, validated issuer/audience/time/nonce inputs, and reserved-claim protection.
- Unit coverage: `tests/Unit/Domain/Oidc/IdTokenBuilderTest.php` (2 tests, 9 assertions).
- Added `app/Domain/Oidc/Services/IdTokenVerifier.php` with strict RS256/kid validation, signature verification, issuer/audience/nonce/time checks, and algorithm-confusion rejection. Builder now includes required `auth_time`.
- Coverage: builder/verifier unit tests plus OIDC nonce binding (10 tests, 26 assertions across the slice).
- Passport authorization-code token exchange now uses an application-owned `OidcBearerTokenResponse` adapter to issue a signed RS256 `id_token` for OIDC transactions.
- ID Token issuance reads the persisted versioned claim policy, includes the encrypted-at-rest transaction nonce, clears the encrypted nonce after successful issuance, and is verified through the native OpenSSL verifier.
- OIDC authorization requires both `openid` scope and nonce; token endpoint is Passport-owned but has an application rate-limit middleware attached.
- Remaining: complete standards-negative token error coverage, refresh-token ID Token semantics, UserInfo claim-policy integration coverage, and external client smoke tests.

### 4.4 UserInfo
- Paths: `app/Http/Controllers/Oidc/UserInfoController.php`, `tests/Feature/OidcUserInfoTest.php`.
- Require a valid access token and return only claims allowed by scopes and application policy.
- Verify: `php artisan test tests/Feature/OidcUserInfoTest.php --compact`.

### 4.5 Replay and conformance tests
- Paths: `tests/Feature/OidcReplayProtectionTest.php`, `tests/Feature/OidcConformanceTest.php`.
- Test nonce replay, issuer mismatch, audience mismatch, expired token, algorithm confusion, and claim overexposure.
- Verify: `php artisan test tests/Feature/Oidc --compact`.

## Acceptance Criteria

- Discovery reflects active deployment configuration and keys.
- JWKS contains no secret material.
- ID tokens verify with published keys and reject tampering.
- Full PHP tests, Pint, view cache, and `git diff --check` pass.
