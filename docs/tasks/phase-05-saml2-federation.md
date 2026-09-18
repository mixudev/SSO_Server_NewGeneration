# Phase 5 — SAML 2.0 Federation

## Scope

Add SAML support exclusively behind protocol adapters. XML parsing and cryptographic verification must be hardened before enabling production routes.

## Tasks

### 5.1 Adapter contracts
- Paths: `app/Domain/Saml/Contracts/SamlIdpAdapterInterface.php`, `app/Domain/Saml/Contracts/SamlSpAdapterInterface.php`.
- Define metadata, request, assertion, response, and logout value objects without importing a concrete SAML library.
- Verify: `php artisan test --filter=SamlAdapterContract --compact`.

### 5.2 Metadata endpoint
- Paths: `app/Http/Controllers/Saml/MetadataController.php`, `tests/Feature/SamlMetadataTest.php`.
- Generate metadata from active entity ID, endpoints, certificates, and signing keys; reject incomplete configuration.
- Verify: `php artisan test tests/Feature/SamlMetadataTest.php --compact`.

### 5.3 Request validation
- Paths: `app/Application/Saml/SamlRequestValidator.php`, `tests/Unit/SamlRequestValidatorTest.php`.
- Validate XML schema, destination, issuer, audience, timestamps, signed content, and request ID; disable external entities.
- Verify: `php artisan test tests/Unit/SamlRequestValidatorTest.php --compact`.

### 5.4 Assertion and response builder
- Paths: `app/Application/Saml/SamlAssertionBuilder.php`, `tests/Unit/SamlAssertionBuilderTest.php`.
- Apply audience, recipient, temporal, subject confirmation, attribute, and signature rules from application policy.
- Verify: `php artisan test tests/Unit/SamlAssertionBuilderTest.php --compact`.

### 5.5 Single Logout
- Paths: `app/Http/Controllers/Saml/LogoutController.php`, `tests/Feature/SamlLogoutTest.php`.
- Bind logout requests to the authenticated session and prevent unsigned or replayed requests.
- Verify: `php artisan test tests/Feature/SamlLogoutTest.php --compact`.

### 5.6 XML security suite
- Paths: `tests/Feature/SamlXmlSecurityTest.php`.
- Reject XXE, entity expansion, signature wrapping, assertion substitution, replay, and altered destination.
- Verify: `php artisan test tests/Feature/SamlXmlSecurityTest.php --compact`.

## Acceptance Criteria

- No SAML library class appears in domain code.
- All accepted assertions are signed, scoped, time-bound, and audience-bound.
- XML abuse tests pass before routes are enabled.
- Run the full suite, Pint, and `git diff --check`.
