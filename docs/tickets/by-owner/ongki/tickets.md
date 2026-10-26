# Tiket Ongki

> Sumber: `tickets/tickets.md` (isi tiket identik; file ini hanya difilter dan diurutkan per sprint). Reviewer silang: Ongki ← Akmal ← Naufaldi ← Ongki. Aturan kerja: `docs/11_DoR_DoD.md`.

| | |
|---|---|
| Total | 45 tiket |
| Core | 43 tiket, 35,50 hari-orang |
| Rilis 1.1 | 2 tiket, 1,00 hari-orang |
| Kapasitas tugas | 35,25 hari (core) |

## Urutan kerja

Urutan mengikuti rencana sprint; **sprint adalah rencana awal** dan diperbarui saat sprint planning. Jangan mulai tiket yang dependensinya belum Done.

| # | ID | Task | Est | Sprint | Depends on |
|---|----|------|-----|--------|-----------|
| 1 | SETUP-001 | Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template | S | S1 (26 Okt–6 Nov) | — |
| 2 | SETUP-005 | Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1 | M | S1 (26 Okt–6 Nov) | SETUP-001 |
| 3 | DB-TEN-001 | Migrasi businesses, outlets, users, user_outlets | M | S1 (26 Okt–6 Nov) | SETUP-005 |
| 4 | DB-TEN-002 | Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet | M | S1 (26 Okt–6 Nov) | DB-TEN-001 |
| 5 | DB-RBAC-001 | Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own | M | S1 (26 Okt–6 Nov) | DB-TEN-001 |
| 6 | DB-AUD-001 | Migrasi audit_logs + trigger append-only | S | S1 (26 Okt–6 Nov) | DB-TEN-001 |
| 7 | BE-AUD-001 | AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue | S | S1 (26 Okt–6 Nov) | DB-AUD-001, SETUP-005 |
| 8 | BE-RBAC-001 | Middleware permission + outlet.context, Policy aturan Limited/Own | M | S1 (26 Okt–6 Nov) | DB-RBAC-001, DB-TEN-002 |
| 9 | BE-AUTH-001 | POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi) | M | S1 (26 Okt–6 Nov) | DB-TEN-002, DB-RBAC-001, BE-AUD-001 |
| 10 | SETUP-007 | Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test | S | S2 (9–20 Nov) | SETUP-005 |
| 11 | BE-AUTH-002 | Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED | M | S2 (9–20 Nov) | BE-AUTH-001 |
| 12 | BE-AUTH-004 | /me mengembalikan outlet + permission dengan scope | S | S2 (9–20 Nov) | BE-AUTH-002, BE-RBAC-001 |
| 13 | DB-ORD-001 | Migrasi orders, order_items, order_item_modifiers, order_sequences | M | S2 (9–20 Nov) | DB-CAT-001, DB-SHF-001, DB-REC-001 |
| 14 | BE-ORD-001 | OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut} | S | S2 (9–20 Nov) | DB-ORD-001 |
| 15 | BE-ORD-002 | PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total | M | S2 (9–20 Nov) | SETUP-007, BE-USER-002 |
| 16 | BE-ORD-003 | Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier | M | S2 (9–20 Nov) | BE-ORD-001, BE-ORD-002, BE-CAT-005, BE-SHF-005, BE-REC-001, BE-REC-002 |
| 17 | BE-ORD-005 | State machine order: confirm, reopen (guard gateway PENDING) | M | S2 (9–20 Nov) | BE-ORD-003 |
| 18 | DB-PAY-001 | Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments) | M | S2 (9–20 Nov) | DB-ORD-001 |
| 19 | SETUP-008 | Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite) | M | S3 (23 Nov–4 Des) | SETUP-004 |
| 20 | TEST-AUTH-001 | Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint) | M | S3 (23 Nov–4 Des) | BE-USER-001 |
| 21 | BE-ORD-007 | Riwayat & detail order (Cashier hanya miliknya) | S | S3 (23 Nov–4 Des) | BE-ORD-003 |
| 22 | FE-ORD-001 | Layout layar POS responsif (grid kategori/produk + cart) | M | S3 (23 Nov–4 Des) | BE-CAT-005, FE-SHF-001 |
| 23 | FE-ORD-002 | Cart store (Pinia), modifier picker, qty | M | S3 (23 Nov–4 Des) | FE-ORD-001, BE-ORD-003 |
| 24 | BE-PAY-001 | Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif | M | S3 (23 Nov–4 Des) | DB-PAY-001 |
| 25 | BE-PAY-002 | POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik | M | S3 (23 Nov–4 Des) | BE-PAY-001, BE-ORD-005 |
| 26 | BE-PAY-003 | Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit | S | S3 (23 Nov–4 Des) | BE-PAY-002, BE-ENG-002 |
| 27 | BE-PAY-004 | Builder data struk (pembulatan, metode, kembalian) | S | S3 (23 Nov–4 Des) | BE-PAY-002 |
| 28 | SETUP-002 | Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment | S | S4 (7–18 Des) | SETUP-001 |
| 29 | TEST-RBAC-001 | Test matriks role × endpoint | M | S4 (7–18 Des) | BE-USER-001 |
| 30 | BE-ORD-004 | Diskon order-level (persen/nominal), batas Cashier per outlet, audit | S | S4 (7–18 Des) | BE-ORD-003 |
| 31 | BE-ORD-006 | Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit) | S | S4 (7–18 Des) | BE-ORD-005, BE-AUD-001 |
| 32 | FE-ORD-003 | Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server) | M | S4 (7–18 Des) | FE-ORD-002, BE-ORD-004 |
| 33 | FE-ORD-004 | Halaman riwayat & detail order | M | S4 (7–18 Des) | BE-ORD-007 |
| 34 | BE-PAY-005 | Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit | M | S4 (7–18 Des) | BE-PAY-002, BE-AUD-001, BE-ENG-004 |
| 35 | FE-PAY-002 | Tampilan struk + CSS print 58/80 mm | M | S4 (7–18 Des) | BE-PAY-004 |
| 36 | DEPLOY-001 | Environment production, HTTPS, backup harian + uji restore | M | S4 (7–18 Des) | SETUP-008 |
| 37 | TEST-ORD-001 | Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan) | M | S5 (21–30 Des) | BE-ORD-002 |
| 38 | TEST-ORD-002 | Feature test lifecycle order & cancel | M | S5 (21–30 Des) | BE-ORD-006 |
| 39 | FE-PAY-001 | Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key) | M | S5 (21–30 Des) | BE-PAY-002, FE-ORD-003 |
| 40 | FE-PAY-003 | Dialog refund (alasan, restock) | S | S5 (21–30 Des) | BE-PAY-005, FE-ORD-004 |
| 41 | TEST-PAY-001 | Test pembayaran: double submit, overpay, status invalid, kembalian, rollback | M | S5 (21–30 Des) | BE-PAY-003 |
| 42 | TEST-PAY-002 | Test refund (kas, status, permission, restock) | S | S5 (21–30 Des) | BE-PAY-005 |
| 43 | DEPLOY-002 | Deploy production + smoke test | S | S5 — cadangan 30 Des | DEPLOY-001, TEST-E2E-001, TEST-E2E-002, TEST-SEC-001 |
| 44 | BE-KIT-001 | Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready | S | Rilis 1.1 (Jan 2027) | BE-ORD-005 |
| 45 | FE-KIT-001 | Halaman antrian dapur (polling 10 detik) | S | Rilis 1.1 (Jan 2027) | BE-KIT-001 |

## Detail tiket

## S1 (26 Okt–6 Nov)

### SETUP-001 — Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template

| Field | Value |
|-------|-------|
| ID | SETUP-001 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | S |
| Status | todo |
| Depends on |  |
| References | ADR-001; TD §1, §7 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-001-init-monorepo-backend-laravel-13-fronten |
| PR | |

**Context**
Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template. Rujukan: ADR-001; TD §1, §7.

**Acceptance Criteria**
- [ ] Repo punya /backend dan /frontend, `php artisan serve` dan `npm run dev` jalan di Laragon (PHP 8.4)
- [ ] Branch main/develop/feature dan PR template (memuat ID tiket) tersedia
- [ ] Pest (+ pest-plugin-arch) terpasang dan satu test contoh lulus lewat `./vendor/bin/pest`

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### SETUP-005 — Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1

| Field | Value |
|-------|-------|
| ID | SETUP-005 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-001 |
| References | TD §2, §2.4; SDD §4.2 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-005-skeleton-backend-app-modules-pola-action |
| PR | |

**Context**
Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1. Rujukan: TD §2, §2.4; SDD §4.2.

**Acceptance Criteria**
- [ ] Respons sukses dan error mengikuti envelope SDD §4.2 (`success`, `data`, `error.code`)
- [ ] Exception 404/403/409/422 dipetakan ke kode error SDD §4.3
- [ ] Modul baru cukup didaftarkan di `config/modules.php`; routes, policy, dan event-nya termuat lewat `ModuleServiceProvider` tanpa mengubah `routes/api.php` (TD §2.4)
- [ ] `route:cache` dan `config:cache` berjalan tanpa error; satu modul contoh (`Platform/Tenant`) dengan route ping termuat lewat `ModuleServiceProvider`

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### DB-TEN-001 — Migrasi businesses, outlets, users, user_outlets

| Field | Value |
|-------|-------|
| ID | DB-TEN-001 |
| Type | database |
| Module | TEN |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-005 |
| References | ERD §3.1; BR-022 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | database, ten, core |
| Branch | feature/db-ten-001-migrasi-businesses-outlets-users-user-ou |
| PR | |

**Context**
Migrasi businesses, outlets, users, user_outlets. Rujukan: ERD §3.1; BR-022.

**Acceptance Criteria**
- [ ] Semua kolom, CHECK, dan UNIQUE sesuai ERD §3.1
- [ ] Rollback migrasi bersih
- [ ] Migrasi `users` bawaan Laravel dihapus (diganti skema ERD); migrasi framework (sessions, cache, jobs, failed_jobs) dan Spatie diberi nama berprefix modul (`platform_`, `rbac_`), dan tabel framework dihitung terpisah dari 40 tabel domain

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-TEN-003

---

### DB-TEN-002 — Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet

| Field | Value |
|-------|-------|
| ID | DB-TEN-002 |
| Type | database |
| Module | TEN |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-TEN-001 |
| References | ADR-003; FR-USER-002 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | database, ten, core |
| Branch | feature/db-ten-002-trait-belongstobusiness-global-scope-aut |
| PR | |

**Context**
Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet. Rujukan: ADR-003; FR-USER-002.

**Acceptance Criteria**
- [ ] Query model tenant otomatis terfilter business_id
- [ ] Insert otomatis mengisi business_id; data business lain mengembalikan 404

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-TEN-001, TC-TEN-002

---

### DB-RBAC-001 — Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own

| Field | Value |
|-------|-------|
| ID | DB-RBAC-001 |
| Type | database |
| Module | RBAC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-TEN-001 |
| References | ADR-005; SRS §2.1 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | database, rbac, core |
| Branch | feature/db-rbac-001-install-spatie-permission-teams-business |
| PR | |

**Context**
Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own. Rujukan: ADR-005; SRS §2.1.

**Acceptance Criteria**
- [ ] 5 role dan semua permission SRS §2.1 ter-seed per business saat register
- [ ] Config scope (`OWN_ONLY`, `LIMITED`) terbaca untuk /me

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-RBAC-001

---

### DB-AUD-001 — Migrasi audit_logs + trigger append-only

| Field | Value |
|-------|-------|
| ID | DB-AUD-001 |
| Type | database |
| Module | AUD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DB-TEN-001 |
| References | ADR-007; BR-020 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | database, aud, core |
| Branch | feature/db-aud-001-migrasi-audit-logs-trigger-append-only |
| PR | |

**Context**
Migrasi audit_logs + trigger append-only. Rujukan: ADR-007; BR-020.

**Acceptance Criteria**
- [ ] UPDATE dan DELETE pada audit_logs ditolak trigger DB
- [ ] Index sesuai ERD §4

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-AUD-002

---

### BE-AUD-001 — AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue

| Field | Value |
|-------|-------|
| ID | BE-AUD-001 |
| Type | backend |
| Module | AUD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DB-AUD-001, SETUP-005 |
| References | FR-AUD-001 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | backend, aud, core |
| Branch | feature/be-aud-001-auditlogger-log-action-resource-before-a |
| PR | |

**Context**
AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue. Rujukan: FR-AUD-001.

**Acceptance Criteria**
- [ ] Pemanggilan di dalam transaksi baru tertulis setelah commit (afterCommit)
- [ ] Payload memuat before/after JSON, user, outlet, IP, action (dari katalog SRS FR-AUD-001), reason

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUD-001

---

### BE-RBAC-001 — Middleware permission + outlet.context, Policy aturan Limited/Own

| Field | Value |
|-------|-------|
| ID | BE-RBAC-001 |
| Type | backend |
| Module | RBAC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-RBAC-001, DB-TEN-002 |
| References | FR-USER-002; BR-024, BR-025 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | backend, rbac, core |
| Branch | feature/be-rbac-001-middleware-permission-outlet-context-pol |
| PR | |

**Context**
Middleware permission + outlet.context, Policy aturan Limited/Own. Rujukan: FR-USER-002; BR-024, BR-025.

**Acceptance Criteria**
- [ ] Tanpa permission = 403; X-Outlet-Id di luar user_outlets = 403
- [ ] Policy Cashier hanya order sendiri; Manager hanya mengelola Cashier/Inventory/Kitchen

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RBAC-002, TC-RBAC-003

---

### BE-AUTH-001 — POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi)

| Field | Value |
|-------|-------|
| ID | BE-AUTH-001 |
| Type | backend |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-TEN-002, DB-RBAC-001, BE-AUD-001 |
| References | FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | backend, auth, core |
| Branch | feature/be-auth-001-post-auth-register-business-business-out |
| PR | |

**Context**
POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi). Rujukan: FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01.

**Acceptance Criteria**
- [ ] Data valid mengembalikan 201 dan membuat business, outlet, Owner, user_outlets; audit `BUSINESS_REGISTERED`
- [ ] Email ganda dan password lemah mengembalikan 422 dengan pesan SRS FR-AUTH-001

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-001, TC-AUTH-002, TC-AUTH-003

---

## S2 (9–20 Nov)

### SETUP-007 — Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test

| Field | Value |
|-------|-------|
| ID | SETUP-007 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | S |
| Status | todo |
| Depends on | SETUP-005 |
| References | ADR-004; SRS §1.4 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | setup, core |
| Branch | feature/setup-007-helper-money-quantity-bigint-rupiah-deci |
| PR | |

**Context**
Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test. Rujukan: ADR-004; SRS §1.4.

**Acceptance Criteria**
- [ ] `Money::halfUp()` memberi hasil benar untuk kasus .5 positif dan nol
- [ ] Enum OrderStatus, MovementType, PaymentMethod, ShiftStatus tersedia dan sama dengan CHECK di ERD

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
TC-ORD-001

---

### BE-AUTH-002 — Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED

| Field | Value |
|-------|-------|
| ID | BE-AUTH-002 |
| Type | backend |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-AUTH-001 |
| References | FR-AUTH-002; UC-AUTH-02; BR-023 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | backend, auth, core |
| Branch | feature/be-auth-002-login-logout-sanctum-spa-throttle-lockou |
| PR | |

**Context**
Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED. Rujukan: FR-AUTH-002; UC-AUTH-02; BR-023.

**Acceptance Criteria**
- [ ] Gagal ke-5 mengunci akun 15 menit (423 ACCOUNT_LOCKED); audit `LOGIN`/`LOGIN_FAILED`
- [ ] Akun INACTIVE 403; login gagal email tak dikenal tercatat dengan user_id NULL

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-004, TC-AUTH-005, TC-AUTH-006

---

### BE-AUTH-004 — /me mengembalikan outlet + permission dengan scope

| Field | Value |
|-------|-------|
| ID | BE-AUTH-004 |
| Type | backend |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-AUTH-002, BE-RBAC-001 |
| References | FR-AUTH-004 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | backend, auth, core |
| Branch | feature/be-auth-004-me-mengembalikan-outlet-permission-denga |
| PR | |

**Context**
/me mengembalikan outlet + permission dengan scope. Rujukan: FR-AUTH-004.

**Acceptance Criteria**
- [ ] Respons memuat role, outlets, permissions[{code, scope}] sesuai SDD §4.5
- [ ] Owner otomatis punya semua outlet business

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-007, TC-AUTH-008

---

### DB-ORD-001 — Migrasi orders, order_items, order_item_modifiers, order_sequences

| Field | Value |
|-------|-------|
| ID | DB-ORD-001 |
| Type | database |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-CAT-001, DB-SHF-001, DB-REC-001 |
| References | ERD §3.4 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | database, ord, core |
| Branch | feature/db-ord-001-migrasi-orders-order-items-order-item-mo |
| PR | |

**Context**
Migrasi orders, order_items, order_item_modifiers, order_sequences. Rujukan: ERD §3.4.

**Acceptance Criteria**
- [ ] Kolom snapshot, CHECK table_number ⇒ DINE_IN, rounding_amount boleh negatif
- [ ] FK recipe_id ke recipes (NULL = tanpa recipe)

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-ORD-009

---

### BE-ORD-001 — OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}

| Field | Value |
|-------|-------|
| ID | BE-ORD-001 |
| Type | backend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DB-ORD-001 |
| References | FR-ORD-007; BR-019 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | backend, ord, core |
| Branch | feature/be-ord-001-ordernumbergenerator-aman-konkurensi-out |
| PR | |

**Context**
OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}. Rujukan: FR-ORD-007; BR-019.

**Acceptance Criteria**
- [ ] Nomor reset per outlet per business date
- [ ] 50 order paralel tidak menghasilkan nomor ganda

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-005

---

### BE-ORD-002 — PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total

| Field | Value |
|-------|-------|
| ID | BE-ORD-002 |
| Type | backend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-007, BE-USER-002 |
| References | FR-ORD-002; BR-005, BR-036; ACT-ORD-02 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | backend, ord, core |
| Branch | feature/be-ord-002-pricecalculator-subtotal-diskon-service |
| PR | |

**Context**
PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total. Rujukan: FR-ORD-002; BR-005, BR-036; ACT-ORD-02.

**Acceptance Criteria**
- [ ] Vektor uji: subtotal 54.000 → total 62.400 (rounding +30); diskon 10% → 56.100 (−33)
- [ ] Total > 0 tidak boleh menjadi 0 setelah pembulatan

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-001, TC-ORD-002

---

### BE-ORD-003 — Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier

| Field | Value |
|-------|-------|
| ID | BE-ORD-003 |
| Type | backend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-ORD-001, BE-ORD-002, BE-CAT-005, BE-SHF-005, BE-REC-001, BE-REC-002 |
| References | FR-ORD-001; UC-ORD-01; SEQ-ORD-01 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | backend, ord, core |
| Branch | feature/be-ord-003-create-replace-draft-snapshot-nama-sku-h |
| PR | |

**Context**
Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier. Rujukan: FR-ORD-001; UC-ORD-01; SEQ-ORD-01.

**Acceptance Criteria**
- [ ] Produk INACTIVE 422 PRODUCT_UNAVAILABLE; qty di luar 1–999 422
- [ ] Mengubah harga produk setelah order dibuat tidak mengubah order

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-006, TC-ORD-009

---

### BE-ORD-005 — State machine order: confirm, reopen (guard gateway PENDING)

| Field | Value |
|-------|-------|
| ID | BE-ORD-005 |
| Type | backend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-ORD-003 |
| References | FR-ORD-004; STM-ORDER; BR-028 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | backend, ord, core |
| Branch | feature/be-ord-005-state-machine-order-confirm-reopen-guard |
| PR | |

**Context**
State machine order: confirm, reopen (guard gateway PENDING). Rujukan: FR-ORD-004; STM-ORDER; BR-028.

**Acceptance Criteria**
- [ ] Transisi tidak terdaftar 409 INVALID_STATE_TRANSITION
- [ ] Reopen ditolak bila ada payment SUCCESS; titik ekstensi guard gateway PENDING tersedia

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-007, TC-ORD-010

---

### DB-PAY-001 — Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments)

| Field | Value |
|-------|-------|
| ID | DB-PAY-001 |
| Type | database |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-ORD-001 |
| References | ERD §3.4; Lampiran SDD B |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S2 |
| Labels | database, pay, core |
| Branch | feature/db-pay-001-migrasi-payments-refunds-kolom-gateway-n |
| PR | |

**Context**
Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments). Rujukan: ERD §3.4; Lampiran SDD B.

**Acceptance Criteria**
- [ ] UNIQUE (business_id, idempotency_key) dan UNIQUE parsial SUCCESS per order
- [ ] refunds: UNIQUE(order_id), CHECK CASH ⇒ shift_id NOT NULL; created_by payments nullable

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-PAY-004

---

## S3 (23 Nov–4 Des)

### SETUP-008 — Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite)

| Field | Value |
|-------|-------|
| ID | SETUP-008 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-004 |
| References | NFR-SEC-003; TD §5 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | setup, core |
| Branch | feature/setup-008-staging-https-fe-dan-api-satu-parent-dom |
| PR | |

**Context**
Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite). Rujukan: NFR-SEC-003; TD §5.

**Acceptance Criteria**
- [ ] Login lewat FE staging berhasil dengan cookie Sanctum (Secure, HttpOnly, SameSite)
- [ ] Sertifikat HTTPS aktif dan HTTP dialihkan

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### TEST-AUTH-001 — Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint)

| Field | Value |
|-------|-------|
| ID | TEST-AUTH-001 |
| Type | testing |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-USER-001 |
| References | NFR-SEC-001; ADR-003 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | testing, auth, core |
| Branch | feature/test-auth-001-suite-isolasi-tenant-dataset-driven-busi |
| PR | |

**Context**
Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint). Rujukan: NFR-SEC-001; ADR-003.

**Acceptance Criteria**
- [ ] Setiap endpoint terdaftar dites lintas tenant dan mengembalikan 404
- [ ] Dataset otomatis bertambah saat endpoint baru didaftarkan (DoD tiap modul)

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-TEN-001

---

### BE-ORD-007 — Riwayat & detail order (Cashier hanya miliknya)

| Field | Value |
|-------|-------|
| ID | BE-ORD-007 |
| Type | backend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ORD-003 |
| References | FR-ORD-006; UC-ORD-04 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | backend, ord, core |
| Branch | feature/be-ord-007-riwayat-detail-order-cashier-hanya-milik |
| PR | |

**Context**
Riwayat & detail order (Cashier hanya miliknya). Rujukan: FR-ORD-006; UC-ORD-04.

**Acceptance Criteria**
- [ ] Filter tanggal (business date), status, kasir, nomor order
- [ ] Order tenant lain 404

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-008

---

### FE-ORD-001 — Layout layar POS responsif (grid kategori/produk + cart)

| Field | Value |
|-------|-------|
| ID | FE-ORD-001 |
| Type | frontend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-CAT-005, FE-SHF-001 |
| References | SCR-POS-01 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | frontend, ord, core |
| Branch | feature/fe-ord-001-layout-layar-pos-responsif-grid-kategori |
| PR | |

**Context**
Layout layar POS responsif (grid kategori/produk + cart). Rujukan: SCR-POS-01.

**Acceptance Criteria**
- [ ] Nyaman di HP ≥ 360 px dengan target sentuh ≥ 44 px
- [ ] Load awal < 3 detik (4G, cache dingin)

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-001

---

### FE-ORD-002 — Cart store (Pinia), modifier picker, qty

| Field | Value |
|-------|-------|
| ID | FE-ORD-002 |
| Type | frontend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | FE-ORD-001, BE-ORD-003 |
| References | SCR-POS-01, SCR-POS-02 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | frontend, ord, core |
| Branch | feature/fe-ord-002-cart-store-pinia-modifier-picker-qty |
| PR | |

**Context**
Cart store (Pinia), modifier picker, qty. Rujukan: SCR-POS-01, SCR-POS-02.

**Acceptance Criteria**
- [ ] Group modifier wajib memblokir tombol simpan dengan pesan SRS
- [ ] Cart bertahan saat berpindah layar dalam sesi

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-006

---

### BE-PAY-001 — Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif

| Field | Value |
|-------|-------|
| ID | BE-PAY-001 |
| Type | backend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-PAY-001 |
| References | FR-PAY-001; BR-007 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | backend, pay, core |
| Branch | feature/be-pay-001-interface-paymentmethod-strategy-cash-qr |
| PR | |

**Context**
Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif. Rujukan: FR-PAY-001; BR-007.

**Acceptance Criteria**
- [ ] Cash tendered < total 422; QRIS nominal ≠ total 422
- [ ] CARD/GATEWAY 422 PAYMENT_METHOD_UNAVAILABLE

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-001, TC-PAY-002, TC-PAY-003

---

### BE-PAY-002 — POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik

| Field | Value |
|-------|-------|
| ID | BE-PAY-002 |
| Type | backend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PAY-001, BE-ORD-005 |
| References | FR-PAY-001; UC-PAY-01; SEQ-PAY-01 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | backend, pay, core |
| Branch | feature/be-pay-002-post-orders-id-pay-idempotency-key-lock |
| PR | |

**Context**
POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik. Rujukan: FR-PAY-001; UC-PAY-01; SEQ-PAY-01.

**Acceptance Criteria**
- [ ] Key sama untuk order sama = 200 + Idempotent-Replay; untuk order lain 409; audit `PAYMENT_CREATED` after-commit
- [ ] Order bukan PENDING_PAYMENT 409; header kosong 422

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-004, TC-PAY-005, TC-PAY-007

---

### BE-PAY-003 — Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit

| Field | Value |
|-------|-------|
| ID | BE-PAY-003 |
| Type | backend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-PAY-002, BE-ENG-002 |
| References | ADR-002; FR-ENG-001 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | backend, pay, core |
| Branch | feature/be-pay-003-panggil-deductstockfororder-dalam-transa |
| PR | |

**Context**
Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit. Rujukan: ADR-002; FR-ENG-001.

**Acceptance Criteria**
- [ ] Stok kurang dan minus dilarang: rollback total, payment tidak tersimpan
- [ ] Double submit paralel menghasilkan 1 payment dan 1 set movement

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-006, TC-PAY-008

---

### BE-PAY-004 — Builder data struk (pembulatan, metode, kembalian)

| Field | Value |
|-------|-------|
| ID | BE-PAY-004 |
| Type | backend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-PAY-002 |
| References | FR-PAY-002; UC-PAY-02 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S3 |
| Labels | backend, pay, core |
| Branch | feature/be-pay-004-builder-data-struk-pembulatan-metode-kem |
| PR | |

**Context**
Builder data struk (pembulatan, metode, kembalian). Rujukan: FR-PAY-002; UC-PAY-02.

**Acceptance Criteria**
- [ ] Struk memuat baris pembulatan bila ada
- [ ] Cetak ulang dari riwayat memakai data tersimpan

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-014

---

## S4 (7–18 Des)

### SETUP-002 — Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment

| Field | Value |
|-------|-------|
| ID | SETUP-002 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | S |
| Status | todo |
| Depends on | SETUP-001 |
| References | ADR-006; TD §6 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | setup, core |
| Branch | feature/setup-002-konfigurasi-postgresql-supabase-direct-s |
| PR | |

**Context**
Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment. Rujukan: ADR-006; TD §6.

**Acceptance Criteria**
- [ ] Koneksi memakai direct atau session pooler (bukan transaction pooler) dan `SELECT ... FOR UPDATE` bekerja dalam transaksi
- [ ] `.env.example` memuat semua variabel (DB, CACHE_STORE=database, QUEUE_CONNECTION=database, Supabase Storage) tanpa rahasia

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### TEST-RBAC-001 — Test matriks role × endpoint

| Field | Value |
|-------|-------|
| ID | TEST-RBAC-001 |
| Type | testing |
| Module | RBAC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-USER-001 |
| References | SRS §2.1 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | testing, rbac, core |
| Branch | feature/test-rbac-001-test-matriks-role-endpoint |
| PR | |

**Context**
Test matriks role × endpoint. Rujukan: SRS §2.1.

**Acceptance Criteria**
- [ ] Tiap sel matriks permission diuji (ijin/tolak)
- [ ] Aturan Own/Limited diuji untuk Cashier, Manager, Inventory

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-RBAC-001

---

### BE-ORD-004 — Diskon order-level (persen/nominal), batas Cashier per outlet, audit

| Field | Value |
|-------|-------|
| ID | BE-ORD-004 |
| Type | backend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ORD-003 |
| References | FR-ORD-003; UC-ORD-02 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | backend, ord, core |
| Branch | feature/be-ord-004-diskon-order-level-persen-nominal-batas |
| PR | |

**Context**
Diskon order-level (persen/nominal), batas Cashier per outlet, audit. Rujukan: FR-ORD-003; UC-ORD-02.

**Acceptance Criteria**
- [ ] Diskon > subtotal 422 DISCOUNT_INVALID; audit `DISCOUNT_APPLIED`
- [ ] Cashier melebihi batas outlet 403 DISCOUNT_LIMIT_EXCEEDED

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-003, TC-ORD-004

---

### BE-ORD-006 — Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit)

| Field | Value |
|-------|-------|
| ID | BE-ORD-006 |
| Type | backend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ORD-005, BE-AUD-001 |
| References | FR-ORD-005; UC-ORD-03; BR-025 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | backend, ord, core |
| Branch | feature/be-ord-006-cancel-order-draft-pending-alasan-wajib |
| PR | |

**Context**
Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit). Rujukan: FR-ORD-005; UC-ORD-03; BR-025.

**Acceptance Criteria**
- [ ] Order sudah dibayar 409 "harus melalui refund"; audit `ORDER_CANCELLED`
- [ ] Cashier membatalkan order orang lain 403

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-008

---

### FE-ORD-003 — Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server)

| Field | Value |
|-------|-------|
| ID | FE-ORD-003 |
| Type | frontend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | FE-ORD-002, BE-ORD-004 |
| References | SCR-POS-03 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | frontend, ord, core |
| Branch | feature/fe-ord-003-panel-ringkasan-tipe-order-nomor-meja-di |
| PR | |

**Context**
Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server). Rujukan: SCR-POS-03.

**Acceptance Criteria**
- [ ] Estimasi lokal memakai vektor uji yang sama dengan backend; angka server menjadi final
- [ ] Nomor meja hanya aktif untuk Dine In

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-001, TC-ORD-002, TC-ORD-009

---

### FE-ORD-004 — Halaman riwayat & detail order

| Field | Value |
|-------|-------|
| ID | FE-ORD-004 |
| Type | frontend |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-ORD-007 |
| References | SCR-POS-05 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | frontend, ord, core |
| Branch | feature/fe-ord-004-halaman-riwayat-detail-order |
| PR | |

**Context**
Halaman riwayat & detail order. Rujukan: SCR-POS-05.

**Acceptance Criteria**
- [ ] Cashier hanya melihat order miliknya
- [ ] Tombol cancel/refund tampil sesuai status dan permission

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-008

---

### BE-PAY-005 — Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit

| Field | Value |
|-------|-------|
| ID | BE-PAY-005 |
| Type | backend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PAY-002, BE-AUD-001, BE-ENG-004 |
| References | FR-RFD-001; UC-RFD-01; SEQ-RFD-01 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | backend, pay, core |
| Branch | feature/be-pay-005-refund-penuh-permission-alasan-kas-ke-sh |
| PR | |

**Context**
Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit. Rujukan: FR-RFD-001; UC-RFD-01; SEQ-RFD-01.

**Acceptance Criteria**
- [ ] Refund tunai tanpa shift OPEN pelaku 409 NO_OPEN_SHIFT; QRIS shift_id NULL; audit `REFUND_CREATED`
- [ ] Refund kedua 409 ORDER_ALREADY_REFUNDED

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-009, TC-PAY-010, TC-PAY-011, TC-PAY-012

---

### FE-PAY-002 — Tampilan struk + CSS print 58/80 mm

| Field | Value |
|-------|-------|
| ID | FE-PAY-002 |
| Type | frontend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PAY-004 |
| References | SCR-POS-06 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | frontend, pay, core |
| Branch | feature/fe-pay-002-tampilan-struk-css-print-58-80-mm |
| PR | |

**Context**
Tampilan struk + CSS print 58/80 mm. Rujukan: SCR-POS-06.

**Acceptance Criteria**
- [ ] Print browser 58 dan 80 mm rapi (uji printer thermal)
- [ ] Cetak ulang dari riwayat

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-014

---

### DEPLOY-001 — Environment production, HTTPS, backup harian + uji restore

| Field | Value |
|-------|-------|
| ID | DEPLOY-001 |
| Type | devops |
| Module |  |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-008 |
| References | NFR-REL-002; DO §3 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S4 |
| Labels | devops, setup, core |
| Branch | feature/deploy-001-environment-production-https-backup-hari |
| PR | |

**Context**
Environment production, HTTPS, backup harian + uji restore. Rujukan: NFR-REL-002; DO §3.

**Acceptance Criteria**
- [ ] Backup harian berjalan dan restore berhasil diuji sekali
- [ ] Variabel rahasia hanya di environment server

**Technical Notes**
- Rujuk Deploy & Ops v3.0

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

## S5 (21–30 Des)

### TEST-ORD-001 — Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan)

| Field | Value |
|-------|-------|
| ID | TEST-ORD-001 |
| Type | testing |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-ORD-002 |
| References | BR-005, BR-036 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S5 |
| Labels | testing, ord, core |
| Branch | feature/test-ord-001-unit-test-pricecalculator-table-driven-d |
| PR | |

**Context**
Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan). Rujukan: BR-005, BR-036.

**Acceptance Criteria**
- [ ] Vektor uji ACT-ORD-02 tersimpan sebagai fixture bersama BE dan FE
- [ ] Kasus increment 0/100/500/1000 dan batas .5 diuji

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ORD-001…003

---

### TEST-ORD-002 — Feature test lifecycle order & cancel

| Field | Value |
|-------|-------|
| ID | TEST-ORD-002 |
| Type | testing |
| Module | ORD |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-ORD-006 |
| References | STM-ORDER |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S5 |
| Labels | testing, ord, core |
| Branch | feature/test-ord-002-feature-test-lifecycle-order-cancel |
| PR | |

**Context**
Feature test lifecycle order & cancel. Rujukan: STM-ORDER.

**Acceptance Criteria**
- [ ] Semua transisi STM-ORDER diuji (sah dan tidak sah)
- [ ] Cancel Cashier Own/Limited diuji

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ORD-007, TC-ORD-008

---

### FE-PAY-001 — Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key)

| Field | Value |
|-------|-------|
| ID | FE-PAY-001 |
| Type | frontend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PAY-002, FE-ORD-003 |
| References | SCR-POS-04 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S5 |
| Labels | frontend, pay, core |
| Branch | feature/fe-pay-001-dialog-pembayaran-numpad-nominal-cepat-k |
| PR | |

**Context**
Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key). Rujukan: SCR-POS-04.

**Acceptance Criteria**
- [ ] Key dibuat sekali per dialog dan dipakai ulang saat retry
- [ ] Error stok kurang menampilkan daftar bahan

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-004, TC-PAY-008

---

### FE-PAY-003 — Dialog refund (alasan, restock)

| Field | Value |
|-------|-------|
| ID | FE-PAY-003 |
| Type | frontend |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-PAY-005, FE-ORD-004 |
| References | SCR-POS-07 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S5 |
| Labels | frontend, pay, core |
| Branch | feature/fe-pay-003-dialog-refund-alasan-restock |
| PR | |

**Context**
Dialog refund (alasan, restock). Rujukan: SCR-POS-07.

**Acceptance Criteria**
- [ ] Alasan wajib; restock default tidak dicentang
- [ ] Pesan 409 NO_OPEN_SHIFT jelas

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-009, TC-PAY-010

---

### TEST-PAY-001 — Test pembayaran: double submit, overpay, status invalid, kembalian, rollback

| Field | Value |
|-------|-------|
| ID | TEST-PAY-001 |
| Type | testing |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PAY-003 |
| References | FR-PAY-001; NFR-DATA-002/003 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S5 |
| Labels | testing, pay, core |
| Branch | feature/test-pay-001-test-pembayaran-double-submit-overpay-st |
| PR | |

**Context**
Test pembayaran: double submit, overpay, status invalid, kembalian, rollback. Rujukan: FR-PAY-001; NFR-DATA-002/003.

**Acceptance Criteria**
- [ ] 0 payment tanpa potong stok pada test kegagalan
- [ ] Retry dengan key sama tidak menggandakan payment/movement

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PAY-001…008

---

### TEST-PAY-002 — Test refund (kas, status, permission, restock)

| Field | Value |
|-------|-------|
| ID | TEST-PAY-002 |
| Type | testing |
| Module | PAY |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-PAY-005 |
| References | FR-RFD-001 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S5 |
| Labels | testing, pay, core |
| Branch | feature/test-pay-002-test-refund-kas-status-permission-restoc |
| PR | |

**Context**
Test refund (kas, status, permission, restock). Rujukan: FR-RFD-001.

**Acceptance Criteria**
- [ ] Efek ke Expected Cash shift pelaku diuji
- [ ] RETURN movement terbentuk hanya bila restock

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PAY-009…013

---

## S5 — cadangan 30 Des

### DEPLOY-002 — Deploy production + smoke test

| Field | Value |
|-------|-------|
| ID | DEPLOY-002 |
| Type | devops |
| Module |  |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DEPLOY-001, TEST-E2E-001, TEST-E2E-002, TEST-SEC-001 |
| References | DO §2 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S5 (cadangan 30 Des) |
| Labels | devops, setup, core |
| Branch | feature/deploy-002-deploy-production-smoke-test |
| PR | |

**Context**
Deploy production + smoke test. Rujukan: DO §2.

**Acceptance Criteria**
- [ ] Smoke test (login, buka shift, order, bayar, tutup shift) lulus
- [ ] Rollback ke rilis sebelumnya terdokumentasi dan teruji di staging

**Technical Notes**
- Rujuk Deploy & Ops v3.0

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

## FASE 2 — RILIS 1.1 (Jan 2027)

---

## Rilis 1.1 (Jan 2027)

### BE-KIT-001 — Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready

| Field | Value |
|-------|-------|
| ID | BE-KIT-001 |
| Type | backend |
| Module | KIT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ORD-005 |
| References | FR-KIT-001; UC-KIT-01; BR-033 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | R1.1 |
| Labels | backend, kit, r1.1 |
| Branch | feature/be-kit-001-antrian-dapur-get-order-completed-kitche |
| PR | |

**Context**
Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready. Rujukan: FR-KIT-001; UC-KIT-01; BR-033.

**Acceptance Criteria**
- [ ] Respons tanpa harga dan data pembayaran
- [ ] Order REFUNDED otomatis hilang dari antrian

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-011

---

### FE-KIT-001 — Halaman antrian dapur (polling 10 detik)

| Field | Value |
|-------|-------|
| ID | FE-KIT-001 |
| Type | frontend |
| Module | KIT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-KIT-001 |
| References | SCR-KIT-01 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | R1.1 |
| Labels | frontend, kit, r1.1 |
| Branch | feature/fe-kit-001-halaman-antrian-dapur-polling-10-detik |
| PR | |

**Context**
Halaman antrian dapur (polling 10 detik). Rujukan: SCR-KIT-01.

**Acceptance Criteria**
- [ ] Tombol Siap mengubah NEW menjadi READY
- [ ] State empty antrian tampil

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-011

---
