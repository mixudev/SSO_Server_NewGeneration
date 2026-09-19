# Security Acceptance and Threat Model

## 1. Trust boundaries

```text
Browser/client
  -> provider authorization endpoint
  -> consent/session boundary
  -> Passport token boundary
  -> OIDC claim/key boundary
  -> local client session/user database
  -> admin control plane
```

Attacker controls browser parameters, callback parameters, token presentation, timing, request order, selected headers, and network failures. Attacker must not receive private keys or server secrets.

## 2. Provider invariants

- Redirect URI exact matching; no wildcard/open redirect.
- `state` and redirect bindings are hashed and constant-time compared.
- OIDC `openid` scope and nonce are required.
- PKCE method is `S256`.
- Authorization transaction is one-time, expiring, actor-bound, and client-bound.
- Consent denial produces terminal transaction state and protocol error redirect.
- Unknown consent decisions are rejected.
- Revocation verifies JWT before reading `jti`.
- Access and related refresh records are revoked together.
- Credential lifecycle gates application, organization, credential, and Passport client state.
- Old secrets are never reconstructed or displayed again.
- Signing key rotation retains verification overlap without exposing private material.
- UserInfo emits claims only from scope and claim policy.
- Health responses contain no secret, token, private key, or connection string.
- Passport token route is explicitly owned and throttled by application policy.

## 3. Client package invariants

- Issuer comes only from trusted configuration.
- Discovery issuer must exactly match configuration.
- Metadata endpoint cannot be replaced by request input.
- State, nonce, and PKCE verifier are one-time and expiry-bound.
- Callback code is never logged or persisted plaintext.
- Token response is verified before local login.
- `alg=none`, algorithm confusion, unknown issuer, wrong audience, wrong nonce, expired token, and invalid signature are rejected.
- JWKS unknown `kid` permits one bounded refresh, then fails closed.
- Login and logout intended URLs are allowlisted.
- Local session ID changes after successful authentication.
- Local account linking is not email-only by default.
- Token refresh is single-flight and replay-safe.
- Provider outage does not bypass verification or create an unauthenticated local session.

## 4. Threat matrix

| Threat | Control | Required proof |
|---|---|---|
| Open redirect | Exact registered URI and allowlisted local return URL | Malicious host, path, fragment, encoded separator tests |
| Login CSRF | State binding | Missing, mismatch, expiry, replay tests |
| Code interception | PKCE S256 and exact redirect | Wrong verifier and duplicate exchange tests |
| Token substitution | Signature/issuer/audience/nonce verification | Forged JWT and wrong key tests |
| Algorithm confusion | RS256 allowlist | `none`/HS256 rejection tests |
| JWKS rotation breakage | Cache + key overlap | Unknown `kid`, old/new key test |
| Refresh replay | Rotation and single-flight | Concurrent/replay test |
| Credential compromise | Revoke/rotate/reactivate lifecycle | Old client/secret rejection test |
| Tenant breakout | Organization/application ownership checks | Cross-organization feature tests |
| Session fixation | Regenerate local session | Login session ID test |
| XSS token theft | No browser secret for confidential client; CSP/XSS review | Render/log/token storage tests |
| SSRF | Config-only issuer, endpoint restrictions | Private/loopback metadata test |
| Secret leakage | Redaction and one-time display | Log/HTML/JSON scan tests |
| DoS | Route limits, bounded timeout/retry | Rate-limit and timeout tests |
| Backup compromise | Archive encryption, separate key custody | Linux restore and key recovery drill |

## 5. Production approval gates

Provider/client integration is not production-approved until:

- all focused and full tests pass;
- external HTTPS smoke test passes;
- provider and client key rotation passes;
- refresh replay and concurrent refresh behavior passes;
- backup restore drill passes on Linux;
- signing-key recovery drill passes;
- metrics and alerting are connected;
- secrets are injected through secret manager/environment, not repository;
- TLS, cookie, CSP, CORS, and proxy policy are reviewed;
- incident response and credential rotation runbook is approved.
