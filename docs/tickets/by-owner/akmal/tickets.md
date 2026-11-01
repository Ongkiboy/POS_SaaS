# Tiket Akmal

> Sumber: `tickets/tickets.md` (isi tiket identik; file ini hanya difilter dan diurutkan per sprint). Reviewer silang: Ongki ← Akmal ← Naufaldi ← Ongki. Aturan kerja: `docs/11_DoR_DoD.md`.

| | |
|---|---|
| Total | 53 tiket |
| Core | 42 tiket, 33,25 hari-orang |
| Rilis 1.1 | 11 tiket, 6,75 hari-orang |
| Kapasitas tugas | 35,25 hari (core) |

## Urutan kerja

Urutan mengikuti rencana sprint; **sprint adalah rencana awal** dan diperbarui saat sprint planning. Jangan mulai tiket yang dependensinya belum Done.

| # | ID | Task | Est | Sprint | Depends on |
|---|----|------|-----|--------|-----------|
| 1 | SETUP-003 | Pint, Larastan, ESLint, Prettier, pre-commit hook | S | S1 (26 Okt–6 Nov) | SETUP-001 |
| 2 | SETUP-004 | CI GitHub Actions: lint + Pest + build frontend | M | S1 (26 Okt–6 Nov) | SETUP-003 |
| 3 | SETUP-009 | Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi | M | S1 (26 Okt–6 Nov) | SETUP-005 |
| 4 | DB-ING-001 | Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000) | M | S1 (26 Okt–6 Nov) | DB-TEN-002 |
| 5 | BE-ING-001 | CRUD ingredient (permission inventory.adjust, baca inventory.view) | M | S1 (26 Okt–6 Nov) | DB-ING-001, BE-RBAC-001 |
| 6 | BE-ING-002 | Seeder unit + endpoint GET /units | XS | S1 (26 Okt–6 Nov) | DB-ING-001 |
| 7 | DB-REC-001 | Migrasi recipes (versi), recipe_items, modifier_option_recipe_items | S | S1 (26 Okt–6 Nov) | DB-ING-001, DB-CAT-001 |
| 8 | BE-REC-001 | Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP | M | S2 (9–20 Nov) | DB-REC-001, BE-ING-001, BE-CAT-002 |
| 9 | BE-REC-002 | Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price | S | S2 (9–20 Nov) | BE-REC-001 |
| 10 | DB-INV-001 | Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches | M | S2 (9–20 Nov) | DB-ING-001, DB-AUD-001 |
| 11 | BE-INV-001 | StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative) | M | S2 (9–20 Nov) | DB-INV-001 |
| 12 | BE-INV-002 | Idempotensi movement (referensi sama = movement lama) + helper reverse() | S | S2 (9–20 Nov) | BE-INV-001 |
| 13 | BE-INV-003 | POST /inventory/opening-stock (batch + movement OPENING) | S | S2 (9–20 Nov) | BE-INV-001 |
| 14 | BE-INV-004 | POST/GET /inventory/adjustments (record dibuat sebelum movement) | M | S2 (9–20 Nov) | BE-INV-001, BE-AUD-001 |
| 15 | BE-ENG-001 | RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan | M | S2 (9–20 Nov) | BE-REC-001, DB-ORD-001 |
| 16 | BE-ENG-002 | DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten | M | S2 (9–20 Nov) | BE-ENG-001, BE-INV-002 |
| 17 | TEST-CAT-001 | Feature test katalog (tenant, SKU unik, inactive, cache) | M | S3 (23 Nov–4 Des) | BE-CAT-005 |
| 18 | FE-ING-001 | Halaman ingredient | M | S3 (23 Nov–4 Des) | BE-ING-001, FE-AUTH-003 |
| 19 | BE-INV-005 | POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya) | M | S3 (23 Nov–4 Des) | BE-INV-001, BE-AUD-001 |
| 20 | BE-INV-006 | GET stok, riwayat movement, low stock + stok minus | S | S3 (23 Nov–4 Des) | BE-INV-001 |
| 21 | FE-INV-001 | Halaman stok + badge low stock/minus | M | S3 (23 Nov–4 Des) | BE-INV-006, FE-AUTH-003 |
| 22 | FE-INV-004 | Form & daftar waste | S | S3 (23 Nov–4 Des) | BE-INV-005 |
| 23 | BE-ENG-003 | Kebijakan stok minus: stock_shortage di respons dan audit | S | S3 (23 Nov–4 Des) | BE-ENG-002 |
| 24 | BE-ENG-004 | Jalur restock refund (movement RETURN dari movement SALE order) | S | S3 (23 Nov–4 Des) | BE-ENG-002 |
| 25 | INT-ENG-001 | Integration test Sale → Payment → Recipe → Movement → Balance | M | S3 (23 Nov–4 Des) | BE-PAY-003, BE-ENG-002 |
| 26 | FE-PWA-001 | Manifest + service worker (vite-plugin-pwa), cache aset statis saja | M | S3 (23 Nov–4 Des) | FE-ORD-001 |
| 27 | FE-REC-001 | Editor recipe di form produk + preview HPP | M | S4 (7–18 Des) | BE-REC-002, FE-CAT-002 |
| 28 | FE-INV-003 | Form opening stock & adjustment | M | S4 (7–18 Des) | BE-INV-003, BE-INV-004 |
| 29 | TEST-INV-001 | Unit test StockService: konkurensi, minus, idempotensi, presisi desimal | M | S4 (7–18 Des) | BE-INV-002 |
| 30 | TEST-INV-002 | Feature test opening, adjustment, waste, stok | M | S4 (7–18 Des) | BE-INV-005, BE-INV-006 |
| 31 | TEST-E2E-001 | E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift | M | S4 (7–18 Des) | FE-PAY-002, INT-ENG-001, FE-SHF-003 |
| 32 | TEST-E2E-002 | E2E inventory: opening → purchase receive → adjustment → waste | M | S4 (7–18 Des) | FE-PUR-003, FE-INV-004 |
| 33 | TEST-SEC-001 | Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload | M | S4 (7–18 Des) | TEST-AUTH-001, BE-PAY-002 |
| 34 | TEST-REC-001 | Test recipe (versioning, validasi, HPP) | S | S5 (21–30 Des) | BE-REC-002 |
| 35 | FE-INV-002 | Halaman riwayat movement | S | S5 (21–30 Des) | BE-INV-006 |
| 36 | BE-ENG-005 | GET /orders/{id}/stock-trace | S | S5 (21–30 Des) | BE-ENG-002 |
| 37 | TEST-ENG-001 | Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel | M | S5 (21–30 Des) | INT-ENG-001 |
| 38 | FE-ENG-001 | Indikator stok menipis/habis di layar POS | S | S5 (21–30 Des) | BE-ENG-003, FE-ORD-002 |
| 39 | TEST-RPT-001 | Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone | S | S5 (21–30 Des) | BE-RPT-003 |
| 40 | FE-PWA-002 | Banner offline + aksi tulis ditolak + retry aman (key sama) | S | S5 (21–30 Des) | FE-PWA-001, BE-PAY-002 |
| 41 | DB-SEED-001 | Seeder demo (business, outlet, menu, recipe, stok, supplier) | S | S5 (21–30 Des) | BE-REC-001, BE-INV-003, BE-PUR-001 |
| 42 | TEST-PERF-001 | Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements) | M | S5 (21–30 Des) | INT-ENG-001 |
| 43 | BE-REC-003 | CRUD recipe per modifier option (F-18) | S | Rilis 1.1 (Jan 2027) | DB-REC-001, BE-CAT-004, BE-ING-001 |
| 44 | FE-REC-002 | Editor recipe modifier option (F-18) | S | Rilis 1.1 (Jan 2027) | BE-REC-003, FE-CAT-003 |
| 45 | FE-REC-003 | Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP | XS | Rilis 1.1 (Jan 2027) | BE-REC-001, FE-AUTH-003 |
| 46 | DB-INV-002 | Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS) | S | Rilis 1.1 (Jan 2027) | DB-INV-001 |
| 47 | BE-INV-007 | Opname: mulai (snapshot), input hitungan, batalkan | M | Rilis 1.1 (Jan 2027) | DB-INV-002, BE-INV-004 |
| 48 | BE-INV-008 | Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id) | S | Rilis 1.1 (Jan 2027) | BE-INV-007 |
| 49 | FE-INV-005 | UI opname (mulai, input hitungan, review selisih, submit) | M | Rilis 1.1 (Jan 2027) | BE-INV-008 |
| 50 | TEST-INV-003 | Test opname (snapshot, selisih, konkurensi dengan penjualan) | S | Rilis 1.1 (Jan 2027) | BE-INV-008 |
| 51 | BE-ENG-006 | Pemakaian bahan modifier option ditambahkan ke agregat (F-18) | S | Rilis 1.1 (Jan 2027) | BE-ENG-001, BE-REC-003 |
| 52 | DOC-001 | Dokumentasi API (OpenAPI/Scribe) | M | Rilis 1.1 (Jan 2027) | BE-RPT-003, BE-PUR-004 |
| 53 | DOC-002 | README + runbook dev (Laragon, Supabase, queue worker) | S | Rilis 1.1 (Jan 2027) | SETUP-002 |

## Detail tiket

## S1 (26 Okt–6 Nov)

### SETUP-003 — Pint, Larastan, ESLint, Prettier, pre-commit hook

| Field | Value |
|-------|-------|
| ID | SETUP-003 |
| Type | setup |
| Module |  |
| Phase | FASE 0 — SETUP & INFRASTRUKTUR (S1) |
| Estimate | S |
| Status | todo |
| Depends on | SETUP-001 |
| References | TD §7 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S1 |
| Labels | setup, core |
| Branch | feature/setup-003-pint-larastan-eslint-prettier-pre-commit |
| PR | |

**Context**
Pint, Larastan, ESLint, Prettier, pre-commit hook. Rujukan: TD §7.

**Acceptance Criteria**
- [ ] Commit gagal bila Pint/ESLint melaporkan error
- [ ] Larastan level yang disepakati lulus pada skeleton

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### SETUP-004 — CI GitHub Actions: lint + Pest + build frontend

| Field | Value |
|-------|-------|
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
| PR | |

**Context**
CI GitHub Actions: lint + Pest + build frontend. Rujukan: DEPLOY ops §1.

**Acceptance Criteria**
- [ ] PR memicu lint, Pest, dan `npm run build` (Vite); status check; gagal salah satu = merge diblok
- [ ] Pest memakai database PostgreSQL service container (bukan SQLite)

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### SETUP-009 — Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi

| Field | Value |
|-------|-------|
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
| PR | |

**Context**
Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi. Rujukan: TD §2.3, §2.4.

**Acceptance Criteria**
- [ ] Arch test gagal bila Recipe memanggil POS/Inventory atau Inventory memanggil POS; modul hanya bicara lewat Contract/Facade yang terdaftar
- [ ] Arch test gagal bila ada class `XxxModule` yang belum terdaftar di `config/modules.php` (kecuali `Payments\Gateway`) atau nama migrasi tidak mengikuti pola `modul_aksi_tabel`

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

## FASE 1 — CORE (S1–S5, target 29–30 Des 2026)

---

### DB-ING-001 — Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000)

| Field | Value |
|-------|-------|
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
| PR | |

**Context**
Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000). Rujukan: ERD §3.2; BR-046.

**Acceptance Criteria**
- [ ] Unit g, kg, ml, L, pcs ter-seed; faktor kg→g dan L→ml = 1000
- [ ] last_cost DECIMAL(18,4), CHECK ≥ 0

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-ING-001

---

### BE-ING-001 — CRUD ingredient (permission inventory.adjust, baca inventory.view)

| Field | Value |
|-------|-------|
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
| PR | |

**Context**
CRUD ingredient (permission inventory.adjust, baca inventory.view). Rujukan: FR-ING-001; UC-ING-01.

**Acceptance Criteria**
- [ ] SKU ganda 422; base unit tidak bisa diubah bila sudah ada movement; perubahan `last_cost` manual menulis audit `INGREDIENT_COST_CHANGED`
- [ ] last_cost negatif ditolak; perubahan tidak mengubah movement/order lama

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ING-001, TC-ING-002, TC-ING-003

---

### BE-ING-002 — Seeder unit + endpoint GET /units

| Field | Value |
|-------|-------|
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
| PR | |

**Context**
Seeder unit + endpoint GET /units. Rujukan: FR-ING-001.

**Acceptance Criteria**
- [ ] GET /units mengembalikan 5 satuan
- [ ] Seeder idempoten (aman dijalankan ulang)

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ING-001

---

### DB-REC-001 — Migrasi recipes (versi), recipe_items, modifier_option_recipe_items

| Field | Value |
|-------|-------|
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
| PR | |

**Context**
Migrasi recipes (versi), recipe_items, modifier_option_recipe_items. Rujukan: ERD §3.2; BR-013.

**Acceptance Criteria**
- [ ] UNIQUE parsial 1 recipe aktif per produk
- [ ] UNIQUE (recipe_id, ingredient_id)

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-REC-001

---

## S2 (9–20 Nov)

### BE-REC-001 — Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP

| Field | Value |
|-------|-------|
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
| PR | |

**Context**
Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP. Rujukan: FR-REC-001; UC-REC-01; SEQ-REC-01.

**Acceptance Criteria**
- [ ] Simpan membuat version+1, menonaktifkan versi lama (satu transaksi), audit `RECIPE_UPDATED`
- [ ] Qty ≤ 0, bahan ganda, recipe kosong ditolak dengan pesan SRS

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-001, TC-REC-002, TC-REC-004

---

### BE-REC-002 — Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price

| Field | Value |
|-------|-------|
| ID | BE-REC-002 |
| Type | backend |
| Module | REC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-REC-001 |
| References | FR-REC-003; BR-014 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, rec, core |
| Branch | feature/be-rec-002-kalkulator-hpp-qty-last-cost-half-up-tan |
| PR | |

**Context**
Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price. Rujukan: FR-REC-003; BR-014.

**Acceptance Criteria**
- [ ] HPP produk tanpa recipe = 0 dan ditandai no recipe
- [ ] HPP hanya tampil untuk Owner dan Manager

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-003

---

### DB-INV-001 — Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches

| Field | Value |
|-------|-------|
| ID | DB-INV-001 |
| Type | database |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-ING-001, DB-AUD-001 |
| References | ERD §3.3; ADR-007 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | database, inv, core |
| Branch | feature/db-inv-001-migrasi-stock-balances-stock-movements-t |
| PR | |

**Context**
Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches. Rujukan: ERD §3.3; ADR-007.

**Acceptance Criteria**
- [ ] UNIQUE idempotensi movement dan CHECK arah delta sesuai ERD
- [ ] UPDATE/DELETE movement ditolak trigger

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-INV-011

---

### BE-INV-001 — StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative)

| Field | Value |
|-------|-------|
| ID | BE-INV-001 |
| Type | backend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | DB-INV-001 |
| References | ADR-002; ACT-INV-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, inv, core |
| Branch | feature/be-inv-001-stockservice-apply-lock-for-update-befor |
| PR | |

**Context**
StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative). Rujukan: ADR-002; ACT-INV-01.

**Acceptance Criteria**
- [ ] Stok kurang dan minus dilarang melempar InsufficientStock
- [ ] forceNegative hanya dipakai jalur settlement gateway (default false)

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-002

---

### BE-INV-002 — Idempotensi movement (referensi sama = movement lama) + helper reverse()

| Field | Value |
|-------|-------|
| ID | BE-INV-002 |
| Type | backend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-INV-001 |
| References | BR-009; NFR-DATA-003 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, inv, core |
| Branch | feature/be-inv-002-idempotensi-movement-referensi-sama-move |
| PR | |

**Context**
Idempotensi movement (referensi sama = movement lama) + helper reverse(). Rujukan: BR-009; NFR-DATA-003.

**Acceptance Criteria**
- [ ] Pemanggilan ulang dengan referensi sama mengembalikan movement lama tanpa baris baru
- [ ] reverse() membuat movement lawan dengan referensi baru

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-001

---

### BE-INV-003 — POST /inventory/opening-stock (batch + movement OPENING)

| Field | Value |
|-------|-------|
| ID | BE-INV-003 |
| Type | backend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-INV-001 |
| References | FR-INV-001; UC-INV-01; SEQ-INV-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, inv, core |
| Branch | feature/be-inv-003-post-inventory-opening-stock-batch-movem |
| PR | |

**Context**
POST /inventory/opening-stock (batch + movement OPENING). Rujukan: FR-INV-001; UC-INV-01; SEQ-INV-01.

**Acceptance Criteria**
- [ ] Satu batch menghasilkan satu movement OPENING per bahan dengan reference_id = id batch; audit `OPENING_STOCK`
- [ ] Qty ≤ 0 dan bahan ganda dalam batch ditolak

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-004

---

### BE-INV-004 — POST/GET /inventory/adjustments (record dibuat sebelum movement)

| Field | Value |
|-------|-------|
| ID | BE-INV-004 |
| Type | backend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-001, BE-AUD-001 |
| References | FR-INV-003; UC-INV-03 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, inv, core |
| Branch | feature/be-inv-004-post-get-inventory-adjustments-record-di |
| PR | |

**Context**
POST/GET /inventory/adjustments (record dibuat sebelum movement). Rujukan: FR-INV-003; UC-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib dan qty > 0; before/after tersimpan; audit `STOCK_ADJUSTMENT`
- [ ] Hasil minus saat minus dilarang 422 INSUFFICIENT_STOCK

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-005

---

### BE-ENG-001 — RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan

| Field | Value |
|-------|-------|
| ID | BE-ENG-001 |
| Type | backend |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-REC-001, DB-ORD-001 |
| References | FR-ENG-001; BR-009, BR-010 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, eng, core |
| Branch | feature/be-eng-001-recipeusagecalculator-item-recipe-versi |
| PR | |

**Context**
RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan. Rujukan: FR-ENG-001; BR-009, BR-010.

**Acceptance Criteria**
- [ ] Bahan sama di beberapa item diagregat menjadi satu angka
- [ ] Produk tanpa recipe dilewati

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-001, TC-ENG-002, TC-ENG-003

---

### BE-ENG-002 — DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten

| Field | Value |
|-------|-------|
| ID | BE-ENG-002 |
| Type | backend |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-ENG-001, BE-INV-002 |
| References | ADR-002; SEQ-PAY-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S2 |
| Labels | backend, eng, core |
| Branch | feature/be-eng-002-deductstockfororder-lock-urut-ingredient |
| PR | |

**Context**
DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten. Rujukan: ADR-002; SEQ-PAY-01.

**Acceptance Criteria**
- [ ] Dua order paralel dengan bahan sama tidak deadlock dan stok benar
- [ ] Movement SALE unit_cost memakai biaya dari snapshot order (BR-051), bukan last_cost saat bayar

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-005

---

## S3 (23 Nov–4 Des)

### TEST-CAT-001 — Feature test katalog (tenant, SKU unik, inactive, cache)

| Field | Value |
|-------|-------|
| ID | TEST-CAT-001 |
| Type | testing |
| Module | CAT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-CAT-005 |
| References | FR-CAT-001…004 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | testing, cat, core |
| Branch | feature/test-cat-001-feature-test-katalog-tenant-sku-unik-ina |
| PR | |

**Context**
Feature test katalog (tenant, SKU unik, inactive, cache). Rujukan: FR-CAT-001…004.

**Acceptance Criteria**
- [ ] Semua baris tabel validasi FR-CAT-002/003 punya test
- [ ] Test cache invalidasi lulus

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-CAT-001…006

---

### FE-ING-001 — Halaman ingredient

| Field | Value |
|-------|-------|
| ID | FE-ING-001 |
| Type | frontend |
| Module | ING |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-ING-001, FE-AUTH-003 |
| References | SCR-ING-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | frontend, ing, core |
| Branch | feature/fe-ing-001-halaman-ingredient |
| PR | |

**Context**
Halaman ingredient. Rujukan: SCR-ING-01.

**Acceptance Criteria**
- [ ] CRUD ingredient dengan pesan error SRS
- [ ] Base unit terkunci bila sudah ada movement

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ING-002

---

### BE-INV-005 — POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya)

| Field | Value |
|-------|-------|
| ID | BE-INV-005 |
| Type | backend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-001, BE-AUD-001 |
| References | FR-INV-004; UC-INV-04; BR-034 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | backend, inv, core |
| Branch | feature/be-inv-005-post-get-inventory-waste-alasan-enum-sna |
| PR | |

**Context**
POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya). Rujukan: FR-INV-004; UC-INV-04; BR-034.

**Acceptance Criteria**
- [ ] Alasan OTHER tanpa catatan 422
- [ ] total_cost tidak ada di respons untuk role Kitchen; audit `WASTE`

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-006, TC-INV-007

---

### BE-INV-006 — GET stok, riwayat movement, low stock + stok minus

| Field | Value |
|-------|-------|
| ID | BE-INV-006 |
| Type | backend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-INV-001 |
| References | FR-INV-002, FR-INV-005; UC-INV-02 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | backend, inv, core |
| Branch | feature/be-inv-006-get-stok-riwayat-movement-low-stock-stok |
| PR | |

**Context**
GET stok, riwayat movement, low stock + stok minus. Rujukan: FR-INV-002, FR-INV-005; UC-INV-02.

**Acceptance Criteria**
- [ ] Filter ingredient, tipe, tanggal dengan paginasi
- [ ] Low stock memakai min_stock override outlet; stok minus tampil terpisah

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-008

---

### FE-INV-001 — Halaman stok + badge low stock/minus

| Field | Value |
|-------|-------|
| ID | FE-INV-001 |
| Type | frontend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-006, FE-AUTH-003 |
| References | SCR-INV-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | frontend, inv, core |
| Branch | feature/fe-inv-001-halaman-stok-badge-low-stock-minus |
| PR | |

**Context**
Halaman stok + badge low stock/minus. Rujukan: SCR-INV-01.

**Acceptance Criteria**
- [ ] Badge low stock dan minus berbeda dan menonjol
- [ ] State empty/loading/error ada

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-008

---

### FE-INV-004 — Form & daftar waste

| Field | Value |
|-------|-------|
| ID | FE-INV-004 |
| Type | frontend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-INV-005 |
| References | SCR-INV-04 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | frontend, inv, core |
| Branch | feature/fe-inv-004-form-daftar-waste |
| PR | |

**Context**
Form & daftar waste. Rujukan: SCR-INV-04.

**Acceptance Criteria**
- [ ] Catatan wajib saat alasan OTHER
- [ ] Nilai biaya disembunyikan untuk Kitchen

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-006, TC-INV-007

---

### BE-ENG-003 — Kebijakan stok minus: stock_shortage di respons dan audit

| Field | Value |
|-------|-------|
| ID | BE-ENG-003 |
| Type | backend |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ENG-002 |
| References | FR-ENG-002; BR-011 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | backend, eng, core |
| Branch | feature/be-eng-003-kebijakan-stok-minus-stock-shortage-di-r |
| PR | |

**Context**
Kebijakan stok minus: stock_shortage di respons dan audit. Rujukan: FR-ENG-002; BR-011.

**Acceptance Criteria**
- [ ] allow_negative_stock = true: bayar sukses dan stock_shortage terisi
- [ ] false: 422 INSUFFICIENT_STOCK dengan details.ingredients

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-004

---

### BE-ENG-004 — Jalur restock refund (movement RETURN dari movement SALE order)

| Field | Value |
|-------|-------|
| ID | BE-ENG-004 |
| Type | backend |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ENG-002 |
| References | FR-RFD-001; BR-012 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | backend, eng, core |
| Branch | feature/be-eng-004-jalur-restock-refund-movement-return-dar |
| PR | |

**Context**
Jalur restock refund (movement RETURN dari movement SALE order). Rujukan: FR-RFD-001; BR-012.

**Acceptance Criteria**
- [ ] Restock membuat RETURN positif per bahan dengan reference REFUND:id
- [ ] Tanpa restock tidak ada movement baru

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-013

---

### INT-ENG-001 — Integration test Sale → Payment → Recipe → Movement → Balance

| Field | Value |
|-------|-------|
| ID | INT-ENG-001 |
| Type | integration |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-PAY-003, BE-ENG-002 |
| References | NFR-DATA-001/002 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | integration, eng, core |
| Branch | feature/int-eng-001-integration-test-sale-payment-recipe-mov |
| PR | |

**Context**
Integration test Sale → Payment → Recipe → Movement → Balance. Rujukan: NFR-DATA-001/002.

**Acceptance Criteria**
- [ ] balance = SUM(movements) untuk semua bahan pasca skenario
- [ ] Skenario lengkap order→bayar→refund restock lulus

**Technical Notes**
- Rujuk Test Plan §4

**Test Reference**
TC-ENG-001…005

---

### FE-PWA-001 — Manifest + service worker (vite-plugin-pwa), cache aset statis saja

| Field | Value |
|-------|-------|
| ID | FE-PWA-001 |
| Type | frontend |
| Module | PWA |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | FE-ORD-001 |
| References | FR-PWA-001; F-17 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S3 |
| Labels | frontend, pwa, core |
| Branch | feature/fe-pwa-001-manifest-service-worker-vite-plugin-pwa |
| PR | |

**Context**
Manifest + service worker (vite-plugin-pwa), cache aset statis saja. Rujukan: FR-PWA-001; F-17.

**Acceptance Criteria**
- [ ] Installable (Lighthouse PWA lulus)
- [ ] Respons API tidak pernah di-cache service worker

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PWA-001, TC-PWA-002

---

## S4 (7–18 Des)

### FE-REC-001 — Editor recipe di form produk + preview HPP

| Field | Value |
|-------|-------|
| ID | FE-REC-001 |
| Type | frontend |
| Module | REC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-REC-002, FE-CAT-002 |
| References | SCR-CAT-02; UC-REC-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S4 |
| Labels | frontend, rec, core |
| Branch | feature/fe-rec-001-editor-recipe-di-form-produk-preview-hpp |
| PR | |

**Context**
Editor recipe di form produk + preview HPP. Rujukan: SCR-CAT-02; UC-REC-01.

**Acceptance Criteria**
- [ ] Satuan input memakai base unit bahan
- [ ] Preview HPP disembunyikan untuk non-Owner/Manager

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-003, TC-REC-004

---

### FE-INV-003 — Form opening stock & adjustment

| Field | Value |
|-------|-------|
| ID | FE-INV-003 |
| Type | frontend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-003, BE-INV-004 |
| References | SCR-INV-03 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S4 |
| Labels | frontend, inv, core |
| Branch | feature/fe-inv-003-form-opening-stock-adjustment |
| PR | |

**Context**
Form opening stock & adjustment. Rujukan: SCR-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib tampil pada adjustment
- [ ] Error stok kurang menampilkan pesan SRS

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-004, TC-INV-005

---

### TEST-INV-001 — Unit test StockService: konkurensi, minus, idempotensi, presisi desimal

| Field | Value |
|-------|-------|
| ID | TEST-INV-001 |
| Type | testing |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-002 |
| References | NFR-DATA-001 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S4 |
| Labels | testing, inv, core |
| Branch | feature/test-inv-001-unit-test-stockservice-konkurensi-minus |
| PR | |

**Context**
Unit test StockService: konkurensi, minus, idempotensi, presisi desimal. Rujukan: NFR-DATA-001.

**Acceptance Criteria**
- [ ] 20 apply paralel pada bahan sama: balance = SUM(movements)
- [ ] Presisi DECIMAL(18,4) terjaga

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-001, TC-INV-002, TC-INV-003

---

### TEST-INV-002 — Feature test opening, adjustment, waste, stok

| Field | Value |
|-------|-------|
| ID | TEST-INV-002 |
| Type | testing |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-005, BE-INV-006 |
| References | FR-INV-001…005 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S4 |
| Labels | testing, inv, core |
| Branch | feature/test-inv-002-feature-test-opening-adjustment-waste-st |
| PR | |

**Context**
Feature test opening, adjustment, waste, stok. Rujukan: FR-INV-001…005.

**Acceptance Criteria**
- [ ] Semua baris tabel validasi FR-INV-003/004 diuji
- [ ] Permission Kitchen/Inventory sesuai matriks

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-004…008

---

### TEST-E2E-001 — E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift

| Field | Value |
|-------|-------|
| ID | TEST-E2E-001 |
| Type | testing |
| Module | E2E |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | FE-PAY-002, INT-ENG-001, FE-SHF-003 |
| References | F-01…F-10 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S4 |
| Labels | testing, e2e, core |
| Branch | feature/test-e2e-001-e2e-playwright-register-menu-recipe-shif |
| PR | |

**Context**
E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift. Rujukan: F-01…F-10.

**Acceptance Criteria**
- [ ] Alur utama lulus di staging dan CI
- [ ] Stok setelah bayar sesuai recipe

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-E2E-001

---

### TEST-E2E-002 — E2E inventory: opening → purchase receive → adjustment → waste

| Field | Value |
|-------|-------|
| ID | TEST-E2E-002 |
| Type | testing |
| Module | E2E |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | FE-PUR-003, FE-INV-004 |
| References | F-11, F-12, F-20 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S4 |
| Labels | testing, e2e, core |
| Branch | feature/test-e2e-002-e2e-inventory-opening-purchase-receive-a |
| PR | |

**Context**
E2E inventory: opening → purchase receive → adjustment → waste. Rujukan: F-11, F-12, F-20.

**Acceptance Criteria**
- [ ] Stok dan last_cost berubah sesuai purchase
- [ ] Movement tercatat di riwayat

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-E2E-002

---

### TEST-SEC-001 — Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload

| Field | Value |
|-------|-------|
| ID | TEST-SEC-001 |
| Type | testing |
| Module | SEC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | TEST-AUTH-001, BE-PAY-002 |
| References | NFR-SEC-001…007 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S4 |
| Labels | testing, sec, core |
| Branch | feature/test-sec-001-checklist-keamanan-tenant-throttling-cor |
| PR | |

**Context**
Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload. Rujukan: NFR-SEC-001…007.

**Acceptance Criteria**
- [ ] Semua butir checklist lulus atau punya tiket perbaikan
- [ ] Webhook/gateway ditandai out of scope Phase 1

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-SEC-001

---

## S5 (21–30 Des)

### TEST-REC-001 — Test recipe (versioning, validasi, HPP)

| Field | Value |
|-------|-------|
| ID | TEST-REC-001 |
| Type | testing |
| Module | REC |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-REC-002 |
| References | FR-REC-001, FR-REC-003 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | testing, rec, core |
| Branch | feature/test-rec-001-test-recipe-versioning-validasi-hpp |
| PR | |

**Context**
Test recipe (versioning, validasi, HPP). Rujukan: FR-REC-001, FR-REC-003.

**Acceptance Criteria**
- [ ] Versioning dan 1 aktif teruji (termasuk constraint DB)
- [ ] Perhitungan HPP half-up sesuai vektor uji

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-REC-001…004

---

### FE-INV-002 — Halaman riwayat movement

| Field | Value |
|-------|-------|
| ID | FE-INV-002 |
| Type | frontend |
| Module | INV |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-INV-006 |
| References | SCR-INV-02 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | frontend, inv, core |
| Branch | feature/fe-inv-002-halaman-riwayat-movement |
| PR | |

**Context**
Halaman riwayat movement. Rujukan: SCR-INV-02.

**Acceptance Criteria**
- [ ] Filter tipe/tanggal bekerja
- [ ] Tidak ada aksi ubah/hapus movement

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-011

---

### BE-ENG-005 — GET /orders/{id}/stock-trace

| Field | Value |
|-------|-------|
| ID | BE-ENG-005 |
| Type | backend |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ENG-002 |
| References | FR-ENG-003 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | backend, eng, core |
| Branch | feature/be-eng-005-get-orders-id-stock-trace |
| PR | |

**Context**
GET /orders/{id}/stock-trace. Rujukan: FR-ENG-003.

**Acceptance Criteria**
- [ ] Menampilkan movement SALE dan RETURN milik order
- [ ] Butuh inventory.view; order tenant lain 404

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-006

---

### TEST-ENG-001 — Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel

| Field | Value |
|-------|-------|
| ID | TEST-ENG-001 |
| Type | testing |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | INT-ENG-001 |
| References | BR-013 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | testing, eng, core |
| Branch | feature/test-eng-001-edge-case-tanpa-recipe-recipe-diubah-set |
| PR | |

**Context**
Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel. Rujukan: BR-013.

**Acceptance Criteria**
- [ ] Recipe versi baru tidak mengubah movement order lama
- [ ] Produk tanpa recipe tampil di laporan no recipe

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ENG-002, TC-ENG-003, TC-ENG-005

---

### FE-ENG-001 — Indikator stok menipis/habis di layar POS

| Field | Value |
|-------|-------|
| ID | FE-ENG-001 |
| Type | frontend |
| Module | ENG |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ENG-003, FE-ORD-002 |
| References | SCR-POS-01 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | frontend, eng, core |
| Branch | feature/fe-eng-001-indikator-stok-menipis-habis-di-layar-po |
| PR | |

**Context**
Indikator stok menipis/habis di layar POS. Rujukan: SCR-POS-01.

**Acceptance Criteria**
- [ ] Indikator tampil tanpa memblokir penjualan
- [ ] Data berasal dari endpoint stok (tanpa HPP)

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ENG-004

---

### TEST-RPT-001 — Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone

| Field | Value |
|-------|-------|
| ID | TEST-RPT-001 |
| Type | testing |
| Module | RPT |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-RPT-003 |
| References | FR-RPT-001…005 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | testing, rpt, core |
| Branch | feature/test-rpt-001-test-laporan-sales-payment-angka-vs-fixt |
| PR | |

**Context**
Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone. Rujukan: FR-RPT-001…005.

**Acceptance Criteria**
- [ ] Business date lintas tengah malam benar
- [ ] Isolasi tenant pada semua endpoint laporan

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-RPT-001…005

---

### FE-PWA-002 — Banner offline + aksi tulis ditolak + retry aman (key sama)

| Field | Value |
|-------|-------|
| ID | FE-PWA-002 |
| Type | frontend |
| Module | PWA |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | FE-PWA-001, BE-PAY-002 |
| References | FR-PWA-001 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | frontend, pwa, core |
| Branch | feature/fe-pwa-002-banner-offline-aksi-tulis-ditolak-retry |
| PR | |

**Context**
Banner offline + aksi tulis ditolak + retry aman (key sama). Rujukan: FR-PWA-001.

**Acceptance Criteria**
- [ ] Offline menampilkan banner dan menolak aksi tulis
- [ ] Retry bayar memakai Idempotency-Key yang sama

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PWA-003

---

### DB-SEED-001 — Seeder demo (business, outlet, menu, recipe, stok, supplier)

| Field | Value |
|-------|-------|
| ID | DB-SEED-001 |
| Type | database |
| Module | SEED |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | S |
| Status | todo |
| Depends on | BE-REC-001, BE-INV-003, BE-PUR-001 |
| References | TD §8 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | database, seed, core |
| Branch | feature/db-seed-001-seeder-demo-business-outlet-menu-recipe |
| PR | |

**Context**
Seeder demo (business, outlet, menu, recipe, stok, supplier). Rujukan: TD §8.

**Acceptance Criteria**
- [ ] Satu perintah menghasilkan data demo siap dipakai UAT
- [ ] Aman dijalankan ulang di staging

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### TEST-PERF-001 — Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements)

| Field | Value |
|-------|-------|
| ID | TEST-PERF-001 |
| Type | testing |
| Module | PERF |
| Phase | FASE 1 — CORE (S1–S5, target 29–30 Des 2026) |
| Estimate | M |
| Status | todo |
| Depends on | INT-ENG-001 |
| References | NFR-PERF-003, NFR-PERF-004 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | S5 |
| Labels | testing, perf, core |
| Branch | feature/test-perf-001-load-test-20-pembayaran-paralel-bahan-sa |
| PR | |

**Context**
Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements). Rujukan: NFR-PERF-003, NFR-PERF-004.

**Acceptance Criteria**
- [ ] p95 bayar + potong stok < 800 ms (order ≤ 10 item)
- [ ] 0 deadlock dan balance = SUM(movements)

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PERF-001

---

## Rilis 1.1 (Jan 2027)

### BE-REC-003 — CRUD recipe per modifier option (F-18)

| Field | Value |
|-------|-------|
| ID | BE-REC-003 |
| Type | backend |
| Module | REC |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | DB-REC-001, BE-CAT-004, BE-ING-001 |
| References | FR-REC-002; UC-REC-02 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | backend, rec, r1.1 |
| Branch | feature/be-rec-003-crud-recipe-per-modifier-option-f-18 |
| PR | |

**Context**
CRUD recipe per modifier option (F-18). Rujukan: FR-REC-002; UC-REC-02.

**Acceptance Criteria**
- [ ] Qty > 0 dalam base unit; perubahan tercatat audit
- [ ] Hanya recipes.manage yang boleh mengubah

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-005

---

### FE-REC-002 — Editor recipe modifier option (F-18)

| Field | Value |
|-------|-------|
| ID | FE-REC-002 |
| Type | frontend |
| Module | REC |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-REC-003, FE-CAT-003 |
| References | SCR-CAT-03 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | frontend, rec, r1.1 |
| Branch | feature/fe-rec-002-editor-recipe-modifier-option-f-18 |
| PR | |

**Context**
Editor recipe modifier option (F-18). Rujukan: SCR-CAT-03.

**Acceptance Criteria**
- [ ] Editor muncul di UI modifier option
- [ ] Validasi qty dan bahan ganda sama dengan recipe produk

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-005

---

### FE-REC-003 — Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP

| Field | Value |
|-------|-------|
| ID | FE-REC-003 |
| Type | frontend |
| Module | REC |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | XS |
| Status | todo |
| Depends on | BE-REC-001, FE-AUTH-003 |
| References | UC-REC-03 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | frontend, rec, r1.1 |
| Branch | feature/fe-rec-003-tampilan-recipe-read-only-untuk-inventor |
| PR | |

**Context**
Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP. Rujukan: UC-REC-03.

**Acceptance Criteria**
- [ ] Role tanpa recipes.manage tidak melihat tombol ubah
- [ ] HPP tidak ada di respons maupun UI

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-004

---

### DB-INV-002 — Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS)

| Field | Value |
|-------|-------|
| ID | DB-INV-002 |
| Type | database |
| Module | INV |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | DB-INV-001 |
| References | ERD §3.3; BR-017 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | database, inv, r1.1 |
| Branch | feature/db-inv-002-migrasi-stock-opnames-stock-opname-items |
| PR | |

**Context**
Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS). Rujukan: ERD §3.3; BR-017.

**Acceptance Criteria**
- [ ] Hanya 1 opname IN_PROGRESS per outlet di level DB
- [ ] UNIQUE (opname_id, ingredient_id)

**Technical Notes**
- Ikuti ERD v3.0; migrasi lewat direct connection Supabase; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-INV-010

---

### BE-INV-007 — Opname: mulai (snapshot), input hitungan, batalkan

| Field | Value |
|-------|-------|
| ID | BE-INV-007 |
| Type | backend |
| Module | INV |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | DB-INV-002, BE-INV-004 |
| References | FR-INV-006; UC-INV-05; SEQ-INV-02 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | backend, inv, r1.1 |
| Branch | feature/be-inv-007-opname-mulai-snapshot-input-hitungan-bat |
| PR | |

**Context**
Opname: mulai (snapshot), input hitungan, batalkan. Rujukan: FR-INV-006; UC-INV-05; SEQ-INV-02.

**Acceptance Criteria**
- [ ] Mulai saat ada opname berjalan 409 OPNAME_IN_PROGRESS
- [ ] Hitungan negatif 422

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-009, TC-INV-010

---

### BE-INV-008 — Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id)

| Field | Value |
|-------|-------|
| ID | BE-INV-008 |
| Type | backend |
| Module | INV |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-INV-007 |
| References | BR-017; STM-OPNAME |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | backend, inv, r1.1 |
| Branch | feature/be-inv-008-opname-submit-selisih-vs-snapshot-ditera |
| PR | |

**Context**
Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id). Rujukan: BR-017; STM-OPNAME.

**Acceptance Criteria**
- [ ] Penjualan selama penghitungan tidak hilang (selisih dihitung terhadap snapshot)
- [ ] Submit pada opname bukan IN_PROGRESS 409 OPNAME_NOT_ACTIVE

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-009

---

### FE-INV-005 — UI opname (mulai, input hitungan, review selisih, submit)

| Field | Value |
|-------|-------|
| ID | FE-INV-005 |
| Type | frontend |
| Module | INV |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | BE-INV-008 |
| References | SCR-INV-05 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | frontend, inv, r1.1 |
| Branch | feature/fe-inv-005-ui-opname-mulai-input-hitungan-review-se |
| PR | |

**Context**
UI opname (mulai, input hitungan, review selisih, submit). Rujukan: SCR-INV-05.

**Acceptance Criteria**
- [ ] Layar nyaman di HP (target sentuh ≥ 44 px)
- [ ] Review selisih tampil sebelum submit

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-009

---

### TEST-INV-003 — Test opname (snapshot, selisih, konkurensi dengan penjualan)

| Field | Value |
|-------|-------|
| ID | TEST-INV-003 |
| Type | testing |
| Module | INV |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-INV-008 |
| References | FR-INV-006 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | testing, inv, r1.1 |
| Branch | feature/test-inv-003-test-opname-snapshot-selisih-konkurensi |
| PR | |

**Context**
Test opname (snapshot, selisih, konkurensi dengan penjualan). Rujukan: FR-INV-006.

**Acceptance Criteria**
- [ ] Skenario penjualan selama opname lulus
- [ ] Submit ganda ditolak

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-009, TC-INV-010

---

### BE-ENG-006 — Pemakaian bahan modifier option ditambahkan ke agregat (F-18)

| Field | Value |
|-------|-------|
| ID | BE-ENG-006 |
| Type | backend |
| Module | ENG |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | BE-ENG-001, BE-REC-003 |
| References | FR-REC-002; FR-ENG-001 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | backend, eng, r1.1 |
| Branch | feature/be-eng-006-pemakaian-bahan-modifier-option-ditambah |
| PR | |

**Context**
Pemakaian bahan modifier option ditambahkan ke agregat (F-18). Rujukan: FR-REC-002; FR-ENG-001.

**Acceptance Criteria**
- [ ] Pemakaian modifier dihitung dari recipe option aktif saat pembayaran
- [ ] Modifier tanpa recipe tidak memotong stok

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-007

---

### DOC-001 — Dokumentasi API (OpenAPI/Scribe)

| Field | Value |
|-------|-------|
| ID | DOC-001 |
| Type | docs |
| Module |  |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | M |
| Status | todo |
| Depends on | BE-RPT-003, BE-PUR-004 |
| References | SDD §4 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | docs, setup, r1.1 |
| Branch | feature/doc-001-dokumentasi-api-openapi-scribe |
| PR | |

**Context**
Dokumentasi API (OpenAPI/Scribe). Rujukan: SDD §4.

**Acceptance Criteria**
- [ ] Semua endpoint Phase 1 terdokumentasi dengan contoh
- [ ] Dokumen dapat diakses di staging

**Technical Notes**
- Rujuk SDD §4

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---

### DOC-002 — README + runbook dev (Laragon, Supabase, queue worker)

| Field | Value |
|-------|-------|
| ID | DOC-002 |
| Type | docs |
| Module |  |
| Phase | FASE 2 — RILIS 1.1 (Jan 2027) |
| Estimate | S |
| Status | todo |
| Depends on | SETUP-002 |
| References | TD §6 |
| Assignee | @akmal |
| Reviewer | @naufaldi |
| Sprint | R1.1 |
| Labels | docs, setup, r1.1 |
| Branch | feature/doc-002-readme-runbook-dev-laragon-supabase-queu |
| PR | |

**Context**
README + runbook dev (Laragon, Supabase, queue worker). Rujukan: TD §6.

**Acceptance Criteria**
- [ ] Developer baru bisa menjalankan proyek dari README tanpa bertanya
- [ ] Runbook queue worker dan scheduler tersedia

**Technical Notes**
- Rujuk SDD §4

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)

---
