# Tiket @naufaldi — F&B POS & Inventory

> Dibuat otomatis dari `tickets.md` (jangan diedit tangan; jalankan ulang `generate_by_owner.py` setiap `tickets.md` berubah). Total **50** tiket.
Reviewer silang: Ongki ← Akmal ← Naufaldi ← Ongki. Definition of Ready/Done: `docs/11_DoR_DoD.md`.
> 

## Ringkasan beban (hari tugas)

| Sprint | S1 | S2 | S3 | S4 | S5 | R1.1 | Core (S1–S5) |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Beban | 4,00 | 7,50 | 8,00 | 7,50 | 5,25 | 7,00 | 32,25 |

## Daftar tiket (urut sprint)

| ID | Sprint | Est | Status | Depends on | Reviewer | Judul |
| --- | --- | --- | --- | --- | --- | --- |
| SETUP-006 | S1 | M | todo | SETUP-001 | @ongki | Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, T |
| DB-CAT-001 | S1 | M | todo | DB-TEN-002 | @ongki | Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups |
| BE-CAT-001 | S1 | S | todo | DB-CAT-001, BE-RBAC-001 | @ongki | CRUD category (status, sort order) |
| BE-CAT-002 | S1 | M | todo | BE-CAT-001 | @ongki | CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp) |
| DB-SHF-001 | S1 | S | todo | DB-TEN-002 | @ongki | Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet) |
| BE-USER-002 | S2 | M | todo | BE-RBAC-001, BE-AUD-001 | @ongki | CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk) |
| FE-AUTH-001 | S2 | M | todo | BE-AUTH-002, SETUP-006 | @ongki | Halaman Login |
| FE-AUTH-003 | S2 | M | todo | FE-AUTH-001, BE-AUTH-004 | @ongki | Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet s |
| BE-CAT-004 | S2 | M | todo | BE-CAT-002 | @ongki | CRUD modifier group & option + pasang ke produk |
| BE-CAT-005 | S2 | S | todo | BE-CAT-004 | @ongki | GET /pos/catalog dengan cache + invalidasi saat katalog berubah |
| BE-SHF-001 | S2 | M | todo | DB-SHF-001, BE-RBAC-001 | @ongki | Open shift (opening cash, business_date menurut timezone outlet) |
| BE-SHF-005 | S2 | S | todo | BE-SHF-001 | @ongki | GET /shifts/current + open_hours_exceeded + middleware shift.open |
| DB-PUR-001 | S2 | M | todo | DB-ING-001, DB-TEN-002 | @ongki | Migrasi suppliers, purchases, purchase_items, document_sequences |
| BE-PUR-001 | S2 | S | todo | DB-PUR-001, BE-RBAC-001 | @ongki | CRUD supplier (soft delete, INACTIVE tidak bisa dipilih) |
| BE-USER-001 | S3 | M | todo | BE-RBAC-001, BE-AUD-001 | @ongki | CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit |
| BE-SHF-003 | S3 | M | todo | BE-SHF-001, DB-PAY-001 | @ongki | ShiftSummaryService: Expected Cash + non_cash per metode |
| BE-SHF-004 | S3 | M | todo | BE-SHF-003, BE-ORD-005 | @ongki | Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit |
| FE-SHF-001 | S3 | S | todo | BE-SHF-005, FE-AUTH-003 | @ongki | Dialog open shift (+ banner > 24 jam) |
| BE-PUR-002 | S3 | M | todo | BE-PUR-001, BE-ING-001, BE-INV-003 | @ongki | Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO |
| BE-PUR-003 | S3 | M | todo | BE-PUR-002, BE-INV-002 | @ongki | Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten |
| FE-PUR-001 | S3 | S | todo | BE-PUR-001, FE-AUTH-003 | @ongki | Halaman supplier |
| FE-PUR-002 | S3 | M | todo | BE-PUR-002, FE-PUR-001 | @ongki | Form & daftar purchase (satuan beli, konversi tampil) |
| BE-RPT-001 | S3 | M | todo | BE-PAY-002, BE-ORD-007 | @ongki | Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund) |
| FE-USER-001 | S4 | M | todo | BE-USER-001, FE-AUTH-003 | @ongki | Halaman manajemen user (list, form, role, outlet) |
| FE-CAT-002 | S4 | M | todo | BE-CAT-002, FE-AUTH-003 | @ongki | Halaman list + form produk (upload gambar) |
| FE-CAT-003 | S4 | M | todo | BE-CAT-004, FE-CAT-002 | @ongki | UI manajemen modifier |
| BE-SHF-002 | S4 | S | todo | BE-SHF-001 | @ongki | Cash in / cash out (alasan wajib, cash.manage) |
| FE-SHF-003 | S4 | M | todo | BE-SHF-004 | @ongki | Wizard close shift + ringkasan (cetak) |
| TEST-SHF-001 | S4 | M | todo | BE-SHF-004, BE-PAY-005 | @ongki | Test shift: open ganda, selisih kas, close dengan order pending, efek refund |
| BE-PUR-004 | S4 | S | todo | BE-PUR-002 | @ongki | GET daftar & detail purchase (filter status, supplier, tanggal) |
| FE-PUR-003 | S4 | M | todo | BE-PUR-003, FE-PUR-002 | @ongki | Alur terima barang (konfirmasi, hasil stok baru) |
| BE-RPT-003 | S4 | S | todo | BE-RPT-001 | @ongki | Laporan payment per metode |
| BE-AUTH-003 | S5 | S | todo | BE-AUTH-002 | @ongki | GET/PUT /me dan PUT /me/password |
| BE-USER-003 | S5 | XS | todo | BE-RBAC-001 | @ongki | GET/PUT /business (profil + license tanpa enforcement) |
| FE-AUTH-002 | S5 | S | todo | BE-AUTH-001, SETUP-006 | @ongki | Halaman Register Business |
| FE-USER-002 | S5 | S | todo | BE-USER-002, BE-USER-003, FE-AUTH-003 | @ongki | Halaman pengaturan outlet dan profil business |
| BE-CAT-003 | S5 | S | todo | BE-CAT-002, BE-AUD-001 | @ongki | Audit perubahan harga + guard produk INACTIVE + soft delete |
| FE-CAT-001 | S5 | S | todo | BE-CAT-001, FE-AUTH-003 | @ongki | Halaman kategori |
| FE-SHF-002 | S5 | S | todo | BE-SHF-002 | @ongki | Dialog cash in / cash out |
| TEST-PUR-001 | S5 | M | todo | BE-PUR-003 | @ongki | Test purchasing: konversi, last_cost, idempotensi, permission |
| FE-RPT-001 | S5 | M | todo | BE-RPT-003, FE-AUTH-003 | @ongki | Halaman laporan sales & payment |
| BE-AUD-002 | R1.1 | S | todo | BE-AUD-001, BE-RBAC-001 | @ongki | GET /audit-logs + filter (tanggal, user, aksi, resource) |
| FE-AUD-001 | R1.1 | S | todo | BE-AUD-002, FE-AUTH-003 | @ongki | Halaman audit log |
| BE-PUR-005 | R1.1 | M | todo | BE-PUR-003 | @ongki | Cancel purchase DRAFT + partial receive (purchase_receipts) |
| BE-RPT-002 | R1.1 | S | todo | BE-RPT-001 | @ongki | Laporan produk + daftar no recipe |
| BE-RPT-004 | R1.1 | M | todo | BE-INV-006, BE-INV-005, BE-PUR-003 | @ongki | Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian |
| BE-RPT-005 | R1.1 | M | todo | BE-RPT-001, BE-INV-006, BE-SHF-005 | @ongki | Dashboard permission-aware (timezone outlet, cache TTL pendek) |
| FE-RPT-002 | R1.1 | M | todo | BE-RPT-004, BE-RPT-002 | @ongki | Halaman laporan produk, inventory, waste |
| FE-RPT-003 | R1.1 | M | todo | BE-RPT-005 | @ongki | Dashboard |
| BE-RPT-006 | R1.1 | S | todo | BE-RPT-001, BE-INV-006 | @ongki | Export CSV sales dan stock movement (stream, reports.export) |

## Tiket yang harus direview @naufaldi (52)

| ID | Sprint | Assignee | Judul |
| --- | --- | --- | --- |
| SETUP-004 | S1 | @akmal | CI GitHub Actions: lint + Pest + build frontend |
| SETUP-009 | S1 | @akmal | Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi |
| DB-ING-001 | S1 | @akmal | Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000) |
| BE-ING-001 | S1 | @akmal | CRUD ingredient (permission inventory.adjust, baca inventory.view) |
| BE-ING-002 | S1 | @akmal | Seeder unit + endpoint GET /units |
| DB-REC-001 | S1 | @akmal | Migrasi recipes (versi), recipe_items, modifier_option_recipe_items |
| BE-REC-001 | S2 | @akmal | Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP |
| BE-REC-002 | S2 | @akmal | Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price |
| DB-INV-001 | S2 | @akmal | Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_re |
| BE-INV-001 | S2 | @akmal | StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative) |
| BE-INV-002 | S2 | @akmal | Idempotensi movement (referensi sama = movement lama) + helper reverse() |
| BE-INV-003 | S2 | @akmal | POST /inventory/opening-stock (batch + movement OPENING) |
| BE-INV-004 | S2 | @akmal | POST/GET /inventory/adjustments (record dibuat sebelum movement) |
| BE-ENG-001 | S2 | @akmal | RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan |
| BE-ENG-002 | S2 | @akmal | DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten |
| TEST-CAT-001 | S3 | @akmal | Feature test katalog (tenant, SKU unik, inactive, cache) |
| FE-ING-001 | S3 | @akmal | Halaman ingredient |
| BE-INV-005 | S3 | @akmal | POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya) |
| BE-INV-006 | S3 | @akmal | GET stok, riwayat movement, low stock + stok minus |
| FE-INV-001 | S3 | @akmal | Halaman stok + badge low stock/minus |
| FE-INV-004 | S3 | @akmal | Form & daftar waste |
| BE-ENG-003 | S3 | @akmal | Kebijakan stok minus: stock_shortage di respons dan audit |
| BE-ENG-004 | S3 | @akmal | Jalur restock refund (movement RETURN dari movement SALE order) |
| INT-ENG-001 | S3 | @akmal | Integration test Sale → Payment → Recipe → Movement → Balance |
| FE-PWA-001 | S3 | @akmal | Manifest + service worker (vite-plugin-pwa), cache aset statis saja |
| FE-REC-001 | S4 | @akmal | Editor recipe di form produk + preview HPP |
| FE-INV-003 | S4 | @akmal | Form opening stock & adjustment |
| TEST-INV-001 | S4 | @akmal | Unit test StockService: konkurensi, minus, idempotensi, presisi desimal |
| TEST-INV-002 | S4 | @akmal | Feature test opening, adjustment, waste, stok |
| TEST-E2E-001 | S4 | @akmal | E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shif |
| TEST-E2E-002 | S4 | @akmal | E2E inventory: opening → purchase receive → adjustment → waste |
| TEST-SEC-001 | S4 | @akmal | Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload |
| TEST-REC-001 | S5 | @akmal | Test recipe (versioning, validasi, HPP) |
| FE-INV-002 | S5 | @akmal | Halaman riwayat movement |
| BE-ENG-005 | S5 | @akmal | GET /orders/{id}/stock-trace |
| TEST-ENG-001 | S5 | @akmal | Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel |
| FE-ENG-001 | S5 | @akmal | Indikator stok menipis/habis di layar POS |
| TEST-RPT-001 | S5 | @akmal | Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone |
| FE-PWA-002 | S5 | @akmal | Banner offline + aksi tulis ditolak + retry aman (key sama) |
| DB-SEED-001 | S5 | @akmal | Seeder demo (business, outlet, menu, recipe, stok, supplier) |
| TEST-PERF-001 | S5 | @akmal | Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements) |
| BE-REC-003 | R1.1 | @akmal | CRUD recipe per modifier option (F-18) |
| FE-REC-002 | R1.1 | @akmal | Editor recipe modifier option (F-18) |
| FE-REC-003 | R1.1 | @akmal | Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP |
| DB-INV-002 | R1.1 | @akmal | Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS) |
| BE-INV-007 | R1.1 | @akmal | Opname: mulai (snapshot), input hitungan, batalkan |
| BE-INV-008 | R1.1 | @akmal | Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id) |
| FE-INV-005 | R1.1 | @akmal | UI opname (mulai, input hitungan, review selisih, submit) |
| TEST-INV-003 | R1.1 | @akmal | Test opname (snapshot, selisih, konkurensi dengan penjualan) |
| BE-ENG-006 | R1.1 | @akmal | Pemakaian bahan modifier option ditambahkan ke agregat (F-18) |
| DOC-001 | R1.1 | @akmal | Dokumentasi API (OpenAPI/Scribe) |
| DOC-002 | R1.1 | @akmal | README + runbook dev (Laragon, Supabase, queue worker) |

## Detail tiket

### SETUP-006 — Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog

| Field | Value |
| --- | --- |
| ID | SETUP-006 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-001 |
| References | TD §3; ADR-001 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-006-skeleton-frontend-vue-3-vite-ts-vue-rout |
| PR |  |

**Context**
Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog. Rujukan: TD §3; ADR-001.

**Acceptance Criteria**

- [ ]  API client otomatis mengambil `/sanctum/csrf-cookie` dan menyertakan `X-Outlet-Id`
- [ ]  401 mengarahkan ke login; 422 memetakan `details` ke field form

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### DB-CAT-001 — Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups

| Field | Value |
| --- | --- |
| ID | DB-CAT-001 |
| Type | database |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-TEN-002 |
| References | ERD §3.2 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S1 |
| Labels | database, cat, core |
| Branch | feature/db-cat-001-migrasi-categories-products-modifier-gro |
| PR |  |

**Context**
Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups. Rujukan: ERD §3.2.

**Acceptance Criteria**

- [ ]  UNIQUE parsial SKU dan nama sesuai ERD
- [ ]  products tidak punya kolom cost_price

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-CAT-001

---

### BE-CAT-001 — CRUD category (status, sort order)

| Field | Value |
| --- | --- |
| ID | BE-CAT-001 |
| Type | backend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DB-CAT-001, BE-RBAC-001 |
| References | FR-CAT-001; UC-CAT-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S1 |
| Labels | backend, cat, core |
| Branch | feature/be-cat-001-crud-category-status-sort-order |
| PR |  |

**Context**
CRUD category (status, sort order). Rujukan: FR-CAT-001; UC-CAT-01.

**Acceptance Criteria**

- [ ]  Nama unik per business; INACTIVE menyembunyikan produknya dari POS
- [ ]  Soft delete, bukan hard delete

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-002

---

### BE-CAT-002 — CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp)

| Field | Value |
| --- | --- |
| ID | BE-CAT-002 |
| Type | backend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-CAT-001 |
| References | FR-CAT-002; UC-CAT-01; SEQ-CAT-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S1 |
| Labels | backend, cat, core |
| Branch | feature/be-cat-002-crud-product-upload-gambar-ke-supabase-s |
| PR |  |

**Context**
CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp). Rujukan: FR-CAT-002; UC-CAT-01; SEQ-CAT-01.

**Acceptance Criteria**

- [ ]  SKU ganda 422 “SKU sudah digunakan.”; harga non-bulat/negatif 422
- [ ]  Gambar >2 MB atau format salah 422 dengan pesan SRS

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD
- Dev: `FILESYSTEM_DISK=local` (disk lokal); verifikasi upload ke Supabase Storage (disk `s3`) dilakukan di SETUP-008

**Test Reference**
TC-CAT-001, TC-CAT-006

---

### DB-SHF-001 — Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet)

| Field | Value |
| --- | --- |
| ID | DB-SHF-001 |
| Type | database |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DB-TEN-002 |
| References | ERD §3.4; BR-002 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S1 |
| Labels | database, shf, core |
| Branch | feature/db-shf-001-migrasi-shifts-cash-movements-unique-par |
| PR |  |

**Context**
Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet). Rujukan: ERD §3.4; BR-002.

**Acceptance Criteria**

- [ ]  Shift OPEN kedua milik kasir yang sama di outlet sama ditolak DB
- [ ]  cash_movements append-only (trigger)

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-SHF-001

---

### BE-USER-002 — CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk)

| Field | Value |
| --- | --- |
| ID | BE-USER-002 |
| Type | backend |
| Module | USER |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-RBAC-001, BE-AUD-001 |
| References | FR-BIZ-002, FR-BIZ-003; UC-BIZ-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | backend, user, core |
| Branch | feature/be-user-002-crud-outlet-pengaturan-pajak-service-min |
| PR |  |

**Context**
CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk). Rujukan: FR-BIZ-002, FR-BIZ-003; UC-BIZ-02.

**Acceptance Criteria**

- [ ]  Manager hanya boleh ubah pajak, service, struk; rounding_increment hanya Owner
- [ ]  Kode outlet tidak bisa diubah setelah ada order; perubahan setting tercatat audit before/after

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-USER-003, TC-USER-004, TC-USER-005

---

### FE-AUTH-001 — Halaman Login

| Field | Value |
| --- | --- |
| ID | FE-AUTH-001 |
| Type | frontend |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-AUTH-002, SETUP-006 |
| References | UC-AUTH-02; SCR-AUTH-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | frontend, auth, core |
| Branch | feature/fe-auth-001-halaman-login |
| PR |  |

**Context**
Halaman Login. Rujukan: UC-AUTH-02; SCR-AUTH-01.

**Acceptance Criteria**

- [ ]  Pesan error mengikuti SRS dan tidak membocorkan email atau password yang salah
- [ ]  Akun terkunci menampilkan pesan 423 dan state loading/error ada

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUTH-004

---

### FE-AUTH-003 — Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher

| Field | Value |
| --- | --- |
| ID | FE-AUTH-003 |
| Type | frontend |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | FE-AUTH-001, BE-AUTH-004 |
| References | FR-AUTH-004; ADR-001 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | frontend, auth, core |
| Branch | feature/fe-auth-003-auth-store-pinia-route-guard-vue-router |
| PR |  |

**Context**
Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher. Rujukan: FR-AUTH-004; ADR-001.

**Acceptance Criteria**

- [ ]  Halaman terproteksi mengarahkan ke login bila 401
- [ ]  v-can menyembunyikan UI sesuai permission dan scope; backend tetap menegakkan

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUTH-007

---

### BE-CAT-004 — CRUD modifier group & option + pasang ke produk

| Field | Value |
| --- | --- |
| ID | BE-CAT-004 |
| Type | backend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-CAT-002 |
| References | FR-CAT-003; UC-CAT-02; BR-030 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | backend, cat, core |
| Branch | feature/be-cat-004-crud-modifier-group-option-pasang-ke-pro |
| PR |  |

**Context**
CRUD modifier group & option + pasang ke produk. Rujukan: FR-CAT-003; UC-CAT-02; BR-030.

**Acceptance Criteria**

- [ ]  min_select > max_select ditolak dengan pesan SRS
- [ ]  SINGLE hanya memperbolehkan satu pilihan; option INACTIVE tidak bisa dipilih

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-003

---

### BE-CAT-005 — GET /pos/catalog dengan cache + invalidasi saat katalog berubah

| Field | Value |
| --- | --- |
| ID | BE-CAT-005 |
| Type | backend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-CAT-004 |
| References | FR-CAT-004; SEQ-CAT-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | backend, cat, core |
| Branch | feature/be-cat-005-get-pos-catalog-dengan-cache-invalidasi |
| PR |  |

**Context**
GET /pos/catalog dengan cache + invalidasi saat katalog berubah. Rujukan: FR-CAT-004; SEQ-CAT-01.

**Acceptance Criteria**

- [ ]  Respons tidak memuat HPP
- [ ]  Perubahan produk/kategori/modifier membatalkan cache

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-005

---

### BE-SHF-001 — Open shift (opening cash, business_date menurut timezone outlet)

| Field | Value |
| --- | --- |
| ID | BE-SHF-001 |
| Type | backend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-SHF-001, BE-RBAC-001 |
| References | FR-SHF-001; UC-SHF-01; SEQ-SHF-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | backend, shf, core |
| Branch | feature/be-shf-001-open-shift-opening-cash-business-date-me |
| PR |  |

**Context**
Open shift (opening cash, business_date menurut timezone outlet). Rujukan: FR-SHF-001; UC-SHF-01; SEQ-SHF-01.

**Acceptance Criteria**

- [ ]  Sudah ada shift OPEN 409 SHIFT_ALREADY_OPEN
- [ ]  Opening cash negatif 422; audit SHIFT_OPENED

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-001

---

### BE-SHF-005 — GET /shifts/current + open_hours_exceeded + middleware shift.open

| Field | Value |
| --- | --- |
| ID | BE-SHF-005 |
| Type | backend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-SHF-001 |
| References | FR-SHF-004; BR-026 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | backend, shf, core |
| Branch | feature/be-shf-005-get-shifts-current-open-hours-exceeded-m |
| PR |  |

**Context**
GET /shifts/current + open_hours_exceeded + middleware shift.open. Rujukan: FR-SHF-004; BR-026.

**Acceptance Criteria**

- [ ]  open_hours_exceeded true bila terbuka > 24 jam
- [ ]  Order tanpa shift OPEN ditolak 409 NO_OPEN_SHIFT

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-006

---

### DB-PUR-001 — Migrasi suppliers, purchases, purchase_items, document_sequences

| Field | Value |
| --- | --- |
| ID | DB-PUR-001 |
| Type | database |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-ING-001, DB-TEN-002 |
| References | ERD §3.6; BR-045…050 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | database, pur, core |
| Branch | feature/db-pur-001-migrasi-suppliers-purchases-purchase-ite |
| PR |  |

**Context**
Migrasi suppliers, purchases, purchase_items, document_sequences. Rujukan: ERD §3.6; BR-045…050.

**Acceptance Criteria**

- [ ]  purchases UNIQUE (outlet_id, code); status CHECK DRAFT/RECEIVED/CANCELLED
- [ ]  document_sequences PK (outlet_id, doc_type, seq_date) dengan lock FOR UPDATE

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-PUR-002

---

### BE-PUR-001 — CRUD supplier (soft delete, INACTIVE tidak bisa dipilih)

| Field | Value |
| --- | --- |
| ID | BE-PUR-001 |
| Type | backend |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DB-PUR-001, BE-RBAC-001 |
| References | FR-PUR-001; UC-PUR-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S2 |
| Labels | backend, pur, core |
| Branch | feature/be-pur-001-crud-supplier-soft-delete-inactive-tidak |
| PR |  |

**Context**
CRUD supplier (soft delete, INACTIVE tidak bisa dipilih). Rujukan: FR-PUR-001; UC-PUR-01.

**Acceptance Criteria**

- [ ]  Supplier INACTIVE ditolak saat membuat purchase
- [ ]  Permission suppliers.* sesuai matriks (Owner, Manager, Inventory)

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-001, TC-PUR-006

---

### BE-USER-001 — CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit

| Field | Value |
| --- | --- |
| ID | BE-USER-001 |
| Type | backend |
| Module | USER |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-RBAC-001, BE-AUD-001 |
| References | FR-USER-001; UC-USER-01; BR-024 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | backend, user, core |
| Branch | feature/be-user-001-crud-user-assign-role-outlet-nonaktifkan |
| PR |  |

**Context**
CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit. Rujukan: FR-USER-001; UC-USER-01; BR-024.

**Acceptance Criteria**

- [ ]  Manager yang memilih role di luar Cashier/Inventory/Kitchen mendapat 403
- [ ]  Owner terakhir tidak bisa dinonaktifkan atau diganti role

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-USER-001, TC-USER-002

---

### BE-SHF-003 — ShiftSummaryService: Expected Cash + non_cash per metode

| Field | Value |
| --- | --- |
| ID | BE-SHF-003 |
| Type | backend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-SHF-001, DB-PAY-001 |
| References | FR-SHF-003; BR-004, BR-042 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | backend, shf, core |
| Branch | feature/be-shf-003-shiftsummaryservice-expected-cash-non-ca |
| PR |  |

**Context**
ShiftSummaryService: Expected Cash + non_cash per metode. Rujukan: FR-SHF-003; BR-004, BR-042.

**Acceptance Criteria**

- [ ]  Expected = opening + cash sales (bersih kembalian) + in − out − cash refund
- [ ]  Pembayaran non-tunai tampil di non_cash dan tidak masuk Expected Cash

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-003, TC-SHF-007

---

### BE-SHF-004 — Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit

| Field | Value |
| --- | --- |
| ID | BE-SHF-004 |
| Type | backend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-SHF-003, BE-ORD-005 |
| References | FR-SHF-003; UC-SHF-03; BR-003 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | backend, shf, core |
| Branch | feature/be-shf-004-close-shift-tolak-bila-ada-order-draft-p |
| PR |  |

**Context**
Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit. Rujukan: FR-SHF-003; UC-SHF-03; BR-003.

**Acceptance Criteria**

- [ ]  Ada order pending 409 SHIFT_HAS_PENDING_ORDERS dengan jumlah order
- [ ]  Sukses menyimpan expected, actual, difference; shift CLOSED tidak menerima transaksi

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-004, TC-SHF-005

---

### FE-SHF-001 — Dialog open shift (+ banner > 24 jam)

| Field | Value |
| --- | --- |
| ID | FE-SHF-001 |
| Type | frontend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-SHF-005, FE-AUTH-003 |
| References | SCR-SHF-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | frontend, shf, core |
| Branch | feature/fe-shf-001-dialog-open-shift-banner-24-jam |
| PR |  |

**Context**
Dialog open shift (+ banner > 24 jam). Rujukan: SCR-SHF-01.

**Acceptance Criteria**

- [ ]  Banner peringatan tampil saat open_hours_exceeded
- [ ]  Tidak bisa masuk layar POS tanpa shift OPEN

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-SHF-006

---

### BE-PUR-002 — Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO

| Field | Value |
| --- | --- |
| ID | BE-PUR-002 |
| Type | backend |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PUR-001, BE-ING-001, BE-INV-003 |
| References | FR-PUR-002; UC-PUR-02; BR-046, BR-049 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | backend, pur, core |
| Branch | feature/be-pur-002-purchase-draft-buat-ubah-item-satuan-bel |
| PR |  |

**Context**
Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO. Rujukan: FR-PUR-002; UC-PUR-02; BR-046, BR-049.

**Acceptance Criteria**

- [ ]  quantity_base = quantity × faktor (kg→g, L→ml = 1000)
- [ ]  Purchase RECEIVED tidak bisa diubah 409 INVALID_STATE_TRANSITION

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-002, TC-PUR-005

---

### BE-PUR-003 — Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten

| Field | Value |
| --- | --- |
| ID | BE-PUR-003 |
| Type | backend |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PUR-002, BE-INV-002 |
| References | FR-PUR-003; UC-PUR-03; SEQ-PUR-01; BR-045, BR-047 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | backend, pur, core |
| Branch | feature/be-pur-003-receive-purchase-movement-purchase-updat |
| PR |  |

**Context**
Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten. Rujukan: FR-PUR-003; UC-PUR-03; SEQ-PUR-01; BR-045, BR-047.

**Acceptance Criteria**

- [ ]  Receive menambah stok base unit dan last_cost = harga per satuan beli ÷ faktor
- [ ]  Receive ganda tidak menggandakan movement (409 atau replay)

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-003, TC-PUR-004, TC-PUR-005

---

### FE-PUR-001 — Halaman supplier

| Field | Value |
| --- | --- |
| ID | FE-PUR-001 |
| Type | frontend |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-PUR-001, FE-AUTH-003 |
| References | SCR-PUR-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | frontend, pur, core |
| Branch | feature/fe-pur-001-halaman-supplier |
| PR |  |

**Context**
Halaman supplier. Rujukan: SCR-PUR-01.

**Acceptance Criteria**

- [ ]  CRUD supplier dengan state loading/empty/error
- [ ]  Aksi dibatasi v-can suppliers.*

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PUR-001

---

### FE-PUR-002 — Form & daftar purchase (satuan beli, konversi tampil)

| Field | Value |
| --- | --- |
| ID | FE-PUR-002 |
| Type | frontend |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PUR-002, FE-PUR-001 |
| References | SCR-PUR-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | frontend, pur, core |
| Branch | feature/fe-pur-002-form-daftar-purchase-satuan-beli-konvers |
| PR |  |

**Context**
Form & daftar purchase (satuan beli, konversi tampil). Rujukan: SCR-PUR-02.

**Acceptance Criteria**

- [ ]  Konversi ke base unit tampil sebelum simpan
- [ ]  Purchase RECEIVED tampil read-only

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PUR-002

---

### BE-RPT-001 — Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund)

| Field | Value |
| --- | --- |
| ID | BE-RPT-001 |
| Type | backend |
| Module | RPT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PAY-002, BE-ORD-007 |
| References | FR-RPT-001; UC-RPT-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S3 |
| Labels | backend, rpt, core |
| Branch | feature/be-rpt-001-laporan-sales-gross-diskon-service-pajak |
| PR |  |

**Context**
Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund). Rujukan: FR-RPT-001; UC-RPT-01.

**Acceptance Criteria**

- [ ]  Angka cocok dengan fixture; REFUNDED dipisah; pembulatan baris terpisah
- [ ]  Periode memakai business_date; outlet di luar akses 403

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-001, TC-RPT-002, TC-RPT-003, TC-RPT-004, TC-RPT-005

---

### FE-USER-001 — Halaman manajemen user (list, form, role, outlet)

| Field | Value |
| --- | --- |
| ID | FE-USER-001 |
| Type | frontend |
| Module | USER |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-USER-001, FE-AUTH-003 |
| References | UC-USER-01; SCR-USER-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | frontend, user, core |
| Branch | feature/fe-user-001-halaman-manajemen-user-list-form-role-ou |
| PR |  |

**Context**
Halaman manajemen user (list, form, role, outlet). Rujukan: UC-USER-01; SCR-USER-01.

**Acceptance Criteria**

- [ ]  Dropdown role hanya menampilkan role yang boleh dipilih pelaku (GET /roles)
- [ ]  State empty, loading, error tersedia

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-USER-001

---

### FE-CAT-002 — Halaman list + form produk (upload gambar)

| Field | Value |
| --- | --- |
| ID | FE-CAT-002 |
| Type | frontend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-CAT-002, FE-AUTH-003 |
| References | SCR-CAT-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | frontend, cat, core |
| Branch | feature/fe-cat-002-halaman-list-form-produk-upload-gambar |
| PR |  |

**Context**
Halaman list + form produk (upload gambar). Rujukan: SCR-CAT-02.

**Acceptance Criteria**

- [ ]  Error 422 tampil per field
- [ ]  Preview gambar dan batas 2 MB divalidasi sebelum upload

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-CAT-001

---

### FE-CAT-003 — UI manajemen modifier

| Field | Value |
| --- | --- |
| ID | FE-CAT-003 |
| Type | frontend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-CAT-004, FE-CAT-002 |
| References | SCR-CAT-03 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | frontend, cat, core |
| Branch | feature/fe-cat-003-ui-manajemen-modifier |
| PR |  |

**Context**
UI manajemen modifier. Rujukan: SCR-CAT-03.

**Acceptance Criteria**

- [ ]  Group, option, dan pemasangan ke produk dapat dikelola
- [ ]  Aturan min/max tampil jelas

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-CAT-003

---

### BE-SHF-002 — Cash in / cash out (alasan wajib, cash.manage)

| Field | Value |
| --- | --- |
| ID | BE-SHF-002 |
| Type | backend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-SHF-001 |
| References | FR-SHF-002; UC-SHF-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | backend, shf, core |
| Branch | feature/be-shf-002-cash-in-cash-out-alasan-wajib-cash-manag |
| PR |  |

**Context**
Cash in / cash out (alasan wajib, cash.manage). Rujukan: FR-SHF-002; UC-SHF-02.

**Acceptance Criteria**

- [ ]  Alasan kosong 422; tanpa shift OPEN 409
- [ ]  Tercatat append-only dan masuk audit

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-002

---

### FE-SHF-003 — Wizard close shift + ringkasan (cetak)

| Field | Value |
| --- | --- |
| ID | FE-SHF-003 |
| Type | frontend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-SHF-004 |
| References | SCR-SHF-03 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | frontend, shf, core |
| Branch | feature/fe-shf-003-wizard-close-shift-ringkasan-cetak |
| PR |  |

**Context**
Wizard close shift + ringkasan (cetak). Rujukan: SCR-SHF-03.

**Acceptance Criteria**

- [ ]  Expected Cash tampil sebelum input uang fisik
- [ ]  Pesan error order pending menampilkan jumlah

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-SHF-004, TC-SHF-005

---

### TEST-SHF-001 — Test shift: open ganda, selisih kas, close dengan order pending, efek refund

| Field | Value |
| --- | --- |
| ID | TEST-SHF-001 |
| Type | testing |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-SHF-004, BE-PAY-005 |
| References | FR-SHF-001…004 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | testing, shf, core |
| Branch | feature/test-shf-001-test-shift-open-ganda-selisih-kas-close |
| PR |  |

**Context**
Test shift: open ganda, selisih kas, close dengan order pending, efek refund. Rujukan: FR-SHF-001…004.

**Acceptance Criteria**

- [ ]  Formula Expected Cash diuji dengan data campuran (cash, QRIS, refund)
- [ ]  Konkurensi open shift ganda lulus

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-SHF-001…007

---

### BE-PUR-004 — GET daftar & detail purchase (filter status, supplier, tanggal)

| Field | Value |
| --- | --- |
| ID | BE-PUR-004 |
| Type | backend |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-PUR-002 |
| References | FR-PUR-004; UC-PUR-04 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | backend, pur, core |
| Branch | feature/be-pur-004-get-daftar-detail-purchase-filter-status |
| PR |  |

**Context**
GET daftar & detail purchase (filter status, supplier, tanggal). Rujukan: FR-PUR-004; UC-PUR-04.

**Acceptance Criteria**

- [ ]  Filter dan paginasi bekerja; data tenant lain 404
- [ ]  Permission purchases.view

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-006

---

### FE-PUR-003 — Alur terima barang (konfirmasi, hasil stok baru)

| Field | Value |
| --- | --- |
| ID | FE-PUR-003 |
| Type | frontend |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PUR-003, FE-PUR-002 |
| References | SCR-PUR-03 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | frontend, pur, core |
| Branch | feature/fe-pur-003-alur-terima-barang-konfirmasi-hasil-stok |
| PR |  |

**Context**
Alur terima barang (konfirmasi, hasil stok baru). Rujukan: SCR-PUR-03.

**Acceptance Criteria**

- [ ]  Konfirmasi menampilkan ringkasan stok sebelum dan sesudah
- [ ]  Klik ganda aman

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PUR-003, TC-PUR-004

---

### BE-RPT-003 — Laporan payment per metode

| Field | Value |
| --- | --- |
| ID | BE-RPT-003 |
| Type | backend |
| Module | RPT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-RPT-001 |
| References | FR-RPT-003 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S4 |
| Labels | backend, rpt, core |
| Branch | feature/be-rpt-003-laporan-payment-per-metode |
| PR |  |

**Context**
Laporan payment per metode. Rujukan: FR-RPT-003.

**Acceptance Criteria**

- [ ]  Total Cash dan QRIS cocok dengan payments
- [ ]  Cashier hanya ringkasan miliknya

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-001

---

### BE-AUTH-003 — GET/PUT /me dan PUT /me/password

| Field | Value |
| --- | --- |
| ID | BE-AUTH-003 |
| Type | backend |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-AUTH-002 |
| References | FR-AUTH-003; UC-AUTH-03 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | backend, auth, core |
| Branch | feature/be-auth-003-get-put-me-dan-put-me-password |
| PR |  |

**Context**
GET/PUT /me dan PUT /me/password. Rujukan: FR-AUTH-003; UC-AUTH-03.

**Acceptance Criteria**

- [ ]  Password lama salah mengembalikan 422 “Password lama tidak sesuai.”
- [ ]  Ganti password mengakhiri sesi lain milik user

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-009

---

### BE-USER-003 — GET/PUT /business (profil + license tanpa enforcement)

| Field | Value |
| --- | --- |
| ID | BE-USER-003 |
| Type | backend |
| Module | USER |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | XS |
| Status | todo |
| Depends on | BE-RBAC-001 |
| References | FR-BIZ-001; UC-BIZ-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | backend, user, core |
| Branch | feature/be-user-003-get-put-business-profil-license-tanpa-en |
| PR |  |

**Context**
GET/PUT /business (profil + license tanpa enforcement). Rujukan: FR-BIZ-001; UC-BIZ-01.

**Acceptance Criteria**

- [ ]  Hanya Owner yang dapat mengubah; perubahan tercatat audit
- [ ]  license_plan dan license_valid_until tersimpan tanpa memblokir fitur

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-USER-003

---

### FE-AUTH-002 — Halaman Register Business

| Field | Value |
| --- | --- |
| ID | FE-AUTH-002 |
| Type | frontend |
| Module | AUTH |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-AUTH-001, SETUP-006 |
| References | UC-AUTH-01; SCR-AUTH-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | frontend, auth, core |
| Branch | feature/fe-auth-002-halaman-register-business |
| PR |  |

**Context**
Halaman Register Business. Rujukan: UC-AUTH-01; SCR-AUTH-02.

**Acceptance Criteria**

- [ ]  Validasi per field menampilkan pesan SRS
- [ ]  Sukses mengarahkan ke login/onboarding

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUTH-001

---

### FE-USER-002 — Halaman pengaturan outlet dan profil business

| Field | Value |
| --- | --- |
| ID | FE-USER-002 |
| Type | frontend |
| Module | USER |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-USER-002, BE-USER-003, FE-AUTH-003 |
| References | UC-BIZ-02; SCR-BIZ-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | frontend, user, core |
| Branch | feature/fe-user-002-halaman-pengaturan-outlet-dan-profil-bus |
| PR |  |

**Context**
Halaman pengaturan outlet dan profil business. Rujukan: UC-BIZ-02; SCR-BIZ-01.

**Acceptance Criteria**

- [ ]  Field di luar hak Manager tampil nonaktif
- [ ]  Tarif di luar 0–100 menampilkan pesan SRS

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-USER-003

---

### BE-CAT-003 — Audit perubahan harga + guard produk INACTIVE + soft delete

| Field | Value |
| --- | --- |
| ID | BE-CAT-003 |
| Type | backend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-CAT-002, BE-AUD-001 |
| References | FR-CAT-002; BR-018, BR-029 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | backend, cat, core |
| Branch | feature/be-cat-003-audit-perubahan-harga-guard-produk-inact |
| PR |  |

**Context**
Audit perubahan harga + guard produk INACTIVE + soft delete. Rujukan: FR-CAT-002; BR-018, BR-029.

**Acceptance Criteria**

- [ ]  Perubahan selling_price menulis PRODUCT_PRICE_CHANGED (before/after)
- [ ]  Produk yang pernah dipakai transaksi tidak bisa dihapus permanen

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-004

---

### FE-CAT-001 — Halaman kategori

| Field | Value |
| --- | --- |
| ID | FE-CAT-001 |
| Type | frontend |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-CAT-001, FE-AUTH-003 |
| References | SCR-CAT-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | frontend, cat, core |
| Branch | feature/fe-cat-001-halaman-kategori |
| PR |  |

**Context**
Halaman kategori. Rujukan: SCR-CAT-01.

**Acceptance Criteria**

- [ ]  CRUD kategori jalan dengan state loading/empty/error
- [ ]  Aksi dibatasi v-can products.*

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-CAT-002

---

### FE-SHF-002 — Dialog cash in / cash out

| Field | Value |
| --- | --- |
| ID | FE-SHF-002 |
| Type | frontend |
| Module | SHF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-SHF-002 |
| References | SCR-SHF-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | frontend, shf, core |
| Branch | feature/fe-shf-002-dialog-cash-in-cash-out |
| PR |  |

**Context**
Dialog cash in / cash out. Rujukan: SCR-SHF-02.

**Acceptance Criteria**

- [ ]  Alasan wajib divalidasi
- [ ]  Saldo ringkasan ter-refresh setelah simpan

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-SHF-002

---

### TEST-PUR-001 — Test purchasing: konversi, last_cost, idempotensi, permission

| Field | Value |
| --- | --- |
| ID | TEST-PUR-001 |
| Type | testing |
| Module | PUR |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PUR-003 |
| References | FR-PUR-001…004 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | testing, pur, core |
| Branch | feature/test-pur-001-test-purchasing-konversi-last-cost-idemp |
| PR |  |

**Context**
Test purchasing: konversi, last_cost, idempotensi, permission. Rujukan: FR-PUR-001…004.

**Acceptance Criteria**

- [ ]  Semua BR-045…050 punya test
- [ ]  balance = SUM(movements) tetap benar setelah receive

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PUR-001…006

---

### FE-RPT-001 — Halaman laporan sales & payment

| Field | Value |
| --- | --- |
| ID | FE-RPT-001 |
| Type | frontend |
| Module | RPT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-RPT-003, FE-AUTH-003 |
| References | SCR-RPT-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | S5 |
| Labels | frontend, rpt, core |
| Branch | feature/fe-rpt-001-halaman-laporan-sales-payment |
| PR |  |

**Context**
Halaman laporan sales & payment. Rujukan: SCR-RPT-01.

**Acceptance Criteria**

- [ ]  Filter tanggal/outlet/kasir/metode
- [ ]  State empty dan loading ada

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-RPT-001

---

### BE-AUD-002 — GET /audit-logs + filter (tanggal, user, aksi, resource)

| Field | Value |
| --- | --- |
| ID | BE-AUD-002 |
| Type | backend |
| Module | AUD |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-AUD-001, BE-RBAC-001 |
| References | FR-AUD-002; UC-AUD-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | backend, aud, r1.1 |
| Branch | feature/be-aud-002-get-audit-logs-filter-tanggal-user-aksi |
| PR |  |

**Context**
GET /audit-logs + filter (tanggal, user, aksi, resource). Rujukan: FR-AUD-002; UC-AUD-01.

**Acceptance Criteria**

- [ ]  Filter tanggal, user, aksi, resource bekerja
- [ ]  Manager hanya melihat outlet miliknya

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUD-003

---

### FE-AUD-001 — Halaman audit log

| Field | Value |
| --- | --- |
| ID | FE-AUD-001 |
| Type | frontend |
| Module | AUD |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-AUD-002, FE-AUTH-003 |
| References | UC-AUD-01; SCR-AUD-01 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | frontend, aud, r1.1 |
| Branch | feature/fe-aud-001-halaman-audit-log |
| PR |  |

**Context**
Halaman audit log. Rujukan: UC-AUD-01; SCR-AUD-01.

**Acceptance Criteria**

- [ ]  Daftar memakai paginasi dan filter
- [ ]  Order/aksi sensitif menampilkan before/after

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUD-003

---

### BE-PUR-005 — Cancel purchase DRAFT + partial receive (purchase_receipts)

| Field | Value |
| --- | --- |
| ID | BE-PUR-005 |
| Type | backend |
| Module | PUR |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PUR-003 |
| References | FR-PUR-005; UC-PUR-05 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | backend, pur, r1.1 |
| Branch | feature/be-pur-005-cancel-purchase-draft-partial-receive-pu |
| PR |  |

**Context**
Cancel purchase DRAFT + partial receive (purchase_receipts). Rujukan: FR-PUR-005; UC-PUR-05.

**Acceptance Criteria**

- [ ]  Cancel hanya DRAFT dengan alasan wajib
- [ ]  Partial receive memotong sisa dan status mengikuti jumlah diterima

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-007

---

### BE-RPT-002 — Laporan produk + daftar no recipe

| Field | Value |
| --- | --- |
| ID | BE-RPT-002 |
| Type | backend |
| Module | RPT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-RPT-001 |
| References | FR-RPT-002; BR-010 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | backend, rpt, r1.1 |
| Branch | feature/be-rpt-002-laporan-produk-daftar-no-recipe |
| PR |  |

**Context**
Laporan produk + daftar no recipe. Rujukan: FR-RPT-002; BR-010.

**Acceptance Criteria**

- [ ]  Produk tanpa recipe aktif tampil di daftar
- [ ]  Revenue dan qty cocok dengan fixture

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-006

---

### BE-RPT-004 — Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian

| Field | Value |
| --- | --- |
| ID | BE-RPT-004 |
| Type | backend |
| Module | RPT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-006, BE-INV-005, BE-PUR-003 |
| References | FR-RPT-004 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | backend, rpt, r1.1 |
| Branch | feature/be-rpt-004-laporan-inventory-stok-movement-per-tipe |
| PR |  |

**Context**
Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian. Rujukan: FR-RPT-004.

**Acceptance Criteria**

- [ ]  Stok minus muncul pada laporan
- [ ]  Waste menampilkan biaya (Kitchen tanpa nilai)

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-008

---

### BE-RPT-005 — Dashboard permission-aware (timezone outlet, cache TTL pendek)

| Field | Value |
| --- | --- |
| ID | BE-RPT-005 |
| Type | backend |
| Module | RPT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | BE-RPT-001, BE-INV-006, BE-SHF-005 |
| References | FR-RPT-005; UC-RPT-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | backend, rpt, r1.1 |
| Branch | feature/be-rpt-005-dashboard-permission-aware-timezone-outl |
| PR |  |

**Context**
Dashboard permission-aware (timezone outlet, cache TTL pendek). Rujukan: FR-RPT-005; UC-RPT-02.

**Acceptance Criteria**

- [ ]  Widget sesuai permission; Cashier hanya data shift sendiri
- [ ]  Rata-rata order = net sales ÷ jumlah order

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-007

---

### FE-RPT-002 — Halaman laporan produk, inventory, waste

| Field | Value |
| --- | --- |
| ID | FE-RPT-002 |
| Type | frontend |
| Module | RPT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | BE-RPT-004, BE-RPT-002 |
| References | SCR-RPT-02 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | frontend, rpt, r1.1 |
| Branch | feature/fe-rpt-002-halaman-laporan-produk-inventory-waste |
| PR |  |

**Context**
Halaman laporan produk, inventory, waste. Rujukan: SCR-RPT-02.

**Acceptance Criteria**

- [ ]  Daftar no recipe dan stok minus menonjol
- [ ]  Nilai biaya disembunyikan sesuai role

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-RPT-006, TC-RPT-008

---

### FE-RPT-003 — Dashboard

| Field | Value |
| --- | --- |
| ID | FE-RPT-003 |
| Type | frontend |
| Module | RPT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | BE-RPT-005 |
| References | SCR-RPT-03 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | frontend, rpt, r1.1 |
| Branch | feature/fe-rpt-003-dashboard |
| PR |  |

**Context**
Dashboard. Rujukan: SCR-RPT-03.

**Acceptance Criteria**

- [ ]  Widget mengikuti respons permission
- [ ]  Polling/refresh tidak membebani (cache TTL)

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-RPT-007

---

### BE-RPT-006 — Export CSV sales dan stock movement (stream, reports.export)

| Field | Value |
| --- | --- |
| ID | BE-RPT-006 |
| Type | backend |
| Module | RPT |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-RPT-001, BE-INV-006 |
| References | FR-RPT-006; UC-RPT-03 |
| Assignee | @naufaldi |
| Reviewer | @ongki |
| Sprint | R1.1 |
| Labels | backend, rpt, r1.1 |
| Branch | feature/be-rpt-006-export-csv-sales-dan-stock-movement-stre |
| PR |  |

**Context**
Export CSV sales dan stock movement (stream, reports.export). Rujukan: FR-RPT-006; UC-RPT-03.

**Acceptance Criteria**

- [ ]  Filter sama dengan layar; hanya business/outlet user
- [ ]  Tanpa permission 403

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-009

---