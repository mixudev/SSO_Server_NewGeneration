# Phase 3 — OAuth 2.0 Infrastructure

## Scope

Integrate OAuth 2.0 through an infrastructure adapter while keeping protocol/vendor classes out of `app/Domain`.

## Tasks

### 3.1 Package and boundary audit
- Confirm installed package with `composer show laravel/passport`.
- Inspect `app/Domain`, `app/Application`, and `app/Infrastructure` before creating symbols.
- Target: domain contracts contain no Passport imports.
- Verify: `composer show laravel/passport` and `grep -R "Laravel\\Passport" app/Domain` (expected: no matches).

### 3.2 Client repository adapter
- Paths: `app/Domain/OAuth/Contracts/ClientRepositoryInterface.php`, `app/Infrastructure/OAuth/PassportClientRepository.php`.
- Write failing repository contract tests first, then implement CRUD mapping for application clients.
- Verify: `php artisan test --filter=PassportClientRepository --compact`.

### 3.3 Authorization Code and PKCE
- Paths: `app/Http/Controllers/OAuth/AuthorizationController.php`, `app/Http/Middleware/EnforcePkceS256.php`, `tests/Feature/OAuthAuthorizationCodeTest.php`.
- Require S256 for public clients, bind code to client, redirect URI, user, and code challenge, and make codes single-use.
- TDD cases: valid exchange, verifier mismatch, missing PKCE, code replay, expired code.
- Verify: `php artisan test tests/Feature/OAuthAuthorizationCodeTest.php --compact`.

### 3.4 Client Credentials grant
- Paths: `app/Http/Controllers/OAuth/TokenController.php`, `tests/Feature/OAuthClientCredentialsTest.php`.
- Validate confidential client authentication and permitted scopes; never log secrets.
- Verify: `php artisan test tests/Feature/OAuthClientCredentialsTest.php --compact`.

### 3.5 Revocation and lifecycle
- Paths: `app/Http/Controllers/OAuth/RevocationController.php`, `tests/Feature/OAuthTokenLifecycleTest.php`.
- Implement idempotent revocation and audit success/failure without exposing token values.
- Verify: `php artisan test tests/Feature/OAuthTokenLifecycleTest.php --compact`.

## Acceptance Criteria

- OAuth flows conform to configured redirect, scope, PKCE, expiry, and revocation policies.
- Domain code imports no Passport classes.
- Replay, downgrade, redirect traversal, and secret-redaction tests pass.
- Run `php artisan test --compact`, `vendor/bin/pint --dirty --format agent`, and `git diff --check`.
