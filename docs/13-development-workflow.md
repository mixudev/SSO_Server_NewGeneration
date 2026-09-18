# Development Workflow — Mixu SSO / Identity Platform

- Document ID: `SSO-DEV-001`
- Version: `0.2`
- Status: Baseline
- Date: `2026-09-17`

## 1. Goal

Semua contributor dan AI agent mengikuti satu alur kerja agar repository tetap mudah dipahami ketika fitur, protocol, dan tenant semakin banyak.

```text
Understand
→ inspect
→ plan
→ implement small slice
→ test intended behavior
→ attack the slice
→ review boundaries
→ update docs
→ record decisions
```

## 2. Before coding

Baca:

```text
README.md
01-prd.md
02-srs.md
03-architecture.md
04-protocol-contracts.md
05-data-model.md
06-security-model.md
08-ai-agent-contract.md
09-decision-log.md
10-dashboard-ui.md
11-routing-architecture.md
12-security-testing-strategy.md
```

Setelah itu inspect repository aktual. Dokumentasi tidak boleh dianggap lebih benar daripada code/schema yang benar-benar ada; jika keduanya berbeda, catat discrepancy dan perbaiki dokumentasi/ADR sebelum melanjutkan architectural change.

## 3. Mandatory change analysis

Sebelum membuat file, identifikasi:

```text
Feature:
Bounded context:
Protocol:
Existing contracts:
Existing code to reuse:
Database impact:
Route impact:
UI impact:
Security invariants:
Tests:
Docs affected:
ADR needed?:
```

## 4. Frontend workflow

Untuk dashboard:

1. cari reusable Blade component yang sudah ada (x-dashboard.*, x-form.*, x-table.*);
2. cari local Blade component yang sudah ada;
3. gunakan komponen lokal terlebih dahulu;
4. letakkan query/data preparation di controller/query/application layer, bukan Blade;
5. pecah page menjadi partial/component ketika concern dapat diuji atau dipakai terpisah;
6. jalankan responsive/accessibility check untuk halaman baru;
7. gunakan JavaScript hanya untuk interaksi yang benar-benar membutuhkan client state.

Jangan memperkenalkan Vue untuk CRUD/dashboard normal.

## 5. Backend workflow

Place each class under the bounded-context subfolder that owns it:

- Controllers: `app/Http/Controllers/<Context>/`
- Requests: `app/Http/Requests/<Context>/`
- Domain services: `app/Domain/<Context>/Services/`
- Infrastructure adapters: `app/Infrastructure/<Context>/`
- Models: `app/Models/<Context>/`
- Unit tests: `tests/Unit/Domain/<Context>/`
- Feature tests: `tests/Feature/<Context>/` or `tests/Feature/Admin/`

Tests MUST mirror the production responsibility they exercise. Do not keep new context-specific tests in the root `tests/Unit` or `tests/Feature` folders.

Target flow:

```text
Route
→ Controller
→ Request/Policy
→ Command or Query
→ Domain/Application service
→ Port
→ Infrastructure adapter
```

Hindari:

```text
Route
→ Controller
→ giant service
→ vendor API
→ database everywhere
```

## 6. Feature slicing

Feature besar harus dipecah menjadi vertical slices kecil yang tetap testable.

Contoh implementasi OIDC authorization:

```text
1. resolve client
2. validate redirect URI
3. validate request parameters
4. persist authorization transaction
5. resolve authentication context
6. consent decision
7. issue authorization code
8. redeem code
9. issue token set
10. issue ID Token
11. expose UserInfo
12. expose discovery/JWKS
13. harden + adversarial testing
```

Jangan membuat seluruh OIDC flow dalam satu service/controller.

## 7. Migration safety

Sebelum migration:

- inspect schema aktual;
- inspect vendor-owned tables;
- confirm ownership;
- define indexes/unique constraints;
- define foreign keys;
- define retention/lifecycle;
- test fresh install;
- test upgrade path.

## 8. Security-first implementation loop

Untuk setiap security-sensitive change:

```text
Write invariant
→ happy path
→ invalid input
→ expiry
→ replay
→ authorization bypass attempt
→ concurrency/race
→ logging/secret leakage check
→ regression test
```

## 9. UI regression loop

Untuk perubahan dashboard:

```text
Implement Blade component
→ render page
→ responsive check
→ dark/light check
→ keyboard/focus check
→ no-secret-leak check
→ feature tests
```

## 10. Definition of done

```text
[ ] implementation complete
[ ] code is decomposed by responsibility
[ ] unit tests
[ ] feature/integration tests
[ ] protocol/contract tests when applicable
[ ] adversarial tests
[ ] concurrency tests when applicable
[ ] authorization/tenant isolation test
[ ] logs checked for secret leakage
[ ] route contract checked
[ ] UI responsive/accessibility check when applicable
[ ] documentation updated
[ ] ADR updated when architecture changed
[ ] migration/config notes recorded
```

## 11. Shared-hosting deployment check

Before production deployment:

```text
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

The exact deployment command set may differ by hosting capability. The important invariant is that Node/Vite is used to build assets, while the production dashboard is served by Laravel/PHP + generated static assets.

Laravel documentation recommends serving the application from the web server's configured document root/public directory rather than exposing the project root. Source: https://laravel.com/docs/13.x/installation

## 12. AI workflow contract

AI agent MUST work in small, reviewable commits/slices and after each architectural change update the relevant documentation. It must never assume that a successful compile means security correctness.

## Authorization implementation sequence

When a privileged feature is implemented:

```text
Permission key
   ↓
Role assignment availability
   ↓
Policy / Gate
   ↓
Controller/use case enforcement
   ↓
UI visibility
   ↓
Adversarial tests
   ↓
Audit
```

Do not implement UI buttons first and add authorization later.
