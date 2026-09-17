# Security Model — Mixu SSO / Identity Platform

## 1. Security objective

SSO server adalah security boundary. Kesalahan kecil pada redirect, token, key, session, atau identity mapping dapat mempengaruhi semua aplikasi yang terhubung. Karena itu seluruh desain harus menggunakan prinsip **fail closed** untuk keputusan security-critical.

## 2. Threat model
## 2A. Expanded threat coverage

Security review SHALL cover the full attack surface, not only authentication:

```text
identity/authentication
authorization/IDOR
OIDC/OAuth abuse
SAML/XML abuse
injection
SSRF
XSS/CSRF
path traversal/file upload
command injection/RCE paths
unsafe deserialization
denial of service/resource exhaustion
cache/queue abuse
cryptographic/key compromise
secret leakage
supply-chain compromise
deployment/proxy exposure
backup/restore compromise
tenant breakout
insider/admin compromise
```

Use `16-security-threat-catalog.md` as the detailed threat checklist. The project baseline follows OWASP Top 10:2025 risk categories while extending them with protocol- and identity-specific threats. citeturn230300search8turn230300search9

RCE prevention is treated as a chain of controls: no unnecessary process execution, strict allowlists when execution is unavoidable, safe file handling, hardened parsers, locked production debug tooling, dependency integrity, and separation of writable files from executable application code where the hosting environment allows it.


Threat actors:

- malicious end user;
- compromised client application;
- stolen client credential;
- stolen browser session;
- replay attacker;
- authorization code interceptor;
- malicious/compromised administrator;
- malicious metadata endpoint;
- malicious SAML SP configuration;
- compromised external IdP (future).

## 3. Security invariants

These invariants are architecture-level rules:

1. Tidak ada arbitrary redirect.
2. Authorization code tidak dapat digunakan dua kali.
3. Public client tidak bergantung pada client secret.
4. Nonce mismatch selalu gagal.
5. PKCE mismatch selalu gagal.
6. ID Token `iss` harus issuer platform.
7. ID Token `aud` harus client yang benar.
8. ID Token signature harus valid untuk key `kid` yang sesuai.
9. Expired token/code/session tidak boleh dipakai.
10. Revoked client tidak boleh memperoleh token baru.
11. Client credential dan user credential tidak boleh diperlakukan sebagai identitas user yang sama.
12. Private key tidak pernah dipublikasikan.
13. Audit event security-critical tidak diubah setelah ditulis.
14. Protocol error tidak membocorkan secret/internal stack trace.

## 4. Redirect URI attacks

Gunakan exact match. Jangan menerima prefix/suffix match seperti:

```text
startsWith()
endsWith()
contains()
```

untuk validasi redirect.

OAuth Security BCP menempatkan exact redirect URI matching sebagai kontrol inti untuk melindungi authorization code/token leakage. citeturn600504search2

## 5. PKCE

Untuk public clients:

```text
challenge = BASE64URL(SHA256(verifier))
```

Token endpoint menerima verifier dan membandingkan secara constant-time terhadap challenge yang terkait dengan transaction.

## 6. State / nonce

`state` menjaga request binding dari sisi OAuth client; server harus tidak memantulkan arbitrary state selain state yang berasal dari request yang valid.

`nonce` menghubungkan authentication request dengan ID Token. OIDC mensyaratkan client memverifikasi nilai nonce ketika claim tersebut digunakan. citeturn600504search0

## 7. Authorization code replay

Gunakan:

```text
short TTL
single-use
transaction binding
client binding
redirect binding
PKCE binding
```

Setelah redemption sukses:

```text
consumed_at = now()
```

Second redemption => protocol error + security event.

## 8. Token protection

Never log:

```text
access_token
refresh_token
id_token
authorization_code
client_secret
```

Logging hanya boleh menggunakan:

```text
token_hash
jti
kid
client_id
request_id
```

bila diperlukan untuk correlation.

## 9. JWT key security

Prefer asymmetric signing:

```text
RS256 / approved stronger modern profile according to interoperability needs
```

Jangan menggunakan symmetric client-shared signing key sebagai default untuk ID Token provider platform.

JWKS hanya berisi public material.

## 10. Key rotation

Rotation policy:

```text
Generate → Validate → Activate → Overlap → Retire → Destroy
```

Automated jobs perlu memastikan tidak ada active token yang membutuhkan retired key setelah destroy.

## 11. Client secret

Secret generation:

- cryptographically secure random;
- sufficient length;
- shown once;
- hash at rest;
- rotate without destroying current app configuration until handover completed.

## 12. Session security

SSO session harus memiliki:

- secure cookie;
- HttpOnly;
- SameSite policy sesuai flow;
- session regeneration after authentication;
- absolute expiry;
- idle timeout;
- revocation state;
- optional step-up timestamp.

## 13. MFA / authentication assurance

SSO core menerima `AuthenticationContext` dari auth package, misalnya:

```text
authenticated = true
method = passkey / password / otp / social
assurance = standard / strong
amr = [...]
auth_time = ...
```

Policy application dapat meminta:

```text
require_assurance = strong
```

Tanpa mengetahui implementation internal auth package.

## 14. Step-up authentication

Future flow:

```text
App requests max_age / policy
        ↓
SSO checks current authentication
        ↓
Enough? → continue
Not enough? → reauthenticate / MFA
```

## 15. SAML security

SAML engine must enforce:

- strict mode/configuration;
- trusted IdP/SP certificates;
- signature validation;
- audience restriction;
- destination/recipient validation;
- time conditions;
- replay detection;
- safe XML parsing;
- secure algorithms; avoid SHA-1 for new signatures.

OneLogin's toolkit documentation, sebagai referensi implementasi SP, juga menekankan strict mode dan penggunaan algoritma signature/digest yang bukan SHA-1 di production. citeturn110952search1

## 16. Metadata import SSRF

When fetching external OIDC/SAML metadata:

```text
allow https/http only as policy permits
DNS resolve validation
block localhost
block loopback
block link-local
block RFC1918/private targets by default
limit redirects
limit response size
short timeout
content-type validation
XML/JSON parser hardening
audit import action
```

Manual upload should be supported so enterprise users can avoid network fetch when policy disallows it.

## 17. Dashboard security

Admin dashboard must have:

- authentication;
- authorization;
- CSRF protection for browser mutations;
- re-authentication for high-risk operations;
- secret one-time reveal;
- audit trail;
- pagination;
- anti-enumeration responses where relevant.

High-risk operations:

```text
rotate client secret
rotate signing key
change redirect URI
change claim policy
change SAML certificate
revoke all sessions
remove administrator
```

## 18. Rate limit architecture

Use separate buckets:

```text
login
authorization endpoint
/token
userinfo
admin mutations
metadata import
```

Do not rely on a single global limiter.

## 19. Security event examples

```text
AUTHORIZATION_REDIRECT_MISMATCH
PKCE_VERIFICATION_FAILED
NONCE_MISMATCH
AUTHORIZATION_CODE_REPLAY
CLIENT_AUTH_FAILED
CLIENT_REVOKED
TOKEN_REUSE_DETECTED
INVALID_JWT_AUDIENCE
UNKNOWN_KID
SAML_SIGNATURE_FAILED
SAML_REPLAY_DETECTED
ADMIN_SECRET_ROTATED
SIGNING_KEY_ROTATED
```

## 20. Incident response hooks

Every security event should carry:

```text
request_id
organization_id
application_id
subject
actor
occurred_at
risk metadata
```

Future security-defense integration dapat consume normalized `security_events` tanpa menyentuh protocol code.

## 21. Security testing baseline

Automated tests must cover:

- open redirect attempts;
- code replay;
- PKCE downgrade/invalid verifier;
- nonce substitution;
- issuer confusion;
- audience confusion;
- expired credentials;
- revoked credentials;
- key rotation overlap;
- unknown `kid`;
- malformed JWT;
- algorithm confusion attempt;
- CSRF on dashboard;
- SSRF metadata import;
- XXE/XML bomb;
- SAML replay/signature/audience/time failures;
- permission escalation in administration.
