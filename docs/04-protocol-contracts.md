# Protocol Contracts — OAuth 2.0, OIDC, SAML

## 1. Purpose

Dokumen ini menetapkan contract protokol yang harus dihormati implementation. Detail vendor tidak boleh mengubah public behavior.

## 2. OIDC Provider metadata

Endpoint:

```text
GET /.well-known/openid-configuration
```

Minimum contract:

```json
{
  "issuer": "https://sso.example.com",
  "authorization_endpoint": "https://sso.example.com/oauth/authorize",
  "token_endpoint": "https://sso.example.com/oauth/token",
  "userinfo_endpoint": "https://sso.example.com/oauth/userinfo",
  "end_session_endpoint": "https://sso.example.com/oauth/end-session",
  "jwks_uri": "https://sso.example.com/.well-known/jwks.json",
  "response_types_supported": ["code"],
  "grant_types_supported": ["authorization_code", "refresh_token"],
  "subject_types_supported": ["public"],
  "id_token_signing_alg_values_supported": ["RS256"],
  "scopes_supported": ["openid", "profile", "email"],
  "code_challenge_methods_supported": ["S256"]
}
```

Values wajib berasal dari capabilities aktif, bukan hardcoded jika feature belum enabled.

OpenID Connect Discovery mensyaratkan issuer, authorization endpoint, dan JWKS URI; issuer harus sama dengan issuer pada token. citeturn600504search1

## 3. Issuer contract

Rules:

- canonical HTTPS URL in production;
- no query;
- no fragment;
- stable across deployments;
- exact match with `iss` claim.

## 4. Authorization request

Required baseline:

```text
client_id
redirect_uri
response_type=code
scope
state
```

OIDC requires `openid` scope for OIDC behavior and the redirect URI must match a pre-registered URI. citeturn600504search0turn600504search1

For OIDC:

```text
nonce
```

must be tracked when provided and returned in ID Token.

For public/native/SPA clients:

```text
code_challenge
code_challenge_method=S256
```

PKCE is mandatory by platform policy for public clients.

## 5. Token endpoint

Baseline grant types:

```text
authorization_code
refresh_token
client_credentials
```

Password grant SHALL NOT be enabled by default. Laravel Passport documentation explicitly no longer recommends password grant in favor of currently recommended grant types. citeturn300680search0

## 6. Client type matrix

| Client type | Secret | PKCE | User login | Client credentials |
|---|---:|---:|---:|---:|
| confidential_web | yes | yes/recommended | yes | optional |
| public_spa | no | mandatory | yes | no |
| public_native | no | mandatory | yes | no |
| machine_to_machine | yes | no | no | yes |
| saml_sp | N/A | N/A | yes via SAML | no |

## 7. ID Token

Minimum signed claims:

```json
{
  "iss": "https://sso.example.com",
  "sub": "stable-subject-id",
  "aud": "client-id",
  "iat": 1770000000,
  "exp": 1770000600,
  "auth_time": 1770000000,
  "nonce": "request-nonce"
}
```

`sub` harus stable dan tidak boleh berubah hanya karena email berubah.

ID Token adalah JWT yang menyampaikan claims autentikasi; OIDC mendefinisikan `iss`, `sub`, `aud`, time claims, dan nonce sesuai kondisi flow. citeturn600504search0

## 8. Subject identifier

Gunakan opaque stable identifier.

Jangan:

```text
sub = email
```

Rekomendasi:

```text
sub = stable opaque UUID/ULID-like identifier
```

Email hanya claim identitas, bukan canonical subject identifier.

## 9. UserInfo contract

`GET /oauth/userinfo` atau configured UserInfo endpoint memerlukan Bearer access token.

Claims dikeluarkan berdasarkan:

```text
user
+ scopes
+ application claim policy
+ organization policy
```

Jangan mengembalikan semua kolom user secara otomatis.

## 10. Scope model

Reserved OIDC scopes:

```text
openid
profile
email
address
phone
```

Custom scopes harus namespaced agar mudah berkembang:

```text
account:read
account:write
school:article:read
school:article:write
admin:users:read
```

## 11. Consent model

Consent key:

```text
subject + client + organization + scope-set + claim-policy-version
```

Perubahan scope/policy harus dapat meng-invalidate consent lama.

## 12. Redirect URI contract

Storage normalized, validation exact.

Dilarang:

```text
https://*.example.com/callback
https://example.com/*
```

kecuali wildcard policy memang dirancang sebagai feature formal dengan semantics, security review, dan strict subdomain ownership. Default system harus exact.

OAuth 2.0 Security BCP mengharuskan exact string matching untuk redirect URI (dengan pengecualian loopback native tertentu) dan mencegah open redirectors. citeturn600504search2

## 13. PKCE contract

Only:

```text
S256
```

untuk default secure profile.

Verifier harus:

- high entropy;
- one-time;
- matched against challenge;
- never logged.

## 14. SAML metadata contract

IdP metadata minimal perlu mempublikasikan:

- Entity ID;
- SSO endpoint(s);
- certificate(s);
- supported binding(s);
- role descriptor.

Contoh konsep:

```xml
<EntityDescriptor entityID="https://sso.example.com/saml">
  <IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    ...
  </IDPSSODescriptor>
</EntityDescriptor>
```

SimpleSAMLphp mendokumentasikan penggunaan IdP hosted metadata, SSO service, certificate dan remote SP metadata sebagai bagian dari konfigurasi IdP. citeturn701336search0

## 15. SAML SP registry

Setiap SAML SP minimal memiliki:

```text
entity_id
acs_urls
slo_urls
requested_attributes
nameid_policy
signing_certificate (optional/required by policy)
encryption_certificate (optional)
attribute_mapping
status
```

## 16. SAML assertion contract

Assertion harus memiliki:

- issuer;
- subject/NameID;
- audience restriction;
- conditions/not-before/not-on-or-after;
- authentication statement;
- attributes sesuai mapping;
- signature sesuai policy.

## 17. Protocol capability flags

Gunakan capability registry, misalnya:

```text
oidc.authorization_code = true
oidc.pkce_s256 = true
oidc.refresh_token = true
saml.http_redirect = true
saml.http_post = true
saml.slo = false
```

Discovery/metadata hanya mempublikasikan capability yang benar-benar aktif.

## 18. Versioning policy

Protocol URL tidak boleh berubah hanya karena internal refactor.

Internal classes boleh berubah selama contract test tetap lulus.

## 19. Conformance tests

Setiap protocol harus mempunyai contract suite independen:

```text
DiscoveryContractTest
AuthorizationRequestContractTest
RedirectUriContractTest
PkceContractTest
TokenContractTest
IdTokenContractTest
JwksContractTest
UserInfoContractTest
SamlMetadataContractTest
SamlAssertionContractTest
```
