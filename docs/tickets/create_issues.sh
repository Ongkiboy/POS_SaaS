#!/usr/bin/env bash
set -euo pipefail
# Butuh: gh auth login. Jalankan dari dalam repo.

gh issue create --title '[SETUP-001] Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template' --body '**Context**
Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template. Rujukan: ADR-001; TD §1, §7.

**Acceptance Criteria**
- [ ] Repo punya /backend dan /frontend, `php artisan serve` dan `npm run dev` jalan di Laragon (PHP 8.4)
- [ ] Branch main/develop/feature dan PR template (memuat ID tiket) tersedia
- [ ] Pest (+ pest-plugin-arch) terpasang dan satu test contoh lulus lewat `./vendor/bin/pest`

**Depends on:** -
**References:** ADR-001; TD §1, §7
**Estimate:** S
**Branch:** `feature/setup-001-init-monorepo-backend-laravel-13-fronten`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[SETUP-002] Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment' --body '**Context**
Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment. Rujukan: ADR-006; TD §6.

**Acceptance Criteria**
- [ ] Koneksi memakai direct atau session pooler (bukan transaction pooler) dan `SELECT ... FOR UPDATE` bekerja dalam transaksi
- [ ] `.env.example` memuat semua variabel (DB, CACHE_STORE=database, QUEUE_CONNECTION=database, Supabase Storage) tanpa rahasia

**Depends on:** SETUP-001
**References:** ADR-006; TD §6
**Estimate:** S
**Branch:** `feature/setup-002-konfigurasi-postgresql-supabase-direct-s`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[SETUP-003] Pint, Larastan, ESLint, Prettier, pre-commit hook' --body '**Context**
Pint, Larastan, ESLint, Prettier, pre-commit hook. Rujukan: TD §7.

**Acceptance Criteria**
- [ ] Commit gagal bila Pint/ESLint melaporkan error
- [ ] Larastan level yang disepakati lulus pada skeleton

**Depends on:** SETUP-001
**References:** TD §7
**Estimate:** S
**Branch:** `feature/setup-003-pint-larastan-eslint-prettier-pre-commit`' --label 'setup,core' --assignee 'akmal'
gh issue create --title '[SETUP-004] CI GitHub Actions: lint + Pest + build frontend' --body '**Context**
CI GitHub Actions: lint + Pest + build frontend. Rujukan: DEPLOY ops §1.

**Acceptance Criteria**
- [ ] PR memicu lint, Pest, dan `npm run build` (Vite); status check; gagal salah satu = merge diblok
- [ ] Pest memakai database PostgreSQL service container (bukan SQLite)

**Depends on:** SETUP-003
**References:** DEPLOY ops §1
**Estimate:** M
**Branch:** `feature/setup-004-ci-github-actions-lint-pest-build-fronte`' --label 'setup,core' --assignee 'akmal'
gh issue create --title '[SETUP-005] Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1' --body '**Context**
Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1. Rujukan: TD §2, §2.4; SDD §4.2.

**Acceptance Criteria**
- [ ] Respons sukses dan error mengikuti envelope SDD §4.2 (`success`, `data`, `error.code`)
- [ ] Exception 404/403/409/422 dipetakan ke kode error SDD §4.3
- [ ] Modul baru cukup didaftarkan di `config/modules.php`; routes, policy, dan event-nya termuat lewat `ModuleServiceProvider` tanpa mengubah `routes/api.php` (TD §2.4)
- [ ] `route:cache` dan `config:cache` berjalan tanpa error; satu modul contoh (`Platform/Tenant`) dengan route ping termuat lewat `ModuleServiceProvider`

**Depends on:** SETUP-001
**References:** TD §2, §2.4; SDD §4.2
**Estimate:** M
**Branch:** `feature/setup-005-skeleton-backend-app-modules-pola-action`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[SETUP-006] Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog' --body '**Context**
Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog. Rujukan: TD §3; ADR-001.

**Acceptance Criteria**
- [ ] API client otomatis mengambil `/sanctum/csrf-cookie` dan menyertakan `X-Outlet-Id`
- [ ] 401 mengarahkan ke login; 422 memetakan `details` ke field form

**Depends on:** SETUP-001
**References:** TD §3; ADR-001
**Estimate:** M
**Branch:** `feature/setup-006-skeleton-frontend-vue-3-vite-ts-vue-rout`' --label 'setup,core' --assignee 'naufaldi'
gh issue create --title '[SETUP-007] Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test' --body '**Context**
Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test. Rujukan: ADR-004; SRS §1.4.

**Acceptance Criteria**
- [ ] `Money::halfUp()` memberi hasil benar untuk kasus .5 positif dan nol
- [ ] Enum OrderStatus, MovementType, PaymentMethod, ShiftStatus tersedia dan sama dengan CHECK di ERD

**Depends on:** SETUP-005
**References:** ADR-004; SRS §1.4
**Estimate:** S
**Branch:** `feature/setup-007-helper-money-quantity-bigint-rupiah-deci`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[SETUP-008] Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite)' --body '**Context**
Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite). Rujukan: NFR-SEC-003; TD §5.

**Acceptance Criteria**
- [ ] Login lewat FE staging berhasil dengan cookie Sanctum (Secure, HttpOnly, SameSite)
- [ ] Sertifikat HTTPS aktif dan HTTP dialihkan

**Depends on:** SETUP-004
**References:** NFR-SEC-003; TD §5
**Estimate:** M
**Branch:** `feature/setup-008-staging-https-fe-dan-api-satu-parent-dom`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[SETUP-009] Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi' --body '**Context**
Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi. Rujukan: TD §2.3, §2.4.

**Acceptance Criteria**
- [ ] Arch test gagal bila Recipe memanggil POS/Inventory atau Inventory memanggil POS; modul hanya bicara lewat Contract/Facade yang terdaftar
- [ ] Arch test gagal bila ada class `XxxModule` yang belum terdaftar di `config/modules.php` (kecuali `Payments\Gateway`) atau nama migrasi tidak mengikuti pola `modul_aksi_tabel`

**Depends on:** SETUP-005
**References:** TD §2.3, §2.4
**Estimate:** M
**Branch:** `feature/setup-009-arch-test-pest-dependensi-modul-peta-pan`' --label 'setup,core' --assignee 'akmal'
gh issue create --title '[DB-TEN-001] Migrasi businesses, outlets, users, user_outlets' --body '**Context**
Migrasi businesses, outlets, users, user_outlets. Rujukan: ERD §3.1; BR-022.

**Acceptance Criteria**
- [ ] Semua kolom, CHECK, dan UNIQUE sesuai ERD §3.1
- [ ] Rollback migrasi bersih
- [ ] Migrasi `users` bawaan Laravel dihapus (diganti skema ERD); migrasi framework (sessions, cache, jobs, failed_jobs) dan Spatie diberi nama berprefix modul (`platform_`, `rbac_`), dan tabel framework dihitung terpisah dari 40 tabel domain

**Depends on:** SETUP-005
**References:** ERD §3.1; BR-022
**Estimate:** M
**Branch:** `feature/db-ten-001-migrasi-businesses-outlets-users-user-ou`' --label 'database,ten,core' --assignee 'ongki'
gh issue create --title '[DB-TEN-002] Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet' --body '**Context**
Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet. Rujukan: ADR-003; FR-USER-002.

**Acceptance Criteria**
- [ ] Query model tenant otomatis terfilter business_id
- [ ] Insert otomatis mengisi business_id; data business lain mengembalikan 404

**Depends on:** DB-TEN-001
**References:** ADR-003; FR-USER-002
**Estimate:** M
**Branch:** `feature/db-ten-002-trait-belongstobusiness-global-scope-aut`' --label 'database,ten,core' --assignee 'ongki'
gh issue create --title '[DB-RBAC-001] Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own' --body '**Context**
Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own. Rujukan: ADR-005; SRS §2.1.

**Acceptance Criteria**
- [ ] 5 role dan semua permission SRS §2.1 ter-seed per business saat register
- [ ] Config scope (`OWN_ONLY`, `LIMITED`) terbaca untuk /me

**Depends on:** DB-TEN-001
**References:** ADR-005; SRS §2.1
**Estimate:** M
**Branch:** `feature/db-rbac-001-install-spatie-permission-teams-business`' --label 'database,rbac,core' --assignee 'ongki'
gh issue create --title '[DB-AUD-001] Migrasi audit_logs + trigger append-only' --body '**Context**
Migrasi audit_logs + trigger append-only. Rujukan: ADR-007; BR-020.

**Acceptance Criteria**
- [ ] UPDATE dan DELETE pada audit_logs ditolak trigger DB
- [ ] Index sesuai ERD §4

**Depends on:** DB-TEN-001
**References:** ADR-007; BR-020
**Estimate:** S
**Branch:** `feature/db-aud-001-migrasi-audit-logs-trigger-append-only`' --label 'database,aud,core' --assignee 'ongki'
gh issue create --title '[BE-AUD-001] AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue' --body '**Context**
AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue. Rujukan: FR-AUD-001.

**Acceptance Criteria**
- [ ] Pemanggilan di dalam transaksi baru tertulis setelah commit (afterCommit)
- [ ] Payload memuat before/after JSON, user, outlet, IP

**Depends on:** DB-AUD-001, SETUP-005
**References:** FR-AUD-001
**Estimate:** S
**Branch:** `feature/be-aud-001-auditlogger-log-action-resource-before-a`' --label 'backend,aud,core' --assignee 'ongki'
gh issue create --title '[BE-RBAC-001] Middleware permission + outlet.context, Policy aturan Limited/Own' --body '**Context**
Middleware permission + outlet.context, Policy aturan Limited/Own. Rujukan: FR-USER-002; BR-024, BR-025.

**Acceptance Criteria**
- [ ] Tanpa permission = 403; X-Outlet-Id di luar user_outlets = 403
- [ ] Policy Cashier hanya order sendiri; Manager hanya mengelola Cashier/Inventory/Kitchen

**Depends on:** DB-RBAC-001, DB-TEN-002
**References:** FR-USER-002; BR-024, BR-025
**Estimate:** M
**Branch:** `feature/be-rbac-001-middleware-permission-outlet-context-pol`' --label 'backend,rbac,core' --assignee 'ongki'
gh issue create --title '[BE-AUTH-001] POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi)' --body '**Context**
POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi). Rujukan: FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01.

**Acceptance Criteria**
- [ ] Data valid mengembalikan 201 dan membuat business, outlet, Owner, user_outlets
- [ ] Email ganda dan password lemah mengembalikan 422 dengan pesan SRS FR-AUTH-001

**Depends on:** DB-TEN-002, DB-RBAC-001, BE-AUD-001
**References:** FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01
**Estimate:** M
**Branch:** `feature/be-auth-001-post-auth-register-business-business-out`' --label 'backend,auth,core' --assignee 'ongki'
gh issue create --title '[BE-AUTH-002] Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED' --body '**Context**
Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED. Rujukan: FR-AUTH-002; UC-AUTH-02; BR-023.

**Acceptance Criteria**
- [ ] Gagal ke-5 mengunci akun 15 menit (423 ACCOUNT_LOCKED)
- [ ] Akun INACTIVE 403; login gagal email tak dikenal tercatat dengan user_id NULL

**Depends on:** BE-AUTH-001
**References:** FR-AUTH-002; UC-AUTH-02; BR-023
**Estimate:** M
**Branch:** `feature/be-auth-002-login-logout-sanctum-spa-throttle-lockou`' --label 'backend,auth,core' --assignee 'ongki'
gh issue create --title '[BE-AUTH-003] GET/PUT /me dan PUT /me/password' --body '**Context**
GET/PUT /me dan PUT /me/password. Rujukan: FR-AUTH-003; UC-AUTH-03.

**Acceptance Criteria**
- [ ] Password lama salah mengembalikan 422 "Password lama tidak sesuai."
- [ ] Ganti password mengakhiri sesi lain milik user

**Depends on:** BE-AUTH-002
**References:** FR-AUTH-003; UC-AUTH-03
**Estimate:** S
**Branch:** `feature/be-auth-003-get-put-me-dan-put-me-password`' --label 'backend,auth,core' --assignee 'naufaldi'
gh issue create --title '[BE-AUTH-004] /me mengembalikan outlet + permission dengan scope' --body '**Context**
/me mengembalikan outlet + permission dengan scope. Rujukan: FR-AUTH-004.

**Acceptance Criteria**
- [ ] Respons memuat role, outlets, permissions[{code, scope}] sesuai SDD §4.5
- [ ] Owner otomatis punya semua outlet business

**Depends on:** BE-AUTH-002, BE-RBAC-001
**References:** FR-AUTH-004
**Estimate:** S
**Branch:** `feature/be-auth-004-me-mengembalikan-outlet-permission-denga`' --label 'backend,auth,core' --assignee 'ongki'
gh issue create --title '[BE-USER-001] CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit' --body '**Context**
CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit. Rujukan: FR-USER-001; UC-USER-01; BR-024.

**Acceptance Criteria**
- [ ] Manager yang memilih role di luar Cashier/Inventory/Kitchen mendapat 403
- [ ] Owner terakhir tidak bisa dinonaktifkan atau diganti role

**Depends on:** BE-RBAC-001, BE-AUD-001
**References:** FR-USER-001; UC-USER-01; BR-024
**Estimate:** M
**Branch:** `feature/be-user-001-crud-user-assign-role-outlet-nonaktifkan`' --label 'backend,user,core' --assignee 'naufaldi'
gh issue create --title '[BE-USER-002] CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk)' --body '**Context**
CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk). Rujukan: FR-BIZ-002, FR-BIZ-003; UC-BIZ-02.

**Acceptance Criteria**
- [ ] Manager hanya boleh ubah pajak, service, struk; rounding_increment hanya Owner
- [ ] Kode outlet tidak bisa diubah setelah ada order; perubahan setting tercatat audit before/after

**Depends on:** BE-RBAC-001, BE-AUD-001
**References:** FR-BIZ-002, FR-BIZ-003; UC-BIZ-02
**Estimate:** M
**Branch:** `feature/be-user-002-crud-outlet-pengaturan-pajak-service-min`' --label 'backend,user,core' --assignee 'naufaldi'
gh issue create --title '[BE-USER-003] GET/PUT /business (profil + license tanpa enforcement)' --body '**Context**
GET/PUT /business (profil + license tanpa enforcement). Rujukan: FR-BIZ-001; UC-BIZ-01.

**Acceptance Criteria**
- [ ] Hanya Owner yang dapat mengubah; perubahan tercatat audit
- [ ] license_plan dan license_valid_until tersimpan tanpa memblokir fitur

**Depends on:** BE-RBAC-001
**References:** FR-BIZ-001; UC-BIZ-01
**Estimate:** XS
**Branch:** `feature/be-user-003-get-put-business-profil-license-tanpa-en`' --label 'backend,user,core' --assignee 'naufaldi'
gh issue create --title '[FE-AUTH-001] Halaman Login' --body '**Context**
Halaman Login. Rujukan: UC-AUTH-02; SCR-AUTH-01.

**Acceptance Criteria**
- [ ] Pesan error mengikuti SRS dan tidak membocorkan email atau password yang salah
- [ ] Akun terkunci menampilkan pesan 423 dan state loading/error ada

**Depends on:** BE-AUTH-002, SETUP-006
**References:** UC-AUTH-02; SCR-AUTH-01
**Estimate:** M
**Branch:** `feature/fe-auth-001-halaman-login`' --label 'frontend,auth,core' --assignee 'naufaldi'
gh issue create --title '[FE-AUTH-002] Halaman Register Business' --body '**Context**
Halaman Register Business. Rujukan: UC-AUTH-01; SCR-AUTH-02.

**Acceptance Criteria**
- [ ] Validasi per field menampilkan pesan SRS
- [ ] Sukses mengarahkan ke login/onboarding

**Depends on:** BE-AUTH-001, SETUP-006
**References:** UC-AUTH-01; SCR-AUTH-02
**Estimate:** S
**Branch:** `feature/fe-auth-002-halaman-register-business`' --label 'frontend,auth,core' --assignee 'naufaldi'
gh issue create --title '[FE-AUTH-003] Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher' --body '**Context**
Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher. Rujukan: FR-AUTH-004; ADR-001.

**Acceptance Criteria**
- [ ] Halaman terproteksi mengarahkan ke login bila 401
- [ ] v-can menyembunyikan UI sesuai permission dan scope; backend tetap menegakkan

**Depends on:** FE-AUTH-001, BE-AUTH-004
**References:** FR-AUTH-004; ADR-001
**Estimate:** M
**Branch:** `feature/fe-auth-003-auth-store-pinia-route-guard-vue-router`' --label 'frontend,auth,core' --assignee 'naufaldi'
gh issue create --title '[FE-USER-001] Halaman manajemen user (list, form, role, outlet)' --body '**Context**
Halaman manajemen user (list, form, role, outlet). Rujukan: UC-USER-01; SCR-USER-01.

**Acceptance Criteria**
- [ ] Dropdown role hanya menampilkan role yang boleh dipilih pelaku (GET /roles)
- [ ] State empty, loading, error tersedia

**Depends on:** BE-USER-001, FE-AUTH-003
**References:** UC-USER-01; SCR-USER-01
**Estimate:** M
**Branch:** `feature/fe-user-001-halaman-manajemen-user-list-form-role-ou`' --label 'frontend,user,core' --assignee 'naufaldi'
gh issue create --title '[FE-USER-002] Halaman pengaturan outlet dan profil business' --body '**Context**
Halaman pengaturan outlet dan profil business. Rujukan: UC-BIZ-02; SCR-BIZ-01.

**Acceptance Criteria**
- [ ] Field di luar hak Manager tampil nonaktif
- [ ] Tarif di luar 0–100 menampilkan pesan SRS

**Depends on:** BE-USER-002, BE-USER-003, FE-AUTH-003
**References:** UC-BIZ-02; SCR-BIZ-01
**Estimate:** S
**Branch:** `feature/fe-user-002-halaman-pengaturan-outlet-dan-profil-bus`' --label 'frontend,user,core' --assignee 'naufaldi'
gh issue create --title '[TEST-AUTH-001] Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint)' --body '**Context**
Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint). Rujukan: NFR-SEC-001; ADR-003.

**Acceptance Criteria**
- [ ] Setiap endpoint terdaftar dites lintas tenant dan mengembalikan 404
- [ ] Dataset otomatis bertambah saat endpoint baru didaftarkan (DoD tiap modul)

**Depends on:** BE-USER-001
**References:** NFR-SEC-001; ADR-003
**Estimate:** M
**Branch:** `feature/test-auth-001-suite-isolasi-tenant-dataset-driven-busi`' --label 'testing,auth,core' --assignee 'ongki'
gh issue create --title '[TEST-RBAC-001] Test matriks role × endpoint' --body '**Context**
Test matriks role × endpoint. Rujukan: SRS §2.1.

**Acceptance Criteria**
- [ ] Tiap sel matriks permission diuji (ijin/tolak)
- [ ] Aturan Own/Limited diuji untuk Cashier, Manager, Inventory

**Depends on:** BE-USER-001
**References:** SRS §2.1
**Estimate:** M
**Branch:** `feature/test-rbac-001-test-matriks-role-endpoint`' --label 'testing,rbac,core' --assignee 'ongki'
gh issue create --title '[DB-CAT-001] Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups' --body '**Context**
Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups. Rujukan: ERD §3.2.

**Acceptance Criteria**
- [ ] UNIQUE parsial SKU dan nama sesuai ERD
- [ ] products tidak punya kolom cost_price

**Depends on:** DB-TEN-002
**References:** ERD §3.2
**Estimate:** M
**Branch:** `feature/db-cat-001-migrasi-categories-products-modifier-gro`' --label 'database,cat,core' --assignee 'naufaldi'
gh issue create --title '[BE-CAT-001] CRUD category (status, sort order)' --body '**Context**
CRUD category (status, sort order). Rujukan: FR-CAT-001; UC-CAT-01.

**Acceptance Criteria**
- [ ] Nama unik per business; INACTIVE menyembunyikan produknya dari POS
- [ ] Soft delete, bukan hard delete

**Depends on:** DB-CAT-001, BE-RBAC-001
**References:** FR-CAT-001; UC-CAT-01
**Estimate:** S
**Branch:** `feature/be-cat-001-crud-category-status-sort-order`' --label 'backend,cat,core' --assignee 'naufaldi'
gh issue create --title '[BE-CAT-002] CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp)' --body '**Context**
CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp). Rujukan: FR-CAT-002; UC-CAT-01; SEQ-CAT-01.

**Acceptance Criteria**
- [ ] SKU ganda 422 "SKU sudah digunakan."; harga non-bulat/negatif 422
- [ ] Gambar >2 MB atau format salah 422 dengan pesan SRS

**Depends on:** BE-CAT-001
**References:** FR-CAT-002; UC-CAT-01; SEQ-CAT-01
**Estimate:** M
**Branch:** `feature/be-cat-002-crud-product-upload-gambar-ke-supabase-s`' --label 'backend,cat,core' --assignee 'naufaldi'
gh issue create --title '[BE-CAT-003] Audit perubahan harga + guard produk INACTIVE + soft delete' --body '**Context**
Audit perubahan harga + guard produk INACTIVE + soft delete. Rujukan: FR-CAT-002; BR-018, BR-029.

**Acceptance Criteria**
- [ ] Perubahan selling_price menulis PRODUCT_PRICE_CHANGED (before/after)
- [ ] Produk yang pernah dipakai transaksi tidak bisa dihapus permanen

**Depends on:** BE-CAT-002, BE-AUD-001
**References:** FR-CAT-002; BR-018, BR-029
**Estimate:** S
**Branch:** `feature/be-cat-003-audit-perubahan-harga-guard-produk-inact`' --label 'backend,cat,core' --assignee 'naufaldi'
gh issue create --title '[BE-CAT-004] CRUD modifier group & option + pasang ke produk' --body '**Context**
CRUD modifier group & option + pasang ke produk. Rujukan: FR-CAT-003; UC-CAT-02; BR-030.

**Acceptance Criteria**
- [ ] min_select > max_select ditolak dengan pesan SRS
- [ ] SINGLE hanya memperbolehkan satu pilihan; option INACTIVE tidak bisa dipilih

**Depends on:** BE-CAT-002
**References:** FR-CAT-003; UC-CAT-02; BR-030
**Estimate:** M
**Branch:** `feature/be-cat-004-crud-modifier-group-option-pasang-ke-pro`' --label 'backend,cat,core' --assignee 'naufaldi'
gh issue create --title '[BE-CAT-005] GET /pos/catalog dengan cache + invalidasi saat katalog berubah' --body '**Context**
GET /pos/catalog dengan cache + invalidasi saat katalog berubah. Rujukan: FR-CAT-004; SEQ-CAT-01.

**Acceptance Criteria**
- [ ] Respons tidak memuat HPP
- [ ] Perubahan produk/kategori/modifier membatalkan cache

**Depends on:** BE-CAT-004
**References:** FR-CAT-004; SEQ-CAT-01
**Estimate:** S
**Branch:** `feature/be-cat-005-get-pos-catalog-dengan-cache-invalidasi`' --label 'backend,cat,core' --assignee 'naufaldi'
gh issue create --title '[FE-CAT-001] Halaman kategori' --body '**Context**
Halaman kategori. Rujukan: SCR-CAT-01.

**Acceptance Criteria**
- [ ] CRUD kategori jalan dengan state loading/empty/error
- [ ] Aksi dibatasi v-can products.*

**Depends on:** BE-CAT-001, FE-AUTH-003
**References:** SCR-CAT-01
**Estimate:** S
**Branch:** `feature/fe-cat-001-halaman-kategori`' --label 'frontend,cat,core' --assignee 'naufaldi'
gh issue create --title '[FE-CAT-002] Halaman list + form produk (upload gambar)' --body '**Context**
Halaman list + form produk (upload gambar). Rujukan: SCR-CAT-02.

**Acceptance Criteria**
- [ ] Error 422 tampil per field
- [ ] Preview gambar dan batas 2 MB divalidasi sebelum upload

**Depends on:** BE-CAT-002, FE-AUTH-003
**References:** SCR-CAT-02
**Estimate:** M
**Branch:** `feature/fe-cat-002-halaman-list-form-produk-upload-gambar`' --label 'frontend,cat,core' --assignee 'naufaldi'
gh issue create --title '[FE-CAT-003] UI manajemen modifier' --body '**Context**
UI manajemen modifier. Rujukan: SCR-CAT-03.

**Acceptance Criteria**
- [ ] Group, option, dan pemasangan ke produk dapat dikelola
- [ ] Aturan min/max tampil jelas

**Depends on:** BE-CAT-004, FE-CAT-002
**References:** SCR-CAT-03
**Estimate:** M
**Branch:** `feature/fe-cat-003-ui-manajemen-modifier`' --label 'frontend,cat,core' --assignee 'naufaldi'
gh issue create --title '[TEST-CAT-001] Feature test katalog (tenant, SKU unik, inactive, cache)' --body '**Context**
Feature test katalog (tenant, SKU unik, inactive, cache). Rujukan: FR-CAT-001…004.

**Acceptance Criteria**
- [ ] Semua baris tabel validasi FR-CAT-002/003 punya test
- [ ] Test cache invalidasi lulus

**Depends on:** BE-CAT-005
**References:** FR-CAT-001…004
**Estimate:** M
**Branch:** `feature/test-cat-001-feature-test-katalog-tenant-sku-unik-ina`' --label 'testing,cat,core' --assignee 'akmal'
gh issue create --title '[DB-ING-001] Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000)' --body '**Context**
Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000). Rujukan: ERD §3.2; BR-046.

**Acceptance Criteria**
- [ ] Unit g, kg, ml, L, pcs ter-seed; faktor kg→g dan L→ml = 1000
- [ ] last_cost DECIMAL(18,4), CHECK ≥ 0

**Depends on:** DB-TEN-002
**References:** ERD §3.2; BR-046
**Estimate:** M
**Branch:** `feature/db-ing-001-migrasi-units-ingredients-ingredient-uni`' --label 'database,ing,core' --assignee 'akmal'
gh issue create --title '[BE-ING-001] CRUD ingredient (permission inventory.adjust, baca inventory.view)' --body '**Context**
CRUD ingredient (permission inventory.adjust, baca inventory.view). Rujukan: FR-ING-001; UC-ING-01.

**Acceptance Criteria**
- [ ] SKU ganda 422; base unit tidak bisa diubah bila sudah ada movement
- [ ] last_cost negatif ditolak; perubahan tidak mengubah movement/order lama

**Depends on:** DB-ING-001, BE-RBAC-001
**References:** FR-ING-001; UC-ING-01
**Estimate:** M
**Branch:** `feature/be-ing-001-crud-ingredient-permission-inventory-adj`' --label 'backend,ing,core' --assignee 'akmal'
gh issue create --title '[BE-ING-002] Seeder unit + endpoint GET /units' --body '**Context**
Seeder unit + endpoint GET /units. Rujukan: FR-ING-001.

**Acceptance Criteria**
- [ ] GET /units mengembalikan 5 satuan
- [ ] Seeder idempoten (aman dijalankan ulang)

**Depends on:** DB-ING-001
**References:** FR-ING-001
**Estimate:** XS
**Branch:** `feature/be-ing-002-seeder-unit-endpoint-get-units`' --label 'backend,ing,core' --assignee 'akmal'
gh issue create --title '[DB-REC-001] Migrasi recipes (versi), recipe_items, modifier_option_recipe_items' --body '**Context**
Migrasi recipes (versi), recipe_items, modifier_option_recipe_items. Rujukan: ERD §3.2; BR-013.

**Acceptance Criteria**
- [ ] UNIQUE parsial 1 recipe aktif per produk
- [ ] UNIQUE (recipe_id, ingredient_id)

**Depends on:** DB-ING-001, DB-CAT-001
**References:** ERD §3.2; BR-013
**Estimate:** S
**Branch:** `feature/db-rec-001-migrasi-recipes-versi-recipe-items-modif`' --label 'database,rec,core' --assignee 'akmal'
gh issue create --title '[BE-REC-001] Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP' --body '**Context**
Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP. Rujukan: FR-REC-001; UC-REC-01; SEQ-REC-01.

**Acceptance Criteria**
- [ ] Simpan membuat version+1 dan menonaktifkan versi lama dalam satu transaksi
- [ ] Qty ≤ 0, bahan ganda, recipe kosong ditolak dengan pesan SRS

**Depends on:** DB-REC-001, BE-ING-001, BE-CAT-002
**References:** FR-REC-001; UC-REC-01; SEQ-REC-01
**Estimate:** M
**Branch:** `feature/be-rec-001-recipe-per-produk-edit-versi-baru-1-akti`' --label 'backend,rec,core' --assignee 'akmal'
gh issue create --title '[BE-REC-002] Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price' --body '**Context**
Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price. Rujukan: FR-REC-003; BR-014.

**Acceptance Criteria**
- [ ] HPP produk tanpa recipe = 0 dan ditandai no recipe
- [ ] HPP hanya tampil untuk Owner dan Manager

**Depends on:** BE-REC-001
**References:** FR-REC-003; BR-014
**Estimate:** S
**Branch:** `feature/be-rec-002-kalkulator-hpp-qty-last-cost-half-up-tan`' --label 'backend,rec,core' --assignee 'akmal'
gh issue create --title '[FE-ING-001] Halaman ingredient' --body '**Context**
Halaman ingredient. Rujukan: SCR-ING-01.

**Acceptance Criteria**
- [ ] CRUD ingredient dengan pesan error SRS
- [ ] Base unit terkunci bila sudah ada movement

**Depends on:** BE-ING-001, FE-AUTH-003
**References:** SCR-ING-01
**Estimate:** M
**Branch:** `feature/fe-ing-001-halaman-ingredient`' --label 'frontend,ing,core' --assignee 'akmal'
gh issue create --title '[FE-REC-001] Editor recipe di form produk + preview HPP' --body '**Context**
Editor recipe di form produk + preview HPP. Rujukan: SCR-CAT-02; UC-REC-01.

**Acceptance Criteria**
- [ ] Satuan input memakai base unit bahan
- [ ] Preview HPP disembunyikan untuk non-Owner/Manager

**Depends on:** BE-REC-002, FE-CAT-002
**References:** SCR-CAT-02; UC-REC-01
**Estimate:** M
**Branch:** `feature/fe-rec-001-editor-recipe-di-form-produk-preview-hpp`' --label 'frontend,rec,core' --assignee 'akmal'
gh issue create --title '[TEST-REC-001] Test recipe (versioning, validasi, HPP)' --body '**Context**
Test recipe (versioning, validasi, HPP). Rujukan: FR-REC-001, FR-REC-003.

**Acceptance Criteria**
- [ ] Versioning dan 1 aktif teruji (termasuk constraint DB)
- [ ] Perhitungan HPP half-up sesuai vektor uji

**Depends on:** BE-REC-002
**References:** FR-REC-001, FR-REC-003
**Estimate:** S
**Branch:** `feature/test-rec-001-test-recipe-versioning-validasi-hpp`' --label 'testing,rec,core' --assignee 'akmal'
gh issue create --title '[DB-INV-001] Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches' --body '**Context**
Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches. Rujukan: ERD §3.3; ADR-007.

**Acceptance Criteria**
- [ ] UNIQUE idempotensi movement dan CHECK arah delta sesuai ERD
- [ ] UPDATE/DELETE movement ditolak trigger

**Depends on:** DB-ING-001, DB-AUD-001
**References:** ERD §3.3; ADR-007
**Estimate:** M
**Branch:** `feature/db-inv-001-migrasi-stock-balances-stock-movements-t`' --label 'database,inv,core' --assignee 'akmal'
gh issue create --title '[BE-INV-001] StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative)' --body '**Context**
StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative). Rujukan: ADR-002; ACT-INV-01.

**Acceptance Criteria**
- [ ] Stok kurang dan minus dilarang melempar InsufficientStock
- [ ] forceNegative hanya dipakai jalur settlement gateway (default false)

**Depends on:** DB-INV-001
**References:** ADR-002; ACT-INV-01
**Estimate:** M
**Branch:** `feature/be-inv-001-stockservice-apply-lock-for-update-befor`' --label 'backend,inv,core' --assignee 'akmal'
gh issue create --title '[BE-INV-002] Idempotensi movement (referensi sama = movement lama) + helper reverse()' --body '**Context**
Idempotensi movement (referensi sama = movement lama) + helper reverse(). Rujukan: BR-009; NFR-DATA-003.

**Acceptance Criteria**
- [ ] Pemanggilan ulang dengan referensi sama mengembalikan movement lama tanpa baris baru
- [ ] reverse() membuat movement lawan dengan referensi baru

**Depends on:** BE-INV-001
**References:** BR-009; NFR-DATA-003
**Estimate:** S
**Branch:** `feature/be-inv-002-idempotensi-movement-referensi-sama-move`' --label 'backend,inv,core' --assignee 'akmal'
gh issue create --title '[BE-INV-003] POST /inventory/opening-stock (batch + movement OPENING)' --body '**Context**
POST /inventory/opening-stock (batch + movement OPENING). Rujukan: FR-INV-001; UC-INV-01; SEQ-INV-01.

**Acceptance Criteria**
- [ ] Satu batch menghasilkan satu movement OPENING per bahan dengan reference_id = id batch
- [ ] Qty ≤ 0 dan bahan ganda dalam batch ditolak

**Depends on:** BE-INV-001
**References:** FR-INV-001; UC-INV-01; SEQ-INV-01
**Estimate:** S
**Branch:** `feature/be-inv-003-post-inventory-opening-stock-batch-movem`' --label 'backend,inv,core' --assignee 'akmal'
gh issue create --title '[BE-INV-004] POST/GET /inventory/adjustments (record dibuat sebelum movement)' --body '**Context**
POST/GET /inventory/adjustments (record dibuat sebelum movement). Rujukan: FR-INV-003; UC-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib dan qty > 0; before/after tersimpan
- [ ] Hasil minus saat minus dilarang 422 INSUFFICIENT_STOCK

**Depends on:** BE-INV-001, BE-AUD-001
**References:** FR-INV-003; UC-INV-03
**Estimate:** M
**Branch:** `feature/be-inv-004-post-get-inventory-adjustments-record-di`' --label 'backend,inv,core' --assignee 'akmal'
gh issue create --title '[BE-INV-005] POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya)' --body '**Context**
POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya). Rujukan: FR-INV-004; UC-INV-04; BR-034.

**Acceptance Criteria**
- [ ] Alasan OTHER tanpa catatan 422
- [ ] total_cost tidak ada di respons untuk role Kitchen

**Depends on:** BE-INV-001, BE-AUD-001
**References:** FR-INV-004; UC-INV-04; BR-034
**Estimate:** M
**Branch:** `feature/be-inv-005-post-get-inventory-waste-alasan-enum-sna`' --label 'backend,inv,core' --assignee 'akmal'
gh issue create --title '[BE-INV-006] GET stok, riwayat movement, low stock + stok minus' --body '**Context**
GET stok, riwayat movement, low stock + stok minus. Rujukan: FR-INV-002, FR-INV-005; UC-INV-02.

**Acceptance Criteria**
- [ ] Filter ingredient, tipe, tanggal dengan paginasi
- [ ] Low stock memakai min_stock override outlet; stok minus tampil terpisah

**Depends on:** BE-INV-001
**References:** FR-INV-002, FR-INV-005; UC-INV-02
**Estimate:** S
**Branch:** `feature/be-inv-006-get-stok-riwayat-movement-low-stock-stok`' --label 'backend,inv,core' --assignee 'akmal'
gh issue create --title '[FE-INV-001] Halaman stok + badge low stock/minus' --body '**Context**
Halaman stok + badge low stock/minus. Rujukan: SCR-INV-01.

**Acceptance Criteria**
- [ ] Badge low stock dan minus berbeda dan menonjol
- [ ] State empty/loading/error ada

**Depends on:** BE-INV-006, FE-AUTH-003
**References:** SCR-INV-01
**Estimate:** M
**Branch:** `feature/fe-inv-001-halaman-stok-badge-low-stock-minus`' --label 'frontend,inv,core' --assignee 'akmal'
gh issue create --title '[FE-INV-002] Halaman riwayat movement' --body '**Context**
Halaman riwayat movement. Rujukan: SCR-INV-02.

**Acceptance Criteria**
- [ ] Filter tipe/tanggal bekerja
- [ ] Tidak ada aksi ubah/hapus movement

**Depends on:** BE-INV-006
**References:** SCR-INV-02
**Estimate:** S
**Branch:** `feature/fe-inv-002-halaman-riwayat-movement`' --label 'frontend,inv,core' --assignee 'akmal'
gh issue create --title '[FE-INV-003] Form opening stock & adjustment' --body '**Context**
Form opening stock & adjustment. Rujukan: SCR-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib tampil pada adjustment
- [ ] Error stok kurang menampilkan pesan SRS

**Depends on:** BE-INV-003, BE-INV-004
**References:** SCR-INV-03
**Estimate:** M
**Branch:** `feature/fe-inv-003-form-opening-stock-adjustment`' --label 'frontend,inv,core' --assignee 'akmal'
gh issue create --title '[FE-INV-004] Form & daftar waste' --body '**Context**
Form & daftar waste. Rujukan: SCR-INV-04.

**Acceptance Criteria**
- [ ] Catatan wajib saat alasan OTHER
- [ ] Nilai biaya disembunyikan untuk Kitchen

**Depends on:** BE-INV-005
**References:** SCR-INV-04
**Estimate:** S
**Branch:** `feature/fe-inv-004-form-daftar-waste`' --label 'frontend,inv,core' --assignee 'akmal'
gh issue create --title '[TEST-INV-001] Unit test StockService: konkurensi, minus, idempotensi, presisi desimal' --body '**Context**
Unit test StockService: konkurensi, minus, idempotensi, presisi desimal. Rujukan: NFR-DATA-001.

**Acceptance Criteria**
- [ ] 20 apply paralel pada bahan sama: balance = SUM(movements)
- [ ] Presisi DECIMAL(18,4) terjaga

**Depends on:** BE-INV-002
**References:** NFR-DATA-001
**Estimate:** M
**Branch:** `feature/test-inv-001-unit-test-stockservice-konkurensi-minus`' --label 'testing,inv,core' --assignee 'akmal'
gh issue create --title '[TEST-INV-002] Feature test opening, adjustment, waste, stok' --body '**Context**
Feature test opening, adjustment, waste, stok. Rujukan: FR-INV-001…005.

**Acceptance Criteria**
- [ ] Semua baris tabel validasi FR-INV-003/004 diuji
- [ ] Permission Kitchen/Inventory sesuai matriks

**Depends on:** BE-INV-005, BE-INV-006
**References:** FR-INV-001…005
**Estimate:** M
**Branch:** `feature/test-inv-002-feature-test-opening-adjustment-waste-st`' --label 'testing,inv,core' --assignee 'akmal'
gh issue create --title '[DB-SHF-001] Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet)' --body '**Context**
Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet). Rujukan: ERD §3.4; BR-002.

**Acceptance Criteria**
- [ ] Shift OPEN kedua milik kasir yang sama di outlet sama ditolak DB
- [ ] cash_movements append-only (trigger)

**Depends on:** DB-TEN-002
**References:** ERD §3.4; BR-002
**Estimate:** S
**Branch:** `feature/db-shf-001-migrasi-shifts-cash-movements-unique-par`' --label 'database,shf,core' --assignee 'naufaldi'
gh issue create --title '[BE-SHF-001] Open shift (opening cash, business_date menurut timezone outlet)' --body '**Context**
Open shift (opening cash, business_date menurut timezone outlet). Rujukan: FR-SHF-001; UC-SHF-01; SEQ-SHF-01.

**Acceptance Criteria**
- [ ] Sudah ada shift OPEN 409 SHIFT_ALREADY_OPEN
- [ ] Opening cash negatif 422; audit SHIFT_OPENED

**Depends on:** DB-SHF-001, BE-RBAC-001
**References:** FR-SHF-001; UC-SHF-01; SEQ-SHF-01
**Estimate:** M
**Branch:** `feature/be-shf-001-open-shift-opening-cash-business-date-me`' --label 'backend,shf,core' --assignee 'naufaldi'
gh issue create --title '[BE-SHF-002] Cash in / cash out (alasan wajib, cash.manage)' --body '**Context**
Cash in / cash out (alasan wajib, cash.manage). Rujukan: FR-SHF-002; UC-SHF-02.

**Acceptance Criteria**
- [ ] Alasan kosong 422; tanpa shift OPEN 409
- [ ] Tercatat append-only dan masuk audit

**Depends on:** BE-SHF-001
**References:** FR-SHF-002; UC-SHF-02
**Estimate:** S
**Branch:** `feature/be-shf-002-cash-in-cash-out-alasan-wajib-cash-manag`' --label 'backend,shf,core' --assignee 'naufaldi'
gh issue create --title '[BE-SHF-003] ShiftSummaryService: Expected Cash + non_cash per metode' --body '**Context**
ShiftSummaryService: Expected Cash + non_cash per metode. Rujukan: FR-SHF-003; BR-004, BR-042.

**Acceptance Criteria**
- [ ] Expected = opening + cash sales (bersih kembalian) + in − out − cash refund
- [ ] Pembayaran non-tunai tampil di non_cash dan tidak masuk Expected Cash

**Depends on:** BE-SHF-001, DB-PAY-001
**References:** FR-SHF-003; BR-004, BR-042
**Estimate:** M
**Branch:** `feature/be-shf-003-shiftsummaryservice-expected-cash-non-ca`' --label 'backend,shf,core' --assignee 'naufaldi'
gh issue create --title '[BE-SHF-004] Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit' --body '**Context**
Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit. Rujukan: FR-SHF-003; UC-SHF-03; BR-003.

**Acceptance Criteria**
- [ ] Ada order pending 409 SHIFT_HAS_PENDING_ORDERS dengan jumlah order
- [ ] Sukses menyimpan expected, actual, difference; shift CLOSED tidak menerima transaksi

**Depends on:** BE-SHF-003, BE-ORD-005
**References:** FR-SHF-003; UC-SHF-03; BR-003
**Estimate:** M
**Branch:** `feature/be-shf-004-close-shift-tolak-bila-ada-order-draft-p`' --label 'backend,shf,core' --assignee 'naufaldi'
gh issue create --title '[BE-SHF-005] GET /shifts/current + open_hours_exceeded + middleware shift.open' --body '**Context**
GET /shifts/current + open_hours_exceeded + middleware shift.open. Rujukan: FR-SHF-004; BR-026.

**Acceptance Criteria**
- [ ] open_hours_exceeded true bila terbuka > 24 jam
- [ ] Order tanpa shift OPEN ditolak 409 NO_OPEN_SHIFT

**Depends on:** BE-SHF-001
**References:** FR-SHF-004; BR-026
**Estimate:** S
**Branch:** `feature/be-shf-005-get-shifts-current-open-hours-exceeded-m`' --label 'backend,shf,core' --assignee 'naufaldi'
gh issue create --title '[FE-SHF-001] Dialog open shift (+ banner > 24 jam)' --body '**Context**
Dialog open shift (+ banner > 24 jam). Rujukan: SCR-SHF-01.

**Acceptance Criteria**
- [ ] Banner peringatan tampil saat open_hours_exceeded
- [ ] Tidak bisa masuk layar POS tanpa shift OPEN

**Depends on:** BE-SHF-005, FE-AUTH-003
**References:** SCR-SHF-01
**Estimate:** S
**Branch:** `feature/fe-shf-001-dialog-open-shift-banner-24-jam`' --label 'frontend,shf,core' --assignee 'naufaldi'
gh issue create --title '[FE-SHF-002] Dialog cash in / cash out' --body '**Context**
Dialog cash in / cash out. Rujukan: SCR-SHF-02.

**Acceptance Criteria**
- [ ] Alasan wajib divalidasi
- [ ] Saldo ringkasan ter-refresh setelah simpan

**Depends on:** BE-SHF-002
**References:** SCR-SHF-02
**Estimate:** S
**Branch:** `feature/fe-shf-002-dialog-cash-in-cash-out`' --label 'frontend,shf,core' --assignee 'naufaldi'
gh issue create --title '[FE-SHF-003] Wizard close shift + ringkasan (cetak)' --body '**Context**
Wizard close shift + ringkasan (cetak). Rujukan: SCR-SHF-03.

**Acceptance Criteria**
- [ ] Expected Cash tampil sebelum input uang fisik
- [ ] Pesan error order pending menampilkan jumlah

**Depends on:** BE-SHF-004
**References:** SCR-SHF-03
**Estimate:** M
**Branch:** `feature/fe-shf-003-wizard-close-shift-ringkasan-cetak`' --label 'frontend,shf,core' --assignee 'naufaldi'
gh issue create --title '[TEST-SHF-001] Test shift: open ganda, selisih kas, close dengan order pending, efek refund' --body '**Context**
Test shift: open ganda, selisih kas, close dengan order pending, efek refund. Rujukan: FR-SHF-001…004.

**Acceptance Criteria**
- [ ] Formula Expected Cash diuji dengan data campuran (cash, QRIS, refund)
- [ ] Konkurensi open shift ganda lulus

**Depends on:** BE-SHF-004, BE-PAY-005
**References:** FR-SHF-001…004
**Estimate:** M
**Branch:** `feature/test-shf-001-test-shift-open-ganda-selisih-kas-close`' --label 'testing,shf,core' --assignee 'naufaldi'
gh issue create --title '[DB-ORD-001] Migrasi orders, order_items, order_item_modifiers, order_sequences' --body '**Context**
Migrasi orders, order_items, order_item_modifiers, order_sequences. Rujukan: ERD §3.4.

**Acceptance Criteria**
- [ ] Kolom snapshot, CHECK table_number ⇒ DINE_IN, rounding_amount boleh negatif
- [ ] FK recipe_id ke recipes (NULL = tanpa recipe)

**Depends on:** DB-CAT-001, DB-SHF-001, DB-REC-001
**References:** ERD §3.4
**Estimate:** M
**Branch:** `feature/db-ord-001-migrasi-orders-order-items-order-item-mo`' --label 'database,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-001] OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}' --body '**Context**
OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}. Rujukan: FR-ORD-007; BR-019.

**Acceptance Criteria**
- [ ] Nomor reset per outlet per business date
- [ ] 50 order paralel tidak menghasilkan nomor ganda

**Depends on:** DB-ORD-001
**References:** FR-ORD-007; BR-019
**Estimate:** S
**Branch:** `feature/be-ord-001-ordernumbergenerator-aman-konkurensi-out`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-002] PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total' --body '**Context**
PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total. Rujukan: FR-ORD-002; BR-005, BR-036; ACT-ORD-02.

**Acceptance Criteria**
- [ ] Vektor uji: subtotal 54.000 → total 62.400 (rounding +30); diskon 10% → 56.100 (−33)
- [ ] Total > 0 tidak boleh menjadi 0 setelah pembulatan

**Depends on:** SETUP-007, BE-USER-002
**References:** FR-ORD-002; BR-005, BR-036; ACT-ORD-02
**Estimate:** M
**Branch:** `feature/be-ord-002-pricecalculator-subtotal-diskon-service`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-003] Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier' --body '**Context**
Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier. Rujukan: FR-ORD-001; UC-ORD-01; SEQ-ORD-01.

**Acceptance Criteria**
- [ ] Produk INACTIVE 422 PRODUCT_UNAVAILABLE; qty di luar 1–999 422
- [ ] Mengubah harga produk setelah order dibuat tidak mengubah order

**Depends on:** BE-ORD-001, BE-ORD-002, BE-CAT-005, BE-SHF-005, BE-REC-001, BE-REC-002
**References:** FR-ORD-001; UC-ORD-01; SEQ-ORD-01
**Estimate:** M
**Branch:** `feature/be-ord-003-create-replace-draft-snapshot-nama-sku-h`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-004] Diskon order-level (persen/nominal), batas Cashier per outlet, audit' --body '**Context**
Diskon order-level (persen/nominal), batas Cashier per outlet, audit. Rujukan: FR-ORD-003; UC-ORD-02.

**Acceptance Criteria**
- [ ] Diskon > subtotal 422 DISCOUNT_INVALID
- [ ] Cashier melebihi batas outlet 403 DISCOUNT_LIMIT_EXCEEDED

**Depends on:** BE-ORD-003
**References:** FR-ORD-003; UC-ORD-02
**Estimate:** S
**Branch:** `feature/be-ord-004-diskon-order-level-persen-nominal-batas`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-005] State machine order: confirm, reopen (guard gateway PENDING)' --body '**Context**
State machine order: confirm, reopen (guard gateway PENDING). Rujukan: FR-ORD-004; STM-ORDER; BR-028.

**Acceptance Criteria**
- [ ] Transisi tidak terdaftar 409 INVALID_STATE_TRANSITION
- [ ] Reopen ditolak bila ada payment SUCCESS; titik ekstensi guard gateway PENDING tersedia

**Depends on:** BE-ORD-003
**References:** FR-ORD-004; STM-ORDER; BR-028
**Estimate:** M
**Branch:** `feature/be-ord-005-state-machine-order-confirm-reopen-guard`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-006] Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit)' --body '**Context**
Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit). Rujukan: FR-ORD-005; UC-ORD-03; BR-025.

**Acceptance Criteria**
- [ ] Order sudah dibayar 409 "harus melalui refund"
- [ ] Cashier membatalkan order orang lain 403

**Depends on:** BE-ORD-005, BE-AUD-001
**References:** FR-ORD-005; UC-ORD-03; BR-025
**Estimate:** S
**Branch:** `feature/be-ord-006-cancel-order-draft-pending-alasan-wajib`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-007] Riwayat & detail order (Cashier hanya miliknya)' --body '**Context**
Riwayat & detail order (Cashier hanya miliknya). Rujukan: FR-ORD-006; UC-ORD-04.

**Acceptance Criteria**
- [ ] Filter tanggal (business date), status, kasir, nomor order
- [ ] Order tenant lain 404

**Depends on:** BE-ORD-003
**References:** FR-ORD-006; UC-ORD-04
**Estimate:** S
**Branch:** `feature/be-ord-007-riwayat-detail-order-cashier-hanya-milik`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[FE-ORD-001] Layout layar POS responsif (grid kategori/produk + cart)' --body '**Context**
Layout layar POS responsif (grid kategori/produk + cart). Rujukan: SCR-POS-01.

**Acceptance Criteria**
- [ ] Nyaman di HP ≥ 360 px dengan target sentuh ≥ 44 px
- [ ] Load awal < 3 detik (4G, cache dingin)

**Depends on:** BE-CAT-005, FE-SHF-001
**References:** SCR-POS-01
**Estimate:** M
**Branch:** `feature/fe-ord-001-layout-layar-pos-responsif-grid-kategori`' --label 'frontend,ord,core' --assignee 'ongki'
gh issue create --title '[FE-ORD-002] Cart store (Pinia), modifier picker, qty' --body '**Context**
Cart store (Pinia), modifier picker, qty. Rujukan: SCR-POS-01, SCR-POS-02.

**Acceptance Criteria**
- [ ] Group modifier wajib memblokir tombol simpan dengan pesan SRS
- [ ] Cart bertahan saat berpindah layar dalam sesi

**Depends on:** FE-ORD-001, BE-ORD-003
**References:** SCR-POS-01, SCR-POS-02
**Estimate:** M
**Branch:** `feature/fe-ord-002-cart-store-pinia-modifier-picker-qty`' --label 'frontend,ord,core' --assignee 'ongki'
gh issue create --title '[FE-ORD-003] Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server)' --body '**Context**
Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server). Rujukan: SCR-POS-03.

**Acceptance Criteria**
- [ ] Estimasi lokal memakai vektor uji yang sama dengan backend; angka server menjadi final
- [ ] Nomor meja hanya aktif untuk Dine In

**Depends on:** FE-ORD-002, BE-ORD-004
**References:** SCR-POS-03
**Estimate:** M
**Branch:** `feature/fe-ord-003-panel-ringkasan-tipe-order-nomor-meja-di`' --label 'frontend,ord,core' --assignee 'ongki'
gh issue create --title '[FE-ORD-004] Halaman riwayat & detail order' --body '**Context**
Halaman riwayat & detail order. Rujukan: SCR-POS-05.

**Acceptance Criteria**
- [ ] Cashier hanya melihat order miliknya
- [ ] Tombol cancel/refund tampil sesuai status dan permission

**Depends on:** BE-ORD-007
**References:** SCR-POS-05
**Estimate:** M
**Branch:** `feature/fe-ord-004-halaman-riwayat-detail-order`' --label 'frontend,ord,core' --assignee 'ongki'
gh issue create --title '[TEST-ORD-001] Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan)' --body '**Context**
Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan). Rujukan: BR-005, BR-036.

**Acceptance Criteria**
- [ ] Vektor uji ACT-ORD-02 tersimpan sebagai fixture bersama BE dan FE
- [ ] Kasus increment 0/100/500/1000 dan batas .5 diuji

**Depends on:** BE-ORD-002
**References:** BR-005, BR-036
**Estimate:** M
**Branch:** `feature/test-ord-001-unit-test-pricecalculator-table-driven-d`' --label 'testing,ord,core' --assignee 'ongki'
gh issue create --title '[TEST-ORD-002] Feature test lifecycle order & cancel' --body '**Context**
Feature test lifecycle order & cancel. Rujukan: STM-ORDER.

**Acceptance Criteria**
- [ ] Semua transisi STM-ORDER diuji (sah dan tidak sah)
- [ ] Cancel Cashier Own/Limited diuji

**Depends on:** BE-ORD-006
**References:** STM-ORDER
**Estimate:** M
**Branch:** `feature/test-ord-002-feature-test-lifecycle-order-cancel`' --label 'testing,ord,core' --assignee 'ongki'
gh issue create --title '[DB-PAY-001] Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments)' --body '**Context**
Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments). Rujukan: ERD §3.4; Lampiran SDD B.

**Acceptance Criteria**
- [ ] UNIQUE (business_id, idempotency_key) dan UNIQUE parsial SUCCESS per order
- [ ] refunds: UNIQUE(order_id), CHECK CASH ⇒ shift_id NOT NULL; created_by payments nullable

**Depends on:** DB-ORD-001
**References:** ERD §3.4; Lampiran SDD B
**Estimate:** M
**Branch:** `feature/db-pay-001-migrasi-payments-refunds-kolom-gateway-n`' --label 'database,pay,core' --assignee 'ongki'
gh issue create --title '[BE-PAY-001] Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif' --body '**Context**
Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif. Rujukan: FR-PAY-001; BR-007.

**Acceptance Criteria**
- [ ] Cash tendered < total 422; QRIS nominal ≠ total 422
- [ ] CARD/GATEWAY 422 PAYMENT_METHOD_UNAVAILABLE

**Depends on:** DB-PAY-001
**References:** FR-PAY-001; BR-007
**Estimate:** M
**Branch:** `feature/be-pay-001-interface-paymentmethod-strategy-cash-qr`' --label 'backend,pay,core' --assignee 'ongki'
gh issue create --title '[BE-PAY-002] POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik' --body '**Context**
POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik. Rujukan: FR-PAY-001; UC-PAY-01; SEQ-PAY-01.

**Acceptance Criteria**
- [ ] Key sama untuk order sama = 200 + Idempotent-Replay; untuk order lain 409
- [ ] Order bukan PENDING_PAYMENT 409; header kosong 422

**Depends on:** BE-PAY-001, BE-ORD-005
**References:** FR-PAY-001; UC-PAY-01; SEQ-PAY-01
**Estimate:** M
**Branch:** `feature/be-pay-002-post-orders-id-pay-idempotency-key-lock`' --label 'backend,pay,core' --assignee 'ongki'
gh issue create --title '[BE-PAY-003] Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit' --body '**Context**
Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit. Rujukan: ADR-002; FR-ENG-001.

**Acceptance Criteria**
- [ ] Stok kurang dan minus dilarang: rollback total, payment tidak tersimpan
- [ ] Double submit paralel menghasilkan 1 payment dan 1 set movement

**Depends on:** BE-PAY-002, BE-ENG-002
**References:** ADR-002; FR-ENG-001
**Estimate:** S
**Branch:** `feature/be-pay-003-panggil-deductstockfororder-dalam-transa`' --label 'backend,pay,core' --assignee 'ongki'
gh issue create --title '[BE-PAY-004] Builder data struk (pembulatan, metode, kembalian)' --body '**Context**
Builder data struk (pembulatan, metode, kembalian). Rujukan: FR-PAY-002; UC-PAY-02.

**Acceptance Criteria**
- [ ] Struk memuat baris pembulatan bila ada
- [ ] Cetak ulang dari riwayat memakai data tersimpan

**Depends on:** BE-PAY-002
**References:** FR-PAY-002; UC-PAY-02
**Estimate:** S
**Branch:** `feature/be-pay-004-builder-data-struk-pembulatan-metode-kem`' --label 'backend,pay,core' --assignee 'ongki'
gh issue create --title '[BE-PAY-005] Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit' --body '**Context**
Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit. Rujukan: FR-RFD-001; UC-RFD-01; SEQ-RFD-01.

**Acceptance Criteria**
- [ ] Refund tunai tanpa shift OPEN pelaku 409 NO_OPEN_SHIFT; QRIS shift_id NULL
- [ ] Refund kedua 409 ORDER_ALREADY_REFUNDED

**Depends on:** BE-PAY-002, BE-AUD-001, BE-ENG-004
**References:** FR-RFD-001; UC-RFD-01; SEQ-RFD-01
**Estimate:** M
**Branch:** `feature/be-pay-005-refund-penuh-permission-alasan-kas-ke-sh`' --label 'backend,pay,core' --assignee 'ongki'
gh issue create --title '[FE-PAY-001] Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key)' --body '**Context**
Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key). Rujukan: SCR-POS-04.

**Acceptance Criteria**
- [ ] Key dibuat sekali per dialog dan dipakai ulang saat retry
- [ ] Error stok kurang menampilkan daftar bahan

**Depends on:** BE-PAY-002, FE-ORD-003
**References:** SCR-POS-04
**Estimate:** M
**Branch:** `feature/fe-pay-001-dialog-pembayaran-numpad-nominal-cepat-k`' --label 'frontend,pay,core' --assignee 'ongki'
gh issue create --title '[FE-PAY-002] Tampilan struk + CSS print 58/80 mm' --body '**Context**
Tampilan struk + CSS print 58/80 mm. Rujukan: SCR-POS-06.

**Acceptance Criteria**
- [ ] Print browser 58 dan 80 mm rapi (uji printer thermal)
- [ ] Cetak ulang dari riwayat

**Depends on:** BE-PAY-004
**References:** SCR-POS-06
**Estimate:** M
**Branch:** `feature/fe-pay-002-tampilan-struk-css-print-58-80-mm`' --label 'frontend,pay,core' --assignee 'ongki'
gh issue create --title '[FE-PAY-003] Dialog refund (alasan, restock)' --body '**Context**
Dialog refund (alasan, restock). Rujukan: SCR-POS-07.

**Acceptance Criteria**
- [ ] Alasan wajib; restock default tidak dicentang
- [ ] Pesan 409 NO_OPEN_SHIFT jelas

**Depends on:** BE-PAY-005, FE-ORD-004
**References:** SCR-POS-07
**Estimate:** S
**Branch:** `feature/fe-pay-003-dialog-refund-alasan-restock`' --label 'frontend,pay,core' --assignee 'ongki'
gh issue create --title '[TEST-PAY-001] Test pembayaran: double submit, overpay, status invalid, kembalian, rollback' --body '**Context**
Test pembayaran: double submit, overpay, status invalid, kembalian, rollback. Rujukan: FR-PAY-001; NFR-DATA-002/003.

**Acceptance Criteria**
- [ ] 0 payment tanpa potong stok pada test kegagalan
- [ ] Retry dengan key sama tidak menggandakan payment/movement

**Depends on:** BE-PAY-003
**References:** FR-PAY-001; NFR-DATA-002/003
**Estimate:** M
**Branch:** `feature/test-pay-001-test-pembayaran-double-submit-overpay-st`' --label 'testing,pay,core' --assignee 'ongki'
gh issue create --title '[TEST-PAY-002] Test refund (kas, status, permission, restock)' --body '**Context**
Test refund (kas, status, permission, restock). Rujukan: FR-RFD-001.

**Acceptance Criteria**
- [ ] Efek ke Expected Cash shift pelaku diuji
- [ ] RETURN movement terbentuk hanya bila restock

**Depends on:** BE-PAY-005
**References:** FR-RFD-001
**Estimate:** S
**Branch:** `feature/test-pay-002-test-refund-kas-status-permission-restoc`' --label 'testing,pay,core' --assignee 'ongki'
gh issue create --title '[BE-ENG-001] RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan' --body '**Context**
RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan. Rujukan: FR-ENG-001; BR-009, BR-010.

**Acceptance Criteria**
- [ ] Bahan sama di beberapa item diagregat menjadi satu angka
- [ ] Produk tanpa recipe dilewati

**Depends on:** BE-REC-001, DB-ORD-001
**References:** FR-ENG-001; BR-009, BR-010
**Estimate:** M
**Branch:** `feature/be-eng-001-recipeusagecalculator-item-recipe-versi`' --label 'backend,eng,core' --assignee 'akmal'
gh issue create --title '[BE-ENG-002] DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten' --body '**Context**
DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten. Rujukan: ADR-002; SEQ-PAY-01.

**Acceptance Criteria**
- [ ] Dua order paralel dengan bahan sama tidak deadlock dan stok benar
- [ ] Movement SALE unit_cost memakai last_cost saat kejadian

**Depends on:** BE-ENG-001, BE-INV-002
**References:** ADR-002; SEQ-PAY-01
**Estimate:** M
**Branch:** `feature/be-eng-002-deductstockfororder-lock-urut-ingredient`' --label 'backend,eng,core' --assignee 'akmal'
gh issue create --title '[BE-ENG-003] Kebijakan stok minus: stock_shortage di respons dan audit' --body '**Context**
Kebijakan stok minus: stock_shortage di respons dan audit. Rujukan: FR-ENG-002; BR-011.

**Acceptance Criteria**
- [ ] allow_negative_stock = true: bayar sukses dan stock_shortage terisi
- [ ] false: 422 INSUFFICIENT_STOCK dengan details.ingredients

**Depends on:** BE-ENG-002
**References:** FR-ENG-002; BR-011
**Estimate:** S
**Branch:** `feature/be-eng-003-kebijakan-stok-minus-stock-shortage-di-r`' --label 'backend,eng,core' --assignee 'akmal'
gh issue create --title '[BE-ENG-004] Jalur restock refund (movement RETURN dari movement SALE order)' --body '**Context**
Jalur restock refund (movement RETURN dari movement SALE order). Rujukan: FR-RFD-001; BR-012.

**Acceptance Criteria**
- [ ] Restock membuat RETURN positif per bahan dengan reference REFUND:id
- [ ] Tanpa restock tidak ada movement baru

**Depends on:** BE-ENG-002
**References:** FR-RFD-001; BR-012
**Estimate:** S
**Branch:** `feature/be-eng-004-jalur-restock-refund-movement-return-dar`' --label 'backend,eng,core' --assignee 'akmal'
gh issue create --title '[BE-ENG-005] GET /orders/{id}/stock-trace' --body '**Context**
GET /orders/{id}/stock-trace. Rujukan: FR-ENG-003.

**Acceptance Criteria**
- [ ] Menampilkan movement SALE dan RETURN milik order
- [ ] Butuh inventory.view; order tenant lain 404

**Depends on:** BE-ENG-002
**References:** FR-ENG-003
**Estimate:** S
**Branch:** `feature/be-eng-005-get-orders-id-stock-trace`' --label 'backend,eng,core' --assignee 'akmal'
gh issue create --title '[INT-ENG-001] Integration test Sale → Payment → Recipe → Movement → Balance' --body '**Context**
Integration test Sale → Payment → Recipe → Movement → Balance. Rujukan: NFR-DATA-001/002.

**Acceptance Criteria**
- [ ] balance = SUM(movements) untuk semua bahan pasca skenario
- [ ] Skenario lengkap order→bayar→refund restock lulus

**Depends on:** BE-PAY-003, BE-ENG-002
**References:** NFR-DATA-001/002
**Estimate:** M
**Branch:** `feature/int-eng-001-integration-test-sale-payment-recipe-mov`' --label 'integration,eng,core' --assignee 'akmal'
gh issue create --title '[TEST-ENG-001] Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel' --body '**Context**
Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel. Rujukan: BR-013.

**Acceptance Criteria**
- [ ] Recipe versi baru tidak mengubah movement order lama
- [ ] Produk tanpa recipe tampil di laporan no recipe

**Depends on:** INT-ENG-001
**References:** BR-013
**Estimate:** M
**Branch:** `feature/test-eng-001-edge-case-tanpa-recipe-recipe-diubah-set`' --label 'testing,eng,core' --assignee 'akmal'
gh issue create --title '[FE-ENG-001] Indikator stok menipis/habis di layar POS' --body '**Context**
Indikator stok menipis/habis di layar POS. Rujukan: SCR-POS-01.

**Acceptance Criteria**
- [ ] Indikator tampil tanpa memblokir penjualan
- [ ] Data berasal dari endpoint stok (tanpa HPP)

**Depends on:** BE-ENG-003, FE-ORD-002
**References:** SCR-POS-01
**Estimate:** S
**Branch:** `feature/fe-eng-001-indikator-stok-menipis-habis-di-layar-po`' --label 'frontend,eng,core' --assignee 'akmal'
gh issue create --title '[DB-PUR-001] Migrasi suppliers, purchases, purchase_items, document_sequences' --body '**Context**
Migrasi suppliers, purchases, purchase_items, document_sequences. Rujukan: ERD §3.6; BR-045…050.

**Acceptance Criteria**
- [ ] purchases UNIQUE (outlet_id, code); status CHECK DRAFT/RECEIVED/CANCELLED
- [ ] document_sequences PK (outlet_id, doc_type, seq_date) dengan lock FOR UPDATE

**Depends on:** DB-ING-001, DB-TEN-002
**References:** ERD §3.6; BR-045…050
**Estimate:** M
**Branch:** `feature/db-pur-001-migrasi-suppliers-purchases-purchase-ite`' --label 'database,pur,core' --assignee 'naufaldi'
gh issue create --title '[BE-PUR-001] CRUD supplier (soft delete, INACTIVE tidak bisa dipilih)' --body '**Context**
CRUD supplier (soft delete, INACTIVE tidak bisa dipilih). Rujukan: FR-PUR-001; UC-PUR-01.

**Acceptance Criteria**
- [ ] Supplier INACTIVE ditolak saat membuat purchase
- [ ] Permission suppliers.* sesuai matriks (Owner, Manager, Inventory)

**Depends on:** DB-PUR-001, BE-RBAC-001
**References:** FR-PUR-001; UC-PUR-01
**Estimate:** S
**Branch:** `feature/be-pur-001-crud-supplier-soft-delete-inactive-tidak`' --label 'backend,pur,core' --assignee 'naufaldi'
gh issue create --title '[BE-PUR-002] Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO' --body '**Context**
Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO. Rujukan: FR-PUR-002; UC-PUR-02; BR-046, BR-049.

**Acceptance Criteria**
- [ ] quantity_base = quantity × faktor (kg→g, L→ml = 1000)
- [ ] Purchase RECEIVED tidak bisa diubah 409 INVALID_STATE_TRANSITION

**Depends on:** BE-PUR-001, BE-ING-001, BE-INV-003
**References:** FR-PUR-002; UC-PUR-02; BR-046, BR-049
**Estimate:** M
**Branch:** `feature/be-pur-002-purchase-draft-buat-ubah-item-satuan-bel`' --label 'backend,pur,core' --assignee 'naufaldi'
gh issue create --title '[BE-PUR-003] Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten' --body '**Context**
Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten. Rujukan: FR-PUR-003; UC-PUR-03; SEQ-PUR-01; BR-045, BR-047.

**Acceptance Criteria**
- [ ] Receive menambah stok base unit dan last_cost = harga per satuan beli ÷ faktor
- [ ] Receive ganda tidak menggandakan movement (409 atau replay)

**Depends on:** BE-PUR-002, BE-INV-002
**References:** FR-PUR-003; UC-PUR-03; SEQ-PUR-01; BR-045, BR-047
**Estimate:** M
**Branch:** `feature/be-pur-003-receive-purchase-movement-purchase-updat`' --label 'backend,pur,core' --assignee 'naufaldi'
gh issue create --title '[BE-PUR-004] GET daftar & detail purchase (filter status, supplier, tanggal)' --body '**Context**
GET daftar & detail purchase (filter status, supplier, tanggal). Rujukan: FR-PUR-004; UC-PUR-04.

**Acceptance Criteria**
- [ ] Filter dan paginasi bekerja; data tenant lain 404
- [ ] Permission purchases.view

**Depends on:** BE-PUR-002
**References:** FR-PUR-004; UC-PUR-04
**Estimate:** S
**Branch:** `feature/be-pur-004-get-daftar-detail-purchase-filter-status`' --label 'backend,pur,core' --assignee 'naufaldi'
gh issue create --title '[FE-PUR-001] Halaman supplier' --body '**Context**
Halaman supplier. Rujukan: SCR-PUR-01.

**Acceptance Criteria**
- [ ] CRUD supplier dengan state loading/empty/error
- [ ] Aksi dibatasi v-can suppliers.*

**Depends on:** BE-PUR-001, FE-AUTH-003
**References:** SCR-PUR-01
**Estimate:** S
**Branch:** `feature/fe-pur-001-halaman-supplier`' --label 'frontend,pur,core' --assignee 'naufaldi'
gh issue create --title '[FE-PUR-002] Form & daftar purchase (satuan beli, konversi tampil)' --body '**Context**
Form & daftar purchase (satuan beli, konversi tampil). Rujukan: SCR-PUR-02.

**Acceptance Criteria**
- [ ] Konversi ke base unit tampil sebelum simpan
- [ ] Purchase RECEIVED tampil read-only

**Depends on:** BE-PUR-002, FE-PUR-001
**References:** SCR-PUR-02
**Estimate:** M
**Branch:** `feature/fe-pur-002-form-daftar-purchase-satuan-beli-konvers`' --label 'frontend,pur,core' --assignee 'naufaldi'
gh issue create --title '[FE-PUR-003] Alur terima barang (konfirmasi, hasil stok baru)' --body '**Context**
Alur terima barang (konfirmasi, hasil stok baru). Rujukan: SCR-PUR-03.

**Acceptance Criteria**
- [ ] Konfirmasi menampilkan ringkasan stok sebelum dan sesudah
- [ ] Klik ganda aman

**Depends on:** BE-PUR-003, FE-PUR-002
**References:** SCR-PUR-03
**Estimate:** M
**Branch:** `feature/fe-pur-003-alur-terima-barang-konfirmasi-hasil-stok`' --label 'frontend,pur,core' --assignee 'naufaldi'
gh issue create --title '[TEST-PUR-001] Test purchasing: konversi, last_cost, idempotensi, permission' --body '**Context**
Test purchasing: konversi, last_cost, idempotensi, permission. Rujukan: FR-PUR-001…004.

**Acceptance Criteria**
- [ ] Semua BR-045…050 punya test
- [ ] balance = SUM(movements) tetap benar setelah receive

**Depends on:** BE-PUR-003
**References:** FR-PUR-001…004
**Estimate:** M
**Branch:** `feature/test-pur-001-test-purchasing-konversi-last-cost-idemp`' --label 'testing,pur,core' --assignee 'naufaldi'
gh issue create --title '[BE-RPT-001] Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund)' --body '**Context**
Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund). Rujukan: FR-RPT-001; UC-RPT-01.

**Acceptance Criteria**
- [ ] Angka cocok dengan fixture; REFUNDED dipisah; pembulatan baris terpisah
- [ ] Periode memakai business_date; outlet di luar akses 403

**Depends on:** BE-PAY-002, BE-ORD-007
**References:** FR-RPT-001; UC-RPT-01
**Estimate:** M
**Branch:** `feature/be-rpt-001-laporan-sales-gross-diskon-service-pajak`' --label 'backend,rpt,core' --assignee 'naufaldi'
gh issue create --title '[BE-RPT-003] Laporan payment per metode' --body '**Context**
Laporan payment per metode. Rujukan: FR-RPT-003.

**Acceptance Criteria**
- [ ] Total Cash dan QRIS cocok dengan payments
- [ ] Cashier hanya ringkasan miliknya

**Depends on:** BE-RPT-001
**References:** FR-RPT-003
**Estimate:** S
**Branch:** `feature/be-rpt-003-laporan-payment-per-metode`' --label 'backend,rpt,core' --assignee 'naufaldi'
gh issue create --title '[FE-RPT-001] Halaman laporan sales & payment' --body '**Context**
Halaman laporan sales & payment. Rujukan: SCR-RPT-01.

**Acceptance Criteria**
- [ ] Filter tanggal/outlet/kasir/metode
- [ ] State empty dan loading ada

**Depends on:** BE-RPT-003, FE-AUTH-003
**References:** SCR-RPT-01
**Estimate:** M
**Branch:** `feature/fe-rpt-001-halaman-laporan-sales-payment`' --label 'frontend,rpt,core' --assignee 'naufaldi'
gh issue create --title '[TEST-RPT-001] Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone' --body '**Context**
Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone. Rujukan: FR-RPT-001…005.

**Acceptance Criteria**
- [ ] Business date lintas tengah malam benar
- [ ] Isolasi tenant pada semua endpoint laporan

**Depends on:** BE-RPT-003
**References:** FR-RPT-001…005
**Estimate:** S
**Branch:** `feature/test-rpt-001-test-laporan-sales-payment-angka-vs-fixt`' --label 'testing,rpt,core' --assignee 'akmal'
gh issue create --title '[FE-PWA-001] Manifest + service worker (vite-plugin-pwa), cache aset statis saja' --body '**Context**
Manifest + service worker (vite-plugin-pwa), cache aset statis saja. Rujukan: FR-PWA-001; F-17.

**Acceptance Criteria**
- [ ] Installable (Lighthouse PWA lulus)
- [ ] Respons API tidak pernah di-cache service worker

**Depends on:** FE-ORD-001
**References:** FR-PWA-001; F-17
**Estimate:** M
**Branch:** `feature/fe-pwa-001-manifest-service-worker-vite-plugin-pwa`' --label 'frontend,pwa,core' --assignee 'akmal'
gh issue create --title '[FE-PWA-002] Banner offline + aksi tulis ditolak + retry aman (key sama)' --body '**Context**
Banner offline + aksi tulis ditolak + retry aman (key sama). Rujukan: FR-PWA-001.

**Acceptance Criteria**
- [ ] Offline menampilkan banner dan menolak aksi tulis
- [ ] Retry bayar memakai Idempotency-Key yang sama

**Depends on:** FE-PWA-001, BE-PAY-002
**References:** FR-PWA-001
**Estimate:** S
**Branch:** `feature/fe-pwa-002-banner-offline-aksi-tulis-ditolak-retry`' --label 'frontend,pwa,core' --assignee 'akmal'
gh issue create --title '[DB-SEED-001] Seeder demo (business, outlet, menu, recipe, stok, supplier)' --body '**Context**
Seeder demo (business, outlet, menu, recipe, stok, supplier). Rujukan: TD §8.

**Acceptance Criteria**
- [ ] Satu perintah menghasilkan data demo siap dipakai UAT
- [ ] Aman dijalankan ulang di staging

**Depends on:** BE-REC-001, BE-INV-003, BE-PUR-001
**References:** TD §8
**Estimate:** S
**Branch:** `feature/db-seed-001-seeder-demo-business-outlet-menu-recipe`' --label 'database,seed,core' --assignee 'akmal'
gh issue create --title '[TEST-E2E-001] E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift' --body '**Context**
E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift. Rujukan: F-01…F-10.

**Acceptance Criteria**
- [ ] Alur utama lulus di staging dan CI
- [ ] Stok setelah bayar sesuai recipe

**Depends on:** FE-PAY-002, INT-ENG-001, FE-SHF-003
**References:** F-01…F-10
**Estimate:** M
**Branch:** `feature/test-e2e-001-e2e-playwright-register-menu-recipe-shif`' --label 'testing,e2e,core' --assignee 'akmal'
gh issue create --title '[TEST-E2E-002] E2E inventory: opening → purchase receive → adjustment → waste' --body '**Context**
E2E inventory: opening → purchase receive → adjustment → waste. Rujukan: F-11, F-12, F-20.

**Acceptance Criteria**
- [ ] Stok dan last_cost berubah sesuai purchase
- [ ] Movement tercatat di riwayat

**Depends on:** FE-PUR-003, FE-INV-004
**References:** F-11, F-12, F-20
**Estimate:** M
**Branch:** `feature/test-e2e-002-e2e-inventory-opening-purchase-receive-a`' --label 'testing,e2e,core' --assignee 'akmal'
gh issue create --title '[TEST-SEC-001] Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload' --body '**Context**
Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload. Rujukan: NFR-SEC-001…007.

**Acceptance Criteria**
- [ ] Semua butir checklist lulus atau punya tiket perbaikan
- [ ] Webhook/gateway ditandai out of scope Phase 1

**Depends on:** TEST-AUTH-001, BE-PAY-002
**References:** NFR-SEC-001…007
**Estimate:** M
**Branch:** `feature/test-sec-001-checklist-keamanan-tenant-throttling-cor`' --label 'testing,sec,core' --assignee 'akmal'
gh issue create --title '[TEST-PERF-001] Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements)' --body '**Context**
Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements). Rujukan: NFR-PERF-003, NFR-PERF-004.

**Acceptance Criteria**
- [ ] p95 bayar + potong stok < 800 ms (order ≤ 10 item)
- [ ] 0 deadlock dan balance = SUM(movements)

**Depends on:** INT-ENG-001
**References:** NFR-PERF-003, NFR-PERF-004
**Estimate:** M
**Branch:** `feature/test-perf-001-load-test-20-pembayaran-paralel-bahan-sa`' --label 'testing,perf,core' --assignee 'akmal'
gh issue create --title '[DEPLOY-001] Environment production, HTTPS, backup harian + uji restore' --body '**Context**
Environment production, HTTPS, backup harian + uji restore. Rujukan: NFR-REL-002; DO §3.

**Acceptance Criteria**
- [ ] Backup harian berjalan dan restore berhasil diuji sekali
- [ ] Variabel rahasia hanya di environment server

**Depends on:** SETUP-008
**References:** NFR-REL-002; DO §3
**Estimate:** M
**Branch:** `feature/deploy-001-environment-production-https-backup-hari`' --label 'devops,setup,core' --assignee 'ongki'
gh issue create --title '[DEPLOY-002] Deploy production + smoke test' --body '**Context**
Deploy production + smoke test. Rujukan: DO §2.

**Acceptance Criteria**
- [ ] Smoke test (login, buka shift, order, bayar, tutup shift) lulus
- [ ] Rollback ke rilis sebelumnya terdokumentasi dan teruji di staging

**Depends on:** DEPLOY-001, TEST-E2E-001, TEST-E2E-002, TEST-SEC-001
**References:** DO §2
**Estimate:** S
**Branch:** `feature/deploy-002-deploy-production-smoke-test`' --label 'devops,setup,core' --assignee 'ongki'
gh issue create --title '[BE-AUD-002] GET /audit-logs + filter (tanggal, user, aksi, resource)' --body '**Context**
GET /audit-logs + filter (tanggal, user, aksi, resource). Rujukan: FR-AUD-002; UC-AUD-01.

**Acceptance Criteria**
- [ ] Filter tanggal, user, aksi, resource bekerja
- [ ] Manager hanya melihat outlet miliknya

**Depends on:** BE-AUD-001, BE-RBAC-001
**References:** FR-AUD-002; UC-AUD-01
**Estimate:** S
**Branch:** `feature/be-aud-002-get-audit-logs-filter-tanggal-user-aksi`' --label 'backend,aud,r1.1' --assignee 'naufaldi'
gh issue create --title '[FE-AUD-001] Halaman audit log' --body '**Context**
Halaman audit log. Rujukan: UC-AUD-01; SCR-AUD-01.

**Acceptance Criteria**
- [ ] Daftar memakai paginasi dan filter
- [ ] Order/aksi sensitif menampilkan before/after

**Depends on:** BE-AUD-002, FE-AUTH-003
**References:** UC-AUD-01; SCR-AUD-01
**Estimate:** S
**Branch:** `feature/fe-aud-001-halaman-audit-log`' --label 'frontend,aud,r1.1' --assignee 'naufaldi'
gh issue create --title '[BE-REC-003] CRUD recipe per modifier option (F-18)' --body '**Context**
CRUD recipe per modifier option (F-18). Rujukan: FR-REC-002; UC-REC-02.

**Acceptance Criteria**
- [ ] Qty > 0 dalam base unit; perubahan tercatat audit
- [ ] Hanya recipes.manage yang boleh mengubah

**Depends on:** DB-REC-001, BE-CAT-004, BE-ING-001
**References:** FR-REC-002; UC-REC-02
**Estimate:** S
**Branch:** `feature/be-rec-003-crud-recipe-per-modifier-option-f-18`' --label 'backend,rec,r1.1' --assignee 'akmal'
gh issue create --title '[FE-REC-002] Editor recipe modifier option (F-18)' --body '**Context**
Editor recipe modifier option (F-18). Rujukan: SCR-CAT-03.

**Acceptance Criteria**
- [ ] Editor muncul di UI modifier option
- [ ] Validasi qty dan bahan ganda sama dengan recipe produk

**Depends on:** BE-REC-003, FE-CAT-003
**References:** SCR-CAT-03
**Estimate:** S
**Branch:** `feature/fe-rec-002-editor-recipe-modifier-option-f-18`' --label 'frontend,rec,r1.1' --assignee 'akmal'
gh issue create --title '[FE-REC-003] Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP' --body '**Context**
Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP. Rujukan: UC-REC-03.

**Acceptance Criteria**
- [ ] Role tanpa recipes.manage tidak melihat tombol ubah
- [ ] HPP tidak ada di respons maupun UI

**Depends on:** BE-REC-001, FE-AUTH-003
**References:** UC-REC-03
**Estimate:** XS
**Branch:** `feature/fe-rec-003-tampilan-recipe-read-only-untuk-inventor`' --label 'frontend,rec,r1.1' --assignee 'akmal'
gh issue create --title '[DB-INV-002] Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS)' --body '**Context**
Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS). Rujukan: ERD §3.3; BR-017.

**Acceptance Criteria**
- [ ] Hanya 1 opname IN_PROGRESS per outlet di level DB
- [ ] UNIQUE (opname_id, ingredient_id)

**Depends on:** DB-INV-001
**References:** ERD §3.3; BR-017
**Estimate:** S
**Branch:** `feature/db-inv-002-migrasi-stock-opnames-stock-opname-items`' --label 'database,inv,r1.1' --assignee 'akmal'
gh issue create --title '[BE-INV-007] Opname: mulai (snapshot), input hitungan, batalkan' --body '**Context**
Opname: mulai (snapshot), input hitungan, batalkan. Rujukan: FR-INV-006; UC-INV-05; SEQ-INV-02.

**Acceptance Criteria**
- [ ] Mulai saat ada opname berjalan 409 OPNAME_IN_PROGRESS
- [ ] Hitungan negatif 422

**Depends on:** DB-INV-002, BE-INV-004
**References:** FR-INV-006; UC-INV-05; SEQ-INV-02
**Estimate:** M
**Branch:** `feature/be-inv-007-opname-mulai-snapshot-input-hitungan-bat`' --label 'backend,inv,r1.1' --assignee 'akmal'
gh issue create --title '[BE-INV-008] Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id)' --body '**Context**
Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id). Rujukan: BR-017; STM-OPNAME.

**Acceptance Criteria**
- [ ] Penjualan selama penghitungan tidak hilang (selisih dihitung terhadap snapshot)
- [ ] Submit pada opname bukan IN_PROGRESS 409 OPNAME_NOT_ACTIVE

**Depends on:** BE-INV-007
**References:** BR-017; STM-OPNAME
**Estimate:** S
**Branch:** `feature/be-inv-008-opname-submit-selisih-vs-snapshot-ditera`' --label 'backend,inv,r1.1' --assignee 'akmal'
gh issue create --title '[FE-INV-005] UI opname (mulai, input hitungan, review selisih, submit)' --body '**Context**
UI opname (mulai, input hitungan, review selisih, submit). Rujukan: SCR-INV-05.

**Acceptance Criteria**
- [ ] Layar nyaman di HP (target sentuh ≥ 44 px)
- [ ] Review selisih tampil sebelum submit

**Depends on:** BE-INV-008
**References:** SCR-INV-05
**Estimate:** M
**Branch:** `feature/fe-inv-005-ui-opname-mulai-input-hitungan-review-se`' --label 'frontend,inv,r1.1' --assignee 'akmal'
gh issue create --title '[TEST-INV-003] Test opname (snapshot, selisih, konkurensi dengan penjualan)' --body '**Context**
Test opname (snapshot, selisih, konkurensi dengan penjualan). Rujukan: FR-INV-006.

**Acceptance Criteria**
- [ ] Skenario penjualan selama opname lulus
- [ ] Submit ganda ditolak

**Depends on:** BE-INV-008
**References:** FR-INV-006
**Estimate:** S
**Branch:** `feature/test-inv-003-test-opname-snapshot-selisih-konkurensi`' --label 'testing,inv,r1.1' --assignee 'akmal'
gh issue create --title '[BE-KIT-001] Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready' --body '**Context**
Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready. Rujukan: FR-KIT-001; UC-KIT-01; BR-033.

**Acceptance Criteria**
- [ ] Respons tanpa harga dan data pembayaran
- [ ] Order REFUNDED otomatis hilang dari antrian

**Depends on:** BE-ORD-005
**References:** FR-KIT-001; UC-KIT-01; BR-033
**Estimate:** S
**Branch:** `feature/be-kit-001-antrian-dapur-get-order-completed-kitche`' --label 'backend,kit,r1.1' --assignee 'ongki'
gh issue create --title '[FE-KIT-001] Halaman antrian dapur (polling 10 detik)' --body '**Context**
Halaman antrian dapur (polling 10 detik). Rujukan: SCR-KIT-01.

**Acceptance Criteria**
- [ ] Tombol Siap mengubah NEW menjadi READY
- [ ] State empty antrian tampil

**Depends on:** BE-KIT-001
**References:** SCR-KIT-01
**Estimate:** S
**Branch:** `feature/fe-kit-001-halaman-antrian-dapur-polling-10-detik`' --label 'frontend,kit,r1.1' --assignee 'ongki'
gh issue create --title '[BE-ENG-006] Pemakaian bahan modifier option ditambahkan ke agregat (F-18)' --body '**Context**
Pemakaian bahan modifier option ditambahkan ke agregat (F-18). Rujukan: FR-REC-002; FR-ENG-001.

**Acceptance Criteria**
- [ ] Pemakaian modifier dihitung dari recipe option aktif saat pembayaran
- [ ] Modifier tanpa recipe tidak memotong stok

**Depends on:** BE-ENG-001, BE-REC-003
**References:** FR-REC-002; FR-ENG-001
**Estimate:** S
**Branch:** `feature/be-eng-006-pemakaian-bahan-modifier-option-ditambah`' --label 'backend,eng,r1.1' --assignee 'akmal'
gh issue create --title '[BE-PUR-005] Cancel purchase DRAFT + partial receive (purchase_receipts)' --body '**Context**
Cancel purchase DRAFT + partial receive (purchase_receipts). Rujukan: FR-PUR-005; UC-PUR-05.

**Acceptance Criteria**
- [ ] Cancel hanya DRAFT dengan alasan wajib
- [ ] Partial receive memotong sisa dan status mengikuti jumlah diterima

**Depends on:** BE-PUR-003
**References:** FR-PUR-005; UC-PUR-05
**Estimate:** M
**Branch:** `feature/be-pur-005-cancel-purchase-draft-partial-receive-pu`' --label 'backend,pur,r1.1' --assignee 'naufaldi'
gh issue create --title '[BE-RPT-002] Laporan produk + daftar no recipe' --body '**Context**
Laporan produk + daftar no recipe. Rujukan: FR-RPT-002; BR-010.

**Acceptance Criteria**
- [ ] Produk tanpa recipe aktif tampil di daftar
- [ ] Revenue dan qty cocok dengan fixture

**Depends on:** BE-RPT-001
**References:** FR-RPT-002; BR-010
**Estimate:** S
**Branch:** `feature/be-rpt-002-laporan-produk-daftar-no-recipe`' --label 'backend,rpt,r1.1' --assignee 'naufaldi'
gh issue create --title '[BE-RPT-004] Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian' --body '**Context**
Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian. Rujukan: FR-RPT-004.

**Acceptance Criteria**
- [ ] Stok minus muncul pada laporan
- [ ] Waste menampilkan biaya (Kitchen tanpa nilai)

**Depends on:** BE-INV-006, BE-INV-005, BE-PUR-003
**References:** FR-RPT-004
**Estimate:** M
**Branch:** `feature/be-rpt-004-laporan-inventory-stok-movement-per-tipe`' --label 'backend,rpt,r1.1' --assignee 'naufaldi'
gh issue create --title '[BE-RPT-005] Dashboard permission-aware (timezone outlet, cache TTL pendek)' --body '**Context**
Dashboard permission-aware (timezone outlet, cache TTL pendek). Rujukan: FR-RPT-005; UC-RPT-02.

**Acceptance Criteria**
- [ ] Widget sesuai permission; Cashier hanya data shift sendiri
- [ ] Rata-rata order = net sales ÷ jumlah order

**Depends on:** BE-RPT-001, BE-INV-006, BE-SHF-005
**References:** FR-RPT-005; UC-RPT-02
**Estimate:** M
**Branch:** `feature/be-rpt-005-dashboard-permission-aware-timezone-outl`' --label 'backend,rpt,r1.1' --assignee 'naufaldi'
gh issue create --title '[FE-RPT-002] Halaman laporan produk, inventory, waste' --body '**Context**
Halaman laporan produk, inventory, waste. Rujukan: SCR-RPT-02.

**Acceptance Criteria**
- [ ] Daftar no recipe dan stok minus menonjol
- [ ] Nilai biaya disembunyikan sesuai role

**Depends on:** BE-RPT-004, BE-RPT-002
**References:** SCR-RPT-02
**Estimate:** M
**Branch:** `feature/fe-rpt-002-halaman-laporan-produk-inventory-waste`' --label 'frontend,rpt,r1.1' --assignee 'naufaldi'
gh issue create --title '[FE-RPT-003] Dashboard' --body '**Context**
Dashboard. Rujukan: SCR-RPT-03.

**Acceptance Criteria**
- [ ] Widget mengikuti respons permission
- [ ] Polling/refresh tidak membebani (cache TTL)

**Depends on:** BE-RPT-005
**References:** SCR-RPT-03
**Estimate:** M
**Branch:** `feature/fe-rpt-003-dashboard`' --label 'frontend,rpt,r1.1' --assignee 'naufaldi'
gh issue create --title '[BE-RPT-006] Export CSV sales dan stock movement (stream, reports.export)' --body '**Context**
Export CSV sales dan stock movement (stream, reports.export). Rujukan: FR-RPT-006; UC-RPT-03.

**Acceptance Criteria**
- [ ] Filter sama dengan layar; hanya business/outlet user
- [ ] Tanpa permission 403

**Depends on:** BE-RPT-001, BE-INV-006
**References:** FR-RPT-006; UC-RPT-03
**Estimate:** S
**Branch:** `feature/be-rpt-006-export-csv-sales-dan-stock-movement-stre`' --label 'backend,rpt,r1.1' --assignee 'naufaldi'
gh issue create --title '[DOC-001] Dokumentasi API (OpenAPI/Scribe)' --body '**Context**
Dokumentasi API (OpenAPI/Scribe). Rujukan: SDD §4.

**Acceptance Criteria**
- [ ] Semua endpoint Phase 1 terdokumentasi dengan contoh
- [ ] Dokumen dapat diakses di staging

**Depends on:** BE-RPT-003, BE-PUR-004
**References:** SDD §4
**Estimate:** M
**Branch:** `feature/doc-001-dokumentasi-api-openapi-scribe`' --label 'docs,setup,r1.1' --assignee 'akmal'
gh issue create --title '[DOC-002] README + runbook dev (Laragon, Supabase, queue worker)' --body '**Context**
README + runbook dev (Laragon, Supabase, queue worker). Rujukan: TD §6.

**Acceptance Criteria**
- [ ] Developer baru bisa menjalankan proyek dari README tanpa bertanya
- [ ] Runbook queue worker dan scheduler tersedia

**Depends on:** SETUP-002
**References:** TD §6
**Estimate:** S
**Branch:** `feature/doc-002-readme-runbook-dev-laragon-supabase-queu`' --label 'docs,setup,r1.1' --assignee 'akmal'
