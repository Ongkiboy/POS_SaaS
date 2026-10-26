# Task Breakdown — F&B POS & Inventory
> **Versi:** 3.0 (Baseline final) | **Tanggal:** 2026-10-25 | **Mode tiket:** Tim (3 developer)
> **Referensi:** PRD v3.0 · SRS v3.0 · Use Case v3.0 · SDD v3.0 · ERD v3.0 · UI/UX v3.0 · Technical Design v3.0 · Test Plan v3.0
> **Total:** 148 tiket = **115,75 hari-orang** (core 126 tiket / 101,00 hari; rilis 1.1 22 tiket / 14,75 hari)
> **Sumber tiket lengkap (AC, dependency, referensi):** `tickets/tickets.md` — ID tiket = ID task.

---

## 1. Panduan
### Konvensi ID
`[TIPE]-[MODUL]-[NOMOR]` · tipe: SETUP, BE, FE, DB, INT, TEST, DEPLOY, DOC (+ `DB-SEED-001` untuk data demo). Setiap task BE/FE menunjuk FR/UC/SEQ/SCR; setiap task fitur punya Test Reference (TC di Test Plan).

### Estimasi (maks 1 hari per tiket)
| Label | Waktu | Hari-orang di rencana |
|-------|-------|-----------------------|
| XS | < 2 jam | 0,25 |
| S | 2–4 jam | 0,5 |
| M | 4–8 jam | 1 |
Tidak ada tiket L/XL; pekerjaan besar sudah dipecah.

### Status
`🔲 Todo` → `🔄 In Progress` → `👀 Review` → `✅ Done` | `❌ Blocked` (WIP limit: 1 tiket In Progress per orang)

---

## 2. Kapasitas dan Beban
**Kapasitas** (26 Okt–30 Des 2026): 47 hari kerja (25 Des libur) × 3 dev × 75% produktif = **105,75 hari-orang**. Tiap dev: 35,25 hari tugas (7,5 per sprint 2 minggu; S5 = 5,25).

| | Hari-orang |
|---|---|
| Kapasitas | 105,75 |
| Kebutuhan core | 101,00 |
| **Margin core (tanpa buffer 20%)** | **4,75 (±4%)** |
| Rilis 1.1 | 14,75 |

> **Peringatan:** buffer 20% yang lazim tidak tersedia. Margin ±4,75 hari-orang ditutup dengan: (a) cut list §4, (b) 30 Des sebagai hari cadangan, (c) jalur kritis §3 dikerjakan lebih dulu. Estimasi belum dikalibrasi tim: **kalibrasi ulang di akhir S1** dan laporkan penyimpangan.

### Beban per developer (core)
| Developer | Modul utama | Core (hari) | Rilis 1.1 (hari) |
|-----------|-------------|-------------|------------------|
| Ongki | Setup/infra, tenant, auth/RBAC (BE), audit, POS order, payment/refund, deploy | 35,50 | 1,00 |
| Akmal | Ingredient, recipe, inventory, engine Sale→Inventory, PWA, seed, E2E/security/perf test | 33,25 | 6,75 |
| Naufaldi | Katalog, user/outlet/business, FE auth, shift, purchase, laporan | 32,25 | 7,00 |

Perubahan dari pembagian awal yang disetujui (F3): Purchase (BE, FE, test) tetap di Naufaldi; CI/linter (SETUP-003/004), test katalog, arch test modul, dan test keamanan dipindah ke Akmal untuk menyeimbangkan beban; dokumentasi (DOC-001/002) ditunda ke rilis 1.1.

### Beban per sprint (hari tugas, kapasitas dalam kurung)
| Sprint | Tanggal | Ongki | Akmal | Naufaldi |
|--------|---------|-------|-------|----------|
| S1 | 26 Okt–6 Nov | 7,50 (7,50) | 5,25 (7,50) | 4,00 (7,50) |
| S2 | 9–20 Nov | 7,50 (7,50) | 7,50 (7,50) | 7,50 (7,50) |
| S3 | 23 Nov–4 Des | 7,50 (7,50) | 8,00 (7,50) | 8,00 (7,50) |
| S4 | 7–18 Des | 7,50 (7,50) | 7,00 (7,50) | 7,50 (7,50) |
| S5 | 21–30 Des | 5,50 (5,25) | 5,50 (5,25) | 5,25 (5,25) |

Sprint dihitung dari simulasi penjadwalan: tiket diurutkan menurut jalur kritis, dependency lintas developer dihormati, dan tiket mengisi celah kosong developer. Sprint di kolom tiket adalah **rencana awal**; tim memperbarui saat sprint planning.

## 3. Jalur Kritis dan Urutan Kerja
Rantai dependency terpanjang (kerjakan tanpa jeda; keterlambatan di sini menggeser seluruh rilis):
`SETUP-001 → SETUP-005 → DB-TEN-001 → DB-TEN-002 → BE-RBAC-001 → BE-CAT-001 → BE-CAT-002 → BE-CAT-004 → BE-CAT-005 → BE-ORD-003 → BE-ORD-005 → BE-PAY-002 → BE-PAY-003 → INT-ENG-001 → TEST-E2E-001 → DEPLOY-002`

**Walking skeleton** (target CP3, 4 Des): register → login → pilih outlet → buka shift → order → bayar tunai → stok turun → struk. Semua tiket penyusunnya diberi prioritas atas tiket pendukung.

**Titik sinkron antar developer**
| Titik | Pemberi | Penerima | Kenapa penting |
|-------|---------|----------|----------------|
| `BE-RBAC-001`, `DB-TEN-002` | Ongki | Semua | Semua endpoint butuh permission dan tenant scope |
| `BE-INV-001/002` | Akmal | Ongki, Naufaldi | `StockService` dipakai bayar (BE-PAY-003) dan purchase (BE-PUR-003) |
| `BE-REC-001`, `BE-CAT-005` | Akmal, Naufaldi | Ongki | Snapshot order butuh katalog dan recipe |
| `DB-SHF-001`, `BE-SHF-005` | Naufaldi | Ongki | Order wajib shift OPEN |
| `BE-ENG-002` | Akmal | Ongki | Dipanggil di dalam transaksi bayar |

## 4. Cut List dan Aturan Eskalasi
Bila akhir **S2 (20 Nov)** beban terlambat > 2 hari-orang, atau akhir **S4 (18 Des)** ada tiket core belum Done, potong **berurutan** (setelah disetujui Ongki):
| Urutan | Tiket | Hemat | Konsekuensi |
|--------|-------|-------|-------------|
| 1 | `FE-ENG-001` indikator stok di POS | 0,5 | Kasir tidak melihat peringatan stok di layar POS |
| 2 | `FE-PWA-002` banner offline | 0,5 | Offline tidak diberi tahu eksplisit |
| 3 | `TEST-E2E-002` E2E inventory/purchase | 1 | Diuji manual saat UAT |
| 4 | `TEST-PERF-001` load test | 1 | Diuji sederhana (skrip 20 request paralel) |
| 5 | `FE-RPT-001` dipersingkat jadi satu layar sales+payment | 0,5 | Filter terbatas |
| 6 | `FE-PUR-001/002` digabung satu layar | 1 | UX Purchase lebih sederhana |
Yang **tidak boleh** dipotong: seluruh `StockService`, engine, payment, refund, shift, tenant isolation, audit pencatatan, dan test terkait.

## 5. Daftar Task per Fase dan Modul
Status awal semua tiket: 🔲. Kolom **Sprint** = rencana awal.


### 🚀 FASE 0 — SETUP & INFRASTRUKTUR (S1)
> 9 tiket, 7.00 hari-orang

#### Modul: Setup & Infrastruktur

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| SETUP-001 | Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template | S | @ongki | — | ADR-001; TD §1, §7 | S1 | 🔲 |
| SETUP-002 | Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment | S | @ongki | SETUP-001 | ADR-006; TD §6 | S4 | 🔲 |
| SETUP-003 | Pint, Larastan, ESLint, Prettier, pre-commit hook | S | @akmal | SETUP-001 | TD §7 | S1 | 🔲 |
| SETUP-004 | CI GitHub Actions: lint + Pest + build frontend | M | @akmal | SETUP-003 | DEPLOY ops §1 | S1 | 🔲 |
| SETUP-005 | Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1 | M | @ongki | SETUP-001 | TD §2, §2.4; SDD §4.2 | S1 | 🔲 |
| SETUP-006 | Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog | M | @naufaldi | SETUP-001 | TD §3; ADR-001 | S1 | 🔲 |
| SETUP-007 | Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test | S | @ongki | SETUP-005 | ADR-004; SRS §1.4 | S2 | 🔲 |
| SETUP-008 | Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite) | M | @ongki | SETUP-004 | NFR-SEC-003; TD §5 | S3 | 🔲 |
| SETUP-009 | Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi | M | @akmal | SETUP-005 | TD §2.3, §2.4 | S1 | 🔲 |


### 📦 FASE 1 — CORE (S1–S5, target 29–30 Des 2026)
> 117 tiket, 94.00 hari-orang

#### Modul: Platform: Tenant, Auth, RBAC, Audit

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-TEN-001 | Migrasi businesses, outlets, users, user_outlets | M | @ongki | SETUP-005 | ERD §3.1; BR-022 | S1 | 🔲 |
| DB-TEN-002 | Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet | M | @ongki | DB-TEN-001 | ADR-003; FR-USER-002 | S1 | 🔲 |
| DB-RBAC-001 | Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own | M | @ongki | DB-TEN-001 | ADR-005; SRS §2.1 | S1 | 🔲 |
| DB-AUD-001 | Migrasi audit_logs + trigger append-only | S | @ongki | DB-TEN-001 | ADR-007; BR-020 | S1 | 🔲 |
| BE-AUD-001 | AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue | S | @ongki | DB-AUD-001, SETUP-005 | FR-AUD-001 | S1 | 🔲 |
| BE-RBAC-001 | Middleware permission + outlet.context, Policy aturan Limited/Own | M | @ongki | DB-RBAC-001, DB-TEN-002 | FR-USER-002; BR-024, BR-025 | S1 | 🔲 |
| BE-AUTH-001 | POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi) | M | @ongki | DB-TEN-002, DB-RBAC-001, BE-AUD-001 | FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01 | S1 | 🔲 |
| BE-AUTH-002 | Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED | M | @ongki | BE-AUTH-001 | FR-AUTH-002; UC-AUTH-02; BR-023 | S2 | 🔲 |
| BE-AUTH-003 | GET/PUT /me dan PUT /me/password | S | @naufaldi | BE-AUTH-002 | FR-AUTH-003; UC-AUTH-03 | S5 | 🔲 |
| BE-AUTH-004 | /me mengembalikan outlet + permission dengan scope | S | @ongki | BE-AUTH-002, BE-RBAC-001 | FR-AUTH-004 | S2 | 🔲 |
| BE-USER-001 | CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit | M | @naufaldi | BE-RBAC-001, BE-AUD-001 | FR-USER-001; UC-USER-01; BR-024 | S3 | 🔲 |
| BE-USER-002 | CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk) | M | @naufaldi | BE-RBAC-001, BE-AUD-001 | FR-BIZ-002, FR-BIZ-003; UC-BIZ-02 | S2 | 🔲 |
| BE-USER-003 | GET/PUT /business (profil + license tanpa enforcement) | XS | @naufaldi | BE-RBAC-001 | FR-BIZ-001; UC-BIZ-01 | S5 | 🔲 |
| FE-AUTH-001 | Halaman Login | M | @naufaldi | BE-AUTH-002, SETUP-006 | UC-AUTH-02; SCR-AUTH-01 | S2 | 🔲 |
| FE-AUTH-002 | Halaman Register Business | S | @naufaldi | BE-AUTH-001, SETUP-006 | UC-AUTH-01; SCR-AUTH-02 | S5 | 🔲 |
| FE-AUTH-003 | Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher | M | @naufaldi | FE-AUTH-001, BE-AUTH-004 | FR-AUTH-004; ADR-001 | S2 | 🔲 |
| FE-USER-001 | Halaman manajemen user (list, form, role, outlet) | M | @naufaldi | BE-USER-001, FE-AUTH-003 | UC-USER-01; SCR-USER-01 | S4 | 🔲 |
| FE-USER-002 | Halaman pengaturan outlet dan profil business | S | @naufaldi | BE-USER-002, BE-USER-003, FE-AUTH-003 | UC-BIZ-02; SCR-BIZ-01 | S5 | 🔲 |
| TEST-AUTH-001 | Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint) | M | @ongki | BE-USER-001 | NFR-SEC-001; ADR-003 | S3 | 🔲 |
| TEST-RBAC-001 | Test matriks role × endpoint | M | @ongki | BE-USER-001 | SRS §2.1 | S4 | 🔲 |

#### Modul: Katalog

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-CAT-001 | Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups | M | @naufaldi | DB-TEN-002 | ERD §3.2 | S1 | 🔲 |
| BE-CAT-001 | CRUD category (status, sort order) | S | @naufaldi | DB-CAT-001, BE-RBAC-001 | FR-CAT-001; UC-CAT-01 | S1 | 🔲 |
| BE-CAT-002 | CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp) | M | @naufaldi | BE-CAT-001 | FR-CAT-002; UC-CAT-01; SEQ-CAT-01 | S1 | 🔲 |
| BE-CAT-003 | Audit perubahan harga + guard produk INACTIVE + soft delete | S | @naufaldi | BE-CAT-002, BE-AUD-001 | FR-CAT-002; BR-018, BR-029 | S5 | 🔲 |
| BE-CAT-004 | CRUD modifier group & option + pasang ke produk | M | @naufaldi | BE-CAT-002 | FR-CAT-003; UC-CAT-02; BR-030 | S2 | 🔲 |
| BE-CAT-005 | GET /pos/catalog dengan cache + invalidasi saat katalog berubah | S | @naufaldi | BE-CAT-004 | FR-CAT-004; SEQ-CAT-01 | S2 | 🔲 |
| FE-CAT-001 | Halaman kategori | S | @naufaldi | BE-CAT-001, FE-AUTH-003 | SCR-CAT-01 | S5 | 🔲 |
| FE-CAT-002 | Halaman list + form produk (upload gambar) | M | @naufaldi | BE-CAT-002, FE-AUTH-003 | SCR-CAT-02 | S4 | 🔲 |
| FE-CAT-003 | UI manajemen modifier | M | @naufaldi | BE-CAT-004, FE-CAT-002 | SCR-CAT-03 | S4 | 🔲 |
| TEST-CAT-001 | Feature test katalog (tenant, SKU unik, inactive, cache) | M | @akmal | BE-CAT-005 | FR-CAT-001…004 | S3 | 🔲 |

#### Modul: Ingredient & Recipe

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-ING-001 | Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000) | M | @akmal | DB-TEN-002 | ERD §3.2; BR-046 | S1 | 🔲 |
| BE-ING-001 | CRUD ingredient (permission inventory.adjust, baca inventory.view) | M | @akmal | DB-ING-001, BE-RBAC-001 | FR-ING-001; UC-ING-01 | S1 | 🔲 |
| BE-ING-002 | Seeder unit + endpoint GET /units | XS | @akmal | DB-ING-001 | FR-ING-001 | S1 | 🔲 |
| DB-REC-001 | Migrasi recipes (versi), recipe_items, modifier_option_recipe_items | S | @akmal | DB-ING-001, DB-CAT-001 | ERD §3.2; BR-013 | S1 | 🔲 |
| BE-REC-001 | Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP | M | @akmal | DB-REC-001, BE-ING-001, BE-CAT-002 | FR-REC-001; UC-REC-01; SEQ-REC-01 | S2 | 🔲 |
| BE-REC-002 | Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price | S | @akmal | BE-REC-001 | FR-REC-003; BR-014 | S2 | 🔲 |
| FE-ING-001 | Halaman ingredient | M | @akmal | BE-ING-001, FE-AUTH-003 | SCR-ING-01 | S3 | 🔲 |
| FE-REC-001 | Editor recipe di form produk + preview HPP | M | @akmal | BE-REC-002, FE-CAT-002 | SCR-CAT-02; UC-REC-01 | S4 | 🔲 |
| TEST-REC-001 | Test recipe (versioning, validasi, HPP) | S | @akmal | BE-REC-002 | FR-REC-001, FR-REC-003 | S5 | 🔲 |

#### Modul: Inventory

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-INV-001 | Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches | M | @akmal | DB-ING-001, DB-AUD-001 | ERD §3.3; ADR-007 | S2 | 🔲 |
| BE-INV-001 | StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative) | M | @akmal | DB-INV-001 | ADR-002; ACT-INV-01 | S2 | 🔲 |
| BE-INV-002 | Idempotensi movement (referensi sama = movement lama) + helper reverse() | S | @akmal | BE-INV-001 | BR-009; NFR-DATA-003 | S2 | 🔲 |
| BE-INV-003 | POST /inventory/opening-stock (batch + movement OPENING) | S | @akmal | BE-INV-001 | FR-INV-001; UC-INV-01; SEQ-INV-01 | S2 | 🔲 |
| BE-INV-004 | POST/GET /inventory/adjustments (record dibuat sebelum movement) | M | @akmal | BE-INV-001, BE-AUD-001 | FR-INV-003; UC-INV-03 | S2 | 🔲 |
| BE-INV-005 | POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya) | M | @akmal | BE-INV-001, BE-AUD-001 | FR-INV-004; UC-INV-04; BR-034 | S3 | 🔲 |
| BE-INV-006 | GET stok, riwayat movement, low stock + stok minus | S | @akmal | BE-INV-001 | FR-INV-002, FR-INV-005; UC-INV-02 | S3 | 🔲 |
| FE-INV-001 | Halaman stok + badge low stock/minus | M | @akmal | BE-INV-006, FE-AUTH-003 | SCR-INV-01 | S3 | 🔲 |
| FE-INV-002 | Halaman riwayat movement | S | @akmal | BE-INV-006 | SCR-INV-02 | S5 | 🔲 |
| FE-INV-003 | Form opening stock & adjustment | M | @akmal | BE-INV-003, BE-INV-004 | SCR-INV-03 | S4 | 🔲 |
| FE-INV-004 | Form & daftar waste | S | @akmal | BE-INV-005 | SCR-INV-04 | S3 | 🔲 |
| TEST-INV-001 | Unit test StockService: konkurensi, minus, idempotensi, presisi desimal | M | @akmal | BE-INV-002 | NFR-DATA-001 | S4 | 🔲 |
| TEST-INV-002 | Feature test opening, adjustment, waste, stok | M | @akmal | BE-INV-005, BE-INV-006 | FR-INV-001…005 | S4 | 🔲 |

#### Modul: Shift & Kas

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-SHF-001 | Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet) | S | @naufaldi | DB-TEN-002 | ERD §3.4; BR-002 | S1 | 🔲 |
| BE-SHF-001 | Open shift (opening cash, business_date menurut timezone outlet) | M | @naufaldi | DB-SHF-001, BE-RBAC-001 | FR-SHF-001; UC-SHF-01; SEQ-SHF-01 | S2 | 🔲 |
| BE-SHF-002 | Cash in / cash out (alasan wajib, cash.manage) | S | @naufaldi | BE-SHF-001 | FR-SHF-002; UC-SHF-02 | S4 | 🔲 |
| BE-SHF-003 | ShiftSummaryService: Expected Cash + non_cash per metode | M | @naufaldi | BE-SHF-001, DB-PAY-001 | FR-SHF-003; BR-004, BR-042 | S3 | 🔲 |
| BE-SHF-004 | Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit | M | @naufaldi | BE-SHF-003, BE-ORD-005 | FR-SHF-003; UC-SHF-03; BR-003 | S3 | 🔲 |
| BE-SHF-005 | GET /shifts/current + open_hours_exceeded + middleware shift.open | S | @naufaldi | BE-SHF-001 | FR-SHF-004; BR-026 | S2 | 🔲 |
| FE-SHF-001 | Dialog open shift (+ banner > 24 jam) | S | @naufaldi | BE-SHF-005, FE-AUTH-003 | SCR-SHF-01 | S3 | 🔲 |
| FE-SHF-002 | Dialog cash in / cash out | S | @naufaldi | BE-SHF-002 | SCR-SHF-02 | S5 | 🔲 |
| FE-SHF-003 | Wizard close shift + ringkasan (cetak) | M | @naufaldi | BE-SHF-004 | SCR-SHF-03 | S4 | 🔲 |
| TEST-SHF-001 | Test shift: open ganda, selisih kas, close dengan order pending, efek refund | M | @naufaldi | BE-SHF-004, BE-PAY-005 | FR-SHF-001…004 | S4 | 🔲 |

#### Modul: POS Order

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-ORD-001 | Migrasi orders, order_items, order_item_modifiers, order_sequences | M | @ongki | DB-CAT-001, DB-SHF-001, DB-REC-001 | ERD §3.4 | S2 | 🔲 |
| BE-ORD-001 | OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut} | S | @ongki | DB-ORD-001 | FR-ORD-007; BR-019 | S2 | 🔲 |
| BE-ORD-002 | PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total | M | @ongki | SETUP-007, BE-USER-002 | FR-ORD-002; BR-005, BR-036; ACT-ORD-02 | S2 | 🔲 |
| BE-ORD-003 | Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier | M | @ongki | BE-ORD-001, BE-ORD-002, BE-CAT-005, BE-SHF-005, BE-REC-001, BE-REC-002 | FR-ORD-001; UC-ORD-01; SEQ-ORD-01 | S2 | 🔲 |
| BE-ORD-004 | Diskon order-level (persen/nominal), batas Cashier per outlet, audit | S | @ongki | BE-ORD-003 | FR-ORD-003; UC-ORD-02 | S4 | 🔲 |
| BE-ORD-005 | State machine order: confirm, reopen (guard gateway PENDING) | M | @ongki | BE-ORD-003 | FR-ORD-004; STM-ORDER; BR-028 | S2 | 🔲 |
| BE-ORD-006 | Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit) | S | @ongki | BE-ORD-005, BE-AUD-001 | FR-ORD-005; UC-ORD-03; BR-025 | S4 | 🔲 |
| BE-ORD-007 | Riwayat & detail order (Cashier hanya miliknya) | S | @ongki | BE-ORD-003 | FR-ORD-006; UC-ORD-04 | S3 | 🔲 |
| FE-ORD-001 | Layout layar POS responsif (grid kategori/produk + cart) | M | @ongki | BE-CAT-005, FE-SHF-001 | SCR-POS-01 | S3 | 🔲 |
| FE-ORD-002 | Cart store (Pinia), modifier picker, qty | M | @ongki | FE-ORD-001, BE-ORD-003 | SCR-POS-01, SCR-POS-02 | S3 | 🔲 |
| FE-ORD-003 | Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server) | M | @ongki | FE-ORD-002, BE-ORD-004 | SCR-POS-03 | S4 | 🔲 |
| FE-ORD-004 | Halaman riwayat & detail order | M | @ongki | BE-ORD-007 | SCR-POS-05 | S4 | 🔲 |
| TEST-ORD-001 | Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan) | M | @ongki | BE-ORD-002 | BR-005, BR-036 | S5 | 🔲 |
| TEST-ORD-002 | Feature test lifecycle order & cancel | M | @ongki | BE-ORD-006 | STM-ORDER | S5 | 🔲 |

#### Modul: Payment, Struk, Refund

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-PAY-001 | Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments) | M | @ongki | DB-ORD-001 | ERD §3.4; Lampiran SDD B | S2 | 🔲 |
| BE-PAY-001 | Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif | M | @ongki | DB-PAY-001 | FR-PAY-001; BR-007 | S3 | 🔲 |
| BE-PAY-002 | POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik | M | @ongki | BE-PAY-001, BE-ORD-005 | FR-PAY-001; UC-PAY-01; SEQ-PAY-01 | S3 | 🔲 |
| BE-PAY-003 | Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit | S | @ongki | BE-PAY-002, BE-ENG-002 | ADR-002; FR-ENG-001 | S3 | 🔲 |
| BE-PAY-004 | Builder data struk (pembulatan, metode, kembalian) | S | @ongki | BE-PAY-002 | FR-PAY-002; UC-PAY-02 | S3 | 🔲 |
| BE-PAY-005 | Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit | M | @ongki | BE-PAY-002, BE-AUD-001, BE-ENG-004 | FR-RFD-001; UC-RFD-01; SEQ-RFD-01 | S4 | 🔲 |
| FE-PAY-001 | Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key) | M | @ongki | BE-PAY-002, FE-ORD-003 | SCR-POS-04 | S5 | 🔲 |
| FE-PAY-002 | Tampilan struk + CSS print 58/80 mm | M | @ongki | BE-PAY-004 | SCR-POS-06 | S4 | 🔲 |
| FE-PAY-003 | Dialog refund (alasan, restock) | S | @ongki | BE-PAY-005, FE-ORD-004 | SCR-POS-07 | S5 | 🔲 |
| TEST-PAY-001 | Test pembayaran: double submit, overpay, status invalid, kembalian, rollback | M | @ongki | BE-PAY-003 | FR-PAY-001; NFR-DATA-002/003 | S5 | 🔲 |
| TEST-PAY-002 | Test refund (kas, status, permission, restock) | S | @ongki | BE-PAY-005 | FR-RFD-001 | S5 | 🔲 |

#### Modul: Engine Sale → Inventory

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-ENG-001 | RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan | M | @akmal | BE-REC-001, DB-ORD-001 | FR-ENG-001; BR-009, BR-010 | S2 | 🔲 |
| BE-ENG-002 | DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten | M | @akmal | BE-ENG-001, BE-INV-002 | ADR-002; SEQ-PAY-01 | S2 | 🔲 |
| BE-ENG-003 | Kebijakan stok minus: stock_shortage di respons dan audit | S | @akmal | BE-ENG-002 | FR-ENG-002; BR-011 | S3 | 🔲 |
| BE-ENG-004 | Jalur restock refund (movement RETURN dari movement SALE order) | S | @akmal | BE-ENG-002 | FR-RFD-001; BR-012 | S3 | 🔲 |
| BE-ENG-005 | GET /orders/{id}/stock-trace | S | @akmal | BE-ENG-002 | FR-ENG-003 | S5 | 🔲 |
| INT-ENG-001 | Integration test Sale → Payment → Recipe → Movement → Balance | M | @akmal | BE-PAY-003, BE-ENG-002 | NFR-DATA-001/002 | S3 | 🔲 |
| TEST-ENG-001 | Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel | M | @akmal | INT-ENG-001 | BR-013 | S5 | 🔲 |
| FE-ENG-001 | Indikator stok menipis/habis di layar POS | S | @akmal | BE-ENG-003, FE-ORD-002 | SCR-POS-01 | S5 | 🔲 |

#### Modul: Purchasing

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-PUR-001 | Migrasi suppliers, purchases, purchase_items, document_sequences | M | @naufaldi | DB-ING-001, DB-TEN-002 | ERD §3.6; BR-045…050 | S2 | 🔲 |
| BE-PUR-001 | CRUD supplier (soft delete, INACTIVE tidak bisa dipilih) | S | @naufaldi | DB-PUR-001, BE-RBAC-001 | FR-PUR-001; UC-PUR-01 | S2 | 🔲 |
| BE-PUR-002 | Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO | M | @naufaldi | BE-PUR-001, BE-ING-001, BE-INV-003 | FR-PUR-002; UC-PUR-02; BR-046, BR-049 | S3 | 🔲 |
| BE-PUR-003 | Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten | M | @naufaldi | BE-PUR-002, BE-INV-002 | FR-PUR-003; UC-PUR-03; SEQ-PUR-01; BR-045, BR-047 | S3 | 🔲 |
| BE-PUR-004 | GET daftar & detail purchase (filter status, supplier, tanggal) | S | @naufaldi | BE-PUR-002 | FR-PUR-004; UC-PUR-04 | S4 | 🔲 |
| FE-PUR-001 | Halaman supplier | S | @naufaldi | BE-PUR-001, FE-AUTH-003 | SCR-PUR-01 | S3 | 🔲 |
| FE-PUR-002 | Form & daftar purchase (satuan beli, konversi tampil) | M | @naufaldi | BE-PUR-002, FE-PUR-001 | SCR-PUR-02 | S3 | 🔲 |
| FE-PUR-003 | Alur terima barang (konfirmasi, hasil stok baru) | M | @naufaldi | BE-PUR-003, FE-PUR-002 | SCR-PUR-03 | S4 | 🔲 |
| TEST-PUR-001 | Test purchasing: konversi, last_cost, idempotensi, permission | M | @naufaldi | BE-PUR-003 | FR-PUR-001…004 | S5 | 🔲 |

#### Modul: Laporan & Dashboard

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-RPT-001 | Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund) | M | @naufaldi | BE-PAY-002, BE-ORD-007 | FR-RPT-001; UC-RPT-01 | S3 | 🔲 |
| BE-RPT-003 | Laporan payment per metode | S | @naufaldi | BE-RPT-001 | FR-RPT-003 | S4 | 🔲 |
| FE-RPT-001 | Halaman laporan sales & payment | M | @naufaldi | BE-RPT-003, FE-AUTH-003 | SCR-RPT-01 | S5 | 🔲 |
| TEST-RPT-001 | Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone | S | @akmal | BE-RPT-003 | FR-RPT-001…005 | S5 | 🔲 |

#### Modul: PWA, Hardening, Deploy

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| FE-PWA-001 | Manifest + service worker (vite-plugin-pwa), cache aset statis saja | M | @akmal | FE-ORD-001 | FR-PWA-001; F-17 | S3 | 🔲 |
| FE-PWA-002 | Banner offline + aksi tulis ditolak + retry aman (key sama) | S | @akmal | FE-PWA-001, BE-PAY-002 | FR-PWA-001 | S5 | 🔲 |
| DB-SEED-001 | Seeder demo (business, outlet, menu, recipe, stok, supplier) | S | @akmal | BE-REC-001, BE-INV-003, BE-PUR-001 | TD §8 | S5 | 🔲 |
| TEST-E2E-001 | E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift | M | @akmal | FE-PAY-002, INT-ENG-001, FE-SHF-003 | F-01…F-10 | S4 | 🔲 |
| TEST-E2E-002 | E2E inventory: opening → purchase receive → adjustment → waste | M | @akmal | FE-PUR-003, FE-INV-004 | F-11, F-12, F-20 | S4 | 🔲 |
| TEST-SEC-001 | Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload | M | @akmal | TEST-AUTH-001, BE-PAY-002 | NFR-SEC-001…007 | S4 | 🔲 |
| TEST-PERF-001 | Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements) | M | @akmal | INT-ENG-001 | NFR-PERF-003, NFR-PERF-004 | S5 | 🔲 |
| DEPLOY-001 | Environment production, HTTPS, backup harian + uji restore | M | @ongki | SETUP-008 | NFR-REL-002; DO §3 | S4 | 🔲 |
| DEPLOY-002 | Deploy production + smoke test | S | @ongki | DEPLOY-001, TEST-E2E-001, TEST-E2E-002, TEST-SEC-001 | DO §2 | S5 (cadangan 30 Des) | 🔲 |


### 🔁 FASE 2 — RILIS 1.1 (Jan 2027)
> 22 tiket, 14.75 hari-orang

#### Modul: Platform: Tenant, Auth, RBAC, Audit

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-AUD-002 | GET /audit-logs + filter (tanggal, user, aksi, resource) | S | @naufaldi | BE-AUD-001, BE-RBAC-001 | FR-AUD-002; UC-AUD-01 | R1.1 | 🔲 |
| FE-AUD-001 | Halaman audit log | S | @naufaldi | BE-AUD-002, FE-AUTH-003 | UC-AUD-01; SCR-AUD-01 | R1.1 | 🔲 |

#### Modul: Ingredient & Recipe

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-REC-003 | CRUD recipe per modifier option (F-18) | S | @akmal | DB-REC-001, BE-CAT-004, BE-ING-001 | FR-REC-002; UC-REC-02 | R1.1 | 🔲 |
| FE-REC-002 | Editor recipe modifier option (F-18) | S | @akmal | BE-REC-003, FE-CAT-003 | SCR-CAT-03 | R1.1 | 🔲 |
| FE-REC-003 | Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP | XS | @akmal | BE-REC-001, FE-AUTH-003 | UC-REC-03 | R1.1 | 🔲 |

#### Modul: Inventory

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DB-INV-002 | Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS) | S | @akmal | DB-INV-001 | ERD §3.3; BR-017 | R1.1 | 🔲 |
| BE-INV-007 | Opname: mulai (snapshot), input hitungan, batalkan | M | @akmal | DB-INV-002, BE-INV-004 | FR-INV-006; UC-INV-05; SEQ-INV-02 | R1.1 | 🔲 |
| BE-INV-008 | Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id) | S | @akmal | BE-INV-007 | BR-017; STM-OPNAME | R1.1 | 🔲 |
| FE-INV-005 | UI opname (mulai, input hitungan, review selisih, submit) | M | @akmal | BE-INV-008 | SCR-INV-05 | R1.1 | 🔲 |
| TEST-INV-003 | Test opname (snapshot, selisih, konkurensi dengan penjualan) | S | @akmal | BE-INV-008 | FR-INV-006 | R1.1 | 🔲 |

#### Modul: POS Order

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-KIT-001 | Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready | S | @ongki | BE-ORD-005 | FR-KIT-001; UC-KIT-01; BR-033 | R1.1 | 🔲 |
| FE-KIT-001 | Halaman antrian dapur (polling 10 detik) | S | @ongki | BE-KIT-001 | SCR-KIT-01 | R1.1 | 🔲 |

#### Modul: Engine Sale → Inventory

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-ENG-006 | Pemakaian bahan modifier option ditambahkan ke agregat (F-18) | S | @akmal | BE-ENG-001, BE-REC-003 | FR-REC-002; FR-ENG-001 | R1.1 | 🔲 |

#### Modul: Purchasing

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-PUR-005 | Cancel purchase DRAFT + partial receive (purchase_receipts) | M | @naufaldi | BE-PUR-003 | FR-PUR-005; UC-PUR-05 | R1.1 | 🔲 |

#### Modul: Laporan & Dashboard

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| BE-RPT-002 | Laporan produk + daftar no recipe | S | @naufaldi | BE-RPT-001 | FR-RPT-002; BR-010 | R1.1 | 🔲 |
| BE-RPT-004 | Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian | M | @naufaldi | BE-INV-006, BE-INV-005, BE-PUR-003 | FR-RPT-004 | R1.1 | 🔲 |
| BE-RPT-005 | Dashboard permission-aware (timezone outlet, cache TTL pendek) | M | @naufaldi | BE-RPT-001, BE-INV-006, BE-SHF-005 | FR-RPT-005; UC-RPT-02 | R1.1 | 🔲 |
| FE-RPT-002 | Halaman laporan produk, inventory, waste | M | @naufaldi | BE-RPT-004, BE-RPT-002 | SCR-RPT-02 | R1.1 | 🔲 |
| FE-RPT-003 | Dashboard | M | @naufaldi | BE-RPT-005 | SCR-RPT-03 | R1.1 | 🔲 |
| BE-RPT-006 | Export CSV sales dan stock movement (stream, reports.export) | S | @naufaldi | BE-RPT-001, BE-INV-006 | FR-RPT-006; UC-RPT-03 | R1.1 | 🔲 |

#### Modul: PWA, Hardening, Deploy

| ID | Task | Estimasi | Assignee | Dependency | Referensi | Sprint | Status |
|----|------|----------|----------|------------|-----------|--------|--------|
| DOC-001 | Dokumentasi API (OpenAPI/Scribe) | M | @akmal | BE-RPT-003, BE-PUR-004 | SDD §4 | R1.1 | 🔲 |
| DOC-002 | README + runbook dev (Laragon, Supabase, queue worker) | S | @akmal | SETUP-002 | TD §6 | R1.1 | 🔲 |

---

## 6. Catatan Dependensi Lintas Rilis
- `BE-ENG-001` memproses recipe produk di core; pemakaian bahan modifier ditambahkan oleh `BE-ENG-006` (rilis 1.1) setelah `BE-REC-003`.
- `kitchen_status` terisi `NEW` saat bayar di core meskipun layar dapur baru hadir di 1.1 (tidak ada migrasi tambahan).
- Tabel opname dibuat di `DB-INV-002` (rilis 1.1); jangan membuat migrasinya di core.

## 7. Changelog
| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| 3.0 | 2026-10-25 | Re-baseline penuh dari 134 tiket v1.1 ke 148 tiket: Vue 3 (kembali ke stack awal), Spatie, Supabase, Purchase inti, rilis 1.1, laporan minimal core, jadwal mulai 26 Okt | Claude |
