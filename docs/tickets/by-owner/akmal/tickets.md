# Tiket @akmal — F&B POS & Inventory

> Dibuat otomatis dari `tickets.md` (jangan diedit tangan; jalankan ulang `generate_by_owner.py` setiap `tickets.md` berubah). Total **52** tiket.
Reviewer silang: Ongki ← Akmal ← Naufaldi ← Ongki. Definition of Ready/Done: `docs/11_DoR_DoD.md`.
> 

## Ringkasan beban (hari tugas)

| Sprint | S1 | S2 | S3 | S4 | S5 | R1.1 | Core (S1–S5) |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Beban | 4,75 | 7,50 | 8,00 | 7,00 | 5,50 | 6,75 | 32,75 |

## Daftar tiket (urut sprint)

| ID | Sprint | Est | Status | Depends on | Reviewer | Judul |
| --- | --- | --- | --- | --- | --- | --- |
| SETUP-004 | S1 | M | todo | SETUP-003 | @naufaldi | CI GitHub Actions: lint + Pest + build frontend |
| SETUP-009 | S1 | M | todo | SETUP-005 | @naufaldi | Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi |
| DB-ING-001 | S1 | M | todo | DB-TEN-002 | @naufaldi | Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000) |
| BE-ING-001 | S1 | M | todo | DB-ING-001, BE-RBAC-001 | @naufaldi | CRUD ingredient (permission inventory.adjust, baca inventory.view) |
| BE-ING-002 | S1 | XS | todo | DB-ING-001 | @naufaldi | Seeder unit + endpoint GET /units |
| DB-REC-001 | S1 | S | todo | DB-ING-001, DB-CAT-001 | @naufaldi | Migrasi recipes (versi), recipe_items, modifier_option_recipe_items |
| BE-REC-001 | S2 | M | todo | DB-REC-001, BE-ING-001, BE-CAT-002 | @naufaldi | Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP |
| BE-REC-002 | S2 | S | todo | BE-REC-001 | @naufaldi | Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price |
| DB-INV-001 | S2 | M | todo | DB-ING-001, DB-AUD-001 | @naufaldi | Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_re |
| BE-INV-001 | S2 | M | todo | DB-INV-001 | @naufaldi | StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative) |
| BE-INV-002 | S2 | S | todo | BE-INV-001 | @naufaldi | Idempotensi movement (referensi sama = movement lama) + helper reverse() |
| BE-INV-003 | S2 | S | todo | BE-INV-001 | @naufaldi | POST /inventory/opening-stock (batch + movement OPENING) |
| BE-INV-004 | S2 | M | todo | BE-INV-001, BE-AUD-001 | @naufaldi | POST/GET /inventory/adjustments (record dibuat sebelum movement) |
| BE-ENG-001 | S2 | M | todo | BE-REC-001, DB-ORD-001 | @naufaldi | RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan |
| BE-ENG-002 | S2 | M | todo | BE-ENG-001, BE-INV-002 | @naufaldi | DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten |
| TEST-CAT-001 | S3 | M | todo | BE-CAT-005 | @naufaldi | Feature test katalog (tenant, SKU unik, inactive, cache) |
| FE-ING-001 | S3 | M | todo | BE-ING-001, FE-AUTH-003 | @naufaldi | Halaman ingredient |
| BE-INV-005 | S3 | M | todo | BE-INV-001, BE-AUD-001 | @naufaldi | POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya) |
| BE-INV-006 | S3 | S | todo | BE-INV-001 | @naufaldi | GET stok, riwayat movement, low stock + stok minus |
| FE-INV-001 | S3 | M | todo | BE-INV-006, FE-AUTH-003 | @naufaldi | Halaman stok + badge low stock/minus |
| FE-INV-004 | S3 | S | todo | BE-INV-005 | @naufaldi | Form & daftar waste |
| BE-ENG-003 | S3 | S | todo | BE-ENG-002 | @naufaldi | Kebijakan stok minus: stock_shortage di respons dan audit |
| BE-ENG-004 | S3 | S | todo | BE-ENG-002 | @naufaldi | Jalur restock refund (movement RETURN dari movement SALE order) |
| INT-ENG-001 | S3 | M | todo | BE-PAY-003, BE-ENG-002 | @naufaldi | Integration test Sale → Payment → Recipe → Movement → Balance |
| FE-PWA-001 | S3 | M | todo | FE-ORD-001 | @naufaldi | Manifest + service worker (vite-plugin-pwa), cache aset statis saja |
| FE-REC-001 | S4 | M | todo | BE-REC-002, FE-CAT-002 | @naufaldi | Editor recipe di form produk + preview HPP |
| FE-INV-003 | S4 | M | todo | BE-INV-003, BE-INV-004 | @naufaldi | Form opening stock & adjustment |
| TEST-INV-001 | S4 | M | todo | BE-INV-002 | @naufaldi | Unit test StockService: konkurensi, minus, idempotensi, presisi desimal |
| TEST-INV-002 | S4 | M | todo | BE-INV-005, BE-INV-006 | @naufaldi | Feature test opening, adjustment, waste, stok |
| TEST-E2E-001 | S4 | M | todo | FE-PAY-002, INT-ENG-001, FE-SHF-003 | @naufaldi | E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shif |
| TEST-E2E-002 | S4 | M | todo | FE-PUR-003, FE-INV-004 | @naufaldi | E2E inventory: opening → purchase receive → adjustment → waste |
| TEST-SEC-001 | S4 | M | todo | TEST-AUTH-001, BE-PAY-002 | @naufaldi | Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload |
| TEST-REC-001 | S5 | S | todo | BE-REC-002 | @naufaldi | Test recipe (versioning, validasi, HPP) |
| FE-INV-002 | S5 | S | todo | BE-INV-006 | @naufaldi | Halaman riwayat movement |
| BE-ENG-005 | S5 | S | todo | BE-ENG-002 | @naufaldi | GET /orders/{id}/stock-trace |
| TEST-ENG-001 | S5 | M | todo | INT-ENG-001 | @naufaldi | Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel |
| FE-ENG-001 | S5 | S | todo | BE-ENG-003, FE-ORD-002 | @naufaldi | Indikator stok menipis/habis di layar POS |
| TEST-RPT-001 | S5 | S | todo | BE-RPT-003 | @naufaldi | Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone |
| FE-PWA-002 | S5 | S | todo | FE-PWA-001, BE-PAY-002 | @naufaldi | Banner offline + aksi tulis ditolak + retry aman (key sama) |
| DB-SEED-001 | S5 | S | todo | BE-REC-001, BE-INV-003, BE-PUR-001 | @naufaldi | Seeder demo (business, outlet, menu, recipe, stok, supplier) |
| TEST-PERF-001 | S5 | M | todo | INT-ENG-001 | @naufaldi | Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements) |
| BE-REC-003 | R1.1 | S | todo | DB-REC-001, BE-CAT-004, BE-ING-001 | @naufaldi | CRUD recipe per modifier option (F-18) |
| FE-REC-002 | R1.1 | S | todo | BE-REC-003, FE-CAT-003 | @naufaldi | Editor recipe modifier option (F-18) |
| FE-REC-003 | R1.1 | XS | todo | BE-REC-001, FE-AUTH-003 | @naufaldi | Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP |
| DB-INV-002 | R1.1 | S | todo | DB-INV-001 | @naufaldi | Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS) |
| BE-INV-007 | R1.1 | M | todo | DB-INV-002, BE-INV-004 | @naufaldi | Opname: mulai (snapshot), input hitungan, batalkan |
| BE-INV-008 | R1.1 | S | todo | BE-INV-007 | @naufaldi | Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id) |
| FE-INV-005 | R1.1 | M | todo | BE-INV-008 | @naufaldi | UI opname (mulai, input hitungan, review selisih, submit) |
| TEST-INV-003 | R1.1 | S | todo | BE-INV-008 | @naufaldi | Test opname (snapshot, selisih, konkurensi dengan penjualan) |
| BE-ENG-006 | R1.1 | S | todo | BE-ENG-001, BE-REC-003 | @naufaldi | Pemakaian bahan modifier option ditambahkan ke agregat (F-18) |
| DOC-001 | R1.1 | M | todo | BE-RPT-003, BE-PUR-004 | @naufaldi | Dokumentasi API (OpenAPI/Scribe) |
| DOC-002 | R1.1 | S | todo | SETUP-002 | @naufaldi | README + runbook dev (Laragon, Supabase, queue worker) |

## Tiket yang harus direview @akmal (46)

| ID | Sprint | Assignee | Judul |
| --- | --- | --- | --- |
| SETUP-001 | S1 | @ongki | Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template |
| SETUP-002 | S1 | @ongki | Konfigurasi PostgreSQL lokal (Laragon) + .env.example per environment |
| SETUP-003 | S1 | @ongki | Pint, Larastan, Oxlint, ESLint, Prettier, pre-commit hook |
| SETUP-005 | S1 | @ongki | Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception han |
| DB-TEN-001 | S1 | @ongki | Migrasi businesses, outlets, users, user_outlets |
| DB-TEN-002 | S1 | @ongki | Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet |
| DB-RBAC-001 | S1 | @ongki | Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Lim |
| DB-AUD-001 | S1 | @ongki | Migrasi audit_logs + trigger append-only |
| BE-AUD-001 | S1 | @ongki | AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via que |
| BE-RBAC-001 | S1 | @ongki | Middleware permission + outlet.context, Policy aturan Limited/Own |
| BE-AUTH-001 | S1 | @ongki | POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi) |
| SETUP-007 | S2 | @ongki | Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test |
| BE-AUTH-002 | S2 | @ongki | Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED |
| BE-AUTH-004 | S2 | @ongki | /me mengembalikan outlet + permission dengan scope |
| DB-ORD-001 | S2 | @ongki | Migrasi orders, order_items, order_item_modifiers, order_sequences |
| BE-ORD-001 | S2 | @ongki | OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut} |
| BE-ORD-002 | S2 | @ongki | PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total |
| BE-ORD-003 | S2 | @ongki | Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier |
| BE-ORD-005 | S2 | @ongki | State machine order: confirm, reopen (guard gateway PENDING) |
| DB-PAY-001 | S2 | @ongki | Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments) |
| SETUP-008 | S3 | @ongki | Staging (VPS + Docker + Nginx) + Supabase staging (DB dan Storage) + HTTPS; FE dan API sat |
| TEST-AUTH-001 | S3 | @ongki | Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint |
| BE-ORD-007 | S3 | @ongki | Riwayat & detail order (Cashier hanya miliknya) |
| FE-ORD-001 | S3 | @ongki | Layout layar POS responsif (grid kategori/produk + cart) |
| FE-ORD-002 | S3 | @ongki | Cart store (Pinia), modifier picker, qty |
| BE-PAY-001 | S3 | @ongki | Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif |
| BE-PAY-002 | S3 | @ongki | POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik |
| BE-PAY-003 | S3 | @ongki | Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit |
| BE-PAY-004 | S3 | @ongki | Builder data struk (pembulatan, metode, kembalian) |
| TEST-RBAC-001 | S4 | @ongki | Test matriks role × endpoint |
| BE-ORD-004 | S4 | @ongki | Diskon order-level (persen/nominal), batas Cashier per outlet, audit |
| BE-ORD-006 | S4 | @ongki | Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit) |
| FE-ORD-003 | S4 | @ongki | Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server) |
| FE-ORD-004 | S4 | @ongki | Halaman riwayat & detail order |
| BE-PAY-005 | S4 | @ongki | Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit |
| FE-PAY-002 | S4 | @ongki | Tampilan struk + CSS print 58/80 mm |
| DEPLOY-001 | S4 | @ongki | Environment production, HTTPS, backup harian + uji restore |
| TEST-ORD-001 | S5 | @ongki | Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan) |
| TEST-ORD-002 | S5 | @ongki | Feature test lifecycle order & cancel |
| FE-PAY-001 | S5 | @ongki | Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key) |
| FE-PAY-003 | S5 | @ongki | Dialog refund (alasan, restock) |
| TEST-PAY-001 | S5 | @ongki | Test pembayaran: double submit, overpay, status invalid, kembalian, rollback |
| TEST-PAY-002 | S5 | @ongki | Test refund (kas, status, permission, restock) |
| DEPLOY-002 | S5 | @ongki | Deploy production + smoke test |
| BE-KIT-001 | R1.1 | @ongki | Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready |
| FE-KIT-001 | R1.1 | @ongki | Halaman antrian dapur (polling 10 detik) |

## Detail tiket

### SETUP-004 — CI GitHub Actions: lint + Pest + build frontend

| Field | Value |
| --- | --- |
| ID | SETUP-004 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-003 |
| References | DEPLOY ops §1 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-004-ci-github-actions-lint-pest-build-fronte |
| PR |  |

**Context**
CI GitHub Actions: lint + Pest + build frontend. Rujukan: DEPLOY ops §1.

**Acceptance Criteria**

- [ ]  PR memicu lint, Pest, dan `npm run build` (Vite); status check; gagal salah satu = merge diblok
- [ ]  Pest memakai database PostgreSQL service container (bukan SQLite)

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### SETUP-009 — Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi

| Field | Value |
| --- | --- |
| ID | SETUP-009 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | M |
| Status | todo |
| Depends on | SETUP-005 |
| References | TD §2.3, §2.4 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-009-arch-test-pest-dependensi-modul-peta-pan |
| PR |  |

**Context**
Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi. Rujukan: TD §2.3, §2.4.

**Acceptance Criteria**

- [ ]  Arch test gagal bila Recipe memanggil POS/Inventory atau Inventory memanggil POS; modul hanya bicara lewat Contract/Facade yang terdaftar
- [ ]  Arch test gagal bila ada class `XxxModule` yang belum terdaftar di `config/modules.php` (kecuali `Payments\Gateway`) atau nama migrasi tidak mengikuti pola `modul_aksi_tabel`

**Technical Notes**

- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### DB-ING-001 — Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000)

| Field | Value |
| --- | --- |
| ID | DB-ING-001 |
| Type | database |
| Module | ING |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-TEN-002 |
| References | ERD §3.2; BR-046 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S1 |
| Labels | database, ing, core |
| Branch | feature/db-ing-001-migrasi-units-ingredients-ingredient-uni |
| PR |  |

**Context**
Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000). Rujukan: ERD §3.2; BR-046.

**Acceptance Criteria**

- [ ]  Unit g, kg, ml, L, pcs ter-seed; faktor kg→g dan L→ml = 1000
- [ ]  last_cost DECIMAL(18,4), CHECK ≥ 0

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-ING-001

---

### BE-ING-001 — CRUD ingredient (permission inventory.adjust, baca inventory.view)

| Field | Value |
| --- | --- |
| ID | BE-ING-001 |
| Type | backend |
| Module | ING |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-ING-001, BE-RBAC-001 |
| References | FR-ING-001; UC-ING-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S1 |
| Labels | backend, ing, core |
| Branch | feature/be-ing-001-crud-ingredient-permission-inventory-adj |
| PR |  |

**Context**
CRUD ingredient (permission inventory.adjust, baca inventory.view). Rujukan: FR-ING-001; UC-ING-01.

**Acceptance Criteria**

- [ ]  SKU ganda 422; base unit tidak bisa diubah bila sudah ada movement
- [ ]  last_cost negatif ditolak; perubahan tidak mengubah movement/order lama

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ING-001, TC-ING-002, TC-ING-003

---

### BE-ING-002 — Seeder unit + endpoint GET /units

| Field | Value |
| --- | --- |
| ID | BE-ING-002 |
| Type | backend |
| Module | ING |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | XS |
| Status | todo |
| Depends on | DB-ING-001 |
| References | FR-ING-001 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S1 |
| Labels | backend, ing, core |
| Branch | feature/be-ing-002-seeder-unit-endpoint-get-units |
| PR |  |

**Context**
Seeder unit + endpoint GET /units. Rujukan: FR-ING-001.

**Acceptance Criteria**

- [ ]  GET /units mengembalikan 5 satuan
- [ ]  Seeder idempoten (aman dijalankan ulang)

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ING-001

---

### DB-REC-001 — Migrasi recipes (versi), recipe_items, modifier_option_recipe_items

| Field | Value |
| --- | --- |
| ID | DB-REC-001 |
| Type | database |
| Module | REC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | DB-ING-001, DB-CAT-001 |
| References | ERD §3.2; BR-013 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S1 |
| Labels | database, rec, core |
| Branch | feature/db-rec-001-migrasi-recipes-versi-recipe-items-modif |
| PR |  |

**Context**
Migrasi recipes (versi), recipe_items, modifier_option_recipe_items. Rujukan: ERD §3.2; BR-013.

**Acceptance Criteria**

- [ ]  UNIQUE parsial 1 recipe aktif per produk
- [ ]  UNIQUE (recipe_id, ingredient_id)

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-REC-001

---

### BE-REC-001 — Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP

| Field | Value |
| --- | --- |
| ID | BE-REC-001 |
| Type | backend |
| Module | REC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-REC-001, BE-ING-001, BE-CAT-002 |
| References | FR-REC-001; UC-REC-01; SEQ-REC-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, rec, core |
| Branch | feature/be-rec-001-recipe-per-produk-edit-versi-baru-1-akti |
| PR |  |

**Context**
Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP. Rujukan: FR-REC-001; UC-REC-01; SEQ-REC-01.

**Acceptance Criteria**

- [ ]  Simpan membuat version+1 dan menonaktifkan versi lama dalam satu transaksi
- [ ]  Qty ≤ 0, bahan ganda, recipe kosong ditolak dengan pesan SRS

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-001, TC-REC-002, TC-REC-004

---

### BE-REC-002 — Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price

| --- | --- |

**Context**
Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price. Rujukan: FR-REC-003; BR-014.

**Acceptance Criteria**

- [ ]  HPP produk tanpa recipe = 0 dan ditandai no recipe
- [ ]  HPP hanya tampil untuk Owner dan Manager

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-003

---

### DB-INV-001 — Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches

| --- | --- |

**Context**
Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches. Rujukan: ERD §3.3; ADR-007.

**Acceptance Criteria**

- [ ]  UNIQUE idempotensi movement dan CHECK arah delta sesuai ERD
- [ ]  UPDATE/DELETE movement ditolak trigger

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-INV-011

---

### BE-INV-001 — StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative)

| --- | --- |

**Context**
StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative). Rujukan: ADR-002; ACT-INV-01.

**Acceptance Criteria**

- [ ]  Stok kurang dan minus dilarang melempar InsufficientStock
- [ ]  forceNegative hanya dipakai jalur settlement gateway (default false)

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-002

---

### BE-INV-002 — Idempotensi movement (referensi sama = movement lama) + helper reverse()

| --- | --- |

**Context**
Idempotensi movement (referensi sama = movement lama) + helper reverse(). Rujukan: BR-009; NFR-DATA-003.

**Acceptance Criteria**

- [ ]  Pemanggilan ulang dengan referensi sama mengembalikan movement lama tanpa baris baru
- [ ]  reverse() membuat movement lawan dengan referensi baru

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-001

---

### BE-INV-003 — POST /inventory/opening-stock (batch + movement OPENING)

| --- | --- |

**Context**
POST /inventory/opening-stock (batch + movement OPENING). Rujukan: FR-INV-001; UC-INV-01; SEQ-INV-01.

**Acceptance Criteria**

- [ ]  Satu batch menghasilkan satu movement OPENING per bahan dengan reference_id = id batch
- [ ]  Qty ≤ 0 dan bahan ganda dalam batch ditolak

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-004

---

### BE-INV-004 — POST/GET /inventory/adjustments (record dibuat sebelum movement)

| --- | --- |

**Context**
POST/GET /inventory/adjustments (record dibuat sebelum movement). Rujukan: FR-INV-003; UC-INV-03.

**Acceptance Criteria**

- [ ]  Alasan wajib dan qty > 0; before/after tersimpan
- [ ]  Hasil minus saat minus dilarang 422 INSUFFICIENT_STOCK

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-005

---

### BE-ENG-001 — RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan

| --- | --- |

**Context**
RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan. Rujukan: FR-ENG-001; BR-009, BR-010.

**Acceptance Criteria**

- [ ]  Bahan sama di beberapa item diagregat menjadi satu angka
- [ ]  Produk tanpa recipe dilewati

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-001, TC-ENG-002, TC-ENG-003

---

### BE-ENG-002 — DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten

| --- | --- |

**Context**
DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten. Rujukan: ADR-002; SEQ-PAY-01.

**Acceptance Criteria**

- [ ]  Dua order paralel dengan bahan sama tidak deadlock dan stok benar
- [ ]  Movement SALE unit_cost memakai last_cost saat kejadian

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-005

---

### TEST-CAT-001 — Feature test katalog (tenant, SKU unik, inactive, cache)

| --- | --- |

**Context**
Feature test katalog (tenant, SKU unik, inactive, cache). Rujukan: FR-CAT-001…004.

**Acceptance Criteria**

- [ ]  Semua baris tabel validasi FR-CAT-002/003 punya test
- [ ]  Test cache invalidasi lulus

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-CAT-001…006

---

### FE-ING-001 — Halaman ingredient

| --- | --- |

**Context**
Halaman ingredient. Rujukan: SCR-ING-01.

**Acceptance Criteria**

- [ ]  CRUD ingredient dengan pesan error SRS
- [ ]  Base unit terkunci bila sudah ada movement

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ING-002

---

### BE-INV-005 — POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya)

| --- | --- |

**Context**
POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya). Rujukan: FR-INV-004; UC-INV-04; BR-034.

**Acceptance Criteria**

- [ ]  Alasan OTHER tanpa catatan 422
- [ ]  total_cost tidak ada di respons untuk role Kitchen

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-006, TC-INV-007

---

### BE-INV-006 — GET stok, riwayat movement, low stock + stok minus

| --- | --- |

**Context**
GET stok, riwayat movement, low stock + stok minus. Rujukan: FR-INV-002, FR-INV-005; UC-INV-02.

**Acceptance Criteria**

- [ ]  Filter ingredient, tipe, tanggal dengan paginasi
- [ ]  Low stock memakai min_stock override outlet; stok minus tampil terpisah

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-008

---

### FE-INV-001 — Halaman stok + badge low stock/minus

| --- | --- |

**Context**
Halaman stok + badge low stock/minus. Rujukan: SCR-INV-01.

**Acceptance Criteria**

- [ ]  Badge low stock dan minus berbeda dan menonjol
- [ ]  State empty/loading/error ada

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-008

---

### FE-INV-004 — Form & daftar waste

| --- | --- |

**Context**
Form & daftar waste. Rujukan: SCR-INV-04.

**Acceptance Criteria**

- [ ]  Catatan wajib saat alasan OTHER
- [ ]  Nilai biaya disembunyikan untuk Kitchen

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-006, TC-INV-007

---

### BE-ENG-003 — Kebijakan stok minus: stock_shortage di respons dan audit

| --- | --- |

**Context**
Kebijakan stok minus: stock_shortage di respons dan audit. Rujukan: FR-ENG-002; BR-011.

**Acceptance Criteria**

- [ ]  allow_negative_stock = true: bayar sukses dan stock_shortage terisi
- [ ]  false: 422 INSUFFICIENT_STOCK dengan details.ingredients

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-004

---

### BE-ENG-004 — Jalur restock refund (movement RETURN dari movement SALE order)

| --- | --- |

**Context**
Jalur restock refund (movement RETURN dari movement SALE order). Rujukan: FR-RFD-001; BR-012.

**Acceptance Criteria**

- [ ]  Restock membuat RETURN positif per bahan dengan reference REFUND:id
- [ ]  Tanpa restock tidak ada movement baru

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-013

---

### INT-ENG-001 — Integration test Sale → Payment → Recipe → Movement → Balance

| --- | --- |

**Context**
Integration test Sale → Payment → Recipe → Movement → Balance. Rujukan: NFR-DATA-001/002.

**Acceptance Criteria**

- [ ]  balance = SUM(movements) untuk semua bahan pasca skenario
- [ ]  Skenario lengkap order→bayar→refund restock lulus

**Technical Notes**

- Rujuk Test Plan §4

**Test Reference**
TC-ENG-001…005

---

### FE-PWA-001 — Manifest + service worker (vite-plugin-pwa), cache aset statis saja

| --- | --- |

**Context**
Manifest + service worker (vite-plugin-pwa), cache aset statis saja. Rujukan: FR-PWA-001; F-17.

**Acceptance Criteria**

- [ ]  Installable (Lighthouse PWA lulus)
- [ ]  Respons API tidak pernah di-cache service worker

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PWA-001, TC-PWA-002

---

### FE-REC-001 — Editor recipe di form produk + preview HPP

| --- | --- |

**Context**
Editor recipe di form produk + preview HPP. Rujukan: SCR-CAT-02; UC-REC-01.

**Acceptance Criteria**

- [ ]  Satuan input memakai base unit bahan
- [ ]  Preview HPP disembunyikan untuk non-Owner/Manager

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-003, TC-REC-004

---

### FE-INV-003 — Form opening stock & adjustment

| --- | --- |

**Context**
Form opening stock & adjustment. Rujukan: SCR-INV-03.

**Acceptance Criteria**

- [ ]  Alasan wajib tampil pada adjustment
- [ ]  Error stok kurang menampilkan pesan SRS

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-004, TC-INV-005

---

### TEST-INV-001 — Unit test StockService: konkurensi, minus, idempotensi, presisi desimal

| --- | --- |

**Context**
Unit test StockService: konkurensi, minus, idempotensi, presisi desimal. Rujukan: NFR-DATA-001.

**Acceptance Criteria**

- [ ]  20 apply paralel pada bahan sama: balance = SUM(movements)
- [ ]  Presisi DECIMAL(18,4) terjaga

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-001, TC-INV-002, TC-INV-003

---

### TEST-INV-002 — Feature test opening, adjustment, waste, stok

| --- | --- |

**Context**
Feature test opening, adjustment, waste, stok. Rujukan: FR-INV-001…005.

**Acceptance Criteria**

- [ ]  Semua baris tabel validasi FR-INV-003/004 diuji
- [ ]  Permission Kitchen/Inventory sesuai matriks

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-004…008

---

### TEST-E2E-001 — E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift

| --- | --- |

**Context**
E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift. Rujukan: F-01…F-10.

**Acceptance Criteria**

- [ ]  Alur utama lulus di staging dan CI
- [ ]  Stok setelah bayar sesuai recipe

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-E2E-001

---

### TEST-E2E-002 — E2E inventory: opening → purchase receive → adjustment → waste

| --- | --- |

**Context**
E2E inventory: opening → purchase receive → adjustment → waste. Rujukan: F-11, F-12, F-20.

**Acceptance Criteria**

- [ ]  Stok dan last_cost berubah sesuai purchase
- [ ]  Movement tercatat di riwayat

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-E2E-002

---

### TEST-SEC-001 — Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload

| --- | --- |

**Context**
Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload. Rujukan: NFR-SEC-001…007.

**Acceptance Criteria**

- [ ]  Semua butir checklist lulus atau punya tiket perbaikan
- [ ]  Webhook/gateway ditandai out of scope Phase 1

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-SEC-001

---

### TEST-REC-001 — Test recipe (versioning, validasi, HPP)

| --- | --- |

**Context**
Test recipe (versioning, validasi, HPP). Rujukan: FR-REC-001, FR-REC-003.

**Acceptance Criteria**

- [ ]  Versioning dan 1 aktif teruji (termasuk constraint DB)
- [ ]  Perhitungan HPP half-up sesuai vektor uji

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-REC-001…004

---

### FE-INV-002 — Halaman riwayat movement

| --- | --- |

**Context**
Halaman riwayat movement. Rujukan: SCR-INV-02.

**Acceptance Criteria**

- [ ]  Filter tipe/tanggal bekerja
- [ ]  Tidak ada aksi ubah/hapus movement

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-011

---

### BE-ENG-005 — GET /orders/{id}/stock-trace

| --- | --- |

**Context**
GET /orders/{id}/stock-trace. Rujukan: FR-ENG-003.

**Acceptance Criteria**

- [ ]  Menampilkan movement SALE dan RETURN milik order
- [ ]  Butuh inventory.view; order tenant lain 404

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-006

---

### TEST-ENG-001 — Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel

| --- | --- |

**Context**
Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel. Rujukan: BR-013.

**Acceptance Criteria**

- [ ]  Recipe versi baru tidak mengubah movement order lama
- [ ]  Produk tanpa recipe tampil di laporan no recipe

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ENG-002, TC-ENG-003, TC-ENG-005

---

### FE-ENG-001 — Indikator stok menipis/habis di layar POS

| --- | --- |

**Context**
Indikator stok menipis/habis di layar POS. Rujukan: SCR-POS-01.

**Acceptance Criteria**

- [ ]  Indikator tampil tanpa memblokir penjualan
- [ ]  Data berasal dari endpoint stok (tanpa HPP)

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ENG-004

---

### TEST-RPT-001 — Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone

| --- | --- |

**Context**
Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone. Rujukan: FR-RPT-001…005.

**Acceptance Criteria**

- [ ]  Business date lintas tengah malam benar
- [ ]  Isolasi tenant pada semua endpoint laporan

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-RPT-001…005

---

### FE-PWA-002 — Banner offline + aksi tulis ditolak + retry aman (key sama)

| --- | --- |

**Context**
Banner offline + aksi tulis ditolak + retry aman (key sama). Rujukan: FR-PWA-001.

**Acceptance Criteria**

- [ ]  Offline menampilkan banner dan menolak aksi tulis
- [ ]  Retry bayar memakai Idempotency-Key yang sama

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PWA-003

---

### DB-SEED-001 — Seeder demo (business, outlet, menu, recipe, stok, supplier)

| --- | --- |

**Context**
Seeder demo (business, outlet, menu, recipe, stok, supplier). Rujukan: TD §8.

**Acceptance Criteria**

- [ ]  Satu perintah menghasilkan data demo siap dipakai UAT
- [ ]  Aman dijalankan ulang di staging

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### TEST-PERF-001 — Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements)

| --- | --- |

**Context**
Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements). Rujukan: NFR-PERF-003, NFR-PERF-004.

**Acceptance Criteria**

- [ ]  p95 bayar + potong stok < 800 ms (order ≤ 10 item)
- [ ]  0 deadlock dan balance = SUM(movements)

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PERF-001

---

### BE-REC-003 — CRUD recipe per modifier option (F-18)

| --- | --- |

**Context**
CRUD recipe per modifier option (F-18). Rujukan: FR-REC-002; UC-REC-02.

**Acceptance Criteria**

- [ ]  Qty > 0 dalam base unit; perubahan tercatat audit
- [ ]  Hanya recipes.manage yang boleh mengubah

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-005

---

### FE-REC-002 — Editor recipe modifier option (F-18)

| --- | --- |

**Context**
Editor recipe modifier option (F-18). Rujukan: SCR-CAT-03.

**Acceptance Criteria**

- [ ]  Editor muncul di UI modifier option
- [ ]  Validasi qty dan bahan ganda sama dengan recipe produk

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-005

---

### FE-REC-003 — Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP

| --- | --- |

**Context**
Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP. Rujukan: UC-REC-03.

**Acceptance Criteria**

- [ ]  Role tanpa recipes.manage tidak melihat tombol ubah
- [ ]  HPP tidak ada di respons maupun UI

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-004

---

### DB-INV-002 — Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS)

| --- | --- |

**Context**
Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS). Rujukan: ERD §3.3; BR-017.

**Acceptance Criteria**

- [ ]  Hanya 1 opname IN_PROGRESS per outlet di level DB
- [ ]  UNIQUE (opname_id, ingredient_id)

**Technical Notes**

- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-INV-010

---

### BE-INV-007 — Opname: mulai (snapshot), input hitungan, batalkan

| --- | --- |

**Context**
Opname: mulai (snapshot), input hitungan, batalkan. Rujukan: FR-INV-006; UC-INV-05; SEQ-INV-02.

**Acceptance Criteria**

- [ ]  Mulai saat ada opname berjalan 409 OPNAME_IN_PROGRESS
- [ ]  Hitungan negatif 422

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-009, TC-INV-010

---

### BE-INV-008 — Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id)

| --- | --- |

**Context**
Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id). Rujukan: BR-017; STM-OPNAME.

**Acceptance Criteria**

- [ ]  Penjualan selama penghitungan tidak hilang (selisih dihitung terhadap snapshot)
- [ ]  Submit pada opname bukan IN_PROGRESS 409 OPNAME_NOT_ACTIVE

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-009

---

### FE-INV-005 — UI opname (mulai, input hitungan, review selisih, submit)

| --- | --- |

**Context**
UI opname (mulai, input hitungan, review selisih, submit). Rujukan: SCR-INV-05.

**Acceptance Criteria**

- [ ]  Layar nyaman di HP (target sentuh ≥ 44 px)
- [ ]  Review selisih tampil sebelum submit

**Technical Notes**

- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-009

---

### TEST-INV-003 — Test opname (snapshot, selisih, konkurensi dengan penjualan)

| --- | --- |

**Context**
Test opname (snapshot, selisih, konkurensi dengan penjualan). Rujukan: FR-INV-006.

**Acceptance Criteria**

- [ ]  Skenario penjualan selama opname lulus
- [ ]  Submit ganda ditolak

**Technical Notes**

- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-009, TC-INV-010

---

### BE-ENG-006 — Pemakaian bahan modifier option ditambahkan ke agregat (F-18)

| --- | --- |

**Context**
Pemakaian bahan modifier option ditambahkan ke agregat (F-18). Rujukan: FR-REC-002; FR-ENG-001.

**Acceptance Criteria**

- [ ]  Pemakaian modifier dihitung dari recipe option aktif saat pembayaran
- [ ]  Modifier tanpa recipe tidak memotong stok

**Technical Notes**

- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-007

---

### DOC-001 — Dokumentasi API (OpenAPI/Scribe)

| --- | --- |

**Context**
Dokumentasi API (OpenAPI/Scribe). Rujukan: SDD §4.

**Acceptance Criteria**

- [ ]  Semua endpoint Phase 1 terdokumentasi dengan contoh
- [ ]  Dokumen dapat diakses di staging

**Technical Notes**

- Rujuk SDD §4

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### DOC-002 — README + runbook dev (Laragon, Supabase, queue worker)

| --- | --- |

**Context**
README + runbook dev (Laragon, Supabase, queue worker). Rujukan: TD §6.

**Acceptance Criteria**

- [ ]  Developer baru bisa menjalankan proyek dari README tanpa bertanya
- [ ]  Runbook queue worker dan scheduler tersedia

**Technical Notes**

- Rujuk SDD §4

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---