# Dashboard UI Architecture — Tabler + Laravel Blade

- Document ID: `SSO-UI-001`
- Version: `0.2`
- Status: Baseline
- Date: `2026-09-17`

## 1. UI decision

Dashboard admin Mixu SSO SHALL use:

- Laravel 13.x
- Blade server-rendered views
- Tabler official UI kit
- Bootstrap 5 foundation included by Tabler
- Tabler Icons
- Vanilla JavaScript by default
- Alpine.js only when a small client-side interaction materially improves UX
- Livewire only for server-driven interactive screens that otherwise become unnecessarily complex in Blade
- Vue is NOT the dashboard foundation

The dashboard is a server-rendered control plane. It is not an SPA.

Tabler is an open-source HTML dashboard UI kit built on Bootstrap 5. Its official repository documents installation through `@tabler/core`, provides light/dark support, ready-made layouts/components, and ships Bootstrap inside the package. As of 2026-09-17, the latest official release is `v1.5.1`. Sources: https://github.com/tabler/tabler and https://github.com/tabler/tabler/releases

## 2. Why Tabler

Tabler is selected because its visual language fits an identity/control-plane product:

- dense but readable data presentation;
- mature tables/forms/navigation;
- responsive admin layouts;
- light and dark modes;
- large icon set;
- Bootstrap-based primitives that are easy to host after build;
- no requirement to run a JavaScript application server in production.

The project SHALL depend on Tabler as a presentation system, not as an application architecture.

## 3. Installation strategy

### 3.1 Do not install a random Laravel Tabler wrapper

Do not make an unofficial `tabler-laravel` wrapper a core dependency unless an explicit architecture decision approves it.

Preferred approach:

```text
Official Tabler
    ↓
resources/css/app.scss or app.css
resources/js/app.js
    ↓
Vite build
    ↓
public/build
    ↓
Blade layouts/components
```

### 3.1.1 Fresh Laravel setup

After the Laravel project exists and the normal Vite toolchain is present:

```bash
composer install
npm install
npm install @tabler/core
```

Then import Tabler into the project's frontend entrypoint and reference that entrypoint from the Blade layout with Laravel's Vite helper. Example:

```blade
<!doctype html>
<html lang="en">
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
```

Do not separately install a second Bootstrap major version unless an ADR explicitly approves it; Tabler already contains its Bootstrap foundation.

### 3.2 Recommended package installation

Use the official package:

```bash
npm install @tabler/core
```

Import Tabler from the application's frontend entrypoint according to the project's Vite setup:

```js
import '@tabler/core/dist/css/tabler.min.css';
import '@tabler/core/dist/js/tabler.min.js';
```

The exact entry filename may change when the project's asset strategy is finalized; the architectural rule is that Tabler is bundled into the application's Vite build and not downloaded from a CDN in production.

### 3.3 Build workflow

Development:

```bash
npm install
npm run dev
```

Production:

```bash
npm ci
npm run build
```

Production hosting needs PHP/Laravel plus the generated assets. Node.js does not need to run as a production web server merely because Vite was used during development/build.

### 3.4 Asset policy

Production SHALL prefer self-hosted assets:

- Tabler CSS/JS from the project build;
- Tabler Icons from the project/package or locally generated SVG;
- no runtime dependency on third-party CDN for authentication/security screens;
- version-lock frontend dependencies through the package lock file.

## 4. Dashboard layout contract

The canonical layout is:

```text
resources/views/
├── layouts/
│   ├── admin.blade.php
│   ├── guest.blade.php
│   ├── auth.blade.php
│   └── components/
│
├── partials/
│   ├── admin/
│   │   ├── sidebar.blade.php
│   │   ├── navbar.blade.php
│   │   ├── breadcrumb.blade.php
│   │   ├── footer.blade.php
│   │   └── command-menu.blade.php
│   └── shared/
│
├── components/
│   ├── ui/
│   │   ├── alert.blade.php
│   │   ├── badge.blade.php
│   │   ├── button.blade.php
│   │   ├── card.blade.php
│   │   ├── empty-state.blade.php
│   │   ├── modal.blade.php
│   │   ├── pagination.blade.php
│   │   ├── table.blade.php
│   │   ├── copy-button.blade.php
│   │   ├── status.blade.php
│   │   └── code-block.blade.php
│   ├── form/
│   ├── navigation/
│   ├── security/
│   └── protocol/
│
└── pages/
    └── admin/
        ├── dashboard/
        ├── applications/
        ├── organizations/
        ├── users/
        ├── sessions/
        ├── oauth/
        ├── oidc/
        ├── saml/
        ├── scopes/
        ├── claims/
        ├── keys/
        ├── audit/
        ├── security/
        ├── developer/
        └── settings/
```

## 5. View ownership rule

A page directory owns page composition only.

A reusable component MUST live under `components/` and SHALL NOT contain application-specific business queries.

Example:

```text
GOOD
components/ui/status.blade.php
pages/admin/applications/show.blade.php

BAD
components/ui/application-approval.blade.php
```

The second example mixes a reusable UI concern with a domain workflow.

## 6. Page pattern

Each complex page SHOULD be decomposed into:

```text
show.blade.php
├── page-header.blade.php
├── filters.blade.php
├── table.blade.php
├── dialogs.blade.php
└── scripts.blade.php (only when unavoidable)
```

Do not produce a 1,000-line Blade view for a complex resource.

## 7. Page families

### Dashboard

Operational summary, security posture, active applications, recent events.

### Applications

```text
index
create
edit
show
credentials
redirect-uris
scopes
claims
sessions
activity
```

### Users

```text
index
show
sessions
security
applications
```

### Sessions

```text
index
show
revoke-bulk
```

### Security / Audit

```text
index
show
filters
export
```

### Protocol configuration

OIDC and SAML pages SHALL expose protocol configuration without leaking private key material or client secrets.

## 8. UX principles

### Information density

Identity infrastructure contains tables and identifiers. Prefer compact tables with a clear hierarchy instead of large decorative cards.

### Security states

Use explicit states:

```text
Active
Disabled
Pending
Expired
Revoked
Rotating
Requires attention
```

Do not communicate important security state by color alone.

### Destructive actions

Destructive actions must show:

- what will be changed;
- affected resource;
- reversibility;
- required confirmation;
- audit consequence.

Examples:

```text
Revoke application
Rotate signing key
Revoke all sessions
Delete client
Disable identity provider
```

## 9. Dark mode

Tabler's light/dark support SHALL be retained. The application SHALL keep the semantic state of the UI separate from business state.

Never store authentication/security state only in a CSS class or client-side variable.

## 10. Accessibility

Every interactive component SHALL have:

- keyboard access;
- visible focus state;
- semantic labels;
- accessible error text;
- sufficient contrast;
- no color-only security indicators.

## 11. Shared-hosting constraint

The dashboard MUST remain usable when deployed to ordinary shared hosting with:

- PHP + Laravel;
- database;
- standard web server;
- prebuilt static assets.

No feature may require a long-running Node.js process merely to render the admin dashboard.

## 12. UI contract for AI agents

AI agents MUST:

1. use the existing Tabler primitives before creating custom CSS;
2. search `components/` before introducing a duplicate component;
3. avoid inline style unless required and documented;
4. avoid introducing Vue for a normal CRUD/dashboard screen;
5. keep page-specific markup under `pages/admin/<feature>`;
6. keep reusable Blade components generic;
7. update this document when introducing a new UI subsystem;
8. never copy an entire Tabler demo page into one giant Blade file.

## 13. Upgrade strategy

Tabler SHALL be updated through a controlled dependency change.

Upgrade process:

```text
read Tabler changelog
→ inspect breaking changes
→ update package lock
→ run visual regression tests
→ run dashboard feature tests
→ inspect security-critical pages manually
→ record ADR when behavior changes
```

Source: https://github.com/tabler/tabler/releases


## 14. Official references

- Tabler repository: https://github.com/tabler/tabler
- Tabler releases: https://github.com/tabler/tabler/releases
- Tabler documentation: https://docs.tabler.io/
- Laravel installation: https://laravel.com/docs/13.x/installation
- Laravel Vite: https://laravel.com/docs/13.x/vite

## 7.1 Authorization-aware UI

The dashboard UI is an administrative control plane and should only be rendered after platform-access middleware succeeds.

Navigation/actions MAY use Blade authorization directives such as:

```blade
@can('applications.create')
    ...
@endcan
```

but UI visibility is not security. Every privileged endpoint MUST enforce authorization server-side.

The initial role is `platform_admin`. Future granular roles should be introduced through permission keys rather than duplicating dashboard layouts.


## Authorization-aware UI

Sidebar/menu/action visibility MAY use Laravel `@can()` backed by Spatie permissions, but UI hiding is not security enforcement. Every route/action must still enforce authorization server-side.

Example concept:

```blade
@can('applications.create')
    {{-- show create action --}}
@endcan
```

High-risk actions should use distinct permission keys and may request recent authentication.
