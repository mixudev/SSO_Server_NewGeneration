# SRS — Mixu SSO / Identity Platform

- Document ID: `SSO-SRS-001`
- Version: `0.1`
- Status: Baseline
- Date: `2026-09-17`

## 1. System context

Sistem memiliki empat aktor utama:

```text
End User
   │
   ▼
SSO Server ─────── Admin Dashboard
   │
   ├── OIDC/OAuth2 ─── Web / SPA / Native / API Clients
   │
   └── SAML IdP ─────── SAML Service Providers
```

Authentication user disediakan oleh `mixudev/laravel-authentication` melalui contract `AuthenticationProvider` internal. SSO core hanya membutuhkan jawaban: siapa usernya, bagaimana authentication context-nya, dan apakah user sudah terautentikasi.

## 2. Functional requirements

### FR-001 Application Registry

Sistem SHALL menyediakan CRUD application dengan field minimal:

- organization;
- name;
- slug/identifier;
- protocol mode;
- client type;
- status;
- redirect URIs;
- post logout redirect URIs;
- allowed scopes;
- consent policy;
- claim policy;
- client credential metadata.

### FR-002 Client Authentication

Sistem SHALL mendukung:

- confidential client authentication;
- public client tanpa secret;
- PKCE;
- client credentials untuk machine-to-machine.

Client secret SHALL tidak ditampilkan ulang setelah one-time reveal kecuali ada explicit rotation flow.

### FR-003 OAuth Authorization

Sistem SHALL mendukung Authorization Code Flow sebagai primary interactive flow.

Parameter yang perlu diproses secara ketat antara lain:

- `client_id`;
- `redirect_uri`;
- `response_type`;
- `scope`;
- `state`;
- `code_challenge`;
- `code_challenge_method`;
- `nonce` untuk OIDC.

### FR-004 OIDC Discovery

Sistem SHALL menyediakan:

```text
/.well-known/openid-configuration
```

Metadata minimal mencakup issuer, authorization endpoint, token endpoint, userinfo endpoint, jwks URI, supported scopes, response types, grant types, PKCE methods, dan signing algorithms yang benar-benar didukung.

Issuer harus konsisten dengan `iss` pada ID Token. OpenID Connect Discovery mensyaratkan issuer, authorization endpoint, dan JWKS URI; token/userinfo metadata mengikuti capability server. citeturn600504search1

### FR-005 ID Token

Sistem SHALL menerbitkan signed JWT ID Token pada OIDC Authorization Code flow.

Claim minimum:

- `iss`;
- `sub`;
- `aud`;
- `iat`;
- `exp`;
- `nonce` bila diminta;
- `auth_time` bila policy/parameter mewajibkan.

OpenID Connect mendefinisikan ID Token sebagai JWT dengan claims autentikasi; client wajib memvalidasi identitas issuer, audience, signature, expiration, dan nonce sesuai flow. citeturn600504search0

### FR-006 JWKS

Sistem SHALL menyediakan:

```text
/.well-known/jwks.json
```

Hanya public key yang boleh dipublikasikan.

### FR-007 UserInfo

Sistem SHALL menyediakan endpoint UserInfo untuk claims berbasis access token dan scope.

### FR-008 Consent

Sistem SHALL menampilkan application, requested scopes, dan informasi data yang dibagikan sebelum authorization bila consent policy memerlukannya.

### FR-009 Session SSO

Sistem SHALL memiliki SSO session independent dari access token lifecycle.

SSO session berfungsi sebagai bukti bahwa user telah login ke Identity Provider. Access token tetap memiliki lifecycle terpisah.

### FR-010 Session Management

Admin/user SHALL dapat melihat session aktif dan melakukan revoke.

### FR-011 Audit

Sistem SHALL mencatat minimum:

- login start/success/failure;
- authorization request/approval/denial;
- token issue/revoke;
- client create/update/delete;
- secret rotation;
- key rotation;
- application policy changes;
- admin action;
- SAML assertion event;
- suspicious protocol errors.

### FR-012 Key Management

Sistem SHALL mendukung:

- active signing key;
- previous/overlap verification keys;
- key rotation;
- key status;
- key ID (`kid`);
- public JWKS publication;
- encrypted/private storage.

### FR-013 SAML IdP

Sistem SHALL dirancang untuk menyediakan:

- IdP metadata XML;
- SSO endpoint;
- Assertion Consumer Service interaction dari sisi SP;
- signed assertion;
- NameID/subject mapping;
- attribute/claim mapping;
- SP registration;
- certificate lifecycle;
- SLO bila profile/binding yang dipilih mendukung.

### FR-014 Metadata Import

Client integration wizard SHALL dapat memproses OIDC discovery URL dan SAML metadata XML URL/file pada sisi aplikasi client di masa depan.

### FR-015 Claims Policy

Administrator SHALL dapat memetakan:

```text
identity source → canonical claim → protocol-specific claim
```

Contoh:

```text
user.email        → email
user.name         → name
user.display_name → preferred_username / name
user.id           → sub mapping source
```

### FR-016 Scope Registry

Scope harus memiliki identifier, description, classification, default behavior, dan risk class.

### FR-017 Application Policy

Setiap application dapat menentukan:

- allowed scopes;
- consent required;
- login prompt policy;
- MFA/step-up requirement;
- session max age;
- access token lifetime override dalam batas platform;
- allowed redirect URI;
- logout URI;
- claim policy.

### FR-018 Error Contract

OAuth/OIDC endpoints SHALL mengembalikan error yang tidak membocorkan data internal.

Error protocol mengikuti struktur standar yang relevan, sementara error internal hanya masuk audit/log.

### FR-019 Rate Limiting / Abuse Protection

Protocol endpoint SHALL memiliki throttling terpisah dari login endpoint biasa.

Rate limit key minimal dapat mencakup client, IP, route, dan context sesuai risiko tanpa membuat satu akun/client menjadi single point of denial.

### FR-020 Health / Observability

Sistem SHALL menyediakan endpoint/metrics internal untuk dependency health, queue, database, key availability, dan protocol readiness.


### FR-021 Dashboard Authorization

Dashboard SHALL require both:

1. successful authentication through the configured authentication boundary;
2. active platform authorization assignment.

Authentication alone SHALL NOT grant dashboard access.

### FR-022 Role and Permission Registry

Sistem SHALL use `spatie/laravel-permission` for internal role/permission persistence and registration. Initial seeded role: `platform_admin`. Laravel Gates/Policies SHALL remain the enforcement layer.

Permission identifiers SHALL be stable machine keys and actions SHALL be enforced server-side.

### FR-023 Policy Enforcement

Privileged resources SHALL use Laravel Gates/Policies or an equivalent project-owned authorization contract. Resource ownership/organization scope SHALL be checked in addition to permission.

### FR-024 Scoped Role Assignment

The authorization model SHALL support platform and organization scope. Initial deployment may use platform scope only.

### FR-025 Privilege Escalation Protection

A user SHALL NOT be able to grant themselves a role/permission through mass assignment, direct request manipulation, or an application-level authorization gap.

### FR-026 Last Administrator Invariant

The system SHALL prevent an administrative operation from leaving the platform with zero usable platform administrators, including under concurrent requests.

### FR-027 Authorization Audit

Role assignment, role revocation, permission changes, privileged denials, and high-risk admin authorization decisions SHALL be auditable.

## 2.1 Dashboard UI requirements

### FR-UI-001
Admin dashboard SHALL use custom Blade dashboard components with Tailwind CSS v4, Bootstrap Icons, and Alpine.js.

### FR-UI-002
Frontend SHALL be packaged through Laravel/Vite. Production deployment SHALL use built static assets.

### FR-UI-003
Vue SHALL NOT be required for dashboard CRUD, administration, protocol configuration, audit, or settings screens.

### FR-UI-004
Reusable UI primitives SHALL be implemented as Blade components before introducing duplicated markup.

### FR-UI-005
Complex page views SHALL be split by subview when they become independently understandable/testable.

### FR-UI-006
The dashboard SHALL support responsive and accessible light/dark presentation.

## 2.2 Routing requirements

### FR-ROUTE-001
`routes/web.php` SHALL act as a composition point and MUST NOT become a monolithic route registry.

### FR-ROUTE-002
Project-owned routes SHOULD be separated into `admin.php`, `consent.php`, `oidc.php`, `oauth.php`, `saml.php`, `api.php`, and `health.php` according to actual boundaries.

### FR-ROUTE-003
Protocol endpoints SHALL use protocol-compatible paths and middleware, even when these paths differ from dashboard URLs.

### FR-ROUTE-004
Route names SHALL be stable and used by Blade/application code instead of hardcoded URLs.

### FR-ROUTE-005
Every privileged route SHALL have server-side authorization and tenant isolation checks; hidden UI elements are never considered authorization.

## 2.3 Security testing requirements

### FR-TEST-001
Every security-critical use case SHALL have positive, negative, boundary, replay, expiry, and unauthorized-actor tests where applicable.

### FR-TEST-002
One-time state SHALL be tested concurrently to ensure only one request can consume it successfully.

### FR-TEST-003
Authorization, redirect URI, PKCE, nonce, token validation, tenant isolation, key rotation, and audit logging SHALL have adversarial tests.

### FR-TEST-004
Security fixes SHALL add a regression test that reproduces the defect.

### FR-TEST-005
The security suite SHOULD include property-style/generated malicious input and mutation-style checks for critical validators.

## 3. Security requirements

### SEC-001 Redirect URI

Redirect URI harus exact match terhadap URI yang terdaftar, kecuali pengecualian loopback native app yang memang diizinkan oleh standar dan policy. OAuth 2.0 Security BCP menekankan exact matching untuk redirect URI dan melarang open redirector. citeturn600504search2

### SEC-002 PKCE

Public clients SHALL menggunakan PKCE. Authorization Code Flow menjadi flow utama.

### SEC-003 State

Client state harus diikat ke browser session dan request context untuk memitigasi CSRF/mix-up.

### SEC-004 Nonce

OIDC `nonce` harus dipersistasikan pada request transaction dan dibandingkan dengan claim `nonce` pada ID Token.

### SEC-005 One-Time Authorization Code

Authorization code harus:

- single-use;
- short-lived;
- bound to client;
- bound to redirect URI;
- bound to PKCE challenge jika digunakan;
- invalidated setelah redemption.

### SEC-006 Token Separation

Access token, ID Token, refresh token, authorization code, SSO session, dan client secret adalah credential class yang berbeda dan tidak boleh dicampur.

### SEC-007 Secrets

Client secret dan private key harus di-hash/encrypt sesuai kebutuhan. Private key tidak boleh masuk git atau response API.

Laravel Passport saat ini sudah meng-hash client secret secara default; project harus mengikuti perilaku vendor yang berlaku pada versi yang dipakai. citeturn300680search2

### SEC-008 Key Rotation

Key rotation harus memiliki overlap period. Key lama tetap tersedia di JWKS sampai token yang diterbitkan dengan `kid` lama tidak lagi valid.

### SEC-009 Replay Protection

Semua transaction identifier security-critical harus one-time atau memiliki replay store dengan TTL yang cukup.

### SEC-010 SSRF

Metadata/discovery import dari URL eksternal tidak boleh menjadi SSRF primitive. URL fetching harus memiliki allow/deny policy, scheme restrictions, DNS/IP validation, redirect limit, timeout, body size limit, dan audit.

### SEC-011 SAML XML Security

Parsing XML harus aman terhadap XXE/entity expansion/unsafe external reference. XML signature verification harus dilakukan oleh library yang teruji, bukan custom crypto.

### SEC-012 Audit Integrity

Audit record harus append-only secara aplikasi. Update/delete administratif hanya boleh berupa compensating event, bukan mengubah histori.

## 4. Non-functional requirements

### NFR-001 Maintainability

Core domain harus dapat berjalan tanpa controller dependency dan tanpa mengetahui implementation vendor protocol.

### NFR-002 Testability

Protocol validation, authorization policy, claims mapping, token issuance, dan session policy harus dapat di-test tanpa browser melalui application/integration tests.

### NFR-003 Performance

Discovery/JWKS harus dapat di-cache dan tidak memukul database pada setiap request.

### NFR-004 Availability

Token validation path harus tidak memiliki dependency eksternal yang tidak perlu.

### NFR-005 Observability

Setiap security-sensitive flow memiliki correlation/request ID dan audit event.

### NFR-006 Privacy

Log tidak boleh memasukkan password, raw token, client secret, private key, atau assertion mentah secara default.

### NFR-007 Portability

Storage, cache, queue, email, dan protocol adapters harus dapat dikonfigurasi tanpa perubahan domain code.

## 5. API surface baseline

### Discovery

```text
GET /.well-known/openid-configuration
GET /.well-known/jwks.json
```

### OAuth/OIDC

```text
GET  /oauth/authorize
POST /oauth/token
POST /oauth/revoke
GET  /oauth/userinfo
```

Introspection hanya diaktifkan bila ada use case yang jelas; access token JWT/self-contained validation tidak selalu membutuhkan introspection.

### SAML

```text
GET  /saml/metadata
GET  /saml/sso
POST /saml/sso
GET  /saml/slo
POST /saml/slo
```

Route final dapat berubah berdasarkan binding/protocol implementation, tetapi public contract harus distabilkan melalui adapter.

## 6. Core state machines

### Application

```text
DRAFT → ACTIVE → SUSPENDED → ACTIVE
                    └──────→ REVOKED
```

### Client credential

```text
ACTIVE → ROTATING → ACTIVE
ACTIVE → REVOKED
```

### Signing key

```text
GENERATED → ACTIVE → RETIRED → DESTROYED
```

`RETIRED` berarti tidak digunakan untuk signing baru tetapi masih dipublikasikan untuk verification sampai retention window habis.

### Authorization transaction

```text
CREATED → AUTHENTICATING → CONSENT_PENDING → APPROVED → CODE_ISSUED → REDEEMED
                                  └──────────→ DENIED
CREATED/AUTHENTICATING/CONSENT_PENDING → EXPIRED
```

## 7. Compatibility requirements

- Schema harus versioned dengan migration.
- Protocol response versioning harus backward-compatible selama mungkin.
- Config key tidak boleh diganti tanpa migration path.
- Vendor dependency tidak boleh tersebar ke seluruh project.
- Public URLs harus dapat dikonfigurasi dengan base URL/route namespace abstraction.

## 8. Test requirements

Minimum test suite:

```text
Unit
Feature
Integration
Protocol conformance
Security regression
Abuse/rate-limit
Replay
Key rotation
Browser/session
Contract tests
```

Critical negative cases wajib meliputi redirect mismatch, client mismatch, wrong issuer, wrong audience, expired code, reused code, wrong nonce, invalid PKCE verifier, revoked client, unknown key ID, revoked session, dan malformed protocol payload.


### FR-028 Backup & Disaster Recovery

Sistem SHALL use `spatie/laravel-backup` for application/database backup, support external destination storage, encryption, cleanup, monitoring, and documented restore drills. Backup configuration SHALL define retention, destination, encryption, RPO/RTO policy, and key/secret custody.

### FR-029 Security Threat Coverage

Security architecture SHALL maintain threat coverage for authentication abuse, authorization bypass, protocol abuse, injection, SSRF, XSS, CSRF, path traversal, unsafe file processing, RCE paths, DoS/resource exhaustion, cache/queue abuse, cryptographic failures, supply-chain compromise, deployment exposure, backup compromise, tenant escape, and insider/admin compromise.
