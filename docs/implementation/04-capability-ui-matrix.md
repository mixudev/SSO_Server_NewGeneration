# Capability and UI Matrix

Reviewed against current routes, controllers, models, tests, and `docs/tasks/task-list.md`.

## 1. Provider capability matrix

| Capability | Backend/route | Admin UI | Status |
|---|---|---|---|
| Organization registry | `/admin/organizations` | Yes | [AVAILABLE] |
| Application registry | `/admin/applications` | Yes | [AVAILABLE] |
| Application wizard | Admin wizard routes | Yes | [AVAILABLE] |
| Login redirect URI | Application update/wizard | Yes | [AVAILABLE] |
| Post-logout redirect URI | Application update | Yes | [AVAILABLE] |
| Scope registry | `/admin/scopes` | Yes | [AVAILABLE] |
| Claim registry | `/admin/claims` | Yes | [AVAILABLE] |
| Versioned claim policy | `application_claim_policies` | Initial policy generated; policy editor absent | [PARTIAL] |
| Credential issue | Application detail | Modal | [AVAILABLE] |
| Credential rotate | Application detail | Modal | [AVAILABLE] |
| Credential revoke | Application detail | Modal | [AVAILABLE] |
| Credential reactivate | Application detail | Modal | [AVAILABLE] |
| OAuth authorize | `GET /oauth/authorize` | Consent UI | [AVAILABLE] |
| OAuth token | `POST /oauth/token` | No admin UI required | [AVAILABLE] |
| OAuth revoke | `POST /oauth/revoke` | No admin UI required | [AVAILABLE] |
| OIDC discovery | `/.well-known/openid-configuration` | No admin UI required | [AVAILABLE] |
| JWKS | `/.well-known/jwks.json` | Key Management view | [AVAILABLE] |
| OIDC UserInfo | `GET /oauth/userinfo` | No admin UI required | [AVAILABLE] |
| OIDC ID Token | Token response | No admin UI required | [AVAILABLE] |
| RP-Initiated logout | `GET /oauth/end-session` | Client-driven | [PARTIAL] |
| Front/back-channel logout | None | None | [DISABLED] |
| Key generation/rotation | Admin key routes | Key Management view | [AVAILABLE] |
| User administration | `/admin/users` | Yes | [AVAILABLE] |
| Role/permission registry | `/admin/roles` | Yes | [AVAILABLE] |
| Audit view/export | `/admin/audit` | Yes | [AVAILABLE] |
| Session Inspector | `/admin/sessions` | Read-only | [AVAILABLE] |
| Cross-user session revoke | No complete authorized mutation | No | [NOT AVAILABLE] |
| Health liveness/readiness | `/health/*` | Operational endpoint | [AVAILABLE] |
| Rate limits | Middleware/provider | No UI | [AVAILABLE] |
| Backup scheduling | Scheduler | No dashboard execution UI | [PARTIAL] |
| Backup restore drill | Linux operational runbook | No UI | [NOT VERIFIED] |
| Request metrics | No metrics backend | No UI | [NOT AVAILABLE] |
| Native client | Credential issuance fail-closed | No supported UI | [DISABLED] |
| Public SPA | `public_spa` | Registration supported | [PARTIAL] |
| M2M client credentials | No completed application policy slice | No supported UI | [DISABLED] |
| Device flow | Not integrated | None | [DISABLED] |
| SAML | No active route/adapter | None | [DISABLED] |

## 2. Existing dashboard pages

Implemented view families:

- Dashboard and security posture.
- Applications index, wizard, detail, credential lifecycle.
- Organizations index/detail.
- Users index/detail/activity.
- Roles.
- Scopes.
- Claims.
- Signing keys.
- Audit log/export.
- Session Inspector.
- Profile overview/edit.
- Profile Security Center.
- Two-factor setup.
- Passkey registration/removal boundary.
- Avatar management.

## 3. Features not represented by dashboard UI

These are intentionally protocol or operational functions, not necessarily dashboard CRUD:

- Authorization code exchange.
- Token refresh.
- Token revocation endpoint.
- UserInfo.
- Discovery/JWKS.
- ID Token verification.
- Health probes.
- Scheduler execution.

These require client/operations documentation and integration tests rather than an admin button.

## 4. UI gaps requiring work

1. Claim policy editor with immutable version creation and activation workflow.
2. Credential step-up authentication before issue/rotate/revoke/reactivate.
3. Cross-user session revoke with explicit permission, target authorization, package service boundary, audit, and confirmation.
4. Backup status/last-success dashboard projection without archive secrets.
5. Metrics dashboard after a metrics backend is selected.
6. Complete visual/accessibility pass across all Phase 2 views.
7. Remaining page-local action migration.
8. Native client registration UI only after redirect policy is implemented.
9. M2M UI only after client-credentials policy and audit slice is implemented.
10. SAML UI must not be created until SAML adapter and security contract exist.

## 5. Important discrepancy

`docs/TARGET-CLIENT.md` describes SAML configuration fields and SAML UI. Current active route inventory has no SAML route or SAML implementation. Treat that file as legacy target documentation, not evidence of an available feature. Update it only when SAML is intentionally implemented.
