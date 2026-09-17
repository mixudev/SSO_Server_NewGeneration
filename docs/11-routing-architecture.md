# Routing Architecture — Mixu SSO / Identity Platform

- Document ID: `SSO-ROUTES-001`
- Version: `0.2`
- Status: Baseline
- Date: `2026-09-17`

## 1. Routing principle

`routes/web.php` SHALL be a composition file, not the place where every endpoint is defined.

As the system grows, routes are split by bounded context and protocol while Laravel's route loading remains centralized.

Target:

```text
routes/
├── web.php
├── admin.php
├── consent.php
├── oidc.php
├── oauth.php
├── saml.php
├── api.php
└── health.php
```

The exact split may evolve, but the principle is fixed: route files are organized by protocol or application boundary, not by controller size.

## 2. Route ownership

### `web.php`

Only composition for browser-facing top-level web routes and shared route includes.

### `admin.php`

Admin dashboard UI and admin actions.

Typical structure:

```text
/admin
/admin/applications
/admin/users
/admin/sessions
/admin/audit
/admin/security
/admin/settings
```

Middleware should include the appropriate authenticated/admin authorization chain.

### `consent.php`

User-facing authorization consent screens and consent decisions.

Keep consent separate because it is security-sensitive and has different UX/middleware requirements from admin CRUD.

### `oidc.php`

OpenID Connect endpoints:

```text
/.well-known/openid-configuration
/.well-known/jwks.json
/oauth/authorize
/oauth/userinfo
```

Token endpoint ownership SHALL follow the OAuth/Passport integration decision. Do not duplicate a vendor endpoint manually just because a route name seems convenient.

### `oauth.php`

OAuth2-specific endpoints not already registered by Passport. This file is for project-owned OAuth behavior; vendor-managed routes remain vendor-managed.

### `saml.php`

SAML metadata, SSO, SLO, and protocol callback endpoints.

### `api.php`

Non-browser APIs used by the dashboard or future SDKs. APIs MUST have explicit authentication and authorization contracts.

### `health.php`

Minimal liveness/readiness endpoints. Do not expose configuration, secrets, keys, user data, or detailed exception output.

## 3. Route naming contract

All project-owned routes SHALL have stable names.

Examples:

```text
admin.dashboard
admin.applications.index
admin.applications.create
admin.applications.store
admin.applications.show
admin.applications.update
admin.applications.destroy
admin.audit.index
admin.audit.show
admin.sessions.index
admin.sessions.revoke
consent.show
oidc.discovery
oidc.jwks
oidc.authorize
oidc.userinfo
saml.metadata
saml.sso
saml.slo
health.liveness
```

Do not use a URL string in Blade or controller code when a route name exists.

## 4. URL contract

Project-owned UI URLs SHOULD follow:

```text
/admin/<resource>
/admin/<resource>/<id>
```

Protocol URLs MUST follow their protocol-defined conventions even when they do not match the admin naming convention.

Never move a protocol endpoint just to make the URL visually consistent with admin routes.

## 5. Route groups

Routes SHOULD be grouped by concerns:

```php
Route::middleware([...])->prefix('admin')->name('admin.')->group(function () {
    require base_path('routes/admin.php');
});
```

For more complex projects, each route file may declare its own prefix/name group in the composition layer.

The chosen implementation must avoid double prefixes/names.

## 6. Middleware matrix

```text
Route family        Session   CSRF    Rate limit   AuthZ   Protocol validation
──────────────────────────────────────────────────────────────────────────────
Admin HTML             ✓        ✓          ✓         ✓          n/a
Consent HTML            ✓        ✓          ✓         ✓          ✓
OIDC authorize          ✓*       context    ✓         ✓          ✓
OIDC token              ✗        n/a        ✓         client       ✓
OIDC userinfo           ✗        n/a        ✓         token        ✓
SAML SSO                context  binding    ✓         client       ✓
SAML metadata           ✗        n/a        ✓         public        ✓
Health                  ✗        n/a        ✓         n/a          minimal
```

`✓*` means browser SSO session may be required depending on authorization flow.

Do not blindly apply `web` middleware to protocol endpoints that are defined as API-style token endpoints.

## 7. Controller contract

Protocol controllers MUST be thin.

Example:

```text
HTTP Request
  ↓
Request validation
  ↓
Protocol DTO
  ↓
Application use case
  ↓
Domain decision
  ↓
Protocol response mapper
```

Admin controller:

```text
HTTP Request
  ↓
Form Request
  ↓
Authorization policy
  ↓
Command/Query
  ↓
View model/resource
  ↓
Blade
```

## 8. Route decomposition rule

Split a route file when at least one of these is true:

- it crosses a protocol/bounded-context boundary;
- the file becomes difficult to navigate;
- its middleware requirements differ materially;
- it has a separate lifecycle/versioning policy;
- it is security-critical and deserves isolated review.

Do NOT split routes merely into one file per controller. That creates noise without creating a meaningful boundary.

## 9. Route versioning

Project-owned APIs SHOULD use explicit versions when public API compatibility becomes contractual:

```text
/api/v1/...
```

OIDC/OAuth/SAML protocol endpoints are not arbitrarily versioned by URL. Protocol compatibility is managed by protocol semantics and supported versions.

## 10. Route security rules

AI agents MUST treat these as invariants:

- no state-changing GET route;
- no wildcard admin route without explicit authorization;
- no catch-all route above protocol routes;
- no direct model binding that can bypass tenant/policy checks;
- no public diagnostic route in production;
- no query parameter containing a password, token, or client secret;
- no redirect route that accepts arbitrary target URLs.

## 11. Route tests

Every route family must have tests for:

- authentication requirement;
- authorization/tenant isolation;
- method enforcement;
- CSRF behavior where applicable;
- rate limiting;
- content type;
- protocol-required response headers;
- expected redirect/error behavior.

## 6. Authorization middleware contract

Privileged dashboard routes SHALL follow:

```text
web/session
  → auth middleware
  → platform-access middleware
  → controller
  → policy/gate/application authorization
```

Platform-access middleware only answers "may this subject enter the control plane?". It MUST NOT replace resource-level policies.

Protocol routes such as OAuth/OIDC authorization endpoints MUST use their own protocol security rules and MUST NOT inherit dashboard-only access middleware.


## Authorization middleware

Admin routes SHALL use authentication + authorization middleware/policies. Where Spatie middleware is used, it is an enforcement convenience, not a replacement for resource Policies.

Recommended order:

```text
session/authentication
→ platform authorization
→ permission
→ policy/resource ownership
→ controller/use case
```

Never rely on sidebar/menu visibility to protect a route.
