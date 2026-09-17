# Mixu SSO — Admin Dashboard Design Contract

Status: Draft implementation contract
Date: 2026-09-17

## Tujuan

Dashboard admin adalah control plane server-rendered untuk mengelola identity platform. UI menggunakan Tabler official, Bootstrap 5 yang dibundel Tabler, Blade, dan vanilla JavaScript.

## Visual direction

- Tampilan compact, tenang, dan berorientasi operasional.
- Tabler menjadi sumber primitive visual; jangan menambah framework UI kedua.
- Surface utama netral dengan hierarki jelas untuk tabel, status, identifier, dan security event.
- Warm orange brand accent digunakan terbatas pada titik orientasi atau call-to-action, bukan sebagai warna semua status.
- Status penting selalu memiliki label teks; warna hanya pelengkap.
- Tidak menggunakan emoji. Gunakan icon SVG Tabler/Lucide yang self-hosted.
- Light/dark mode mengikuti kemampuan Tabler dan tidak menyimpan state security/business di client.

## Asset boundary

- Authentication package tetap menggunakan `resources/css/app.css` dan `resources/js/app.js`.
- Dashboard admin menggunakan `resources/css/admin.css` dan `resources/js/admin.js`.
- Admin asset tidak boleh dimuat pada halaman login, register, callback, atau flow authentication.
- Tidak memakai CDN runtime untuk dashboard/security screen.

## Shell structure

```text
layouts/admin.blade.php
├── partials/admin/navbar.blade.php
├── partials/admin/sidebar.blade.php
├── partials/admin/breadcrumb.blade.php
├── partials/shared/flash-messages.blade.php
├── @yield('content')
└── partials/admin/footer.blade.php
```

Layout hanya bertanggung jawab atas document shell dan komposisi partial. Query, mutation, credential, token, dan policy decision tidak boleh berada di layout atau reusable component.

## Navigation groups

- Overview: dashboard.
- Identity: applications, organizations, users.
- Access control: scopes, claims, sessions.
- Security: audit, security events, keys.
- Developer: OAuth/OIDC configuration, integration references.

Navigation visibility boleh memakai `@can()`, tetapi seluruh endpoint tetap wajib memiliki middleware/policy server-side. Link menuju route yang belum tersedia tidak boleh dibuat sebagai fake action.

## Dashboard page contract

Dashboard overview menampilkan:

- page title dan operational summary;
- summary cards dengan unavailable state jika query metrics belum tersedia;
- security posture dengan teks status eksplisit;
- recent activity empty state sampai activity query tersedia;
- tidak menampilkan angka sintetis yang terlihat seperti data production.

Halaman menggunakan heading hierarchy yang valid dan satu main content region.

## Responsive behavior

- Sidebar dapat collapse pada viewport kecil menggunakan primitive Tabler.
- Summary cards berubah menjadi satu kolom pada mobile.
- Identifier panjang harus wrap atau memiliki overflow yang aman, bukan memaksa viewport melebar.
- Tabel kompleks memakai responsive container dan tidak menghilangkan label security-critical.
- Semua interaksi tetap dapat digunakan dengan keyboard.

## Accessibility contract

- Navigation memakai `aria-label="Primary navigation"`.
- Current navigation link memakai `aria-current="page"`.
- Interactive controls memiliki nama accessible dan visible focus state.
- Error/flash message memiliki semantic region yang dapat dibaca assistive technology.
- Status tidak dikomunikasikan melalui warna saja.
- Heading dan landmark tidak dilompati.
- Kontras dan dark mode harus tetap dapat dibaca.

## Security contract

- Semua admin route tetap dilindungi `auth` dan permission/policy server-side.
- Menyembunyikan link bukan authorization.
- User-controlled display name, labels, flash messages, dan validation messages dirender escaped.
- Password, token, client secret, authorization code, private key, dan raw credential tidak boleh muncul di HTML.
- Breadcrumb dan navigation memakai named route, bukan URL string hardcoded jika route tersedia.
- Destructive action harus memiliki confirmation dan audit consequence sebelum implementasi mutation.
- Dashboard tidak memuat protocol endpoint atau credential flow dalam shell design slice.

## File scope implementasi

```text
resources/views/layouts/admin.blade.php
resources/views/partials/admin/navbar.blade.php
resources/views/partials/admin/sidebar.blade.php
resources/views/partials/admin/breadcrumb.blade.php
resources/views/partials/admin/footer.blade.php
resources/views/partials/shared/flash-messages.blade.php
resources/views/pages/admin/dashboard/index.blade.php
tests/Feature/AdminDashboardTest.php
```

Controller hanya dibuat jika dashboard membutuhkan query/data preparation. Untuk shell statis, `Route::view()` dipertahankan sesuai YAGNI.

## Acceptance criteria

- Admin layout terpisah dari authentication layout.
- Tabler admin assets hanya dimuat pada admin layout.
- Dashboard memakai partial modular dan tidak memiliki query di Blade.
- `/admin` tetap menolak anonymous dan ordinary authenticated user.
- Admin dashboard merender `200` dengan semantic landmark, navigation label, heading, dan escaped output.
- Build Vite menghasilkan asset `app-*` dan `admin-*` terpisah.
- PHPUnit, Pint, `npm run build`, dan `git diff --check` lulus.
- Perubahan tidak memasukkan `.env`, OAuth keys, credentials, `docs/docs-work/`, atau unrelated local modifications.

## Implementation sequence

1. Contract test untuk protected dashboard dan asset isolation.
2. Admin layout shell.
3. Modular admin/shared partials.
4. Dashboard overview composition.
5. Route and authorization regression checks.
6. Frontend build and optional browser smoke check.
7. Documentation and Git checkpoint.

## Explicitly deferred

- Applications CRUD UI.
- Real dashboard metrics/query layer.
- OAuth/OIDC/SAML protocol UI.
- Client credential reveal/rotation UI.
- New frontend dependencies.
- Vue, Livewire, or SPA conversion.
