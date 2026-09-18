# Phase 2 — Admin Control Plane and Dashboard Views

## Scope

Build dashboard views only after Phase 1 domain entities, policies, key lifecycle, and audit contracts are implemented. Every view must display real authorized data; no placeholder metrics or mock records are allowed.

## Goals

1. Expose secure admin CRUD screens for identity resources.
2. Reuse the active dashboard shell and existing Blade primitives.
3. Keep controllers thin and move validation/authorization into Form Requests and Policies.
4. Make destructive operations explicit through `x-allert` and `AppPopup`.
5. Preserve the current visual contract: Tailwind CSS v4, Bootstrap Icons, light/dark parity, no CDN, and professional `x-app-modal` dialogs.

## Required existing components

Before creating a component, inspect and reuse:

- `resources/views/components/dashboard/layout.blade.php`
- `resources/views/components/dashboard/header.blade.php`
- `resources/views/components/dashboard/sidebar.blade.php`
- `resources/views/components/dashboard/card.blade.php`
- `resources/views/components/form/button.blade.php`
- `resources/views/components/form/group.blade.php`
- `resources/views/components/form/input.blade.php`
- `resources/views/components/table/action.blade.php`
- `resources/views/components/table/label.blade.php`
- `resources/views/components/table/td.blade.php`
- `resources/views/components/table/text.blade.php`
- `resources/views/components/table/th.blade.php`
- `resources/views/components/table/wrapper.blade.php`
- `resources/views/components/app-modal.blade.php`
- `resources/views/components/allert.blade.php`

Use Bootstrap Icons as `bi bi-*`. Do not introduce another icon package or CDN assets.

## Tasks

### 2.1 Dashboard route and authorization boundary
- Paths: `routes/web.php`, `app/Http/Middleware/`, `app/Policies/`
- Target:
  - All dashboard routes require authentication and the existing admin permission.
  - Unauthorized users receive the project-standard denial response.
  - Route names use one consistent `admin.*` namespace.
- TDD:
  1. Add `tests/Feature/AdminDashboardAuthorizationTest.php` with anonymous and unauthorized-user cases.
  2. Run `php artisan test tests/Feature/AdminDashboardAuthorizationTest.php --compact`; expect failures before implementation.
  3. Implement only the missing middleware/policy wiring.
  4. Rerun; expect all cases to pass.
- Verification:
  ```bash
  php artisan route:list --path=admin
  php artisan test tests/Feature/AdminDashboardAuthorizationTest.php --compact
  ```

Status: completed. `routes/admin.php` enforces `auth` and `can:admin.dashboard.view`; `tests/Feature/Admin/AdminDashboardTest.php` verifies guest, unauthorized, authorized, and safe-output cases. Verified with `php artisan route:list --path=admin` and the focused test suite.

### 2.2 Applications index and CRUD
- Paths: `app/Http/Controllers/Admin/ApplicationController.php`, `app/Http/Requests/Admin/`, `resources/views/pages/admin/applications/`, `tests/Feature/AdminApplicationsTest.php`
- Target:
  - Paginated real records, search, status filter, client type label, empty state, and authorization.
  - Create, edit, show, and delete actions use Form Requests and Policies.
  - Redirect URI validation uses exact matching; never accept a regex or wildcard as an authorization shortcut.
- UI target: reuse `x-dashboard.layout`, `x-table.*`, `x-form.*`, `x-app-modal`, and `x-allert`.
- Verification:
  ```bash
  php artisan test tests/Feature/AdminApplicationsTest.php --compact
  php artisan view:cache
  ```

Status: index slice completed in `app/Http/Controllers/Admin/ApplicationController.php`, `resources/views/pages/admin/applications/index.blade.php`, and `tests/Feature/Admin/ApplicationsIndexTest.php`. Create/edit/show/delete mutations remain separate slices.

Create slice completed: `app/Http/Requests/Admin/StoreApplicationRequest.php` canonicalizes and validates exact redirect URIs, `ApplicationController::store()` persists atomically with draft lifecycle, and `tests/Feature/Admin/ApplicationCreationTest.php` covers permission denial, canonicalization, and redirect abuse. Wizard, credentials, and activation remain separate slices.

Detail slice completed: `ApplicationController::show()`, `resources/views/pages/admin/applications/show.blade.php`, and `tests/Feature/Admin/ApplicationDetailsTest.php` cover permission enforcement, route model binding, organization/redirect URI display, and secret-material exclusion.

Update slice completed: `UpdateApplicationRequest`, `ApplicationController::update()`, and the `applications.update` route update metadata and replace canonical redirect URIs atomically. `tests/Feature/Admin/ApplicationUpdateTest.php` covers permission denial, invalid URI rollback, and successful replacement.

Delete slice completed: `ApplicationController::destroy()` permits only draft applications, requires `applications.delete`, deletes transactionally, and records `APPLICATION_DELETED` through `AuditLoggerInterface`. Migration `2026_09_18_021026_align_security_event_identity_ids.php` aligns audit correlation IDs with ULID identity IDs. `tests/Feature/Admin/ApplicationDeletionTest.php` covers success, active-application guard, audit persistence, and permission denial.

### 2.3 Application creation wizard

Status: slice 2.3.3 completed.
- Slices 2.3.1 and 2.3.2 remain complete: basic information, protocol/client type, exact redirect URIs, active scopes, active claims, review, and draft creation.
- Added explicit security policy step with allowlisted `consent_policy` values: `explicit`, `implicit`, and `none`.
- Added bounded session policy: `session_max_age` 300–86400 seconds and `session_idle_timeout` 60–900 seconds.
- Added protocol/client validation: public SPA idle timeout cannot exceed 600 seconds and idle timeout cannot exceed maximum session age.
- Completion re-reads active scopes and claims inside the transaction. A registry item deactivated after selection causes validation failure and prevents application persistence.
- Persisted only allowlisted policy fields into `consent_policy` and `session_policy_json`; no executable configuration is accepted.
- Review displays scope labels, claim labels, consent policy, and session limits. Internal IDs are not used as review labels.
- Files: `app/Http/Controllers/Admin/ApplicationController.php`, `routes/admin.php`, `resources/views/pages/admin/applications/wizard.blade.php`, `tests/Feature/Admin/ApplicationWizardTest.php`.
- Security coverage: permission denial, step skipping, redirect abuse, scope/claim tampering, inactive/unknown/duplicate registry values, invalid policy values, public SPA timeout abuse, stale registry selections, draft lifecycle, and no persistence after invalid input.
- No credentials are generated and no application is activated in this slice.
- Verification: `php artisan test --compact` (75 tests, 202 assertions), `php artisan test tests/Feature/Admin --compact` (27 tests, 95 assertions), `php artisan view:cache`, `vendor/bin/pint --dirty --format agent`, `npm run build`, and `git diff --check` all pass.

Completed follow-up: final review integrity validation now reconstructs and validates all staged wizard segments before persistence, re-canonicalizes redirect URIs, enforces policy cross-field rules, and re-checks active organization state inside the transaction. Adversarial tests cover tampered security policy, redirect URI, and inactive organization state.

Next slice 2.3.4 — credential boundary:
- Define credential-generation contract without exposing client secrets in HTML, logs, session, or validation errors.
- Keep credential creation separate from activation; newly completed applications remain `draft`.
- Acceptance: a draft is persisted atomically only after current registry/policy validation, and credential material has a separate tested boundary.

### 2.3.5 Profile security center

Status: profile security center implemented with application-owned pages; avatar upload remains separate.
- Added `ProfileSecuritySummary` at `app/Infrastructure/Identity/ProfileSecuritySummary.php` to consume package services for 2FA, passkeys, sessions, and recent login metadata without exposing secrets.
- Added application-owned routes and views under `admin.profile.security`: 2FA setup/confirm, passkey options/register/remove, session revoke/revoke-others, and password-reset request.
- `resources/views/pages/admin/profile/security.blade.php` is the single management page. It does not redirect to package session/profile views.
- Package controllers/services remain the security implementation boundary; host routes delegate to them and preserve ownership, rate limits, password confirmation, WebAuthn challenge, and audit behavior.
- No authentication package files or login routes were modified. Package pages remain available for backward compatibility but are not linked from the admin profile UI.
- Security invariants: no password hashes, reset tokens, WebAuthn response material, 2FA secrets, recovery codes, or private keys are passed to the profile summary; admin authorization remains required.
- Tests: `tests/Feature/Admin/ProfileTest.php` and `tests/Feature/Admin/ProfileSecurityCenterTest.php` cover guest/permission denial, safe output, application-owned route contracts, no package-page navigation, and profile regression.
- Verification: focused profile tests (6 tests, 24 assertions), admin suite (33 tests, 119 assertions), `php artisan view:cache`, Pint, Vite build, and `git diff --check` pass.

Completed follow-up: avatar and final security UX are implemented. The application-owned profile/security pages use shared action components for migrated actions, with remaining page-local icon/password controls intentionally kept local because they carry specialized behavior.

Next slice 2.3.6 — avatar and final security UX:
- Add private avatar storage, upload/remove validation, and safe serving.
- Replace browser `prompt` and native confirms with dashboard modal components.
- Add browser-level WebAuthn verification and explicit destructive-action confirmation.

### 2.4 Users and roles view [completed]
- Paths: `app/Http/Controllers/Admin/UserController.php`, `resources/views/pages/admin/users/`, `tests/Feature/Admin/UsersIndexTest.php`, `tests/Feature/Admin/UserRoleManagementTest.php`, `tests/Feature/Admin/UserLastAdminProtectionTest.php`
- Delivered:
  - Paginated/searchable users, role display, permission summary, and role assignment.
  - `users.view` protects reads and `users.manage` protects mutations.
  - Unknown roles and direct permission assignment are rejected.
  - Last `platform_admin` cannot be demoted; role changes are audited.
  - Installed versions verified from `vendor/composer/installed.php`: `spatie/laravel-permission` 8.3.0 and `laravel/passport` 13.8.0. The Composer launcher remains misconfigured.
- Verification:
  ```bash
  php artisan test tests/Feature/Admin/UsersIndexTest.php tests/Feature/Admin/UserRoleManagementTest.php tests/Feature/Admin/UserLastAdminProtectionTest.php --compact
  php artisan view:cache
  vendor/bin/pint --dirty --format agent
  ```
  Result: 7 tests passed, 22 assertions.

### 2.5 Scopes and claims views
- Paths: `app/Http/Controllers/Admin/ScopeController.php`, `app/Http/Controllers/Admin/ClaimController.php`, `resources/views/pages/admin/scopes/`, `resources/views/pages/admin/claims/`
- Target:
  - CRUD views backed by Phase 1 registries.
  - Prevent deleting a scope or claim referenced by an active application policy.
  - Show version and usage state.
- Verification:
  ```bash
  php artisan test --filter=AdminScope --compact
  php artisan test --filter=AdminClaim --compact
  php artisan view:cache
  ```

### 2.6 Key management view
- Paths: `app/Http/Controllers/Admin/KeyController.php`, `resources/views/pages/admin/keys/`, `tests/Feature/AdminKeysTest.php`
- Target:
  - Show key ID, algorithm, created/activated/retired timestamps, and status.
  - Never render private key material.
  - Rotation uses `x-app-modal` with `icon="key"`, `iconColor="violet"`, and explicit `AppPopup` confirmation.
  - Audit every rotation, revocation, and failed attempt.
- Verification:
  ```bash
  php artisan test tests/Feature/AdminKeysTest.php --compact
  php artisan view:cache
  npm run build
  ```

### 2.7 Session inspector and revocation
- Paths: `app/Http/Controllers/Admin/SessionController.php`, `resources/views/pages/admin/sessions/`, `tests/Feature/AdminSessionsTest.php`
- Target:
  - List sessions with user, client, created time, last activity, and safe device metadata.
  - Revoke only the selected session or explicitly all sessions for a user.
  - Destructive actions use `AppPopup.delete()`; no native `confirm()`.
- Verification:
  ```bash
  php artisan test tests/Feature/AdminSessionsTest.php --compact
  php artisan view:cache
  ```

### 2.8 Audit log view
- Paths: `app/Http/Controllers/Admin/AuditController.php`, `resources/views/pages/admin/audit/`, `tests/Feature/AdminAuditTest.php`
- Target:
  - Filter by actor, action, resource, date range, and outcome.
  - Redact secrets in rows and detail modal.
  - Do not allow unauthorized export or arbitrary query ordering.
- Verification:
  ```bash
  php artisan test tests/Feature/AdminAuditTest.php --compact
  php artisan view:cache
  ```

### 2.9 Dashboard visual and accessibility review
- Paths: `resources/css/dashboard.css`, `resources/css/dashboard/*.css`, `resources/js/dashboard.js`, all Phase 2 Blade views.
- Target:
  - Use current dashboard shell and semantic tokens in both themes.
  - Every icon-only action has an `aria-label` and visible tooltip or screen-reader text.
  - Modal icon is meaningful, Bootstrap-based, and not decorative-only when it conveys intent.
  - Search overlay remains above sidebar (`z-index: 9998` or layout-root equivalent).
- Verification:
  ```bash
  npm run build
  php artisan view:cache
  git diff --check
  ```

## Acceptance Criteria

- Every screen renders authorized, real data and has a tested empty state.
- All mutations have Form Request validation, Policy authorization, and audit events.
- Destructive actions use `x-allert`/`AppPopup` and `x-app-modal`; native browser confirmation is absent.
- No private credentials or secret material appear in HTML, logs, or rendered modal content.
- Full suite, Pint, Blade cache, frontend build, and diff checks pass.
