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

### 2.3 Application creation wizard
- Paths: `app/Http/Controllers/Admin/ApplicationWizardController.php`, `resources/views/pages/admin/applications/wizard/`, `tests/Feature/AdminApplicationWizardTest.php`
- Target steps:
  1. Basic information.
  2. Protocol/client type.
  3. Exact redirect URIs.
  4. Allowed scopes.
  5. Claim policy.
  6. Security policy.
  7. Review and create credentials.
- Store only validated server-side state between steps. Prevent step skipping and cross-user state access.
- Verification:
  ```bash
  php artisan test tests/Feature/AdminApplicationWizardTest.php --compact
  php artisan view:cache
  ```

### 2.4 Users and roles view
- Paths: `app/Http/Controllers/Admin/UserController.php`, `resources/views/pages/admin/users/`, `tests/Feature/AdminUsersTest.php`
- Target:
  - Paginated users, role assignment, permission display, and protected last-admin rule.
  - Use existing Spatie version after confirming `composer show spatie/laravel-permission`.
  - Every mutation is policy-protected and audited.
- Verification:
  ```bash
  composer show spatie/laravel-permission
  php artisan test tests/Feature/AdminUsersTest.php --compact
  ```

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
