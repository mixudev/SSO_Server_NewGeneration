# Client Integration Guide

## 1. Yang wajib dibuat di sisi client

Package atau aplikasi client wajib memiliki:

1. Configuration resolver berbasis issuer.
2. Discovery document fetcher dengan HTTPS dan timeout.
3. Authorization URL builder.
4. Cryptographically secure `state` generator dan server-side state storage.
5. PKCE verifier/challenge generator menggunakan `S256`.
6. OIDC nonce generator dan binding storage.
7. Callback route yang hanya menerima expected parameters.
8. One-time authorization code exchange di backend bila confidential.
9. ID Token verifier.
10. JWKS cache dengan refresh saat `kid` tidak ditemukan.
11. Access-token storage yang tidak masuk log/view/source.
12. Refresh-token rotation handling.
13. UserInfo client.
14. Local user provisioning/linking policy.
15. Local session creation dan session fixation protection.
16. Logout handler dan local token/session cleanup.
17. Revocation handler.
18. Error mapper yang tidak menampilkan raw provider response atau secret.
19. Audit-safe logging dengan redaction.
20. Configuration validation command atau health check.

## 2. Client-side routes

Rekomendasi route aplikasi client:

```text
GET  /auth/sso/redirect
GET  /auth/sso/callback
POST /auth/sso/logout
GET  /auth/sso/status
```

Route names boleh berbeda, tetapi callback harus berada di backend client untuk confidential web.

## 3. State dan PKCE

Saat memulai login:

- generate `state` minimal 128 bit random;
- generate PKCE verifier minimal 256 bit random;
- derive challenge `BASE64URL(SHA256(verifier))`;
- generate nonce untuk OIDC;
- simpan hash atau encrypted transient binding server-side;
- simpan expiry pendek;
- jangan simpan verifier/state/nonce di log.

Saat callback:

- reject state yang hilang, expired, atau mismatch;
- consume binding atomically;
- reject duplicate callback;
- verify exact issuer/client/redirect context;
- exchange code only once.

## 4. ID Token validation

Client harus memeriksa:

- compact JWT parsing tanpa menerima bentuk lain;
- header `alg` tepat `RS256`;
- `kid` ada dan berasal dari JWKS provider;
- signature valid;
- `iss` exact sama dengan configured issuer;
- `aud` memuat client ID;
- `azp` bila diperlukan oleh multiple audience;
- `sub` ada dan stabil;
- `exp` belum lewat dengan bounded clock skew;
- `iat` tidak future di luar skew;
- `nonce` sama dengan nonce request;
- `auth_time` bila max-age/policy memerlukannya.

Jangan menerima `alg=none`, mengganti RS256 menjadi HS256, atau mempercayai claim sebelum signature diverifikasi.

## 5. Local identity mapping

Jangan memakai email sebagai primary identity key. Gunakan:

```text
(provider_issuer, sub)
```

Email dan name dapat berubah. Client harus menentukan policy untuk:

- first login;
- existing local account match;
- verified email requirement;
- account linking;
- inactive local account;
- organization/role mapping;
- account deletion atau provider subject retention.

Auto-link berdasarkan email saja harus disabled atau memerlukan explicit verified-email policy.

## 6. Token storage

Confidential web:

- simpan token server-side atau encrypted session store;
- browser hanya menerima session cookie `Secure`, `HttpOnly`, `SameSite` sesuai flow;
- jangan expose refresh token ke JavaScript.

Public SPA:

- jangan menganggap localStorage aman untuk bearer token;
- gunakan memory/worker/BFF strategy bila memungkinkan;
- batasi lifetime dan scope;
- dokumentasikan XSS risk sebelum production.

## 7. Refresh dan revoke

Client harus:

- melakukan refresh sebelum expiry dengan single-flight lock;
- tidak menjalankan refresh paralel dengan token yang sama;
- mengganti refresh token dengan token terbaru;
- menghapus seluruh token lokal jika refresh ditolak;
- melakukan logout lokal walaupun provider logout redirect gagal;
- tidak retry tanpa batas pada `invalid_grant` atau `invalid_client`.

## 8. Error handling

Map error ke kategori aman:

| Provider error | Client action |
|---|---|
| `invalid_request` | Tampilkan retry-safe message, log correlation ID saja. |
| `invalid_client` | Configuration/operator alert; jangan tampilkan secret. |
| `invalid_grant` | Clear authorization transaction/token dan mulai login baru. |
| `access_denied` | Kembali ke halaman login dengan status user-cancelled. |
| `temporarily_unavailable` | Retry bounded dengan backoff. |
| timeout/TLS failure | Fail closed; jangan bypass verification. |

## 9. Laravel package recommendation

Tidak ada package resmi Laravel yang menjadi generic OIDC client penuh untuk semua provider.

- `laravel/passport` adalah OAuth2 server milik provider ini, bukan client SDK.
- `laravel/socialite` cocok untuk social login/provider driver tertentu, tetapi bukan fondasi ideal untuk generic OIDC enterprise client karena abstraction dan lifecycle token perlu contract yang lebih ketat.
- Package `ssoclient` sebaiknya menjadi package Laravel application-owned dengan adapter protocol yang jelas.
- HTTP transport dapat memakai Laravel HTTP Client.
- JWT/JWKS verification harus memakai implementation JOSE yang dipelihara dan memiliki test algorithm-confusion; jangan menulis parser JWT ad hoc untuk production.
- Bila dependency JOSE belum dipilih, package tidak boleh mengklaim production OIDC verification.

Prinsip dependency: gunakan Laravel HTTP Client dan session/cache bawaan; tambahkan hanya satu dependency JOSE yang terawat bila native OpenSSL tidak cukup untuk verification portability.
