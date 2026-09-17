# Security Threat Catalog — Mixu SSO

- Document ID: `SSO-THREAT-001`
- Version: `0.1`
- Status: Baseline
- Date: `2026-09-17`

## 1. Purpose

Mixu SSO adalah high-value identity boundary. Threat model harus mencakup bukan hanya login attack sederhana, tetapi juga abuse terhadap protocol, browser, admin control plane, storage, dependencies, deployment, parser, database, network, dan recovery system.

Gunakan **OWASP Top 10:2025** sebagai baseline awareness. Kategorinya mencakup Broken Access Control, Security Misconfiguration, Software Supply Chain Failures, Cryptographic Failures, Injection, Insecure Design, Authentication Failures, Software/Data Integrity Failures, Security Logging & Alerting Failures, dan Mishandling of Exceptional Conditions. citeturn230300search8turn230300search9

Threat testing harus dilaksanakan hanya terhadap environment yang dimiliki/diotorisasi project.

## 2. Threat modeling method

Gunakan kombinasi:

```text
STRIDE
+
OWASP Top 10:2025
+
OIDC/OAuth/SAML specific abuse cases
+
Infrastructure threat model
+
Supply-chain threat model
+
Insider/admin threat model
```

Setiap threat harus mempunyai:

```text
asset
actor
precondition
attack path
security invariant
preventive control
detective control
response
adversarial test
```

## 3. Critical assets

```text
user identity
admin identity
SSO browser session
authorization codes
access tokens
refresh tokens
ID Tokens
client secrets
signing private keys
JWKS
application registry
redirect URIs
scope/claim policy
role/permission assignments
audit logs
backup archives
backup encryption secret
metadata trust configuration
application/database credentials
server environment
```

## 4. Authentication threats

Defend against:

```text
credential stuffing
password spraying
brute force
credential reuse
login enumeration
timing side channel
session fixation
session theft
session replay
MFA bypass
OTP replay
recovery code abuse
passkey/WebAuthn flow misuse
social-login account linking abuse
account takeover after email change
```

Controls:

- hardened authentication package;
- rate limiting;
- generic failure responses where enumeration-sensitive;
- session regeneration;
- strong cookie settings;
- step-up authentication for privileged actions;
- revocation;
- audit events;
- anomaly detection where available.

## 5. Authorization threats

Defend against:

```text
IDOR/BOLA
vertical privilege escalation
horizontal privilege escalation
role self-assignment
permission self-assignment
mass assignment
policy bypass
route-only protection bypass
UI-only authorization
cross-organization access
last-admin removal race
confused deputy
```

Every protected action must be checked server-side.

## 6. OAuth/OIDC threats

Defend against:

```text
open redirect
redirect URI substitution
authorization code interception
authorization code replay
PKCE downgrade
state fixation/mismatch
nonce replay/mismatch
issuer confusion
audience confusion
token substitution
client impersonation
scope escalation
consent confusion
JWK poisoning
kid/key confusion
algorithm confusion
alg=none
expired token acceptance
revoked token acceptance
cross-client code redemption
```

Critical invariants are defined in `06-security-model.md` and `04-protocol-contracts.md`.

## 7. SAML threats

Defend against:

```text
XML external entity (XXE)
XML entity expansion / parser DoS
signature wrapping
unsigned assertion acceptance
issuer confusion
audience bypass
destination/recipient mismatch
assertion replay
clock-skew abuse
certificate substitution
metadata poisoning
weak signature algorithms
unsafe parser settings
SAML request/response confusion
```

Never implement a SAML parser casually in application code. Keep the implementation behind a protocol adapter and use a mature SAML library with secure parser/signature behavior.

## 8. Injection threats

Consider at least:

```text
SQL injection
command injection
OS command injection
LDAP injection (future integrations)
header injection
log injection
template injection
HTML injection
JavaScript injection
XPath/XML injection where applicable
```

Rules:

- parameterized queries;
- Laravel query builder/Eloquent safely;
- strict validation;
- escaping at output boundary;
- never pass user input directly to shell/process execution;
- never compile/execute user-controlled templates;
- sanitize/log structure rather than concatenated attacker strings where feasible.

## 9. RCE threat class

RCE is a consequence, not a single bug.

Primary paths to evaluate:

```text
command injection
unsafe deserialization
malicious file upload
template injection
arbitrary code execution through debug tooling
dependency compromise
stolen deployment credential
writable executable directories
unsafe process invocation
server-side plugin/script loading
```

The system must:

- avoid runtime shell execution where unnecessary;
- use allowlisted commands/arguments if process execution is unavoidable;
- disable/lock developer-only tooling in production;
- keep upload directories non-executable;
- validate file type by content and policy, not extension only;
- avoid unsafe deserialization of attacker-controlled data;
- pin/verify dependencies and review supply-chain changes;
- separate writable storage from executable application code where hosting permits.

Adversarial tests must prove malicious inputs are rejected without executing attacker-controlled code.

## 10. File upload threats

Future admin features may accept:

```text
SAML metadata XML
logos
certificates
CSV exports/imports
backup archives
```

Threats:

```text
path traversal
polyglot files
malicious XML
XXE
oversized payload
ZIP bomb / decompression bomb
stored XSS through file name/content
web-shell upload
content-type spoofing
symlink attacks
```

Controls:

- maximum size;
- strict MIME/content policy;
- safe temporary directory;
- random server-side file names;
- no executable permission;
- archive extraction path confinement;
- scan/validate structured files before processing;
- never trust filename or Content-Type.

## 11. SSRF

Any feature that fetches:

```text
OIDC discovery
SAML metadata
remote JWKs/federation metadata
webhook targets
```

creates SSRF risk.

Defenses:

```text
scheme allowlist
DNS validation
private/loopback/link-local block by default
redirect revalidation
response size limit
timeout
connection limit
safe resolver behavior
audit trail
```

Do not trust only the first hostname string check; DNS can change between validation and connection.

## 12. Path traversal / local file access

Defend against:

```text
../
encoded traversal
absolute paths
Windows drive paths
UNC paths
symlink escape
zip-slip style extraction
```

Use Laravel filesystem abstractions and server-side generated paths; never concatenate attacker-controlled paths directly into filesystem operations.

## 13. XSS

Threat types:

```text
stored XSS
reflected XSS
DOM XSS
HTML attribute injection
URL/script injection
log-to-dashboard XSS
```

Special attention:

- audit log user-agent strings;
- application names/descriptions;
- redirect URI display;
- claim values;
- SAML entity names;
- error messages;
- imported metadata fields.

Blade escaping remains default. Raw HTML rendering requires explicit security review.

## 14. CSRF / browser abuse

Protect state-changing browser routes.

High-risk examples:

```text
rotate secret
revoke sessions
change redirect URI
assign role
change SAML certificate
rotate signing key
change backup policy
restore
```

Use correct CSRF/session protections and consider recent-authentication/step-up.

## 15. CORS / origin / browser trust

Do not use permissive wildcard CORS for privileged APIs without a documented use case.

Do not use Origin/Referer as the only authorization control.

Ensure credentialed cross-origin behavior is explicitly configured.

## 16. Host header / proxy trust

Threats:

```text
host header poisoning
forwarded header spoofing
cache poisoning
wrong issuer generation
wrong redirect generation
```

Trusted proxy configuration must be explicit.

Never derive canonical OIDC issuer from arbitrary request Host unless the host is validated against trusted configuration.

## 17. HTTP request smuggling / parser differential

Infrastructure/front proxy must be configured consistently with the application server.

Security review should consider:

```text
Content-Length / Transfer-Encoding discrepancies
HTTP/1.1 proxy behavior
upgrade/proxy normalization
```

Application itself must not implement custom request parsing unless required.

## 18. DoS / resource exhaustion

Protect:

```text
request body size
metadata size
XML complexity
JSON nesting
DB query cost
pagination limits
login attempts
authorization request volume
audit volume
backup disk consumption
queue flooding
```

Avoid unbounded:

```text
loops
regex over attacker-controlled huge input
archive extraction
remote fetches
database pagination offsets
```

Use quotas/rate limits and bounded work.

## 19. Database threats

Defend against:

```text
SQL injection
IDOR through IDs
mass assignment
unsafe dynamic column/order input
migration mistakes
race conditions
privilege confusion
connection exhaustion
```

Use explicit allowlists for dynamic sort/filter fields.

Critical uniqueness/security constraints belong at database level when possible.

## 20. Cache threats

Consider:

```text
cache poisoning
cross-user cache leakage
cross-tenant cache leakage
stale permission cache
stale JWKS cache
stale session state
```

Cache keys must include relevant security dimensions such as tenant/client/user/version where required.

Permission changes and security revocation must define cache invalidation semantics.

## 21. Queue / job threats

Consider:

```text
job payload tampering
secret leakage in serialized jobs
duplicate execution
retry storms
poisoned job
privilege context confusion
```

Jobs should carry stable identifiers, not plaintext secrets.

High-risk jobs must be idempotent or guarded against duplicate execution.

## 22. Logging and audit threats

Defend against:

```text
secret leakage
log injection
PII overcollection
log deletion/tampering
missing security event
incorrect actor attribution
clock/time inconsistency
```

Use structured logging and correlation IDs.

Never log raw tokens/passwords/private keys.

## 23. Cryptography threats

Defend against:

```text
weak randomness
hardcoded keys
key reuse
plaintext secrets
weak algorithms
algorithm confusion
improper key rotation
insecure key backup
nonce reuse where applicable
```

Use framework/library cryptographic primitives rather than homegrown crypto.

Key lifecycle must be documented and tested.

## 24. Secret management

Sensitive values include:

```text
client secret
DB password
mail credentials
backup encryption secret
private signing keys
external provider credentials
API keys
```

Secrets must:

- stay outside source control;
- not appear in logs;
- not be exposed to Blade;
- be rotated;
- have minimum necessary scope;
- be separately backed up/escrowed when required for disaster recovery.

## 25. Supply-chain threats

Consider:

```text
malicious dependency release
compromised maintainer
dependency confusion
typosquatting
transitive vulnerability
post-install script abuse
compromised CI action
stolen deploy token
```

Controls:

- use official packages where available;
- pin/lock dependencies using `composer.lock`/`package-lock.json`;
- review dependency diffs;
- run vulnerability scanning;
- keep CI permissions minimal;
- verify package source/namespace before installation;
- avoid abandoned/random packages for security-critical protocol work.

## 26. Debug / observability tool exposure

Development tooling such as Telescope, debug pages, Tinker endpoints, profiling, verbose exception pages, and browser logs can expose secrets.

Production must:

```text
APP_DEBUG=false
restricted admin observability
no public debug routes
no unrestricted Tinker/webshell
```

## 27. Deployment threats

Consider:

```text
webroot misconfiguration
exposed .env
writable application code
unsafe permissions
stale release
partial deployment
missing HTTPS
weak PHP configuration
exposed backups
```

Laravel must be served from `public/`, not project root.

## 28. Tenant isolation threats

Every organization-scoped query/action must be tested against cross-tenant identifiers.

Test access through:

```text
URL
route binding
POST/JSON body
query parameters
headers
exports
bulk actions
background jobs
cached results
```

## 29. Admin / insider threats

Assume an authenticated admin may be malicious or compromised.

Controls:

- least privilege roles;
- high-risk operation permissions;
- step-up authentication;
- audit logging;
- dual control for future key/restore operations when justified;
- session revocation;
- anomaly detection;
- immutable/off-site backup.

## 30. Backup threats

See `15-backup-and-disaster-recovery.md`.

Backup-specific attacks include:

```text
backup deletion
backup replacement
backup exfiltration
backup password theft
ransomware encryption
malicious restore
restore old vulnerable release
```

## 31. Security regression gate

A merge is not security-complete merely because functional tests pass.

For security-sensitive changes, CI should require:

```text
unit tests
feature tests
integration tests
protocol tests
adversarial tests
race/concurrency tests when relevant
static analysis
dependency audit
architecture tests
secret leak checks
```

## 32. Adversarial test mindset

For every feature ask:

```text
What if the attacker controls this parameter?
What if they call the endpoint twice?
What if two requests happen at the same time?
What if they change the resource ID?
What if the user is authenticated but unauthorized?
What if the token was issued to another client?
What if a dependency is compromised?
What if the backup server is compromised?
What if the proxy rewrites Host?
What if a parser receives a malicious document?
What if a trusted admin account is stolen?
```

The test suite should prove the security invariant survives these conditions.
