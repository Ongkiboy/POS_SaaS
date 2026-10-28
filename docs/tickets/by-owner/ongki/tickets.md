# Tiket @ongki — F&B POS & Inventory

> Dibuat otomatis dari `tickets.md` (jangan diedit tangan; jalankan ulang `generate_by_owner.py` setiap `tickets.md` berubah). Total **46** tiket.
Reviewer silang: Ongki ← Akmal ← Naufaldi ← Ongki. Definition of Ready/Done: `docs/11_DoR_DoD.md`.
> 

## Ringkasan beban (hari tugas)

| Sprint | S1 | S2 | S3 | S4 | S5 | R1.1 | Core (S1–S5) |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Beban | 8,50 | 7,50 | 7,50 | 7,00 | 5,50 | 1,00 | 36,00 |

## Daftar tiket (urut sprint)

| ID | Sprint | Est | Status | Depends on | Reviewer | Judul |
| --- | --- | --- | --- | --- | --- | --- |
| SETUP-001 | S1 | S | todo | — | @akmal | Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template |
| SETUP-002 | S1 | S | todo | SETUP-001 | @akmal | Konfigurasi PostgreSQL lokal (Laragon) + .env.example per environment |
| SETUP-003 | S1 | S | todo | SETUP-001 | @akmal | Pint, Larastan, Oxlint, ESLint, Prettier, pre-commit hook |
| SETUP-005 | S1 | M | todo | SETUP-001 | @akmal | Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception han |
| DB-TEN-001 | S1 | M | todo | SETUP-005 | @akmal | Migrasi businesses, outlets, users, user_outlets |
| DB-TEN-002 | S1 | M | todo | DB-TEN-001 | @akmal | Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet |
| DB-RBAC-001 | S1 | M | todo | DB-TEN-001 | @akmal | Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Lim |
| DB-AUD-001 | S1 | S | todo | DB-TEN-001 | @akmal | Migrasi audit_logs + trigger append-only |
| BE-AUD-001 | S1 | S | todo | DB-AUD-001, SETUP-005 | @akmal | AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via que |
| BE-RBAC-001 | S1 | M | todo | DB-RBAC-001, DB-TEN-002 | @akmal | Middleware permission + outlet.context, Policy aturan Limited/Own |
| BE-AUTH-001 | S1 | M | todo | DB-TEN-002, DB-RBAC-001, BE-AUD-001 | @akmal | POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi) |
| SETUP-007 | S2 | S | todo | SETUP-005 | @akmal | Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test |
| BE-AUTH-002 | S2 | M | todo | BE-AUTH-001 | @akmal | Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED |
| BE-AUTH-004 | S2 | S | todo | BE-AUTH-002, BE-RBAC-001 | @akmal | /me mengembalikan outlet + permission dengan scope |
| DB-ORD-001 | S2 | M | todo | DB-CAT-001, DB-SHF-001, DB-REC-001 | @akmal | Migrasi orders, order_items, order_item_modifiers, order_sequences |
| BE-ORD-001 | S2 | S | todo | DB-ORD-001 | @akmal | OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut} |
| BE-ORD-002 | S2 | M | todo | SETUP-007, BE-USER-002 | @akmal | PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total |
| BE-ORD-003 | S2 | M | todo | BE-ORD-001, BE-ORD-002, BE-CAT-005, BE-SHF-005, BE-REC-001, BE-REC-002 | @akmal | Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier |
| BE-ORD-005 | S2 | M | todo | BE-ORD-003 | @akmal | State machine order: confirm, reopen (guard gateway PENDING) |
| DB-PAY-001 | S2 | M | todo | DB-ORD-001 | @akmal | Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments) |
| SETUP-008 | S3 | M | todo | SETUP-004 | @akmal | Staging (VPS + Docker + Nginx) + Supabase staging (DB dan Storage) + HTTPS; FE dan API sat |
| TEST-AUTH-001 | S3 | M | todo | BE-USER-001 | @akmal | Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint |
| BE-ORD-007 | S3 | S | todo | BE-ORD-003 | @akmal | Riwayat & detail order (Cashier hanya miliknya) |
| FE-ORD-001 | S3 | M | todo | BE-CAT-005, FE-SHF-001 | @akmal | Layout layar POS responsif (grid kategori/produk + cart) |
| FE-ORD-002 | S3 | M | todo | FE-ORD-001, BE-ORD-003 | @akmal | Cart store (Pinia), modifier picker, qty |
| BE-PAY-001 | S3 | M | todo | DB-PAY-001 | @akmal | Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif |
| BE-PAY-002 | S3 | M | todo | BE-PAY-001, BE-ORD-005 | @akmal | POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik |
| BE-PAY-003 | S3 | S | todo | BE-PAY-002, BE-ENG-002 | @akmal | Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit |
| BE-PAY-004 | S3 | S | todo | BE-PAY-002 | @akmal | Builder data struk (pembulatan, metode, kembalian) |
| TEST-RBAC-001 | S4 | M | todo | BE-USER-001 | @akmal | Test matriks role × endpoint |
| BE-ORD-004 | S4 | S | todo | BE-ORD-003 | @akmal | Diskon order-level (persen/nominal), batas Cashier per outlet, audit |
| BE-ORD-006 | S4 | S | todo | BE-ORD-005, BE-AUD-001 | @akmal | Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit) |
| FE-ORD-003 | S4 | M | todo | FE-ORD-002, BE-ORD-004 | @akmal | Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server) |
| FE-ORD-004 | S4 | M | todo | BE-ORD-007 | @akmal | Halaman riwayat & detail order |
| BE-PAY-005 | S4 | M | todo | BE-PAY-002, BE-AUD-001, BE-ENG-004 | @akmal | Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit |
| FE-PAY-002 | S4 | M | todo | BE-PAY-004 | @akmal | Tampilan struk + CSS print 58/80 mm |
| DEPLOY-001 | S4 | M | todo | SETUP-008 | @akmal | Environment production, HTTPS, backup harian + uji restore |
| TEST-ORD-001 | S5 | M | todo | BE-ORD-002 | @akmal | Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan) |
| TEST-ORD-002 | S5 | M | todo | BE-ORD-006 | @akmal | Feature test lifecycle order & cancel |
| FE-PAY-001 | S5 | M | todo | BE-PAY-002, FE-ORD-003 | @akmal | Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key) |
| FE-PAY-003 | S5 | S | todo | BE-PAY-005, FE-ORD-004 | @akmal | Dialog refund (alasan, restock) |
| TEST-PAY-001 | S5 | M | todo | BE-PAY-003 | @akmal | Test pembayaran: double submit, overpay, status invalid, kembalian, rollback |
| TEST-PAY-002 | S5 | S | todo | BE-PAY-005 | @akmal | Test refund (kas, status, permission, restock) |
| DEPLOY-002 | S5 | S | todo | DEPLOY-001, TEST-E2E-001, TEST-E2E-002, TEST-SEC-001 | @akmal | Deploy production + smoke test |
| BE-KIT-001 | R1.1 | S | todo | BE-ORD-005 | @akmal | Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready |
| FE-KIT-001 | R1.1 | S | todo | BE-KIT-001 | @akmal | Halaman antrian dapur (polling 10 detik) |

## Tiket yang harus direview @ongki (50)

| ID | Sprint | Assignee | Judul |
| --- | --- | --- | --- |
| SETUP-006 | S1 | @naufaldi | Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, T |
| DB-CAT-001 | S1 | @naufaldi | Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups |
| BE-CAT-001 | S1 | @naufaldi | CRUD category (status, sort order) |
| BE-CAT-002 | S1 | @naufaldi | CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp) |
| DB-SHF-001 | S1 | @naufaldi | Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet) |
| BE-USER-002 | S2 | @naufaldi | CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk) |
| FE-AUTH-001 | S2 | @naufaldi | Halaman Login |
| FE-AUTH-003 | S2 | @naufaldi | Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet s |
| BE-CAT-004 | S2 | @naufaldi | CRUD modifier group & option + pasang ke produk |
| BE-CAT-005 | S2 | @naufaldi | GET /pos/catalog dengan cache + invalidasi saat katalog berubah |
| BE-SHF-001 | S2 | @naufaldi | Open shift (opening cash, business_date menurut timezone outlet) |
| BE-SHF-005 | S2 | @naufaldi | GET /shifts/current + open_hours_exceeded + middleware shift.open |
| DB-PUR-001 | S2 | @naufaldi | Migrasi suppliers, purchases, purchase_items, document_sequences |
| BE-PUR-001 | S2 | @naufaldi | CRUD supplier (soft delete, INACTIVE tidak bisa dipilih) |
| BE-USER-001 | S3 | @naufaldi | CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit |
| BE-SHF-003 | S3 | @naufaldi | ShiftSummaryService: Expected Cash + non_cash per metode |
| BE-SHF-004 | S3 | @naufaldi | Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit |
| FE-SHF-001 | S3 | @naufaldi | Dialog open shift (+ banner > 24 jam) |
| BE-PUR-002 | S3 | @naufaldi | Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO |
| BE-PUR-003 | S3 | @naufaldi | Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten |
| FE-PUR-001 | S3 | @naufaldi | Halaman supplier |
| FE-PUR-002 | S3 | @naufaldi | Form & daftar purchase (satuan beli, konversi tampil) |
| BE-RPT-001 | S3 | @naufaldi | Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund) |
| FE-USER-001 | S4 | @naufaldi | Halaman manajemen user (list, form, role, outlet) |
| FE-CAT-002 | S4 | @naufaldi | Halaman list + form produk (upload gambar) |
| FE-CAT-003 | S4 | @naufaldi | UI manajemen modifier |
| BE-SHF-002 | S4 | @naufaldi | Cash in / cash out (alasan wajib, cash.manage) |
| FE-SHF-003 | S4 | @naufaldi | Wizard close shift + ringkasan (cetak) |
| TEST-SHF-001 | S4 | @naufaldi | Test shift: open ganda, selisih kas, close dengan order pending, efek refund |
| BE-PUR-004 | S4 | @naufaldi | GET daftar & detail purchase (filter status, supplier, tanggal) |
| FE-PUR-003 | S4 | @naufaldi | Alur terima barang (konfirmasi, hasil stok baru) |
| BE-RPT-003 | S4 | @naufaldi | Laporan payment per metode |
| BE-AUTH-003 | S5 | @naufaldi | GET/PUT /me dan PUT /me/password |
| BE-USER-003 | S5 | @naufaldi | GET/PUT /business (profil + license tanpa enforcement) |
| FE-AUTH-002 | S5 | @naufaldi | Halaman Register Business |
| FE-USER-002 | S5 | @naufaldi | Halaman pengaturan outlet dan profil business |
| BE-CAT-003 | S5 | @naufaldi | Audit perubahan harga + guard produk INACTIVE + soft delete |
| FE-CAT-001 | S5 | @naufaldi | Halaman kategori |
| FE-SHF-002 | S5 | @naufaldi | Dialog cash in / cash out |
| TEST-PUR-001 | S5 | @naufaldi | Test purchasing: konversi, last_cost, idempotensi, permission |
| FE-RPT-001 | S5 | @naufaldi | Halaman laporan sales & payment |
| BE-AUD-002 | R1.1 | @naufaldi | GET /audit-logs + filter (tanggal, user, aksi, resource) |
| FE-AUD-001 | R1.1 | @naufaldi | Halaman audit log |
| BE-PUR-005 | R1.1 | @naufaldi | Cancel purchase DRAFT + partial receive (purchase_receipts) |
| BE-RPT-002 | R1.1 | @naufaldi | Laporan produk + daftar no recipe |
| BE-RPT-004 | R1.1 | @naufaldi | Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian |
| BE-RPT-005 | R1.1 | @naufaldi | Dashboard permission-aware (timezone outlet, cache TTL pendek) |
| FE-RPT-002 | R1.1 | @naufaldi | Halaman laporan produk, inventory, waste |
| FE-RPT-003 | R1.1 | @naufaldi | Dashboard |
| BE-RPT-006 | R1.1 | @naufaldi | Export CSV sales dan stock movement (stream, reports.export) |

## Detail tiket

### SETUP-001 — Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template

| Field | Value |
| --- | --- |
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
| PR |  |

**Context**
Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template. Rujukan: ADR-001; TD §1, §7.

**Acceptance Criteria**

- [ ]  Repo punya /backend dan /frontend, `php artisan serve` dan `npm run dev` jalan di Laragon (PHP 8.4)
- [ ]  Branch main/develop/feature dan PR template (memuat ID tiket) tersedia
- [ ]  Pest (+ pest-plugin-arch) terpasang dan satu test contoh lulus lewat `./vendor/bin/pest`

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### SETUP-002 — Konfigurasi PostgreSQL lokal (Laragon) + .env.example per environment

| Field | Value |
| --- | --- |
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
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-002-konfigurasi-postgresql-supabase-direct-s |
| PR |  |

**Context**
Konfigurasi PostgreSQL lokal (Laragon) + .env.example per environment. Supabase hanya untuk staging/production dan diverifikasi di SETUP-008 (keputusan 2026-10-26). Rujukan: ADR-006; TD §6.

**Acceptance Criteria**

- [ ]  Koneksi PostgreSQL lokal berfungsi dan `SELECT ... FOR UPDATE` bekerja dalam transaksi
- [ ]  `.env.example` memuat semua variabel (DB, CACHE_STORE=database, QUEUE_CONNECTION=database, Supabase Storage) tanpa rahasia dan menyebut larangan transaction pooler

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### SETUP-003 — Pint, Larastan, Oxlint, ESLint, Prettier, pre-commit hook

| Field | Value |
| --- | --- |
| ID | SETUP-003 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | S |
| Status | todo |
| Depends on | SETUP-001 |
| References | TD §7 |
| Assignee | @ongki |
| Reviewer | @akmal |
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-003-pint-larastan-eslint-prettier-pre-commit |
| PR |  |

**Context**
Pint, Larastan, Oxlint, ESLint, Prettier, pre-commit hook. Lint frontend memakai standar scaffold Vue (Oxlint + ESLint + Prettier); hook dan CI tanpa `--fix` (keputusan 2026-10-27). Dikerjakan Ongki sebagai pengecualian satu kali karena WIP sudah ada (2026-10-27). Rujukan: TD §7.

**Acceptance Criteria**

- [ ]  Commit gagal bila Pint/Oxlint/ESLint melaporkan error
- [ ]  Larastan level yang disepakati lulus pada skeleton

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### SETUP-005 — Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1

| Field | Value |
| --- | --- |
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
| PR |  |

**Context**
Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1. Rujukan: TD §2, §2.4; SDD §4.2.

**Acceptance Criteria**

- [ ]  Respons sukses dan error mengikuti envelope SDD §4.2 (`success`, `data`, `error.code`)
- [ ]  Exception 404/403/409/422 dipetakan ke kode error SDD §4.3
- [ ]  Modul baru cukup didaftarkan di `config/modules.php`; routes, policy, dan event-nya termuat lewat `ModuleServiceProvider` tanpa mengubah `routes/api.php` (TD §2.4)
- [ ]  `route:cache` dan `config:cache` berjalan tanpa error; satu modul contoh (`Platform/Tenant`) dengan route ping termuat lewat `ModuleServiceProvider`

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### DB-TEN-001 — Migrasi businesses, outlets, users, user_outlets

| Field | Value |
| --- | --- |
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
| PR |  |

**Context**
Migrasi businesses, outlets, users, user_outlets. Rujukan: ERD §3.1; BR-022.

**Acceptance Criteria**

- [ ]  Semua kolom, CHECK, dan UNIQUE sesuai ERD §3.1
- [ ]  Rollback migrasi bersih
- [ ]  Migrasi `users` bawaan Laravel dihapus (diganti skema ERD); migrasi framework (sessions, cache, jobs, failed_jobs) dan Spatie diberi nama berprefix modul (`platform_`, `rbac_`), dan tabel framework dihitung terpisah dari 40 tabel domain

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-TEN-003

---

### DB-TEN-002 — Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet

| Field | Value |
| --- | --- |
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
| PR |  |

**Context**
Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet. Rujukan: ADR-003; FR-USER-002.

**Acceptance Criteria**

- [ ]  Query model tenant otomatis terfilter business_id
- [ ]  Insert otomatis mengisi business_id; data business lain mengembalikan 404

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-TEN-001, TC-TEN-002

---

### DB-RBAC-001 — Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own

| Field | Value |
| --- | --- |
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
| PR |  |

**Context**
Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own. Rujukan: ADR-005; SRS §2.1.

**Acceptance Criteria**

- [ ]  5 role dan semua permission SRS §2.1 ter-seed per business saat register
- [ ]  Config scope (`OWN_ONLY`, `LIMITED`) terbaca untuk /me

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-RBAC-001

---

### DB-AUD-001 — Migrasi audit_logs + trigger append-only

| --- | --- |

**Context**
Migrasi audit_logs + trigger append-only. Rujukan: ADR-007; BR-020.

**Acceptance Criteria**

- [ ]  UPDATE dan DELETE pada audit_logs ditolak trigger DB
- [ ]  Index sesuai ERD §4

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-AUD-002

---

### BE-AUD-001 — AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue

| --- | --- |

**Context**
AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue. Rujukan: FR-AUD-001.

**Acceptance Criteria**

- [ ]  Pemanggilan di dalam transaksi baru tertulis setelah commit (afterCommit)
- [ ]  Payload memuat before/after JSON, user, outlet, IP

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUD-001

---

### BE-RBAC-001 — Middleware permission + outlet.context, Policy aturan Limited/Own

| --- | --- |

**Context**
Middleware permission + outlet.context, Policy aturan Limited/Own. Rujukan: FR-USER-002; BR-024, BR-025.

**Acceptance Criteria**

- [ ]  Tanpa permission = 403; X-Outlet-Id di luar user_outlets = 403
- [ ]  Policy Cashier hanya order sendiri; Manager hanya mengelola Cashier/Inventory/Kitchen

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RBAC-002, TC-RBAC-003

---

### BE-AUTH-001 — POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi)

| --- | --- |

**Context**
POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi). Rujukan: FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01.

**Acceptance Criteria**

- [ ]  Data valid mengembalikan 201 dan membuat business, outlet, Owner, user_outlets
- [ ]  Email ganda dan password lemah mengembalikan 422 dengan pesan SRS FR-AUTH-001

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-001, TC-AUTH-002, TC-AUTH-003

---

### SETUP-007 — Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test

| --- | --- |

**Context**
Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test. Rujukan: ADR-004; SRS §1.4.

**Acceptance Criteria**

- [ ]  `Money::halfUp()` memberi hasil benar untuk kasus .5 positif dan nol
- [ ]  Enum OrderStatus, MovementType, PaymentMethod, ShiftStatus tersedia dan sama dengan CHECK di ERD

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
TC-ORD-001

---

### BE-AUTH-002 — Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED

| --- | --- |

**Context**
Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED. Rujukan: FR-AUTH-002; UC-AUTH-02; BR-023.

**Acceptance Criteria**

- [ ]  Gagal ke-5 mengunci akun 15 menit (423 ACCOUNT_LOCKED)
- [ ]  Akun INACTIVE 403; login gagal email tak dikenal tercatat dengan user_id NULL

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-004, TC-AUTH-005, TC-AUTH-006

---

### BE-AUTH-004 — /me mengembalikan outlet + permission dengan scope

| --- | --- |

**Context**
/me mengembalikan outlet + permission dengan scope. Rujukan: FR-AUTH-004.

**Acceptance Criteria**

- [ ]  Respons memuat role, outlets, permissions[{code, scope}] sesuai SDD §4.5
- [ ]  Owner otomatis punya semua outlet business

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-007, TC-AUTH-008

---

### DB-ORD-001 — Migrasi orders, order_items, order_item_modifiers, order_sequences

| --- | --- |

**Context**
Migrasi orders, order_items, order_item_modifiers, order_sequences. Rujukan: ERD §3.4.

**Acceptance Criteria**

- [ ]  Kolom snapshot, CHECK table_number ⇒ DINE_IN, rounding_amount boleh negatif
- [ ]  FK recipe_id ke recipes (NULL = tanpa recipe)

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-ORD-009

---

### BE-ORD-001 — OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}

| --- | --- |

**Context**
OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}. Rujukan: FR-ORD-007; BR-019.

**Acceptance Criteria**

- [ ]  Nomor reset per outlet per business date
- [ ]  50 order paralel tidak menghasilkan nomor ganda

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-005

---

### BE-ORD-002 — PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total

| --- | --- |

**Context**
PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total. Rujukan: FR-ORD-002; BR-005, BR-036; ACT-ORD-02.

**Acceptance Criteria**

- [ ]  Vektor uji: subtotal 54.000 → total 62.400 (rounding +30); diskon 10% → 56.100 (−33)
- [ ]  Total > 0 tidak boleh menjadi 0 setelah pembulatan

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-001, TC-ORD-002

---

### BE-ORD-003 — Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier

| --- | --- |

**Context**
Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier. Rujukan: FR-ORD-001; UC-ORD-01; SEQ-ORD-01.

**Acceptance Criteria**

- [ ]  Produk INACTIVE 422 PRODUCT_UNAVAILABLE; qty di luar 1–999 422
- [ ]  Mengubah harga produk setelah order dibuat tidak mengubah order

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-006, TC-ORD-009

---

### BE-ORD-005 — State machine order: confirm, reopen (guard gateway PENDING)

| --- | --- |

**Context**
State machine order: confirm, reopen (guard gateway PENDING). Rujukan: FR-ORD-004; STM-ORDER; BR-028.

**Acceptance Criteria**

- [ ]  Transisi tidak terdaftar 409 INVALID_STATE_TRANSITION
- [ ]  Reopen ditolak bila ada payment SUCCESS; titik ekstensi guard gateway PENDING tersedia

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-007, TC-ORD-010

---

### DB-PAY-001 — Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments)

| --- | --- |

**Context**
Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments). Rujukan: ERD §3.4; Lampiran SDD B.

**Acceptance Criteria**

- [ ]  UNIQUE (business_id, idempotency_key) dan UNIQUE parsial SUCCESS per order
- [ ]  refunds: UNIQUE(order_id), CHECK CASH ⇒ shift_id NOT NULL; created_by payments nullable

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-PAY-004

---

### SETUP-008 — Staging (VPS + Docker + Nginx) + Supabase staging (DB dan Storage) + HTTPS; FE dan API satu parent domain (atau proxy rewrite)

| --- | --- |

**Context**
Staging (VPS + Docker + Nginx) + Supabase staging (project, DB, Storage) + HTTPS; FE dan API satu parent domain (atau proxy rewrite). Seluruh pekerjaan Supabase (project staging, migrasi lewat direct connection, verifikasi R-B, Storage) ada di tiket ini; dev lokal memakai PostgreSQL Laragon (keputusan 2026-10-26). Rujukan: NFR-SEC-003; TD §5, §6; ADR-006.

**Acceptance Criteria**

- [ ]  Login lewat FE staging berhasil dengan cookie Sanctum (Secure, HttpOnly, SameSite)
- [ ]  Sertifikat HTTPS aktif dan HTTP dialihkan
- [ ]  Koneksi staging ke Supabase memakai direct atau session pooler (bukan transaction pooler) dan `SELECT ... FOR UPDATE` bekerja dalam transaksi (R-B; dipindah dari SETUP-002)
- [ ]  Project Supabase staging (terpisah dari production) dibuat dan seluruh migrasi (termasuk trigger append-only) berhasil dijalankan lewat direct connection
- [ ]  Upload dan baca gambar produk lewat disk `s3` ke bucket Supabase Storage staging berhasil (`FILESYSTEM_DISK=s3`, endpoint Supabase, paket Flysystem S3 terpasang)

**Technical Notes**

- Rujuk Technical Design §1, §5, dan §6. Dev lokal: disk lokal untuk gambar (`FILESYSTEM_DISK=local`); disk `s3` hanya diverifikasi di staging

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### TEST-AUTH-001 — Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint)

| --- | --- |

**Context**
Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint). Rujukan: NFR-SEC-001; ADR-003.

**Acceptance Criteria**

- [ ]  Setiap endpoint terdaftar dites lintas tenant dan mengembalikan 404
- [ ]  Dataset otomatis bertambah saat endpoint baru didaftarkan (DoD tiap modul)

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-TEN-001

---

### BE-ORD-007 — Riwayat & detail order (Cashier hanya miliknya)

| --- | --- |

**Context**
Riwayat & detail order (Cashier hanya miliknya). Rujukan: FR-ORD-006; UC-ORD-04.

**Acceptance Criteria**

- [ ]  Filter tanggal (business date), status, kasir, nomor order
- [ ]  Order tenant lain 404

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-008

---

### FE-ORD-001 — Layout layar POS responsif (grid kategori/produk + cart)

| --- | --- |

**Context**
Layout layar POS responsif (grid kategori/produk + cart). Rujukan: SCR-POS-01.

**Acceptance Criteria**

- [ ]  Nyaman di HP ≥ 360 px dengan target sentuh ≥ 44 px
- [ ]  Load awal < 3 detik (4G, cache dingin)

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-001

---

### FE-ORD-002 — Cart store (Pinia), modifier picker, qty

| --- | --- |

**Context**
Cart store (Pinia), modifier picker, qty. Rujukan: SCR-POS-01, SCR-POS-02.

**Acceptance Criteria**

- [ ]  Group modifier wajib memblokir tombol simpan dengan pesan SRS
- [ ]  Cart bertahan saat berpindah layar dalam sesi

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-006

---

### BE-PAY-001 — Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif

| --- | --- |

**Context**
Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif. Rujukan: FR-PAY-001; BR-007.

**Acceptance Criteria**

- [ ]  Cash tendered < total 422; QRIS nominal ≠ total 422
- [ ]  CARD/GATEWAY 422 PAYMENT_METHOD_UNAVAILABLE

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-001, TC-PAY-002, TC-PAY-003

---

### BE-PAY-002 — POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik

| --- | --- |

**Context**
POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik. Rujukan: FR-PAY-001; UC-PAY-01; SEQ-PAY-01.

**Acceptance Criteria**

- [ ]  Key sama untuk order sama = 200 + Idempotent-Replay; untuk order lain 409
- [ ]  Order bukan PENDING_PAYMENT 409; header kosong 422

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-004, TC-PAY-005, TC-PAY-007

---

### BE-PAY-003 — Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit

| --- | --- |

**Context**
Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit. Rujukan: ADR-002; FR-ENG-001.

**Acceptance Criteria**

- [ ]  Stok kurang dan minus dilarang: rollback total, payment tidak tersimpan
- [ ]  Double submit paralel menghasilkan 1 payment dan 1 set movement

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-006, TC-PAY-008

---

### BE-PAY-004 — Builder data struk (pembulatan, metode, kembalian)

| --- | --- |

**Context**
Builder data struk (pembulatan, metode, kembalian). Rujukan: FR-PAY-002; UC-PAY-02.

**Acceptance Criteria**

- [ ]  Struk memuat baris pembulatan bila ada
- [ ]  Cetak ulang dari riwayat memakai data tersimpan

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-014

---

### TEST-RBAC-001 — Test matriks role × endpoint

| --- | --- |

**Context**
Test matriks role × endpoint. Rujukan: SRS §2.1.

**Acceptance Criteria**

- [ ]  Tiap sel matriks permission diuji (ijin/tolak)
- [ ]  Aturan Own/Limited diuji untuk Cashier, Manager, Inventory

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-RBAC-001

---

### BE-ORD-004 — Diskon order-level (persen/nominal), batas Cashier per outlet, audit

| --- | --- |

**Context**
Diskon order-level (persen/nominal), batas Cashier per outlet, audit. Rujukan: FR-ORD-003; UC-ORD-02.

**Acceptance Criteria**

- [ ]  Diskon > subtotal 422 DISCOUNT_INVALID
- [ ]  Cashier melebihi batas outlet 403 DISCOUNT_LIMIT_EXCEEDED

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-003, TC-ORD-004

---

### BE-ORD-006 — Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit)

| --- | --- |

**Context**
Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit). Rujukan: FR-ORD-005; UC-ORD-03; BR-025.

**Acceptance Criteria**

- [ ]  Order sudah dibayar 409 “harus melalui refund”
- [ ]  Cashier membatalkan order orang lain 403

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-008

---

### FE-ORD-003 — Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server)

| --- | --- |

**Context**
Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server). Rujukan: SCR-POS-03.

**Acceptance Criteria**

- [ ]  Estimasi lokal memakai vektor uji yang sama dengan backend; angka server menjadi final
- [ ]  Nomor meja hanya aktif untuk Dine In

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-001, TC-ORD-002, TC-ORD-009

---

### FE-ORD-004 — Halaman riwayat & detail order

| --- | --- |

**Context**
Halaman riwayat & detail order. Rujukan: SCR-POS-05.

**Acceptance Criteria**

- [ ]  Cashier hanya melihat order miliknya
- [ ]  Tombol cancel/refund tampil sesuai status dan permission

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-008

---

### BE-PAY-005 — Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit

| --- | --- |

**Context**
Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit. Rujukan: FR-RFD-001; UC-RFD-01; SEQ-RFD-01.

**Acceptance Criteria**

- [ ]  Refund tunai tanpa shift OPEN pelaku 409 NO_OPEN_SHIFT; QRIS shift_id NULL
- [ ]  Refund kedua 409 ORDER_ALREADY_REFUNDED

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-009, TC-PAY-010, TC-PAY-011, TC-PAY-012

---

### FE-PAY-002 — Tampilan struk + CSS print 58/80 mm

| --- | --- |

**Context**
Tampilan struk + CSS print 58/80 mm. Rujukan: SCR-POS-06.

**Acceptance Criteria**

- [ ]  Print browser 58 dan 80 mm rapi (uji printer thermal)
- [ ]  Cetak ulang dari riwayat

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-014

---

### DEPLOY-001 — Environment production, HTTPS, backup harian + uji restore

| --- | --- |

**Context**
Environment production, HTTPS, backup harian + uji restore. Rujukan: NFR-REL-002; DO §3.

**Acceptance Criteria**

- [ ]  Backup harian berjalan dan restore berhasil diuji sekali
- [ ]  Variabel rahasia hanya di environment server

**Technical Notes**

- Rujuk Deploy & Ops v3.0

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### TEST-ORD-001 — Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan)

| --- | --- |

**Context**
Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan). Rujukan: BR-005, BR-036.

**Acceptance Criteria**

- [ ]  Vektor uji ACT-ORD-02 tersimpan sebagai fixture bersama BE dan FE
- [ ]  Kasus increment 0/100/500/1000 dan batas .5 diuji

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ORD-001…003

---

### TEST-ORD-002 — Feature test lifecycle order & cancel

| --- | --- |

**Context**
Feature test lifecycle order & cancel. Rujukan: STM-ORDER.

**Acceptance Criteria**

- [ ]  Semua transisi STM-ORDER diuji (sah dan tidak sah)
- [ ]  Cancel Cashier Own/Limited diuji

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ORD-007, TC-ORD-008

---

### FE-PAY-001 — Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key)

| --- | --- |

**Context**
Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key). Rujukan: SCR-POS-04.

**Acceptance Criteria**

- [ ]  Key dibuat sekali per dialog dan dipakai ulang saat retry
- [ ]  Error stok kurang menampilkan daftar bahan

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-004, TC-PAY-008

---

### FE-PAY-003 — Dialog refund (alasan, restock)

| --- | --- |

**Context**
Dialog refund (alasan, restock). Rujukan: SCR-POS-07.

**Acceptance Criteria**

- [ ]  Alasan wajib; restock default tidak dicentang
- [ ]  Pesan 409 NO_OPEN_SHIFT jelas

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-009, TC-PAY-010

---

### TEST-PAY-001 — Test pembayaran: double submit, overpay, status invalid, kembalian, rollback

| --- | --- |

**Context**
Test pembayaran: double submit, overpay, status invalid, kembalian, rollback. Rujukan: FR-PAY-001; NFR-DATA-002/003.

**Acceptance Criteria**

- [ ]  0 payment tanpa potong stok pada test kegagalan
- [ ]  Retry dengan key sama tidak menggandakan payment/movement

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PAY-001…008

---

### TEST-PAY-002 — Test refund (kas, status, permission, restock)

| --- | --- |

**Context**
Test refund (kas, status, permission, restock). Rujukan: FR-RFD-001.

**Acceptance Criteria**

- [ ]  Efek ke Expected Cash shift pelaku diuji
- [ ]  RETURN movement terbentuk hanya bila restock

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PAY-009…013

---

### DEPLOY-002 — Deploy production + smoke test

| --- | --- |

**Context**
Deploy production + smoke test. Rujukan: DO §2.

**Acceptance Criteria**

- [ ]  Smoke test (login, buka shift, order, bayar, tutup shift) lulus
- [ ]  Rollback ke rilis sebelumnya terdokumentasi dan teruji di staging

**Technical Notes**

- Rujuk Deploy & Ops v3.0

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### BE-KIT-001 — Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready

| --- | --- |

**Context**
Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready. Rujukan: FR-KIT-001; UC-KIT-01; BR-033.

**Acceptance Criteria**

- [ ]  Respons tanpa harga dan data pembayaran
- [ ]  Order REFUNDED otomatis hilang dari antrian

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-011

---

### FE-KIT-001 — Halaman antrian dapur (polling 10 detik)

| --- | --- |

**Context**
Halaman antrian dapur (polling 10 detik). Rujukan: SCR-KIT-01.

**Acceptance Criteria**

- [ ]  Tombol Siap mengubah NEW menjadi READY
- [ ]  State empty antrian tampil

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-011

---