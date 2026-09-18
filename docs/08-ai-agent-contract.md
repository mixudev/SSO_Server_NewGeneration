# AI Agent Contract — Mixu SSO / Identity Platform

## 1. Purpose

Dokumen ini adalah aturan wajib untuk AI coding agent yang mengerjakan repository. Tujuan utamanya adalah menjaga arsitektur tetap modular, file kecil, logic mudah diuji, dan perubahan tidak menimbulkan architectural drift.

## 2. Mandatory reading order

Sebelum coding:

```text
01-prd.md
02-srs.md
03-architecture.md
04-protocol-contracts.md
05-data-model.md
06-security-model.md
08-ai-agent-contract.md
09-decision-log.md
```

Jika perubahan menyentuh area tertentu, baca dokumen area tersebut secara penuh sebelum membuat patch.

## 3. No-code-before-plan

Untuk task yang menyentuh lebih dari satu bounded context:

1. identifikasi requirement;
2. tentukan bounded context;
3. tentukan contract yang dipakai;
4. daftar file yang akan berubah;
5. tulis acceptance criteria;
6. baru coding.

AI agent tidak boleh langsung membuat file besar hanya karena task terlihat cepat.

## 4. File decomposition contract

### Rule A — One responsibility

Satu class memiliki satu tanggung jawab utama.

Dilarang membuat:

```text
SsoManager.php
```

yang sekaligus:

- validate request;
- query database;
- create token;
- sign JWT;
- render view;
- write audit;
- send notification.

Pecah menjadi contoh:

```text
ValidateAuthorizationRequest
ResolveClient
ResolveScopes
CreateAuthorizationTransaction
AuthenticateUser
EvaluateConsent
IssueAuthorizationCode
RecordAuditEvent
```

### Rule B — Thin controller

Controller hanya:

```text
receive request
→ validate/DTO
→ call use case
→ transform response
```

Jangan memindahkan domain/business logic ke controller.

### Rule C — Thin model

Eloquent model fokus pada persistence mapping, cast, relation, dan invariant persistence yang memang cocok berada di model. Business orchestration berada di application/domain layer.

### Rule D — Vendor isolation

Class domain/application dilarang import concrete classes dari:

```text
Laravel\Passport\...
Laravel\Socialite\...
SAML library namespaces
```

Vendor hanya boleh muncul di Infrastructure/Adapters.

### Rule E — No generic god services

Dilarang membuat service bernama:

```text
SsoService
AuthService
SecurityService
HelperService
Manager
Utility
```

bila class tersebut menangani banyak domain. Gunakan nama berdasarkan capability.

## 5. Recommended class size

Ini adalah guardrail, bukan hukum absolut:

- Controller: ideal <= 100 lines.
- Request/DTO: ideal <= 120 lines.
- Use case/handler: ideal <= 180 lines.
- Domain service: ideal <= 200 lines.
- Mapper/formatter: ideal <= 150 lines.
- Complex protocol validator: boleh lebih besar jika semua logic memang satu cohesive concern, tetapi harus dibagi bila terdapat beberapa independent responsibility.

Ketika file melewati batas tanpa alasan kuat, AI agent harus mengevaluasi extraction.

## 6. Method size

Metode harus mudah dipahami dalam satu screen. Bila method mulai melakukan tiga atau lebih business phases, pecah menjadi private methods atau use case/component.

## 7. DTO contract

Request protocol tidak boleh diteruskan langsung sebagai associative array jauh ke domain.

Gunakan typed DTO/value object:

```text
AuthorizationRequestData
ClientCredentialsData
TokenRequestData
ClaimsContext
AuthenticationContext
```

## 8. Error contract

Gunakan typed domain/application exceptions:

```text
InvalidClient
InvalidRedirectUri
InvalidAuthorizationRequest
AuthorizationDenied
AuthorizationTransactionExpired
AuthorizationCodeReplayed
PkceVerificationFailed
InvalidNonce
ClientRevoked
ProtocolNotSupported
```

Protocol adapter yang mengubah exception tersebut menjadi HTTP/OAuth/SAML response.

## 9. Database contract

AI agent tidak boleh membuat migration yang menggandakan data yang sudah dikelola vendor jika tidak ada kebutuhan nyata.

Sebelum membuat tabel baru:

1. cari apakah Passport/package sudah menyediakan tabel;
2. cek data model document;
3. tentukan ownership table;
4. baru migration.

Setiap migration harus:

- punya index yang sesuai;
- foreign key yang masuk akal;
- unique constraint untuk invariant;
- timestamp/lifecycle field yang diperlukan;
- aman untuk deployment berulang.

## 10. API contract

Jangan langsung mengubah public route karena internal refactor.

Bila endpoint perlu berubah:

```text
document reason
→ ADR
→ compatibility plan
→ migration/deprecation
```

## 11. Security contract

AI agent DILARANG:

- mematikan verification untuk membuat test pass;
- menggunakan wildcard redirect sebagai workaround;
- logging token/secret untuk debugging permanent;
- menerima `alg=none`;
- mempercayai claims tanpa signature/issuer/audience validation;
- mem-bypass PKCE;
- membuat SAML signature verification custom untuk menggantikan library security primitives;
- menganggap email sebagai subject identifier permanen;
- menggunakan password grant sebagai shortcut tanpa ADR dan security review.

## 12. Testing contract

Setiap security feature minimal punya:

```text
happy path
invalid input
expired state
replay
unauthorized actor
revoked resource
boundary/edge case
```

Untuk bug fix security, buat regression test sebelum atau bersama patch.

## 13. Change impact analysis

Setiap task wajib menjawab secara internal:

```text
Touched bounded contexts:
Touched contracts:
Database impact:
Public API impact:
Security impact:
Backward compatibility impact:
Tests required:
Docs affected:
```

## 14. No hidden architecture

AI agent tidak boleh membuat abstraction yang tidak terdokumentasi ketika abstraction tersebut menjadi dependency boundary baru.

Jika membuat interface baru yang akan menjadi architectural port, update:

```text
03-architecture.md
04-protocol-contracts.md (jika protocol)
08-ai-agent-contract.md (jika mengubah coding contract)
09-decision-log.md
```

## 15. No file dump

Jangan membuat satu file 800–1500 lines untuk satu feature dengan alasan "lebih cepat".

Contoh pemecahan OIDC:

```text
Protocols/Oidc/
├── Controllers/AuthorizationController.php
├── Controllers/TokenController.php
├── Controllers/UserInfoController.php
├── Discovery/OidcDiscoveryDocument.php
├── Requests/AuthorizationRequest.php
├── Requests/TokenRequest.php
├── Services/OidcAuthorizationService.php
├── Services/OidcTokenService.php
├── Services/OidcUserInfoService.php
├── Validators/AuthorizationRequestValidator.php
├── Validators/RedirectUriValidator.php
├── Validators/PkceValidator.php
├── Tokens/IdTokenIssuer.php
├── Tokens/IdTokenClaimsFactory.php
└── Responses/OidcErrorResponse.php
```

Struktur akhir boleh berbeda, tetapi principle decomposition harus dipertahankan.

## 16. Reuse contract

Sebelum membuat class baru, AI agent harus search repository untuk:

- existing contract;
- existing value object;
- existing validator;
- existing policy;
- existing event;
- existing audit recorder;
- existing exception.

Jangan membuat duplicate implementation dengan nama berbeda.

## 17. AI documentation discipline

Setelah perubahan:

- update relevant docs;
- update ADR jika architectural decision berubah;
- update examples jika public behavior berubah;
- catat migration requirement;
- jangan meninggalkan docs yang menggambarkan behavior lama.

## 18. Definition of done for AI agent

Task hanya dianggap selesai jika:

```text
Code
+ tests
+ security checks
+ architecture compliance
+ docs synchronization
+ migration/config notes
```

semuanya selesai.

## 19. Preferred implementation sequence

```text
Contract
→ Domain/value object
→ Use case
→ Adapter
→ Controller/transport
→ Tests
→ Docs
```

Bukan:

```text
Controller
→ query DB
→ call vendor
→ patch error
→ add more code
```

## 20. Stop conditions

AI agent harus berhenti menambah code dan mengubah strategy ketika:

- requirement bertentangan dengan protocol standard;
- dependency vendor tidak menyediakan required behavior;
- solusi memerlukan weakening security invariant;
- perubahan akan memecahkan public contract tanpa migration path;
- satu file mulai menjadi god object;
- implementation membutuhkan custom cryptography.

Dalam kondisi tersebut, dokumentasikan gap dan pilih adapter/extension point yang benar.


## 20. Dashboard/UI contract

The dashboard baseline is Blade components + Tailwind CSS v4 + Alpine.js + Bootstrap Icons.

AI MUST:

- use existing project Blade components (x-dashboard.*, x-app-modal, x-allert, x-form.*, x-table.*);
- reuse local Blade components;
- keep page-specific fragments under `resources/views/pages/admin/<feature>`;
- never introduce Vue for ordinary admin pages;
- never introduce another UI kit or icon library without an ADR;
- update `10-dashboard-ui.md` when the UI architecture changes.

## 21. Route contract

AI MUST inspect `11-routing-architecture.md` before adding routes.

AI MUST NOT:

- dump every route into `web.php`;
- duplicate vendor-managed Passport routes;
- create one route file per controller without an actual boundary;
- bypass policy/tenant middleware for convenience.

## 22. Adversarial test contract

For each security-sensitive feature, AI must explicitly attempt to violate the security invariant after the happy path works.

Minimum questions:

```text
Can it be replayed?
Can it be raced concurrently?
Can it cross tenants?
Can redirect/callback input escape the registered target?
Can a token be modified or confused with another token type?
Can an expired/revoked object still be used?
Can claims/scope be escalated?
Can secrets reach logs/responses?
Can malformed parser input crash or bypass validation?
```

A feature is not complete merely because its nominal test passes.

## 11. Authorization rules

`mixudev/laravel-authentication` provides authentication, not Mixu SSO dashboard authorization.

The agent MUST implement authorization as a separate project-owned boundary using Laravel Gates/Policies plus role/permission persistence.

Initial seeded role: `platform_admin`. Do not hardcode admin emails or introduce a global `is_admin` bypass.

Before adding an authorization check:

1. identify the permission key;
2. identify platform vs organization/resource scope;
3. find or create the appropriate policy;
4. add positive and negative tests;
5. add audit behavior for high-risk changes.

The agent MUST treat hidden UI actions as usability only; server-side enforcement remains mandatory.

Do not add Spatie or another RBAC package merely because role/permission functionality is required. Add a third-party package only after an explicit ADR demonstrates why the project-owned authorization layer is insufficient.


## Approved infrastructure packages

For this project, do not replace established package boundaries with ad-hoc implementations:

```text
Authentication → mixudev/laravel-authentication
OAuth2 → laravel/passport
RBAC → spatie/laravel-permission
Backup → spatie/laravel-backup
Dashboard → Blade + Tailwind v4 + Alpine.js + Bootstrap Icons
```

Before adding an alternative package, inspect the official package capability and current project contract first.

For backup-related changes, the agent MUST consider restore, encryption, retention, destination failure, monitoring failure, and secret/key custody—not only whether `backup:run` exits successfully.
