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
- [ ] Payload memuat before/after JSON, user, outlet, IP, action (dari katalog SRS FR-AUD-001), reason

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
- [ ] Data valid mengembalikan 201 dan membuat business, outlet, Owner, user_outlets; audit `BUSINESS_REGISTERED`
- [ ] Email ganda dan password lemah mengembalikan 422 dengan pesan SRS FR-AUTH-001

**Depends on:** DB-TEN-002, DB-RBAC-001, BE-AUD-001
**References:** FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01
**Estimate:** M
**Branch:** `feature/be-auth-001-post-auth-register-business-business-out`' --label 'backend,auth,core' --assignee 'ongki'
gh issue create --title '[SETUP-007] Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test' --body '**Context**
Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test. Rujukan: ADR-004; SRS §1.4.

**Acceptance Criteria**
- [ ] `Money::halfUp()` memberi hasil benar untuk kasus .5 positif dan nol
- [ ] Enum OrderStatus, MovementType, PaymentMethod, ShiftStatus tersedia dan sama dengan CHECK di ERD

**Depends on:** SETUP-005
**References:** ADR-004; SRS §1.4
**Estimate:** S
**Branch:** `feature/setup-007-helper-money-quantity-bigint-rupiah-deci`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[BE-AUTH-002] Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED' --body '**Context**
Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED. Rujukan: FR-AUTH-002; UC-AUTH-02; BR-023.

**Acceptance Criteria**
- [ ] Gagal ke-5 mengunci akun 15 menit (423 ACCOUNT_LOCKED); audit `LOGIN`/`LOGIN_FAILED`
- [ ] Akun INACTIVE 403; login gagal email tak dikenal tercatat dengan user_id NULL

**Depends on:** BE-AUTH-001
**References:** FR-AUTH-002; UC-AUTH-02; BR-023
**Estimate:** M
**Branch:** `feature/be-auth-002-login-logout-sanctum-spa-throttle-lockou`' --label 'backend,auth,core' --assignee 'ongki'
gh issue create --title '[BE-AUTH-004] /me mengembalikan outlet + permission dengan scope' --body '**Context**
/me mengembalikan outlet + permission dengan scope. Rujukan: FR-AUTH-004.

**Acceptance Criteria**
- [ ] Respons memuat role, outlets, permissions[{code, scope}] sesuai SDD §4.5
- [ ] Owner otomatis punya semua outlet business

**Depends on:** BE-AUTH-002, BE-RBAC-001
**References:** FR-AUTH-004
**Estimate:** S
**Branch:** `feature/be-auth-004-me-mengembalikan-outlet-permission-denga`' --label 'backend,auth,core' --assignee 'ongki'
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
gh issue create --title '[BE-ORD-005] State machine order: confirm, reopen (guard gateway PENDING)' --body '**Context**
State machine order: confirm, reopen (guard gateway PENDING). Rujukan: FR-ORD-004; STM-ORDER; BR-028.

**Acceptance Criteria**
- [ ] Transisi tidak terdaftar 409 INVALID_STATE_TRANSITION
- [ ] Reopen ditolak bila ada payment SUCCESS; titik ekstensi guard gateway PENDING tersedia

**Depends on:** BE-ORD-003
**References:** FR-ORD-004; STM-ORDER; BR-028
**Estimate:** M
**Branch:** `feature/be-ord-005-state-machine-order-confirm-reopen-guard`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[DB-PAY-001] Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments)' --body '**Context**
Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments). Rujukan: ERD §3.4; Lampiran SDD B.

**Acceptance Criteria**
- [ ] UNIQUE (business_id, idempotency_key) dan UNIQUE parsial SUCCESS per order
- [ ] refunds: UNIQUE(order_id), CHECK CASH ⇒ shift_id NOT NULL; created_by payments nullable

**Depends on:** DB-ORD-001
**References:** ERD §3.4; Lampiran SDD B
**Estimate:** M
**Branch:** `feature/db-pay-001-migrasi-payments-refunds-kolom-gateway-n`' --label 'database,pay,core' --assignee 'ongki'
gh issue create --title '[SETUP-008] Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite)' --body '**Context**
Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite). Rujukan: NFR-SEC-003; TD §5.

**Acceptance Criteria**
- [ ] Login lewat FE staging berhasil dengan cookie Sanctum (Secure, HttpOnly, SameSite)
- [ ] Sertifikat HTTPS aktif dan HTTP dialihkan

**Depends on:** SETUP-004
**References:** NFR-SEC-003; TD §5
**Estimate:** M
**Branch:** `feature/setup-008-staging-https-fe-dan-api-satu-parent-dom`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[TEST-AUTH-001] Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint)' --body '**Context**
Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint). Rujukan: NFR-SEC-001; ADR-003.

**Acceptance Criteria**
- [ ] Setiap endpoint terdaftar dites lintas tenant dan mengembalikan 404
- [ ] Dataset otomatis bertambah saat endpoint baru didaftarkan (DoD tiap modul)

**Depends on:** BE-USER-001
**References:** NFR-SEC-001; ADR-003
**Estimate:** M
**Branch:** `feature/test-auth-001-suite-isolasi-tenant-dataset-driven-busi`' --label 'testing,auth,core' --assignee 'ongki'
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
- [ ] Key sama untuk order sama = 200 + Idempotent-Replay; untuk order lain 409; audit `PAYMENT_CREATED` after-commit
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
gh issue create --title '[SETUP-002] Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment' --body '**Context**
Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment. Rujukan: ADR-006; TD §6.

**Acceptance Criteria**
- [ ] Koneksi memakai direct atau session pooler (bukan transaction pooler) dan `SELECT ... FOR UPDATE` bekerja dalam transaksi
- [ ] `.env.example` memuat semua variabel (DB, CACHE_STORE=database, QUEUE_CONNECTION=database, Supabase Storage) tanpa rahasia

**Depends on:** SETUP-001
**References:** ADR-006; TD §6
**Estimate:** S
**Branch:** `feature/setup-002-konfigurasi-postgresql-supabase-direct-s`' --label 'setup,core' --assignee 'ongki'
gh issue create --title '[TEST-RBAC-001] Test matriks role × endpoint' --body '**Context**
Test matriks role × endpoint. Rujukan: SRS §2.1.

**Acceptance Criteria**
- [ ] Tiap sel matriks permission diuji (ijin/tolak)
- [ ] Aturan Own/Limited diuji untuk Cashier, Manager, Inventory

**Depends on:** BE-USER-001
**References:** SRS §2.1
**Estimate:** M
**Branch:** `feature/test-rbac-001-test-matriks-role-endpoint`' --label 'testing,rbac,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-004] Diskon order-level (persen/nominal), batas Cashier per outlet, audit' --body '**Context**
Diskon order-level (persen/nominal), batas Cashier per outlet, audit. Rujukan: FR-ORD-003; UC-ORD-02.

**Acceptance Criteria**
- [ ] Diskon > subtotal 422 DISCOUNT_INVALID; audit `DISCOUNT_APPLIED`
- [ ] Cashier melebihi batas outlet 403 DISCOUNT_LIMIT_EXCEEDED

**Depends on:** BE-ORD-003
**References:** FR-ORD-003; UC-ORD-02
**Estimate:** S
**Branch:** `feature/be-ord-004-diskon-order-level-persen-nominal-batas`' --label 'backend,ord,core' --assignee 'ongki'
gh issue create --title '[BE-ORD-006] Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit)' --body '**Context**
Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit). Rujukan: FR-ORD-005; UC-ORD-03; BR-025.

**Acceptance Criteria**
- [ ] Order sudah dibayar 409 "harus melalui refund"; audit `ORDER_CANCELLED`
- [ ] Cashier membatalkan order orang lain 403

**Depends on:** BE-ORD-005, BE-AUD-001
**References:** FR-ORD-005; UC-ORD-03; BR-025
**Estimate:** S
**Branch:** `feature/be-ord-006-cancel-order-draft-pending-alasan-wajib`' --label 'backend,ord,core' --assignee 'ongki'
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
gh issue create --title '[BE-PAY-005] Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit' --body '**Context**
Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit. Rujukan: FR-RFD-001; UC-RFD-01; SEQ-RFD-01.

**Acceptance Criteria**
- [ ] Refund tunai tanpa shift OPEN pelaku 409 NO_OPEN_SHIFT; QRIS shift_id NULL; audit `REFUND_CREATED`
- [ ] Refund kedua 409 ORDER_ALREADY_REFUNDED

**Depends on:** BE-PAY-002, BE-AUD-001, BE-ENG-004
**References:** FR-RFD-001; UC-RFD-01; SEQ-RFD-01
**Estimate:** M
**Branch:** `feature/be-pay-005-refund-penuh-permission-alasan-kas-ke-sh`' --label 'backend,pay,core' --assignee 'ongki'
gh issue create --title '[FE-PAY-002] Tampilan struk + CSS print 58/80 mm' --body '**Context**
Tampilan struk + CSS print 58/80 mm. Rujukan: SCR-POS-06.

**Acceptance Criteria**
- [ ] Print browser 58 dan 80 mm rapi (uji printer thermal)
- [ ] Cetak ulang dari riwayat

**Depends on:** BE-PAY-004
**References:** SCR-POS-06
**Estimate:** M
**Branch:** `feature/fe-pay-002-tampilan-struk-css-print-58-80-mm`' --label 'frontend,pay,core' --assignee 'ongki'
gh issue create --title '[DEPLOY-001] Environment production, HTTPS, backup harian + uji restore' --body '**Context**
Environment production, HTTPS, backup harian + uji restore. Rujukan: NFR-REL-002; DO §3.

**Acceptance Criteria**
- [ ] Backup harian berjalan dan restore berhasil diuji sekali
- [ ] Variabel rahasia hanya di environment server

**Depends on:** SETUP-008
**References:** NFR-REL-002; DO §3
**Estimate:** M
**Branch:** `feature/deploy-001-environment-production-https-backup-hari`' --label 'devops,setup,core' --assignee 'ongki'
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
gh issue create --title '[FE-PAY-001] Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key)' --body '**Context**
Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key). Rujukan: SCR-POS-04.

**Acceptance Criteria**
- [ ] Key dibuat sekali per dialog dan dipakai ulang saat retry
- [ ] Error stok kurang menampilkan daftar bahan

**Depends on:** BE-PAY-002, FE-ORD-003
**References:** SCR-POS-04
**Estimate:** M
**Branch:** `feature/fe-pay-001-dialog-pembayaran-numpad-nominal-cepat-k`' --label 'frontend,pay,core' --assignee 'ongki'
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
gh issue create --title '[DEPLOY-002] Deploy production + smoke test' --body '**Context**
Deploy production + smoke test. Rujukan: DO §2.

**Acceptance Criteria**
- [ ] Smoke test (login, buka shift, order, bayar, tutup shift) lulus
- [ ] Rollback ke rilis sebelumnya terdokumentasi dan teruji di staging

**Depends on:** DEPLOY-001, TEST-E2E-001, TEST-E2E-002, TEST-SEC-001
**References:** DO §2
**Estimate:** S
**Branch:** `feature/deploy-002-deploy-production-smoke-test`' --label 'devops,setup,core' --assignee 'ongki'
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
