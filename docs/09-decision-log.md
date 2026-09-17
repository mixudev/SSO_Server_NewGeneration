# Architecture Decision Log — Mixu SSO / Identity Platform

Format setiap keputusan:

```text
ADR-XXX
Title
Status
Date
Context
Decision
Consequences
Alternatives
```

---

## ADR-001 — Laravel application sebagai Identity Platform kernel

**Status:** Accepted
**Date:** 2026-09-17

### Context

Sistem ingin dibangun sendiri agar control plane, domain logic, UX, dan integration contracts dapat disesuaikan.

### Decision

Laravel menjadi application kernel. Tidak menggunakan produk IdP siap pakai sebagai core deployment.

### Consequences

Kita bertanggung jawab atas protocol integration, security review, observability, dan long-term maintenance.

---

## ADR-002 — `mixudev/laravel-authentication` sebagai Authentication Boundary

**Status:** Accepted
**Date:** 2026-09-17

### Context

User authentication sudah tersedia melalui package `mixudev/laravel-authentication`.

### Decision

SSO core tidak mengimplementasikan password/passkey/OTP login ulang. SSO berkomunikasi melalui authentication contract.

### Consequences

Authentication implementation dapat di-upgrade/replaced tanpa mengubah OIDC/SAML domain.

---

## ADR-003 — Passport hanya sebagai OAuth2 adapter

**Status:** Accepted
**Date:** 2026-09-17

### Context

Laravel Passport menyediakan OAuth2 server implementation dan mengurangi risiko membuat OAuth2 token machinery dari nol.

### Decision

Gunakan Passport di infrastructure layer. Jangan menjadikan concrete Passport APIs sebagai domain API.

### Consequences

Upgrade/replacement Passport tetap terisolasi. OIDC layer tetap milik project.

---

## ADR-004 — OIDC dibangun sebagai identity layer di atas OAuth2

**Status:** Accepted
**Date:** 2026-09-17

### Context

OIDC membutuhkan discovery, ID Token, claims, nonce, UserInfo, dan issuer semantics di atas OAuth2.

### Decision

Implementasi OIDC custom berada di Protocol/Application layer dengan signing/key contracts sendiri.

### Consequences

Project tidak menganggap OAuth2 token server otomatis sebagai OpenID Provider.

---

## ADR-005 — SAML di belakang adapter

**Status:** Accepted
**Date:** 2026-09-17

### Context

SAML kompleks dan memiliki dependency pada XML signature/canonicalization/binding/metadata.

### Decision

SAML engine apa pun harus berada di Infrastructure Adapter. Tidak boleh ada dependency SAML langsung di Domain.

### Consequences

Engine dapat diganti tanpa mengubah claim model/application model.

---

## ADR-006 — Multi-tenant ready sejak awal

**Status:** Accepted
**Date:** 2026-09-17

### Context

Refactor tenant isolation setelah banyak data tercipta memiliki risiko tinggi.

### Decision

Gunakan `organizations` dan tenant-scoped application resources sejak migration awal, walaupun deployment pertama hanya satu organization.

### Consequences

Schema sedikit lebih eksplisit sejak awal, tetapi expansion ke SaaS/multi-org tidak membutuhkan redesign total.

---

## ADR-007 — Discovery-first integration

**Status:** Accepted
**Date:** 2026-09-17

### Context

Client harus dapat terhubung dengan konfigurasi minimal.

### Decision

OIDC client integration dimulai dari `/.well-known/openid-configuration`; SAML dari metadata endpoint/XML.

### Consequences

Developer experience lebih baik dan endpoint baru bisa ditambahkan tanpa meminta client hardcode banyak URL.

---


---

## ADR-008 — Admin dashboard uses official Tabler + Blade

**Status:** Accepted
**Date:** 2026-09-17

### CSS boundary clarification

Authentication views owned by `mixudev/laravel-authentication` retain the Tailwind entrypoint. Tabler is loaded only by the admin layout through dedicated `resources/css/admin.css` and `resources/js/admin.js` entries. This prevents Tabler's Bootstrap/reset selectors from changing the package authentication UI.

### Context

The dashboard must be professional, modular, lightweight to deploy, and suitable for shared hosting.

### Decision

Use official Tabler through `@tabler/core`, Laravel Blade, and Tabler's Bootstrap 5 foundation. Vite is the build pipeline; production serves generated static assets. Vue is not the dashboard baseline.

### Consequences

Dashboard rendering remains server-first and shared-hosting friendly. UI can later be replaced because business logic does not depend on Tabler markup.

---

## ADR-009 — Modular route registry

**Status:** Accepted
**Date:** 2026-09-17

### Context

A protocol platform becomes difficult to review when all routes accumulate in `web.php`.

### Decision

Split routes by meaningful boundary: admin, consent, OIDC, OAuth, SAML, API, and health. `web.php` remains a composition point.

### Consequences

Security review is easier because middleware and protocol endpoints are grouped by responsibility.

---

## ADR-010 — Adversarial security testing is a release gate

**Status:** Accepted
**Date:** 2026-09-17

### Context

SSO has security-critical state transitions and parser/protocol boundaries where happy-path unit tests are insufficient.

### Decision

Every security-sensitive feature must include negative, replay, boundary, concurrency, and authorization-isolation tests as applicable. Critical validators should be strong enough to catch deliberate mutation of security branches.

### Consequences

The suite becomes more expensive to run, but protects protocol invariants against regressions and implementation shortcuts.


---

## ADR-011 — Project-owned authorization for the administrative control plane

**Status:** Accepted  \n**Date:** 2026-09-17

### Context

`mixudev/laravel-authentication` provides authentication but not the role/permission model required to protect and later delegate administration of the SSO control plane. The initial product only needs administrator access, while the long-term architecture must support organization-scoped roles and granular permissions.

### Decision

Implement a small project-owned authorization module using Laravel Gates/Policies with persisted `roles`, `permissions`, `role_permissions`, and `role_assignments`. Seed only `platform_admin` initially. Support platform and organization scope in the assignment model.

Do not add a third-party RBAC package as a baseline dependency solely for this requirement.

### Consequences

The platform has an explicit authorization boundary from day one, prevents hardcoded admin bypasses, and can grow into granular RBAC without replacing controller/policy contracts. The project owns the schema, tests, and maintenance.

### Alternatives

- Hardcoded admin email: rejected; not scalable and weak as an authorization model.
- `is_admin` boolean: rejected; cannot express granular/scoped permissions safely.
- Third-party RBAC package: deferred; may be considered later only if requirements exceed the project-owned model and an explicit ADR approves it.


## ADR-014 — Redirect URIs use normalized storage and exact matching

**Status:** Accepted
**Date:** 2026-09-17

### Decision

Store redirect URIs under an application with a SHA-256 lookup hash, canonicalize scheme/host and default ports, and compare exact canonical strings. Reject fragments, wildcards, userinfo, unsafe encoded separators, dot segments, and non-loopback HTTP. Loopback HTTP is retained for native development clients only.

### Security consequence

Protocol handlers cannot accept arbitrary callback destinations or wildcard registrations by accident. Production policy may further restrict loopback behavior by client type.

---

## ADR-013 — Application Registry is tenant-scoped before protocol integration

**Status:** Accepted
**Date:** 2026-09-17

### Context

Applications are the trust anchor for redirect URIs, credentials, scopes, claims, and later OAuth/OIDC protocol transactions. Protocol implementation before ownership and lifecycle boundaries are persisted would create a tenant-breakout risk.

### Decision

Create applications with ULID identifiers, an organization foreign key, organization-scoped slug uniqueness, explicit lifecycle/status fields, and protocol/client metadata before implementing redirect validation or protocol endpoints. Keep protocol-specific child resources in separate slices.

### Consequences

Application resolution can enforce organization ownership and active lifecycle before any protocol action. The registry schema is explicit, while redirect URIs, credentials, scopes, claims, and CRUD UI remain independently testable.

---

## ADR-012 — Use Spatie Laravel Permission for Control-Plane RBAC

**Status:** Accepted
**Date:** 2026-09-17

### Decision
Use `spatie/laravel-permission:^8.3` for role/permission persistence and Laravel Gate/Policies for enforcement.

### Reason
Avoid unnecessary custom RBAC persistence/security code while retaining project-owned authorization semantics and audit policy.

### Boundary
Spatie owns role/permission mechanics. Mixu owns which permissions exist, when they are required, scope/resource policy, admin workflows, and auditing.

## ADR-013 — Use Spatie Laravel Backup for Recovery Baseline

**Status:** Accepted
**Date:** 2026-09-17

### Decision
Use `spatie/laravel-backup:^10` for application/database backup, cleanup, external storage, and monitoring.

### Reason
Established package reduces custom backup implementation risk. Recovery policy remains project-owned.

### Important constraint
Current v10 documentation states Windows servers are unsupported; production baseline is Linux. Backup encryption, destination separation, retention, monitoring, and restore drills are mandatory parts of the design. citeturn230300search4turn230300search0
