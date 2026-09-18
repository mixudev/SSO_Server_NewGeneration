# Mixu Dashboard Migration

- Date: 2026-09-17
- Scope: dashboard presentation shell
- Status: completed and verified

## Implemented

The dashboard shell now uses the project-owned Blade components under:

- `resources/views/components/admin/layout.blade.php`
- `resources/views/components/dashboard/`
- `resources/css/admin.css`
- `resources/js/admin.js`
- `resources/views/pages/admin/dashboard/index.blade.php`

The migrated `mixu-dashboard` primitives include the header, card, badge, empty state, form controls, modal, alert popup, and table primitives. Internal component references were namespaced under `x-dashboard.*` to avoid collisions with unrelated application components.

The shell uses the existing authentication package's `logout` route, CSRF protection, authenticated user output, and the existing `admin.dashboard.view` permission. User-controlled name and email output remains escaped by Blade.

## Removed

Removed after migration and verification:

- source folder `mixu-dashboard`
- previous TailAdmin source folder and copied demo assets
- TailAdmin demo component classes and unused JavaScript modules
- unused chart, calendar, map, date-picker, and carousel dependencies
- external CDN references from the migrated dashboard layout

Authentication assets remain separate from the admin entrypoint.

## Security invariants

- Guest access remains protected by authentication middleware.
- Authenticated users without `admin.dashboard.view` remain forbidden.
- Logout is a POST form with CSRF protection.
- Navigation is permission-gated server-side.
- User output is escaped; sensitive strings are not rendered.
- No demo credentials, fake resource routes, or external runtime CDN assets are included in the new shell.

## Verification

Passed:

```text
php artisan view:clear
php artisan view:cache
Tests: 4 passed (11 assertions)
npm run build
vite v7.3.6
61 modules transformed
found 0 vulnerabilities

git diff --check
```

`vendor/bin/pint --dirty --format agent` also completed, but it formatted unrelated untracked TailAdmin files before they were removed. Those files are not part of the final dashboard slice.

## Known gaps

- Only the dashboard overview route exists currently; application, organization, scope, claim, audit, and security resource pages remain future slices.
- The dashboard summary values are current foundation labels, not live metrics.
- Additional migrated primitives should only be retained when a real SSO page uses them.

## Next bounded slice

Build the first Application Registry admin page using the existing `Application` model, tenant/permission boundaries, a feature test, and the migrated dashboard table/form primitives. Do not add protocol endpoints in that slice.
