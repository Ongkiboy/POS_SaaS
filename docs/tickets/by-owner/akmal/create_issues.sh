#!/usr/bin/env bash
set -euo pipefail
# Butuh: gh auth login. Jalankan dari dalam repo.

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
gh issue create --title '[SETUP-009] Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi' --body '**Context**
Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi. Rujukan: TD §2.3, §2.4.

**Acceptance Criteria**
- [ ] Arch test gagal bila Recipe memanggil POS/Inventory atau Inventory memanggil POS; modul hanya bicara lewat Contract/Facade yang terdaftar
- [ ] Arch test gagal bila ada class `XxxModule` yang belum terdaftar di `config/modules.php` (kecuali `Payments\Gateway`) atau nama migrasi tidak mengikuti pola `modul_aksi_tabel`

**Depends on:** SETUP-005
**References:** TD §2.3, §2.4
**Estimate:** M
**Branch:** `feature/setup-009-arch-test-pest-dependensi-modul-peta-pan`' --label 'setup,core' --assignee 'akmal'
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
- [ ] SKU ganda 422; base unit tidak bisa diubah bila sudah ada movement; perubahan `last_cost` manual menulis audit `INGREDIENT_COST_CHANGED`
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
- [ ] Simpan membuat version+1, menonaktifkan versi lama (satu transaksi), audit `RECIPE_UPDATED`
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
- [ ] Satu batch menghasilkan satu movement OPENING per bahan dengan reference_id = id batch; audit `OPENING_STOCK`
- [ ] Qty ≤ 0 dan bahan ganda dalam batch ditolak

**Depends on:** BE-INV-001
**References:** FR-INV-001; UC-INV-01; SEQ-INV-01
**Estimate:** S
**Branch:** `feature/be-inv-003-post-inventory-opening-stock-batch-movem`' --label 'backend,inv,core' --assignee 'akmal'
gh issue create --title '[BE-INV-004] POST/GET /inventory/adjustments (record dibuat sebelum movement)' --body '**Context**
POST/GET /inventory/adjustments (record dibuat sebelum movement). Rujukan: FR-INV-003; UC-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib dan qty > 0; before/after tersimpan; audit `STOCK_ADJUSTMENT`
- [ ] Hasil minus saat minus dilarang 422 INSUFFICIENT_STOCK

**Depends on:** BE-INV-001, BE-AUD-001
**References:** FR-INV-003; UC-INV-03
**Estimate:** M
**Branch:** `feature/be-inv-004-post-get-inventory-adjustments-record-di`' --label 'backend,inv,core' --assignee 'akmal'
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
- [ ] Movement SALE unit_cost memakai biaya dari snapshot order (BR-051), bukan last_cost saat bayar

**Depends on:** BE-ENG-001, BE-INV-002
**References:** ADR-002; SEQ-PAY-01
**Estimate:** M
**Branch:** `feature/be-eng-002-deductstockfororder-lock-urut-ingredient`' --label 'backend,eng,core' --assignee 'akmal'
gh issue create --title '[TEST-CAT-001] Feature test katalog (tenant, SKU unik, inactive, cache)' --body '**Context**
Feature test katalog (tenant, SKU unik, inactive, cache). Rujukan: FR-CAT-001…004.

**Acceptance Criteria**
- [ ] Semua baris tabel validasi FR-CAT-002/003 punya test
- [ ] Test cache invalidasi lulus

**Depends on:** BE-CAT-005
**References:** FR-CAT-001…004
**Estimate:** M
**Branch:** `feature/test-cat-001-feature-test-katalog-tenant-sku-unik-ina`' --label 'testing,cat,core' --assignee 'akmal'
gh issue create --title '[FE-ING-001] Halaman ingredient' --body '**Context**
Halaman ingredient. Rujukan: SCR-ING-01.

**Acceptance Criteria**
- [ ] CRUD ingredient dengan pesan error SRS
- [ ] Base unit terkunci bila sudah ada movement

**Depends on:** BE-ING-001, FE-AUTH-003
**References:** SCR-ING-01
**Estimate:** M
**Branch:** `feature/fe-ing-001-halaman-ingredient`' --label 'frontend,ing,core' --assignee 'akmal'
gh issue create --title '[BE-INV-005] POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya)' --body '**Context**
POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya). Rujukan: FR-INV-004; UC-INV-04; BR-034.

**Acceptance Criteria**
- [ ] Alasan OTHER tanpa catatan 422
- [ ] total_cost tidak ada di respons untuk role Kitchen; audit `WASTE`

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
gh issue create --title '[FE-INV-004] Form & daftar waste' --body '**Context**
Form & daftar waste. Rujukan: SCR-INV-04.

**Acceptance Criteria**
- [ ] Catatan wajib saat alasan OTHER
- [ ] Nilai biaya disembunyikan untuk Kitchen

**Depends on:** BE-INV-005
**References:** SCR-INV-04
**Estimate:** S
**Branch:** `feature/fe-inv-004-form-daftar-waste`' --label 'frontend,inv,core' --assignee 'akmal'
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
gh issue create --title '[INT-ENG-001] Integration test Sale → Payment → Recipe → Movement → Balance' --body '**Context**
Integration test Sale → Payment → Recipe → Movement → Balance. Rujukan: NFR-DATA-001/002.

**Acceptance Criteria**
- [ ] balance = SUM(movements) untuk semua bahan pasca skenario
- [ ] Skenario lengkap order→bayar→refund restock lulus

**Depends on:** BE-PAY-003, BE-ENG-002
**References:** NFR-DATA-001/002
**Estimate:** M
**Branch:** `feature/int-eng-001-integration-test-sale-payment-recipe-mov`' --label 'integration,eng,core' --assignee 'akmal'
gh issue create --title '[FE-PWA-001] Manifest + service worker (vite-plugin-pwa), cache aset statis saja' --body '**Context**
Manifest + service worker (vite-plugin-pwa), cache aset statis saja. Rujukan: FR-PWA-001; F-17.

**Acceptance Criteria**
- [ ] Installable (Lighthouse PWA lulus)
- [ ] Respons API tidak pernah di-cache service worker

**Depends on:** FE-ORD-001
**References:** FR-PWA-001; F-17
**Estimate:** M
**Branch:** `feature/fe-pwa-001-manifest-service-worker-vite-plugin-pwa`' --label 'frontend,pwa,core' --assignee 'akmal'
gh issue create --title '[FE-REC-001] Editor recipe di form produk + preview HPP' --body '**Context**
Editor recipe di form produk + preview HPP. Rujukan: SCR-CAT-02; UC-REC-01.

**Acceptance Criteria**
- [ ] Satuan input memakai base unit bahan
- [ ] Preview HPP disembunyikan untuk non-Owner/Manager

**Depends on:** BE-REC-002, FE-CAT-002
**References:** SCR-CAT-02; UC-REC-01
**Estimate:** M
**Branch:** `feature/fe-rec-001-editor-recipe-di-form-produk-preview-hpp`' --label 'frontend,rec,core' --assignee 'akmal'
gh issue create --title '[FE-INV-003] Form opening stock & adjustment' --body '**Context**
Form opening stock & adjustment. Rujukan: SCR-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib tampil pada adjustment
- [ ] Error stok kurang menampilkan pesan SRS

**Depends on:** BE-INV-003, BE-INV-004
**References:** SCR-INV-03
**Estimate:** M
**Branch:** `feature/fe-inv-003-form-opening-stock-adjustment`' --label 'frontend,inv,core' --assignee 'akmal'
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
gh issue create --title '[TEST-REC-001] Test recipe (versioning, validasi, HPP)' --body '**Context**
Test recipe (versioning, validasi, HPP). Rujukan: FR-REC-001, FR-REC-003.

**Acceptance Criteria**
- [ ] Versioning dan 1 aktif teruji (termasuk constraint DB)
- [ ] Perhitungan HPP half-up sesuai vektor uji

**Depends on:** BE-REC-002
**References:** FR-REC-001, FR-REC-003
**Estimate:** S
**Branch:** `feature/test-rec-001-test-recipe-versioning-validasi-hpp`' --label 'testing,rec,core' --assignee 'akmal'
gh issue create --title '[FE-INV-002] Halaman riwayat movement' --body '**Context**
Halaman riwayat movement. Rujukan: SCR-INV-02.

**Acceptance Criteria**
- [ ] Filter tipe/tanggal bekerja
- [ ] Tidak ada aksi ubah/hapus movement

**Depends on:** BE-INV-006
**References:** SCR-INV-02
**Estimate:** S
**Branch:** `feature/fe-inv-002-halaman-riwayat-movement`' --label 'frontend,inv,core' --assignee 'akmal'
gh issue create --title '[BE-ENG-005] GET /orders/{id}/stock-trace' --body '**Context**
GET /orders/{id}/stock-trace. Rujukan: FR-ENG-003.

**Acceptance Criteria**
- [ ] Menampilkan movement SALE dan RETURN milik order
- [ ] Butuh inventory.view; order tenant lain 404

**Depends on:** BE-ENG-002
**References:** FR-ENG-003
**Estimate:** S
**Branch:** `feature/be-eng-005-get-orders-id-stock-trace`' --label 'backend,eng,core' --assignee 'akmal'
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
gh issue create --title '[TEST-RPT-001] Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone' --body '**Context**
Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone. Rujukan: FR-RPT-001…005.

**Acceptance Criteria**
- [ ] Business date lintas tengah malam benar
- [ ] Isolasi tenant pada semua endpoint laporan

**Depends on:** BE-RPT-003
**References:** FR-RPT-001…005
**Estimate:** S
**Branch:** `feature/test-rpt-001-test-laporan-sales-payment-angka-vs-fixt`' --label 'testing,rpt,core' --assignee 'akmal'
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
gh issue create --title '[TEST-PERF-001] Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements)' --body '**Context**
Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements). Rujukan: NFR-PERF-003, NFR-PERF-004.

**Acceptance Criteria**
- [ ] p95 bayar + potong stok < 800 ms (order ≤ 10 item)
- [ ] 0 deadlock dan balance = SUM(movements)

**Depends on:** INT-ENG-001
**References:** NFR-PERF-003, NFR-PERF-004
**Estimate:** M
**Branch:** `feature/test-perf-001-load-test-20-pembayaran-paralel-bahan-sa`' --label 'testing,perf,core' --assignee 'akmal'
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
gh issue create --title '[BE-ENG-006] Pemakaian bahan modifier option ditambahkan ke agregat (F-18)' --body '**Context**
Pemakaian bahan modifier option ditambahkan ke agregat (F-18). Rujukan: FR-REC-002; FR-ENG-001.

**Acceptance Criteria**
- [ ] Pemakaian modifier dihitung dari recipe option aktif saat pembayaran
- [ ] Modifier tanpa recipe tidak memotong stok

**Depends on:** BE-ENG-001, BE-REC-003
**References:** FR-REC-002; FR-ENG-001
**Estimate:** S
**Branch:** `feature/be-eng-006-pemakaian-bahan-modifier-option-ditambah`' --label 'backend,eng,r1.1' --assignee 'akmal'
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
