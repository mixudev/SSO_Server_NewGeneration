# Data Model — Mixu SSO / Identity Platform

## 1. Principles

- UUID/ULID-like identifiers untuk public-facing resources.
- Tidak menggunakan incremental integer sebagai subject/client identifier yang dipublikasikan.
- Timestamp tersimpan UTC.
- Semua mutable security resource memiliki lifecycle/status.
- Credential plaintext hanya ada saat creation/reveal/verification sesuai kebutuhan.
- Audit tidak diedit untuk memperbaiki histori; gunakan corrective event.

## 2. Core entities

```text
Organization
 ├── Application
 │    ├── ClientCredential
 │    ├── RedirectUri
 │    ├── ScopeGrant
 │    ├── ClaimPolicy
 │    └── SamlServiceProvider
 │
 ├── Membership
 │
 └── AuditEvent

User
 ├── SsoSession
 ├── Consent
 └── AuthorizationGrant

SigningKey
AuthorizationTransaction
OAuthToken*          # Passport-managed where applicable
SamlAssertionEvent
```

## 3. `organizations`

Purpose: tenant boundary.

Suggested fields:

```text
id
name
slug
status
settings_json
created_at
updated_at
```

Unique:

```text
slug
```

Implementation status: foundation slice completed. The application uses a ULID primary key, required `name`, unique `slug`, indexed lifecycle `status`, nullable JSON settings, and factory coverage. Application ownership and organization foreign keys are intentionally deferred to the Application Registry slice.

Security notes:

- The ULID is the public-facing identifier; no incremental integer is exposed by the model.
- The database enforces slug uniqueness.
- `settings_json` is cast to an array and is not interpreted as executable configuration.
- Organization lifecycle remains explicit through `status`; deletion semantics are deferred until dependent resources exist.

Verified with `tests/Feature/FoundationBaselineTest.php`.
## 4. `applications`

Implementation status: Application Registry foundation slice completed. The application uses a ULID primary key, an organization foreign key with restrict-on-delete behavior, tenant-scoped slug uniqueness, explicit protocol/client/status fields, policy metadata, and creator/updater references. Redirect URIs, credentials, scopes, claims, and admin CRUD remain separate slices.

Security notes:

- Application identity is ULID-based and tenant ownership is enforced by a foreign key.
- Slug uniqueness is scoped to the organization at the database boundary.
- `session_policy_json` is stored as data and is not interpreted as executable configuration.
- Protocol flows must resolve an active application and verify its organization boundary before use.

```text
id
organization_id
name
slug
protocol_mode
client_type
status
description
homepage_url
privacy_url
terms_url
consent_policy
session_policy_json
claim_policy_version
created_by
updated_by
created_at
updated_at
```

Indexes:

```text
organization_id + slug
organization_id + status
protocol_mode + status
```

## 5. `application_redirect_uris`

Implementation status: Registry schema and pure exact-match validator completed. Stored URI values are canonicalized before hashing; wildcard, fragment, userinfo, unsafe path encoding, and non-loopback HTTP are rejected. Persistence and administration use `ApplicationRedirectUri`; protocol endpoints must call the validator before redirecting.

```text
id
application_id
uri
uri_hash
kind              # login/logout
created_at
```

Unique per application/kind/URI.

`uri_hash` is optional optimization; always retain canonical URI for administration and exact comparison.

## 6. `client_credentials`

Conceptually separate from application.

```text
id
application_id
client_id
client_secret_hash
status
last_used_at
expires_at
rotated_at
created_at
updated_at
```

`client_id` unique globally or scoped according to protocol contract.

Secrets must not be reversible if only one-way verification is required.

## 7. `scopes`

```text
id
name
description
category
risk_level
is_system
is_default
status
created_at
updated_at
```

Unique:

```text
name
```

## 8. `application_scopes`

```text
application_id
scope_id
allowed
consent_required
```

Composite unique:

```text
application_id + scope_id
```

## 9. `claims`

Optional canonical registry:

```text
id
key
description
source
sensitivity
status
```

Examples:

```text
email
name
preferred_username
phone_number
roles
organization_id
```

## 10. `application_claim_policies`

```text
id
application_id
version
rules_json
status
created_by
created_at
```

Versioning is important because a consent decision must be tied to the claim policy version active saat itu.

## 11. `authorization_transactions`

```text
id
transaction_id
client_id
application_id
user_id
redirect_uri_hash
response_type
scope_string
state_hash
nonce_hash
code_challenge
code_challenge_method
status
expires_at
authenticated_at
consented_at
completed_at
created_at
```

Never store raw `state`/`nonce` unless justified. Prefer hashed transaction evidence where raw values are only needed client-side/session-side.

## 12. `authorization_codes`

If not fully managed by Passport:

```text
id
code_hash
transaction_id
client_id
user_id
redirect_uri_hash
scope_string
code_challenge
expires_at
consumed_at
created_at
```

Code should be one-time.

Where Passport owns the OAuth2 code/token lifecycle, the application should not duplicate those tables unnecessarily.

## 13. `consents`

```text
id
organization_id
user_id
application_id
scope_set_hash
claim_policy_version
status
given_at
revoked_at
last_used_at
```

Unique active consent tuple based on application/user/organization/policy version.

## 14. `sso_sessions`

```text
id
session_id_hash
organization_id
user_id
authentication_method
authentication_assurance
ip_hash
user_agent_hash
device_id_hash
last_seen_at
expires_at
revoked_at
created_at
```

Raw bearer/session cookie value must not be stored.

## 15. `signing_keys`

```text
id
kid
algorithm
key_type
public_jwk_json
encrypted_private_material
status
activated_at
retired_at
destroyed_at
created_at
updated_at
```

Unique:

```text
kid
```

Private material must be encrypted at rest. Key management should be abstracted behind `SigningKeyProvider`.

## 16. `saml_service_providers`

```text
id
organization_id
application_id
entity_id
metadata_source_type
metadata_source
acs_bindings_json
slo_bindings_json
nameid_policy
attribute_mapping_json
signing_certificate_pem
encryption_certificate_pem
status
metadata_fetched_at
metadata_fingerprint
created_at
updated_at
```

Certificate fields should be encrypted or access controlled according to use case; public certificates are not secret, but administrative changes still require audit.

## 17. `audit_events`

```text
id
event_id
organization_id
actor_type
actor_id
action
category
subject_type
subject_id
request_id
ip_hash
user_agent_hash
metadata_json
occurred_at
```

Never store raw tokens, passwords, client secrets, private keys, or complete assertion blobs by default.

## 18. `security_events`

Optional normalized security event stream:

```text
id
organization_id
event_type
severity
source
subject_type
subject_id
application_id
fingerprint
metadata_json
occurred_at
```

Can feed future anomaly/security-defense integrations without coupling the protocol layer.

## 19. Lifecycle rules

Deletion should prefer soft/revocation semantics for security resources.

Examples:

- client secret → revoke/rotate;
- signing key → retire/destroy after verification retention;
- session → revoke;
- consent → revoke;
- application → suspend/revoke.

Hard-delete may be reserved for privacy retention workflows where audit/legal policy permits it.

## 20. Indexing strategy

High-traffic indexes should target:

```text
applications(status, protocol_mode)
client_credentials(client_id, status)
authorization_transactions(transaction_id, expires_at)
authorization_transactions(client_id, status, expires_at)
sso_sessions(user_id, revoked_at, expires_at)
sso_sessions(session_id_hash)
signing_keys(status, algorithm)
audit_events(organization_id, occurred_at)
```

## 21. Retention

Retention must be configurable by resource class.

Short-lived:

```text
authorization transaction
authorization code
nonce/state replay evidence
```

Medium-lived:

```text
session metadata
consent usage
security event
```

Long-lived:

```text
administrative audit
application configuration history
key lifecycle metadata
```


## 21. Authorization entities

The Identity Platform has a small project-owned authorization model for its administrative control plane.

```text
User
 └── RoleAssignment → Role ── RolePermission → Permission
                         
RoleAssignment.scope_type: platform | organization
```

### `roles`

```text
id
key
name
description
is_system
status
created_at
updated_at
```

### `permissions`

```text
id
key
name
group
description
is_system
status
created_at
updated_at
```

### `role_permissions`

```text
role_id
permission_id
created_at
```

### `role_assignments`

```text
id
user_id
role_id
scope_type
organization_id
status
assigned_by
assigned_at
revoked_at
created_at
updated_at
```

The first seed provides only `platform_admin`. The schema supports future operator roles and organization-scoped access without using a hardcoded administrator flag.
