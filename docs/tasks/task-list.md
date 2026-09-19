# Project Task List

Updated: 2026-09-19

## Completed

- [x] Establish dashboard shell with authenticated admin authorization.
- [x] Build Applications index with search, status filtering, pagination, and safe empty state.
- [x] Build application create, detail, update, and guarded draft deletion flows.
- [x] Build application creation wizard with redirect, scope, claim, consent, and session-policy validation.
- [x] Add profile overview and profile edit flows with server-side validation.
- [x] Add avatar upload and removal with image validation, storage cleanup, migration, and throttling.
- [x] Build application-owned Profile Security Center.
- [x] Integrate package-backed 2FA setup, verification, and recovery-code download/copy.
- [x] Integrate package-backed passkey registration and removal boundaries.
- [x] Integrate active-session listing and session revocation actions.
- [x] Integrate password update and password-reset-link adapter using `ForgotPasswordRequest`.
- [x] Add confirmation alerts for sensitive profile and security actions.
- [x] Add dark-mode tokens for primary actions, inputs, recovery codes, banners, and flash notifications.
- [x] Add password visibility controls and minimum password length of eight characters.
- [x] Use application-owned pages without links to authentication-package UI.
- [x] Verify focused profile/security tests, admin suite, Blade cache, Pint, Vite build, and diff checks.

## Current next steps

- [x] Complete the final review integrity boundary for the application wizard, including tamper-resistant final validation and active-organization revalidation.
- [x] Define and test credential generation separately from application activation.
- [x] Implement application credential issuance, rotation, and revocation boundary.
- [x] Add application-aware OAuth client runtime enforcement for suspended/revoked applications and organizations.
- [x] Build read-only Security Audit dashboard with bounded filters and redacted metadata.
- [x] Complete authentication security-event projection for all subscribed lifecycle events.
- [x] Build Key Management view with rotation, revocation, audit, and private-key exclusion.
- [x] Build read-only global Session Inspector from the canonical database session table.
- [ ] Add separately authorized cross-user session revocation after package boundary review.
- [x] Add database-level active signing-key uniqueness and active-slot rotation hardening.
- [x] Build the Audit Log export boundary with explicit authorization and redaction.
- [x] Build Users administration with UUID public identity, status/ban lifecycle, last-admin protection, and audit coverage.
- [x] Build the Roles registry view with separate permissions, system-role protection, and allowlisted permission assignment.
- [x] Build Organizations administration view with lifecycle status, application counts, search, authorization, and audit coverage.
- [x] Build Scopes and Claims registry views with validator-backed creation and separate permissions.
- [x] Add modal-based Scope create/update/revocation flow with system/reference guards.
- [x] Add modal-based Claim create/update/deactivation flow with validator and permission boundaries.
- [x] Add modal-based OAuth client issue/rotate/revoke flow on application details with one-time secret handling.
- [ ] Define persisted versioned application claim-policy records before policy editing or protocol issuance.
- [x] Fail closed for native client credential issuance until custom-scheme/universal-link redirect policy is implemented.
- [ ] Complete dashboard visual and accessibility review across all Phase 2 views.
- [x] Start OAuth 2.0 boundary audit and Passport adapter contracts.
- [x] Extract Passport client lifecycle behind an application-owned OAuth port and DTO.
- [x] Add server-side authorization transaction persistence with hashed state/nonce/redirect bindings and lifecycle status.
- [x] Add OAuth authorization endpoint with exact redirect/scope binding, application lifecycle checks, and S256-only PKCE validation.
- [x] Complete OAuth consent approval, one-time authorization-code handoff, transaction binding, expiry, replay, and actor-isolation tests.
- [x] Add atomic application-owned authorization-code redemption with PKCE S256, client/redirect binding, expiry, and replay protection.
- [x] Expose Passport-backed authorization-code handoff; Passport owns `/oauth/token`, PKCE redemption, token issuance, refresh tokens, and code replay revocation.
- [x] Add protocol-level `/oauth/token` integration tests for valid exchange, invalid verifier, and standard OAuth errors.
- [x] Remove the unused parallel application-owned authorization-code table and redeemer.
- [x] Add idempotent OAuth revocation endpoint and token-redaction tests.
- [x] Add consent denial protocol error redirect and terminal transaction status.
- [x] Allowlist consent decisions and reject unknown values before consuming transaction session state.
- [ ] Add adversarial client/redirect/expiry/replay coverage for every token error path.
- [x] Add OIDC RP-initiated end-session endpoint with registered post-logout redirect validation and state preservation.
- [x] Add OIDC discovery, JWKS, and scoped UserInfo endpoints.
- [x] Add OIDC nonce-bound ID Token issuance and verification tests. (Passport authorization-code exchange now returns a signed RS256 `id_token`; issuer, audience, subject, nonce, expiry, signature, and replay coverage is present.)
- [x] Add database/signing-key readiness health check.
- [ ] Add audit metrics and complete backup drill/recovery runbook. (OIDC/OAuth rate limits implemented, token endpoint middleware verified, scheduler and archive verification configured; Linux restore drill and metrics remain.)
- [ ] Add adversarial and integration coverage for newly introduced profile/security mutations.
- [ ] Replace remaining page-local button/link markup with shared Blade action components where the existing components apply.

## Component standard

- [x] Reuse `x-form.button` for text actions and links that need a shared visual contract.
- [x] Reuse `x-table.action` for icon-only table actions.
- [x] Reuse `x-app-modal` for modal workflows.
- [x] Reuse `x-allert` and `AppPopup` for flash messages and confirmations.
- [ ] Migrate remaining legacy page-local actions incrementally; do not rewrite unrelated pages in one broad pass.

## Verification gate

Every completed slice must pass the narrow feature tests, relevant admin suite, `php artisan view:cache`, `vendor/bin/pint --dirty --format agent`, `npm run build` when assets change, and `git diff --check`.
