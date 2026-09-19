# Dashboard UI Architecture — Mixu Dashboard + Laravel Blade

- Document ID: `SSO-UI-001`
- Version: `0.4`
- Status: Implemented shell and shared component system
- Date: `2026-09-17`

## 1. UI decision

Dashboard admin Mixu SSO SHALL use:

- Laravel 13.x
- Blade server-rendered views
- Mixu Dashboard primitives as the visual foundation
- Tailwind CSS v4 bundled with Vite
- Alpine.js only for small local interactions
- Vue is NOT the dashboard foundation

The dashboard is a server-rendered identity control plane, not an SPA. The source template was inspected from `mixu-dashboard`, then removed after its required presentation files were migrated into project-owned components.

The template's MAM Limpung-specific layout, external CDN assets, demo pages, fake data, and unrelated dependencies were not migrated.

## 2. Why Mixu Dashboard

Mixu Dashboard is selected because its primitives fit an identity/control-plane product:

- compact cards, forms, tables, and status badges;
- server-rendered Blade components;
- responsive layout primitives;
- light and dark mode support;
- self-hosted SVG icons and no runtime CDN requirement.

The project owns the migrated components and does not depend on the source template as runtime infrastructure.

## 3. Installation and asset strategy

The dashboard uses the existing Laravel Vite toolchain. Runtime UI assets are project-owned and bundled through the separate admin entries:

```text
resources/css/dashboard.css
resources/js/dashboard.js
        ↓
Vite build
        ↓
public/build
        ↓
admin Blade layout/components
```

Build workflow:

```bash
npm install
npm run build
```

Authentication continues to use `resources/css/app.css` and `resources/js/app.js`; the two asset boundaries must remain separate.

### 3.1 Build workflow

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

- Dashboard CSS/JS from the project build;
- Bootstrap Icons from `bootstrap-icons` npm package bundled locally;
- no runtime dependency on third-party CDN for authentication/security screens;
- version-lock frontend dependencies through the package lock file.

## 4. Dashboard layout contract

The canonical layout is:

```text
resources/views/
├── layouts/
│   ├── guest.blade.php
│   └── auth.blade.php
│
├── components/
│   ├── admin/
│   │   ├── layout.blade.php
│   │   ├── sidebar.blade.php
│   │   ├── navbar.blade.php
│   │   ├── breadcrumb.blade.php
│   │   └── footer.blade.php
│   ├── shared/
│   │   └── flash-messages.blade.php
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
activity
```

User detail is read-only Bento Grid. Mutations use `x-app-modal`. Public URLs use UUID, never numeric IDs.

### Roles

```text
index
```

Role create/update stays in modals. `platform_admin` is a system role.

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

Mixu Dashboard's light/dark support SHALL be retained. The application SHALL keep the semantic state of the UI separate from business state.

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

1. use the existing Mixu Dashboard primitives before creating custom CSS;
2. search `resources/views/components/` before introducing a duplicate component;
3. avoid inline style unless required and documented;
4. avoid introducing Vue for a normal CRUD/dashboard screen;
5. keep page-specific markup under `pages/admin/<feature>`;
6. keep reusable Blade components generic;
7. update this document when introducing a new UI subsystem;
8. keep dashboard pages modular; never copy a complete demo page into one giant Blade file.

## 13. Upgrade strategy

Dashboard assets SHALL be updated through controlled npm and composer package upgrades.

Upgrade process:

```text
read package changelog
→ inspect breaking changes
→ update package.json / composer.json
→ run npm run build / test suite
```

## 14. Shared component styling contract

The dashboard's reusable Blade components are styled with Tailwind CSS v4 utility classes and semantic CSS variables from `resources/css/dashboard/theme.css`.

- Forms: `x-form.input` and `x-form.group` use theme surfaces, 2px borders, violet focus states, mono labels, and accessible validation text.
- Buttons: `x-form.button` is the standard text/action button with `primary`, `secondary`, `danger`, `outline`, and `ghost` variants plus `sm`, `md`, and `lg` sizes.
- Tables: `x-table.wrapper`, `x-table.th`, `x-table.td`, `x-table.label`, and `x-table.action` use compact spacing, hairline borders, theme-aware surfaces, and responsive horizontal overflow.
- Rectangular controls use a 2px radius. Profile triggers and status/security labels use `rounded-full`.
- New dashboard components MUST use Tailwind utilities and semantic `var(--dash-*)` values. Do not introduce legacy slate/zinc palettes, inline styles, or component-specific CSS when utilities are sufficient.
- Dashboard CSS remains limited to shared tokens, shell geometry, responsive behavior, and interaction states that cannot be expressed safely in Blade utilities.

## 15. Official references

- Bootstrap Icons: https://icons.getbootstrap.com/
- Tailwind CSS: https://tailwindcss.com/
- Alpine.js: https://alpinejs.dev/
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
