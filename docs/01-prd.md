# PRD — Mixu SSO / Identity Platform

- Document ID: `SSO-PRD-001`
- Version: `0.1`
- Status: Approved Architecture Baseline
- Date: `2026-09-17`
- Target: Laravel 13.x

## 1. Ringkasan produk

Mixu SSO / Identity Platform adalah server identitas yang menyediakan satu pusat autentikasi dan otorisasi untuk banyak aplikasi. Aplikasi pelanggan bertindak sebagai **Relying Party / OAuth Client / Service Provider**, sementara platform bertindak sebagai **OpenID Provider (OIDC)** dan **SAML Identity Provider (IdP)**.

Produk dibangun dari aplikasi Laravel sendiri, bukan memasang produk IdP monolitik siap pakai. Authentication user yang sudah tersedia pada `mixudev/laravel-authentication` diperlakukan sebagai komponen autentikasi yang dapat diganti melalui adapter contract.

Target pengalaman integrasi:

```text
Client memasukkan Issuer / Metadata URL
        ↓
Auto discovery / import
        ↓
Register application
        ↓
Generate credentials
        ↓
Copy config / SDK integration
        ↓
Login via SSO
```

Tujuan utamanya adalah membuat SSO yang terasa sederhana seperti SaaS enterprise, tetapi memiliki pondasi internal yang modular dan dapat berkembang tanpa refactor besar.

## 2. Masalah yang ingin diselesaikan

Tanpa identity platform, setiap aplikasi biasanya mempunyai login, session, MFA, reset password, role mapping, dan audit sendiri. Ini menghasilkan duplikasi, konfigurasi yang tidak konsisten, serta biaya pemeliharaan tinggi.

Platform ini memusatkan:

- autentikasi pengguna;
- sesi SSO;
- registrasi aplikasi;
- client credentials;
- consent dan scope;
- token issuance;
- identity claims;
- protocol federation;
- key publication/rotation;
- audit dan security events;
- pengaturan akses aplikasi.

## 3. Tujuan produk

### P0 — Wajib

1. Menjadi OAuth 2.0 Authorization Server untuk aplikasi terdaftar.
2. Menjadi OpenID Connect Provider dengan discovery, ID Token, UserInfo, JWKS, nonce, state, PKCE, dan authorization code flow.
3. Menyediakan application/client registry melalui dashboard.
4. Menyediakan SSO session terpusat menggunakan authentication system yang sudah ada.
5. Menyediakan scope/consent dan claims policy per aplikasi.
6. Menyediakan audit trail keamanan yang immutable secara logis.
7. Menyediakan key management dan key rotation tanpa memutus validasi token secara mendadak.
8. Menyediakan dashboard admin yang jelas dan aman.

### P1 — Wajib setelah core stabil

1. SAML 2.0 IdP dengan metadata XML.
2. Single Logout/federated logout yang sesuai kemampuan masing-masing protocol/client.
3. Upstream social/federated identity provider melalui Socialite bila dibutuhkan.
4. Client provisioning/import/export.
5. SDK/package integrasi Laravel.
6. Multi-tenant-ready organization model.

### P2 — Ekspansi

1. Dynamic Client Registration dengan policy ketat.
2. Device/session management.
3. Step-up authentication berdasarkan application policy.
4. SCIM provisioning.
5. Enterprise directory integration.
6. Advanced policy engine.
7. Webhooks/event streaming.

## 3.1 Technology baseline

Baseline yang diverifikasi pada 2026-09-17:

- Laravel 13.x sebagai application kernel.
- `mixudev/laravel-authentication` `v1.7.5` sebagai authentication boundary.
- `laravel/passport` `v13.8.0` sebagai OAuth2 engine.
- **Dashboard UI:** Blade components + Tailwind CSS v4 + Alpine.js; Bootstrap Icons via npm; Vue bukan fondasi dashboard.
- Sanctum hanya bila dashboard/internal SPA/API membutuhkan first-party authentication.
- Socialite hanya untuk upstream social/federated identity.
- Horizon untuk queue operations bila Redis queue digunakan.
- SAML tidak dipaksa menggunakan package Laravel resmi karena layer IdP SAML tidak disediakan oleh stack resmi Laravel yang dipakai; gunakan adapter terhadap mature external engine.

Passport `v13.8.0` mendukung Laravel 13 dan merupakan release yang lebih baru dari security-advisory cutoff `13.7.1`; versi affected `>=13.0.0 <13.7.1` tidak boleh digunakan. citeturn796571search0turn300680search5

## 4. Non-goals awal

- Menjadi IAM cloud lengkap dengan semua protocol pada release pertama.
- Mendukung semua OAuth grant lama hanya demi kompatibilitas.
- Menyimpan password atau credential aplikasi client di banyak tempat.
- Membuat implementation cryptography sendiri tanpa library teruji.
- Membuat seluruh fitur SAML/OIDC sekaligus sebelum foundation dan security baseline selesai.

## 5. Target pengguna

### Platform Administrator

Mengelola tenant/organization, aplikasi, policy, keys, scope, session, dan audit.

### Application Developer

Mendaftarkan aplikasi, mendapatkan client configuration, discovery URL, metadata, dan contoh integrasi.

### End User

Login sekali ke SSO lalu mengakses aplikasi yang telah diotorisasi tanpa mengulang autentikasi selama SSO session masih berlaku.

### Security/Operations

Memantau authentication event, consent, token events, failed flows, client changes, key rotation, dan administrative actions.

## 6. Mode produk

### Protocol modes

- `oidc`: OpenID Connect Provider di atas OAuth 2.0.
- `oauth2`: OAuth 2.0 untuk API/machine-to-machine; tidak digunakan sebagai identity login jika `openid` tidak diminta.
- `saml`: SAML 2.0 Identity Provider.

### Client modes

- `confidential_web`: aplikasi server-side dengan secret.
- `public_spa`: aplikasi yang tidak dapat menjaga secret; gunakan PKCE.
- `public_native`: aplikasi native/mobile; gunakan PKCE dan loopback/custom URI policy yang aman.
- `machine_to_machine`: client credentials; tidak mewakili user.
- `saml_sp`: Service Provider yang terhubung melalui SAML.

### Deployment modes

- `single_org`: satu organization aktif; tetap menggunakan schema multi-tenant-ready.
- `multi_org`: satu deployment melayani beberapa organization dengan isolation policy.


## 6.1 Authorization & Admin Access

SSO Server memiliki control plane yang hanya dapat diakses oleh subject yang terautentikasi dan memiliki authorization assignment yang valid. Authentication disediakan oleh `mixudev/laravel-authentication`; authorization dashboard adalah tanggung jawab platform.

Release awal menggunakan satu role sistem:

```text
platform_admin
```

Permission registry dan scoped role assignment disiapkan sejak foundation agar platform dapat berkembang menjadi multi-operator dan multi-organization tanpa mengubah authorization contract.

Authorization menggunakan Laravel Gates/Policies dan persistence role/permission milik project. Third-party RBAC tidak menjadi dependency baseline hanya untuk kebutuhan admin-only.

## 7. Prinsip UX dashboard

Dashboard harus terasa seperti control plane, bukan halaman CRUD biasa.

Struktur utama:

```text
Overview
Applications
Users
Sessions
Scopes & Claims
Identity Providers
Keys & Certificates
Security / Audit
Settings
Developer Center
```

Halaman Application harus menampilkan status integration secara jelas:

```text
Application
Protocol
Client type
Status
Redirect URIs
Allowed scopes
Last used
Security warnings
```

Wizard registrasi harus melakukan validasi, normalized input, preview configuration, dan final review sebelum credential dibuat.

## 7.1 Dashboard UI baseline

Dashboard admin SHALL menggunakan Mixu Dashboard shell mandiri berbasis Blade components, Tailwind CSS v4, dan Bootstrap Icons (self-hosted). Asset dipasang melalui Vite dan dibundle menjadi static production assets.

Target struktur view:

```text
resources/views/
├── layouts/
├── partials/
├── components/
│   ├── ui/
│   ├── form/
│   ├── navigation/
│   ├── security/
│   └── protocol/
└── pages/admin/
    ├── dashboard/
    ├── applications/
    ├── organizations/
    ├── users/
    ├── sessions/
    ├── oauth/
    ├── oidc/
    ├── saml/
    ├── scopes/
    ├── claims/
    ├── keys/
    ├── audit/
    ├── security/
    ├── developer/
    └── settings/
```

UI components tidak boleh memiliki query/database logic. Page composition tidak boleh menjadi file raksasa.

## 8. Developer experience

Satu field `Issuer URL` harus cukup untuk client modern melakukan discovery:

```text
https://sso.example.com/.well-known/openid-configuration
```

Untuk SAML, gunakan:

```text
https://sso.example.com/saml/metadata
```

Dashboard menyediakan:

- copyable endpoints;
- download metadata XML;
- JWKS preview;
- sample `.env`;
- sample Laravel config;
- client ID dan secret one-time reveal;
- security warnings bila redirect URI terlalu longgar.

## 9. Product principles

1. Secure by default.
2. Protocol-compliant before feature-rich.
3. Domain core tidak bergantung langsung pada library protocol.
4. Configuration over special-case code.
5. Explicit lifecycle untuk application, client, key, session, consent, dan credential.
6. Observability built-in.
7. Backward-compatible extension points.
8. Every security-critical decision is testable.

## 10. Success criteria

Release core dianggap usable apabila:

- aplikasi web confidential berhasil OIDC Authorization Code + PKCE;
- public client berhasil menggunakan PKCE tanpa secret;
- client dapat menemukan konfigurasi melalui discovery;
- ID Token dapat diverifikasi memakai JWKS;
- UserInfo hanya mengeluarkan claims sesuai scope/policy;
- redirect URI exact match;
- authorization code one-time dan expiry enforced;
- nonce/state terverifikasi;
- logout/session revocation memiliki audit event;
- key rotation tidak mematahkan token yang masih valid selama overlap window;
- seluruh critical flow memiliki automated tests.

## 11. Metrics

### Reliability

- OAuth/OIDC endpoint success rate.
- authorization-to-token completion rate.
- 5xx rate per endpoint.
- latency p50/p95/p99.

### Security

- invalid redirect attempt count.
- replay detection count.
- invalid client authentication count.
- revoked session/token usage attempts.
- administrative security events.

### Product

- applications created.
- active applications.
- SSO login completion.
- consent acceptance/denial.
- protocol distribution.

## 11.1 Development/testing quality gate

Security-critical feature SHALL melewati: unit invariant tests, feature HTTP tests, integration tests, protocol contract tests, adversarial abuse tests, dan concurrency/race tests bila state one-time atau mutation-sensitive.

Test suite harus mencoba mematahkan invariant, bukan hanya membuktikan happy path. Detail ada di `12-security-testing-strategy.md`.

## 12. Release strategy

### Phase A — Foundation

Domain model, database, application registry, client credentials, audit, keys, dashboard shell, architecture contracts.

### Phase B — OAuth2/OIDC

Passport integration, authorization endpoint, token endpoint, OIDC discovery, ID Token, JWKS, UserInfo, consent, PKCE, logout foundations.

### Phase C — Hardening

Abuse protection, replay protection, key rotation, security review, negative tests, operational observability.

### Phase D — SAML

SAML metadata, IdP SSO, assertion mapping, signatures, certificate lifecycle, SLO as supported.

### Phase E — Federation / enterprise

Upstream IdP, tenant features, provisioning, advanced policy.

## 13. PRD acceptance rule

Tidak ada fitur baru yang boleh menembus `Domain` secara langsung dari controller atau vendor package. Setiap integrasi baru harus melewati contract/port yang sudah didefinisikan pada architecture document.
