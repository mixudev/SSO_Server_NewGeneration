# Development Roadmap — Mixu SSO / Identity Platform

## Phase 0 — Repository & documentation foundation

Deliver:

```text
README
PRD
SRS
Architecture
Protocol contracts
Data model
Security model
AI agent contract
ADR
```

Also establish:
- Tabler dashboard baseline and asset pipeline;
- modular route loading;
- test folder taxonomy and adversarial testing harness;

- CI;
- Pint/static analysis;
- test matrix;
- architecture tests;
- dependency audit;
- `.env.example`;
- local development profile.

Do not start protocol implementation before Phase 0 is accepted.

## Phase 1 — Identity Core

Implement:

- [done] Application entity;
- [done] Organization entity;
- scope registry;
- claims registry;
- application policy;
- audit subsystem;
- signing key abstraction;
- authentication adapter for `mixudev/laravel-authentication`;
- session model;
- authorization transaction contract.

Acceptance:

- zero protocol dependency in domain layer;
- all critical contracts have tests.

## Phase 2 — Admin Control Plane

### UI implementation order

Before implementing feature pages:

```text
install official Tabler asset dependency
→ build base layout
→ create local Blade primitives
→ create navigation/sidebar
→ create table/form/feedback primitives
→ implement feature pages
```

Never paste a complete template into a single view. Extract reusable pieces first.

Implement dashboard:

```text
Overview
Applications
Users
Sessions
Scopes
Claims
Keys
Audit
Settings
Developer Center
```

Application wizard:

```text
Basic info
→ protocol/client type
→ redirect URIs
→ scopes
→ claim policy
→ security policy
→ review
→ create credentials
```

## Phase 3 — OAuth2 Infrastructure

Integrate Passport only through infrastructure adapter.

Implement:

- client registry synchronization;
- authorization code flow;
- PKCE policy;
- scope policy;
- client credentials;
- token revocation;
- token lifecycle observability.

Acceptance:

- OAuth2 conformance tests pass;
- Passport classes are not imported by domain classes.

## Phase 4 — OIDC Provider

Implement:

- discovery;
- JWKS;
- ID Token issuance;
- nonce;
- UserInfo;
- OIDC claims;
- `openid` scope;
- login/consent policy;
- OIDC integration tests.

Acceptance:

```text
Issuer discovery
Authorization Code
PKCE
ID Token verification
UserInfo
JWKS rotation
```

## Phase 5 — Hardening

Implement:

- rate limits;
- replay stores;
- key rotation job;
- transaction cleanup;
- SSRF-safe metadata fetcher;
- security event normalization;
- security regression suite;
- operational monitoring.

## Phase 6 — SAML IdP

Do not place SAML library calls into controllers/domain.

Create:

```text
SamlProtocolAdapter
SamlMetadataService
SamlRequestValidator
SamlAssertionFactory
SamlAttributeMapper
SamlReplayStore
SamlCertificateStore
```

Engine options:

1. external mature SAML engine behind adapter;
2. lower-level XML/security libraries behind adapter;
3. never custom-implement cryptographic primitives.

SimpleSAMLphp is a reference-capable full PHP IdP/SP implementation and currently documents SAML IdP, metadata, authentication sources, attribute filtering and advanced profiles; if used operationally, isolate it behind the SAML adapter rather than allowing SimpleSAMLphp configuration concepts to leak into the core model. citeturn701336search0

## Phase 7 — Developer Experience

Deliver:

- Laravel integration package;
- OIDC auto-discovery helper;
- config generator;
- health check command;
- `sso:doctor` Artisan command;
- docs examples.

Example target:

```bash
composer require mixudev/laravel-sso-client
php artisan sso:discover https://sso.example.com
php artisan sso:install
```

## Phase 8 — Federation

Optional:

- Socialite upstream IdP;
- external OIDC IdP;
- SAML upstream IdP;
- account linking;
- domain verification.

## Phase 9 — Enterprise

- SCIM;
- group mapping;
- organization policy;
- dynamic client registration;
- device/session management;
- webhooks;
- advanced admin roles.

## Definition of done per phase

A phase is done only if:

1. implementation follows architecture;
2. tests cover happy and failure paths;
3. public contracts are documented;
4. migrations are reversible/forward-safe;
5. audit events exist for security-sensitive mutations;
6. AI agent docs remain synchronized;
7. no new giant class/file is introduced.


## Foundation additions — authorization & recovery

Before protocol-heavy implementation, baseline:

```text
mixudev authentication
→ Passport
→ Spatie Permission
→ Tabler/Blade
→ modular routes
→ Spatie Backup
→ security baseline
→ adversarial test harness
```

OIDC/SAML implementation should not be considered production-ready until authorization and recovery controls are exercised in tests.
