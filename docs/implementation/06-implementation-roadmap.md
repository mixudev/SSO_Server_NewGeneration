# Implementation Roadmap

Semua task harus dikerjakan sebagai vertical slice: failing test, implementation minimum, adversarial test, documentation, verification, commit, dan push.

## Phase A — Package client foundation

### A1. Package skeleton

- Create standalone package repository or approved package directory.
- Add Laravel service provider, config, publishable migrations, routes, and contracts.
- Define PHP/Laravel support matrix.
- Add config validation and fail-closed defaults.
- Add no-secret logging test.

Acceptance: package installs into a clean Laravel app and config cache succeeds.

### A2. Discovery and transport

- Implement issuer-based discovery client.
- Enforce HTTPS in production.
- Validate discovery issuer exact match.
- Add timeout, connect timeout, bounded retry, response-size limit, and TLS verification.
- Add cache with stale/refresh policy.

Acceptance: malformed, spoofed, private, timeout, and wrong-issuer metadata are rejected.

### A3. State, nonce, PKCE

- Add one-time server-side authorization transaction storage.
- Generate high-entropy state, nonce, and verifier.
- Store hashes/bindings and expiry.
- Consume atomically.
- Reject replay, mismatch, missing values, and cross-session use.

Acceptance: login CSRF, callback replay, and wrong-client callback tests pass.

## Phase B — OIDC protocol client

### B1. Authorization and callback

- Add client-owned redirect/callback routes.
- Build authorization URL from discovery.
- Exchange code with form-encoded request.
- Normalize safe protocol errors.
- Never expose raw token response.

### B2. JWKS and ID Token verification

- Choose and pin a maintained JOSE/JWT dependency.
- Implement RS256 allowlist, JWKS cache, unknown-kid refresh, issuer/audience/nonce/expiry checks.
- Add clock-skew policy.

Acceptance: forged, expired, wrong-audience, wrong-issuer, wrong-nonce, unknown-kid, and algorithm-confusion tests pass.

### B3. UserInfo and local identity

- Implement bearer UserInfo client.
- Define `UserResolver` contract.
- Resolve by `(issuer, sub)`.
- Add explicit verified-email linking policy.
- Add inactive-user and provisioning hooks.
- Regenerate local session after success.

Acceptance: no automatic unsafe email linking; local account/session tests pass.

## Phase C — Lifecycle and operations

### C1. Refresh and token storage

- Add encrypted server-side token store for confidential clients.
- Add single-flight refresh lock.
- Handle rotation and replay.
- Clear token set on invalid grant.

### C2. Logout and revocation

- Add local logout cleanup.
- Add provider end-session integration.
- Validate post-logout redirect from configured registry only.
- Add revocation and bounded retry/error policy.

### C3. Observability

- Add redacted correlation logging.
- Add counters/latency metrics backend selection.
- Expose health without secrets.
- Add alert conditions for repeated invalid signature, refresh replay, and provider outage.

## Phase D — Provider UI completion

### D1. Claim policy editor

- Add permission-separated policy view/edit.
- Create immutable new version instead of mutating issued policy.
- Revalidate active claims/scopes inside transaction.
- Show effective scopes and claims without internal IDs.
- Audit every version activation.

### D2. Credential step-up

- Require current password or package-backed step-up before issue, rotate, revoke, and reactivate.
- Add rate limits and audit correlation.
- Never return old secret.

### D3. Cross-user session revocation

- Confirm package service boundary.
- Add dedicated permission distinct from session view.
- Authorize target user and organization scope.
- Use package service, not direct table mutation.
- Add audit and confirmation UI.

### D4. Accessibility and action review

- Review all Phase 2 pages keyboard, focus, labels, contrast, modal semantics, and responsive behavior.
- Replace remaining ordinary page-local buttons/links with shared components.
- Keep password toggles, copy controls, WebAuthn, and modal state controls specialized.

## Phase E — Operational proof and interoperability

### E1. Linux backup restore drill

- Run encrypted backup on Linux/CI with secret manager injection.
- Restore database and approved files into isolated environment.
- Verify application, client registry, audit, and key continuity.
- Record RPO/RTO without secrets.

### E2. Signing-key recovery drill

- Restore approved key custody material separately.
- Verify old/new JWKS and ID Token validation.
- Rotate compromised key and revoke affected credentials.
- Record operator approvals and audit events.

### E3. External client smoke tests

- Use a real HTTPS staging domain.
- Test confidential web flow end to end.
- Test public SPA only after browser token policy is approved.
- Test discovery, authorize, consent, token, ID Token, UserInfo, refresh, revoke, and logout.
- Test provider outage, key rotation, and invalid callback cases.

## Explicitly deferred

- Native/mobile support until loopback/custom-scheme/universal-link policy and interoperability tests exist.
- M2M until client-credentials policy, audience/scope model, secret lifecycle, and audit are complete.
- Device flow until device authorization UX and polling abuse controls exist.
- SAML until adapter, metadata, signature, assertion, replay, and UI contracts exist.

## Final definition of done

The system may be called production-ready only when every enabled capability has:

1. route/API contract;
2. authorization boundary;
3. persistence/lifecycle behavior;
4. user/operator UI where applicable;
5. positive, negative, replay, expiry, and isolation tests;
6. documentation;
7. operational monitoring;
8. recovery proof;
9. external interoperability proof.
