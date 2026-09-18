# Implementation Tasks

## Purpose

This directory contains the executable roadmap for the Mixu SSO identity platform. Each phase is independently reviewable and must be completed in order unless a dependency is explicitly documented.

## Status

| Phase | Area | Target | Status |
|---|---|---|---|
| 0 | Foundation and dashboard shell | Repository, docs, CI, UI baseline | Completed |
| 1 | Identity core and policies | Registry, claims, keys, audit | Planned (Next) |
| 2 | Admin control plane (Dashboard views) | CRUD, roles, sessions, operations | Planned (After Phase 1) |
| 3 | OAuth 2.0 infrastructure | Authorization, PKCE, tokens | Planned |
| 4 | OpenID Connect provider | Discovery, JWKS, ID token, UserInfo | Planned |
| 5 | SAML federation | Metadata, assertions, SLO | Planned |
| 6 | Production hardening | Limits, backup, metrics, recovery | Planned |

## Rule on Dashboard View Development

- Dashboard view implementation for domain entities (Applications, Users, Scopes, Claims, Keys, Audit) **MUST ONLY** be built once the corresponding domain entities and services exist (Phase 1).
- Phase 2 contains the explicit, detailed breakdown of all administrative dashboard screens and components.
- Do not build empty or mock screens before the domain models and policies are in place.

## Standard task contract

Every task must follow this loop:

1. Inspect existing symbols and package versions.
2. Write the smallest failing PHPUnit test.
3. Run the focused test and record the failure.
4. Implement the minimum change.
5. Run the focused test, full relevant suite, Pint, and Vite when frontend files change.
6. Review the diff for secrets, unrelated changes, and architecture violations.
7. Commit one cohesive slice with a conventional commit message.

## Definition of Done

- The acceptance criteria in the phase file are met.
- Positive, negative, expiry, replay, authorization, and abuse tests exist where relevant.
- `php artisan test --compact` passes.
- `vendor/bin/pint --dirty --format agent` passes.
- `npm run build` passes for frontend changes.
- `php artisan view:cache` passes for Blade changes.
- No runtime CDN assets, secrets, or credentials are committed.
- Domain code does not import protocol/vendor implementation classes.
- UI uses existing modular Blade components (`x-dashboard.*`, `x-app-modal`, `x-allert`, `x-form.*`, `x-table.*`) and Bootstrap Icons (`bi bi-*`).
- `git diff --check` is clean.

## Dependency order

`phase-01` enables `phase-02` (Dashboard Views); `phase-02` provides control-plane workflows for `phase-03`; `phase-03` is required by `phase-04`; `phase-05` may proceed after protocol contracts but must remain adapter-isolated; `phase-06` starts after the security-critical flows are exercised.

## Phase files

- [Phase 1 — Identity Core and Policies](phase-01-identity-core-policies.md)
- [Phase 2 — Admin Control Plane (Dashboard Views)](phase-02-admin-control-plane-crud.md)
- [Phase 3 — OAuth 2.0 Infrastructure](phase-03-oauth2-infrastructure.md)
- [Phase 4 — OpenID Connect Provider](phase-04-oidc-provider.md)
- [Phase 5 — SAML Federation](phase-05-saml2-federation.md)
- [Phase 6 — Production Hardening and Observability](phase-06-production-hardening-observability.md)
