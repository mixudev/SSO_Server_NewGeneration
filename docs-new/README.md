# Mixu SSO — Engineering Work Log

Folder ini menyimpan dokumentasi pengerjaan aktual per vertical slice. Dokumentasi arsitektur dan contract tetap berada di `docs/`.

## Status saat ini

- Foundation Laravel, authentication boundary, RBAC, backup baseline, Passport, Tabler, modular routes: selesai.
- Organization Registry: selesai.
- Application Registry foundation: selesai.
- Redirect URI Registry dan exact-match validator: selesai.
- Scope Registry dan application scope allowlist: selesai.
- OAuth/OIDC/SAML protocol flow: belum dimulai.

## Verification baseline

- Laravel: 13.32.0
- Authentication package: 1.7.0
- Passport: 13.8.0
- Spatie Permission: 8.3.0
- Spatie Backup: 10.3.0
- Full test terakhir: 14 tests passed, 31 assertions.
- Frontend build terakhir: `npm run build` lulus.
- PHP formatting: Pint lulus.
- Windows development: Pail tidak dijalankan melalui default `composer run dev` karena membutuhkan `pcntl`; Pail tetap tersedia untuk Linux/WSL.

## Slice log

### 2026-09-17 — Organization Registry

Implementasi:

- ULID primary key.
- Unique organization slug.
- Lifecycle status.
- Nullable JSON settings.
- Factory dan feature tests.

Security checks:

- Tidak mengekspos incremental identifier.
- Database menegakkan unique slug.
- JSON settings diperlakukan sebagai data, bukan executable configuration.

Verifikasi: 5 tests passed, 8 assertions pada slice awal.

### 2026-09-17 — Application Registry foundation

Implementasi:

- `applications` tenant-scoped ke `organizations`.
- ULID primary key.
- Unique `(organization_id, slug)`.
- Explicit protocol, client type, lifecycle, consent, session, dan claim policy metadata.
- Foreign key creator/updater.
- Relasi `Application::organization()`, `creator()`, dan `updater()`.

Security checks:

- Tenant ownership ditegakkan di database.
- Slug yang sama di organization berbeda diizinkan.
- Slug duplikat di organization yang sama ditolak.
- Application memakai identifier non-sequential.

Verifikasi: migration fresh dan feature tests lulus.

### 2026-09-17 — Redirect URI Registry

Implementasi:

- `application_redirect_uris` dengan ULID, application foreign key, URI, SHA-256 hash, dan `login`/`logout` kind.
- `ApplicationRedirectUri` model dan factory.
- `Application::redirectUris()` relation.
- `RedirectUriValidator` sebagai pure domain/application security service.

Validation policy:

- Storage menggunakan canonical representation.
- Matching exact menggunakan `hash_equals`.
- Scheme dan hostname dinormalisasi.
- Default HTTPS/HTTP ports dinormalisasi.
- Wildcard, fragment, userinfo, control character, whitespace, dot segment, encoded slash/backslash, URI malformed, dan HTTP non-loopback ditolak.
- HTTP loopback dipertahankan untuk native development.

Adversarial tests:

- Open redirect variants.
- Host mismatch.
- Path traversal.
- Encoded path separator.
- Fragment injection.
- Wildcard registration.
- Invalid scheme dan malformed URI.
- Loopback HTTP exception.

Verifikasi: 12 tests passed, 29 assertions pada focused slice; full suite terakhir 14 tests passed, 31 assertions.

### 2026-09-17 — Scope Registry

Implementasi:

- `scopes` canonical registry dengan ULID, unique name, category, risk level, system/default flags, dan lifecycle status.
- `application_scopes` sebagai allowlist tenant-bound melalui application.
- Unique `(application_id, scope_id)` dan foreign keys cascade.
- `Scope`, `ApplicationScope`, dan relasi `Application::scopes()` serta `Scope::applications()`.
- `ScopeNameValidator` untuk reserved OIDC scopes dan namespaced custom scopes.
- `ScopeSetNormalizer` untuk whitespace, deduplikasi, dan urutan deterministik.

Validation policy:

- Reserved names yang diizinkan: `openid`, `profile`, `email`, `address`, `phone`.
- Custom scope wajib namespaced, contoh `account:read`.
- Nama kosong, terlalu panjang, control character, whitespace internal, uppercase, slash, dan custom unnamespaced ditolak.
- Scope registry global; assignment tetap terikat ke application dan organization melalui foreign key.
- `allowed` dan `consent_required` disimpan di pivot untuk policy layer berikutnya.

Adversarial tests:

- Duplicate scope name.
- Duplicate application-scope assignment.
- Scope assignment lintas organization yang sah melalui application berbeda.
- Unnamespaced, uppercase, malformed, slash, dan control-character scope names.
- Duplicate whitespace dan ordering normalization.

Verifikasi focused slice: 7 tests passed, 14 assertions.

### 2026-09-17 — Scope Authorization Policy

Implementasi:

- `ScopeAuthorizationEvaluator` menerima hanya scope yang terdaftar pada application target.
- Scope harus berstatus `active` dan pivot harus memiliki `allowed=true`.
- Requested scope dinormalisasi melalui validator yang sama sebelum evaluasi.
- Scope unknown, inactive, disallowed, dan scope dari application lain ditolak secara fail-closed.

Adversarial tests:

- Unknown scope tidak boleh di-downgrade menjadi partial approval.
- `allowed=false` ditolak.
- Scope inactive ditolak.
- Scope yang hanya terdaftar pada application lain ditolak.
- Normalisasi hasil tetap deterministik.

Verifikasi focused slice: 8 tests passed, 12 assertions. Full suite: 25 tests passed, 51 assertions.

### 2026-09-17 — Claim Registry

Implementasi:

- `claims` canonical registry dengan ULID, unique key, source, sensitivity, dan lifecycle status.
- `Claim` model dan factory.
- `ClaimKeyValidator` untuk reserved OIDC claims dan canonical custom claim keys.
- Claim key leading/trailing whitespace tidak dipangkas; input ambigu ditolak.

Validation policy:

- Reserved keys mencakup `iss`, `sub`, `aud`, `exp`, `iat`, `auth_time`, `nonce`, `email`, `name`, `preferred_username`, `phone_number`, `roles`, dan `organization_id`.
- Custom keys menggunakan karakter lowercase canonical dengan separator `.`, `_`, atau `-`.
- Empty, uppercase, whitespace, slash, wildcard, duplicate separator, assignment syntax, control character, dan key terlalu panjang ditolak.
- Claim value resolution dan application claim policy belum diaktifkan.

Adversarial tests:

- Malformed dan ambiguous claim keys.
- Leading/trailing whitespace bypass.
- Control character injection.
- Duplicate claim key pada database.
- ULID identifier dan metadata persistence.

Verifikasi focused slice: 4 tests passed, 18 assertions. Full suite: 29 tests passed, 69 assertions.

## Langkah berikutnya

1. Claim policy versioning dan application claim policy.
2. Application authorization policy serta admin CRUD.
3. Client credentials sebelum Passport/OAuth integration.

Protocol endpoint belum boleh diaktifkan sebelum registry, policy, credential, state, nonce, dan PKCE contracts memiliki implementation serta adversarial tests.

## Catatan repository

Repository menggunakan remote `origin` pada `mixudev/SSO_Server_NewGeneration`. `gh` CLI tidak tersedia di environment ini; operasi Git remote dilakukan menggunakan Git CLI. File `.env`, OAuth keys, credential, dan secret tidak termasuk checkpoint.
