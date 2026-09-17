# Security & Adversarial Testing Strategy

- Document ID: `SSO-SEC-TEST-001`
- Version: `0.2`
- Status: Baseline
- Date: `2026-09-17`

## 1. Testing philosophy

Testing is not only verification that expected requests succeed. The security suite SHALL actively attempt to violate system invariants.

The mindset is:

```text
Assume the attacker controls:
- browser input;
- callback query parameters;
- authorization parameters;
- client requests;
- token presentation;
- timing and request ordering;
- tenant/resource identifiers;
- selected HTTP headers;
- network failures;
- concurrent requests.
```

The attacker does NOT receive private keys or server-only secrets unless a separate test explicitly simulates compromise.

## 2. Test layers

```text
Unit
  ↓
Feature / HTTP
  ↓
Integration
  ↓
Protocol conformance
  ↓
Security / adversarial
  ↓
Concurrency / race
  ↓
Property / mutation-style checks
  ↓
End-to-end smoke
```

All layers are required for security-critical functionality.

## 3. Test classification

### Unit tests

Target pure invariants:

- redirect URI matcher;
- scope parser;
- claims resolver;
- nonce validator;
- PKCE verifier;
- audience validator;
- issuer validator;
- expiry calculator;
- key selection;
- tenant/resource policy.

### Feature tests

Exercise real Laravel HTTP boundaries, middleware, session, database, policies, and responses.

### Integration tests

Verify adapters against Passport, authentication package, queue, cache, key store, and selected SAML implementation.

### Protocol tests

Verify wire-level behavior against the applicable RFC/OIDC/SAML expectations.

### Security tests

Actively attempt misuse, tampering, replay, confusion, privilege escalation, and state manipulation.

## 4. Authentication attack cases

Test at minimum:

- unauthenticated access to admin routes;
- authentication context downgraded from MFA to non-MFA;
- revoked session reused;
- session fixation attempts;
- session cookie rotation after authentication;
- logout followed by immediate privileged request;
- cross-tenant session/resource access;
- stale authentication context used for step-up protected action.

## 5. Authorization endpoint attacks

### Redirect URI attacks

Attempt:

```text
https://trusted.example/callback.evil
https://trusted.example@evil.example/callback
https://evil.example/?redirect=https://trusted.example
https://trusted.example/%2e%2e/
https://trusted.example/callback%2f..%2fevil
https://trusted.example/callback#evil
```

Also test:

- trailing slash mismatch;
- scheme mismatch;
- port mismatch;
- punycode/Unicode host edge cases;
- percent encoding variants;
- empty redirect URI;
- duplicate redirect URI parameters;
- HTTP on a production HTTPS registration;
- wildcard registrations.

Expected behavior: exact, normalized comparison according to the selected protocol/client policy; no open redirect.

### Authorization parameter attacks

Test:

- duplicate parameters;
- unknown parameters;
- conflicting `client_id` values;
- conflicting `redirect_uri` values;
- scope escalation;
- invalid response type;
- unsupported grant type;
- malformed state;
- oversized state;
- oversized request URI;
- invalid `code_challenge_method`;
- PKCE downgrade attempts;
- missing nonce where OIDC requires it.

## 6. State / nonce / PKCE attacks

Test that:

- a code tied to transaction A cannot be redeemed under transaction B;
- state from a different browser session fails;
- nonce mismatch invalidates OIDC authentication result;
- code verifier mismatch fails;
- S256 is handled correctly;
- missing verifier cannot bypass PKCE for a PKCE-required client;
- code verifier length/boundaries are validated;
- consumed authorization code can never be consumed again.

## 7. Authorization code replay and race tests

The critical invariant is:

```text
one authorization code
→ at most one successful redemption
```

Execute two concurrent token requests using the same code.

Expected result:

```text
request A → success
request B → invalid_grant/replay failure
```

Never implement this using a naive read-then-update sequence without atomic protection.

## 8. Token attacks

Test:

- expired token;
- token issued to another client;
- token issued by another issuer;
- wrong audience;
- wrong signing key;
- revoked token;
- malformed JWT;
- invalid signature;
- changed payload with unchanged signature;
- unknown `kid`;
- stale `kid` during rotation;
- unsupported algorithm;
- algorithm confusion;
- `alg=none` rejection;
- duplicated critical claims where parser behavior could be ambiguous;
- token type confusion.

## 9. OIDC attacks

Test:

- discovery document reflects active issuer only;
- discovery cannot be poisoned by request host header where host is not trusted;
- ID Token issuer mismatch;
- audience mismatch;
- nonce mismatch;
- `azp` behavior when required;
- auth time/max_age semantics when implemented;
- UserInfo called with wrong token type;
- claims outside requested scope are not silently exposed if policy disallows them.

## 10. JWKS and key rotation tests

Test lifecycle:

```text
key A active
→ publish A
→ create B
→ publish A+B
→ start signing with B
→ old tokens still verify with A during overlap
→ retire A
→ remove A after policy window
```

Attack cases:

- unknown key id;
- duplicate key ids;
- malformed JWK;
- wrong key usage;
- private key accidentally exposed through endpoint;
- rotation race;
- cache containing stale key set;
- cache poisoning attempt.

## 11. Multi-tenant authorization tests

For every tenant-scoped resource, create:

```text
Tenant A
  User A
  Application A

Tenant B
  User B
  Application B
```

Then attempt every CRUD/read/action using the opposite tenant's identifiers.

Test through:

- direct URL;
- route model binding;
- form payload;
- JSON payload;
- query filters;
- bulk action IDs;
- export endpoints;
- background jobs.

Expected result: no cross-tenant read or mutation.

## 12. Admin authorization tests

Test every privileged action with:

- unauthenticated actor;
- authenticated non-admin;
- admin from another tenant;
- valid administrator;
- revoked administrator session;
- stale permission cache;
- permission changed during active session.

No UI hiding is considered authorization. The server-side policy must reject the action.

## 13. Secret leakage tests

Assert that responses/logs do not contain:

- client secrets;
- private keys;
- access tokens;
- refresh tokens;
- authorization codes;
- password values;
- OTP/TOTP secrets;
- session cookies;
- full authorization requests containing credentials.

Use test log handlers to inspect emitted log records.

## 14. SSRF and metadata import tests

If the product later supports importing SAML/OIDC metadata by URL, test:

- localhost targets;
- loopback IPv4/IPv6;
- private RFC1918 ranges;
- link-local addresses;
- cloud metadata IPs;
- redirects from public URL to private target;
- DNS rebinding where practical;
- oversized response;
- decompression bombs;
- unexpected content types;
- invalid XML;
- entity expansion / unsafe XML features;
- certificate/hostname mismatch.

Metadata import SHALL use a dedicated controlled HTTP client with allow/deny policy, redirect restrictions, timeouts, response size limits, and content validation.

## 15. SAML adversarial tests

When SAML is implemented, include at minimum:

- invalid XML;
- unsigned response when signature is required;
- invalid signature;
- wrong issuer;
- wrong audience;
- wrong destination;
- assertion expired/not-yet-valid;
- replayed assertion;
- duplicate assertion IDs;
- response/assertion signature confusion;
- XML signature wrapping attempts;
- unexpected NameID/claim mapping;
- certificate rollover;
- stale metadata;
- malicious metadata import.

Use the mature SAML engine's security primitives; do not write an alternative XML-signature verifier for convenience.

## 16. CSRF and browser attacks

Test state-changing admin/consent operations for:

- missing CSRF token;
- cross-origin form submission;
- GET-based state change;
- cross-site iframe where applicable;
- incorrect SameSite cookie behavior;
- logout CSRF semantics;
- clickjacking protections on security-sensitive pages.

## 17. Rate-limit and abuse tests

Test both low-volume and burst behavior:

```text
1 request
10 requests
100 requests
concurrent burst
```

Targets:

- authorization endpoint;
- token endpoint;
- client authentication;
- admin login;
- metadata import;
- SAML callback;
- password/OTP flows provided by authentication package.

The test must confirm both enforcement and correct recovery after the limiter window.

## 18. Queue and asynchronous security tests

For jobs involving security changes:

- duplicate job delivery;
- delayed job;
- stale job after resource deletion;
- job executed after authorization revocation;
- concurrent key rotation job;
- audit event duplication.

Use idempotency keys or domain-level idempotency guarantees where required.

## 19. Property-style tests

Where practical, generate classes of malicious input rather than a few hand-picked examples.

Useful properties:

```text
For any redirect URI not exactly registered:
  authorization must never redirect to it.

For any consumed authorization code:
  every later redemption must fail.

For any token whose signature/payload/issuer/audience is changed:
  validation must fail.

For any tenant A request referencing tenant B resource:
  authorization must fail.
```

The objective is to test invariants over input space, not to maximize test count.

## 20. Mutation-style expectation

For security-critical validators, tests should be strong enough that intentionally removing a security branch causes test failure.

Examples of mutations that tests should catch:

```text
remove redirect URI check
remove nonce check
skip PKCE
accept expired code
ignore audience
skip tenant constraint
allow replay
log client secret
```

If a mutation passes the suite, the suite is considered insufficient for that invariant.

## 21. Concurrency tests

Security-sensitive state transitions require concurrency tests:

- authorization code redemption;
- session revocation;
- client credential rotation;
- key activation/retirement;
- consent updates;
- device/session changes.

Test with parallel requests and database transactions/locking appropriate to the invariant.

## 22. Database invariant tests

Verify unique constraints and foreign keys enforce:

- unique client identifier;
- unique active key identifier where required;
- one-time transaction consumption;
- tenant ownership;
- valid application-client relationship;
- non-orphaned credential records.

Do not rely exclusively on application checks when a database constraint can enforce the invariant safely.

## 23. External contract tests

Build contract tests for:

- discovery document shape;
- JWKS shape;
- token response shape;
- UserInfo claims;
- OIDC authorization error responses;
- SAML metadata;
- SAML assertion mapping.

These tests protect integrations when internal implementation is refactored.

## 24. Security regression policy

Every discovered security bug SHALL result in:

```text
reproduction test
→ fix
→ regression test
→ threat/model update
→ relevant docs update
→ ADR if architecture changed
```

A fix without a regression test is not considered complete for security-critical defects unless a documented exception is approved.

## 25. Test data policy

Use synthetic identities, clients, keys, and tokens. Never paste production credentials into fixtures.

Secrets used by tests must be disposable.

## 26. CI gates

Minimum merge gate:

```text
static analysis
unit tests
feature tests
integration tests
protocol tests
security tests
migration test
route test
```

High-risk protocol/security changes SHOULD additionally run a full adversarial suite and concurrency suite.

## 27. AI agent testing contract

An AI agent implementing a security-sensitive feature MUST add tests that attempt to break the feature.

Prompt pattern:

```text
Implement the feature and then try to violate its security invariants.
Do not only test the intended happy path.
List the invariants.
For each invariant create at least one negative test and one boundary/replay/race test when applicable.
```

The agent SHALL not declare a task complete because a nominal happy-path test passes.


## 28. Official Laravel testing references

- Laravel Testing: https://laravel.com/docs/13.x/testing
- Laravel HTTP client/testing: https://laravel.com/docs/13.x/http-client
- Laravel Dusk: https://laravel.com/docs/dusk
- Laravel testing with parallel execution/background tooling should be validated against the version actually installed.

The project treats these framework capabilities as tooling, not as a substitute for protocol/security test design.

## Authorization Attack Matrix

Authorization tests must actively attempt to break the control plane:

| Attack | Expected result |
|---|---|
| Unauthenticated dashboard request | Denied |
| Authenticated non-admin dashboard request | Denied |
| Revoked admin role | Denied |
| Disabled role assignment | Denied |
| User self-assigns platform_admin | Denied |
| User grants own missing permission | Denied |
| Organization A admin accesses Organization B resource | Denied |
| Resource ID substitution | Denied |
| Alternate route bypass | Denied |
| Hidden UI action called directly | Denied |
| Mass assignment of role/permission fields | Denied |
| Last admin removed concurrently | Invariant preserved |
| Authorization cache stale after revocation | Access denied within defined consistency boundary |

Tests should inspect both response and database authorization state.


## Expanded attack surface coverage

Security tests SHALL also cover:

```text
SQL injection
command injection
unsafe deserialization
RCE paths
malicious file upload
path traversal / symlink escape
XXE / XML parser abuse
SSRF
XSS
CSRF
host-header/proxy confusion
request parser differentials
DoS/resource exhaustion
cache poisoning / stale authorization
queue duplication/poisoning
secret leakage
supply-chain integrity assumptions
backup theft/deletion/restore abuse
```

Do not write exploit payloads that target arbitrary third-party systems. These tests are for controlled project environments only.

## Backup recovery tests

Security/reliability CI SHOULD include:

- create encrypted backup;
- verify backup metadata;
- verify archive integrity;
- restore to isolated environment;
- run migrations/health checks according to the recovery runbook;
- verify permissions and revoked credentials after restore;
- verify OIDC discovery/JWKS and signing key behavior;
- verify audit continuity.
