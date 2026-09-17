# Mixu SSO — Engineering Work Log

Folder ini menyimpan dokumentasi pengerjaan aktual per vertical slice. Dokumentasi arsitektur dan contract tetap berada di `docs/`.

## Status saat ini

- Foundation Laravel, authentication boundary, RBAC, backup baseline, Passport, Tabler, modular routes: selesai.
- Organization Registry: selesai.
- Application Registry foundation: selesai.
- Redirect URI Registry dan exact-match validator: selesai.
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

## Langkah berikutnya

1. Scope Registry dan `application_scopes`.
2. Scope parser/normalizer dengan reserved OIDC scope policy dan risk level.
3. Scope escalation serta tenant-isolation tests.
4. Claim registry setelah scope contract stabil.
5. Application policy dan admin CRUD setelah model/policy boundary stabil.
6. Client credentials sebelum Passport/OAuth integration.

Protocol endpoint belum boleh diaktifkan sebelum registry, policy, credential, state, nonce, dan PKCE contracts memiliki implementation serta adversarial tests.

## Catatan repository

Repository menggunakan remote `origin` pada `mixudev/SSO_Server_NewGeneration`. `gh` CLI tidak tersedia di environment ini; operasi Git remote dilakukan menggunakan Git CLI. File `.env`, OAuth keys, credential, dan secret tidak termasuk checkpoint.
