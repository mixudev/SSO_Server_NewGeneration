# Implementation Documentation — Mixu SSO

Status: Implementation baseline
Last reviewed: 2026-09-19

Folder ini adalah panduan implementasi provider Mixu SSO dan rancangan package client `ssoclient`.

## Isi

| File | Isi |
|---|---|
| `01-provider-usage.md` | Endpoint aktif, konfigurasi provider, alur OAuth2/OIDC, dan operasi admin. |
| `02-client-integration-guide.md` | Hal yang wajib dibuat aplikasi client dan contoh alur integrasi. |
| `03-ssoclient-prd-srs.md` | PRD/SRS lengkap untuk package Laravel client `ssoclient`. |
| `04-capability-ui-matrix.md` | Perbandingan kemampuan backend/provider, UI dashboard, API, dan status implementasi. |
| `05-security-acceptance.md` | Invariant keamanan, threat model, dan acceptance criteria package client. |
| `06-implementation-roadmap.md` | Task terstruktur untuk package client, provider UI, observability, dan interoperability. |

## Status provider saat ini

Didukung sebagai foundation:

- OAuth 2.0 Authorization Code.
- OIDC Authorization Code.
- PKCE `S256` untuk public client.
- Client credential untuk confidential web melalui lifecycle credential admin.
- OIDC Discovery.
- JWKS dengan overlap key rotation.
- UserInfo dengan scope dan claim policy.
- Signed ID Token `RS256`.
- Refresh-token lifecycle Passport.
- Token revocation.
- RP-Initiated Logout dasar.
- Application, organization, scope, claim, credential, key, audit, user, role, dan session registry.

Belum production-supported:

- Native/mobile client (`native` fail-closed sampai redirect policy selesai).
- Machine-to-machine/client credentials sebagai vertical slice resmi.
- Device Authorization Grant.
- SAML.
- Back-channel/front-channel logout.
- Linux backup restore drill dan signing-key recovery drill.
- Request metrics backend.
- External HTTPS interoperability smoke test.

## Aturan membaca status

`[AVAILABLE]` berarti route, implementation, dan test lokal tersedia.

`[PARTIAL]` berarti sebagian contract ada, tetapi interoperabilitas, UI, atau operational proof belum lengkap.

`[DISABLED]` berarti jangan mengaktifkan melalui konfigurasi client atau menampilkan sebagai kemampuan production.

`[NOT VERIFIED]` berarti belum boleh diklaim production-ready tanpa eksekusi nyata.

## Source of truth

- Provider architecture: `docs/03-architecture.md`
- Protocol contract: `docs/04-protocol-contracts.md`
- Security model: `docs/06-security-model.md`
- Routing: `docs/11-routing-architecture.md`
- Testing: `docs/12-security-testing-strategy.md`
- Workflow: `docs/13-development-workflow.md`
- Current checklist: `docs/tasks/task-list.md`

`docs/TARGET-CLIENT.md` adalah dokumen legacy/reference. Bagian SAML di sana tidak berarti SAML sudah aktif pada provider saat ini.
