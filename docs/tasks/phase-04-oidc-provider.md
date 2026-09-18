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
- Paths: `app/Domain/Oidc/Contracts/IdTokenBuilderInterface.php`, `app/Application/Oidc/IdTokenBuilder.php`, `tests/Unit/IdTokenBuilderTest.php`.
- Produce RS256 tokens with validated `iss`, `sub`, `aud`, `iat`, `exp`, and nonce; resolve claims through policy engine.
- Verify: `php artisan test tests/Unit/IdTokenBuilderTest.php --compact`.

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
