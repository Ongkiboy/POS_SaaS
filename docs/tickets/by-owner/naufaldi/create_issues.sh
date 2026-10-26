#!/usr/bin/env bash
set -euo pipefail
# Butuh: gh auth login. Jalankan dari dalam repo.

gh issue create --title '[SETUP-006] Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog' --body '**Context**
Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog. Rujukan: TD §3; ADR-001.

**Acceptance Criteria**
- [ ] API client otomatis mengambil `/sanctum/csrf-cookie` dan menyertakan `X-Outlet-Id`
- [ ] 401 mengarahkan ke login; 422 memetakan `details` ke field form

**Depends on:** SETUP-001
**References:** TD §3; ADR-001
**Estimate:** M
**Branch:** `feature/setup-006-skeleton-frontend-vue-3-vite-ts-vue-rout`' --label 'setup,core' --assignee 'naufaldi'
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
gh issue create --title '[DB-SHF-001] Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet)' --body '**Context**
Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet). Rujukan: ERD §3.4; BR-002.

**Acceptance Criteria**
- [ ] Shift OPEN kedua milik kasir yang sama di outlet sama ditolak DB
- [ ] cash_movements append-only (trigger)

**Depends on:** DB-TEN-002
**References:** ERD §3.4; BR-002
**Estimate:** S
**Branch:** `feature/db-shf-001-migrasi-shifts-cash-movements-unique-par`' --label 'database,shf,core' --assignee 'naufaldi'
gh issue create --title '[BE-USER-002] CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk)' --body '**Context**
CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk). Rujukan: FR-BIZ-002, FR-BIZ-003; UC-BIZ-02.

**Acceptance Criteria**
- [ ] Manager hanya boleh ubah pajak, service, struk; rounding_increment hanya Owner
- [ ] Kode outlet tidak bisa diubah setelah ada order; perubahan setting tercatat audit before/after

**Depends on:** BE-RBAC-001, BE-AUD-001
**References:** FR-BIZ-002, FR-BIZ-003; UC-BIZ-02
**Estimate:** M
**Branch:** `feature/be-user-002-crud-outlet-pengaturan-pajak-service-min`' --label 'backend,user,core' --assignee 'naufaldi'
gh issue create --title '[FE-AUTH-001] Halaman Login' --body '**Context**
Halaman Login. Rujukan: UC-AUTH-02; SCR-AUTH-01.

**Acceptance Criteria**
- [ ] Pesan error mengikuti SRS dan tidak membocorkan email atau password yang salah
- [ ] Akun terkunci menampilkan pesan 423 dan state loading/error ada

**Depends on:** BE-AUTH-002, SETUP-006
**References:** UC-AUTH-02; SCR-AUTH-01
**Estimate:** M
**Branch:** `feature/fe-auth-001-halaman-login`' --label 'frontend,auth,core' --assignee 'naufaldi'
gh issue create --title '[FE-AUTH-003] Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher' --body '**Context**
Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher. Rujukan: FR-AUTH-004; ADR-001.

**Acceptance Criteria**
- [ ] Halaman terproteksi mengarahkan ke login bila 401
- [ ] v-can menyembunyikan UI sesuai permission dan scope; backend tetap menegakkan

**Depends on:** FE-AUTH-001, BE-AUTH-004
**References:** FR-AUTH-004; ADR-001
**Estimate:** M
**Branch:** `feature/fe-auth-003-auth-store-pinia-route-guard-vue-router`' --label 'frontend,auth,core' --assignee 'naufaldi'
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
gh issue create --title '[BE-SHF-001] Open shift (opening cash, business_date menurut timezone outlet)' --body '**Context**
Open shift (opening cash, business_date menurut timezone outlet). Rujukan: FR-SHF-001; UC-SHF-01; SEQ-SHF-01.

**Acceptance Criteria**
- [ ] Sudah ada shift OPEN 409 SHIFT_ALREADY_OPEN
- [ ] Opening cash negatif 422; audit SHIFT_OPENED

**Depends on:** DB-SHF-001, BE-RBAC-001
**References:** FR-SHF-001; UC-SHF-01; SEQ-SHF-01
**Estimate:** M
**Branch:** `feature/be-shf-001-open-shift-opening-cash-business-date-me`' --label 'backend,shf,core' --assignee 'naufaldi'
gh issue create --title '[BE-SHF-005] GET /shifts/current + open_hours_exceeded + middleware shift.open' --body '**Context**
GET /shifts/current + open_hours_exceeded + middleware shift.open. Rujukan: FR-SHF-004; BR-026.

**Acceptance Criteria**
- [ ] open_hours_exceeded true bila terbuka > 24 jam
- [ ] Order tanpa shift OPEN ditolak 409 NO_OPEN_SHIFT

**Depends on:** BE-SHF-001
**References:** FR-SHF-004; BR-026
**Estimate:** S
**Branch:** `feature/be-shf-005-get-shifts-current-open-hours-exceeded-m`' --label 'backend,shf,core' --assignee 'naufaldi'
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
gh issue create --title '[BE-USER-001] CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit' --body '**Context**
CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit. Rujukan: FR-USER-001; UC-USER-01; BR-024.

**Acceptance Criteria**
- [ ] Manager yang memilih role di luar Cashier/Inventory/Kitchen mendapat 403
- [ ] Owner terakhir tidak bisa dinonaktifkan atau diganti role

**Depends on:** BE-RBAC-001, BE-AUD-001
**References:** FR-USER-001; UC-USER-01; BR-024
**Estimate:** M
**Branch:** `feature/be-user-001-crud-user-assign-role-outlet-nonaktifkan`' --label 'backend,user,core' --assignee 'naufaldi'
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
gh issue create --title '[FE-SHF-001] Dialog open shift (+ banner > 24 jam)' --body '**Context**
Dialog open shift (+ banner > 24 jam). Rujukan: SCR-SHF-01.

**Acceptance Criteria**
- [ ] Banner peringatan tampil saat open_hours_exceeded
- [ ] Tidak bisa masuk layar POS tanpa shift OPEN

**Depends on:** BE-SHF-005, FE-AUTH-003
**References:** SCR-SHF-01
**Estimate:** S
**Branch:** `feature/fe-shf-001-dialog-open-shift-banner-24-jam`' --label 'frontend,shf,core' --assignee 'naufaldi'
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
gh issue create --title '[BE-RPT-001] Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund)' --body '**Context**
Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund). Rujukan: FR-RPT-001; UC-RPT-01.

**Acceptance Criteria**
- [ ] Angka cocok dengan fixture; REFUNDED dipisah; pembulatan baris terpisah
- [ ] Periode memakai business_date; outlet di luar akses 403

**Depends on:** BE-PAY-002, BE-ORD-007
**References:** FR-RPT-001; UC-RPT-01
**Estimate:** M
**Branch:** `feature/be-rpt-001-laporan-sales-gross-diskon-service-pajak`' --label 'backend,rpt,core' --assignee 'naufaldi'
gh issue create --title '[FE-USER-001] Halaman manajemen user (list, form, role, outlet)' --body '**Context**
Halaman manajemen user (list, form, role, outlet). Rujukan: UC-USER-01; SCR-USER-01.

**Acceptance Criteria**
- [ ] Dropdown role hanya menampilkan role yang boleh dipilih pelaku (GET /roles)
- [ ] State empty, loading, error tersedia

**Depends on:** BE-USER-001, FE-AUTH-003
**References:** UC-USER-01; SCR-USER-01
**Estimate:** M
**Branch:** `feature/fe-user-001-halaman-manajemen-user-list-form-role-ou`' --label 'frontend,user,core' --assignee 'naufaldi'
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
gh issue create --title '[BE-SHF-002] Cash in / cash out (alasan wajib, cash.manage)' --body '**Context**
Cash in / cash out (alasan wajib, cash.manage). Rujukan: FR-SHF-002; UC-SHF-02.

**Acceptance Criteria**
- [ ] Alasan kosong 422; tanpa shift OPEN 409
- [ ] Tercatat append-only dan masuk audit

**Depends on:** BE-SHF-001
**References:** FR-SHF-002; UC-SHF-02
**Estimate:** S
**Branch:** `feature/be-shf-002-cash-in-cash-out-alasan-wajib-cash-manag`' --label 'backend,shf,core' --assignee 'naufaldi'
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
gh issue create --title '[BE-PUR-004] GET daftar & detail purchase (filter status, supplier, tanggal)' --body '**Context**
GET daftar & detail purchase (filter status, supplier, tanggal). Rujukan: FR-PUR-004; UC-PUR-04.

**Acceptance Criteria**
- [ ] Filter dan paginasi bekerja; data tenant lain 404
- [ ] Permission purchases.view

**Depends on:** BE-PUR-002
**References:** FR-PUR-004; UC-PUR-04
**Estimate:** S
**Branch:** `feature/be-pur-004-get-daftar-detail-purchase-filter-status`' --label 'backend,pur,core' --assignee 'naufaldi'
gh issue create --title '[FE-PUR-003] Alur terima barang (konfirmasi, hasil stok baru)' --body '**Context**
Alur terima barang (konfirmasi, hasil stok baru). Rujukan: SCR-PUR-03.

**Acceptance Criteria**
- [ ] Konfirmasi menampilkan ringkasan stok sebelum dan sesudah
- [ ] Klik ganda aman

**Depends on:** BE-PUR-003, FE-PUR-002
**References:** SCR-PUR-03
**Estimate:** M
**Branch:** `feature/fe-pur-003-alur-terima-barang-konfirmasi-hasil-stok`' --label 'frontend,pur,core' --assignee 'naufaldi'
gh issue create --title '[BE-RPT-003] Laporan payment per metode' --body '**Context**
Laporan payment per metode. Rujukan: FR-RPT-003.

**Acceptance Criteria**
- [ ] Total Cash dan QRIS cocok dengan payments
- [ ] Cashier hanya ringkasan miliknya

**Depends on:** BE-RPT-001
**References:** FR-RPT-003
**Estimate:** S
**Branch:** `feature/be-rpt-003-laporan-payment-per-metode`' --label 'backend,rpt,core' --assignee 'naufaldi'
gh issue create --title '[BE-AUTH-003] GET/PUT /me dan PUT /me/password' --body '**Context**
GET/PUT /me dan PUT /me/password. Rujukan: FR-AUTH-003; UC-AUTH-03.

**Acceptance Criteria**
- [ ] Password lama salah mengembalikan 422 "Password lama tidak sesuai."
- [ ] Ganti password mengakhiri sesi lain milik user

**Depends on:** BE-AUTH-002
**References:** FR-AUTH-003; UC-AUTH-03
**Estimate:** S
**Branch:** `feature/be-auth-003-get-put-me-dan-put-me-password`' --label 'backend,auth,core' --assignee 'naufaldi'
gh issue create --title '[BE-USER-003] GET/PUT /business (profil + license tanpa enforcement)' --body '**Context**
GET/PUT /business (profil + license tanpa enforcement). Rujukan: FR-BIZ-001; UC-BIZ-01.

**Acceptance Criteria**
- [ ] Hanya Owner yang dapat mengubah; perubahan tercatat audit
- [ ] license_plan dan license_valid_until tersimpan tanpa memblokir fitur

**Depends on:** BE-RBAC-001
**References:** FR-BIZ-001; UC-BIZ-01
**Estimate:** XS
**Branch:** `feature/be-user-003-get-put-business-profil-license-tanpa-en`' --label 'backend,user,core' --assignee 'naufaldi'
gh issue create --title '[FE-AUTH-002] Halaman Register Business' --body '**Context**
Halaman Register Business. Rujukan: UC-AUTH-01; SCR-AUTH-02.

**Acceptance Criteria**
- [ ] Validasi per field menampilkan pesan SRS
- [ ] Sukses mengarahkan ke login/onboarding

**Depends on:** BE-AUTH-001, SETUP-006
**References:** UC-AUTH-01; SCR-AUTH-02
**Estimate:** S
**Branch:** `feature/fe-auth-002-halaman-register-business`' --label 'frontend,auth,core' --assignee 'naufaldi'
gh issue create --title '[FE-USER-002] Halaman pengaturan outlet dan profil business' --body '**Context**
Halaman pengaturan outlet dan profil business. Rujukan: UC-BIZ-02; SCR-BIZ-01.

**Acceptance Criteria**
- [ ] Field di luar hak Manager tampil nonaktif
- [ ] Tarif di luar 0–100 menampilkan pesan SRS

**Depends on:** BE-USER-002, BE-USER-003, FE-AUTH-003
**References:** UC-BIZ-02; SCR-BIZ-01
**Estimate:** S
**Branch:** `feature/fe-user-002-halaman-pengaturan-outlet-dan-profil-bus`' --label 'frontend,user,core' --assignee 'naufaldi'
gh issue create --title '[BE-CAT-003] Audit perubahan harga + guard produk INACTIVE + soft delete' --body '**Context**
Audit perubahan harga + guard produk INACTIVE + soft delete. Rujukan: FR-CAT-002; BR-018, BR-029.

**Acceptance Criteria**
- [ ] Perubahan selling_price menulis PRODUCT_PRICE_CHANGED (before/after)
- [ ] Produk yang pernah dipakai transaksi tidak bisa dihapus permanen

**Depends on:** BE-CAT-002, BE-AUD-001
**References:** FR-CAT-002; BR-018, BR-029
**Estimate:** S
**Branch:** `feature/be-cat-003-audit-perubahan-harga-guard-produk-inact`' --label 'backend,cat,core' --assignee 'naufaldi'
gh issue create --title '[FE-CAT-001] Halaman kategori' --body '**Context**
Halaman kategori. Rujukan: SCR-CAT-01.

**Acceptance Criteria**
- [ ] CRUD kategori jalan dengan state loading/empty/error
- [ ] Aksi dibatasi v-can products.*

**Depends on:** BE-CAT-001, FE-AUTH-003
**References:** SCR-CAT-01
**Estimate:** S
**Branch:** `feature/fe-cat-001-halaman-kategori`' --label 'frontend,cat,core' --assignee 'naufaldi'
gh issue create --title '[FE-SHF-002] Dialog cash in / cash out' --body '**Context**
Dialog cash in / cash out. Rujukan: SCR-SHF-02.

**Acceptance Criteria**
- [ ] Alasan wajib divalidasi
- [ ] Saldo ringkasan ter-refresh setelah simpan

**Depends on:** BE-SHF-002
**References:** SCR-SHF-02
**Estimate:** S
**Branch:** `feature/fe-shf-002-dialog-cash-in-cash-out`' --label 'frontend,shf,core' --assignee 'naufaldi'
gh issue create --title '[TEST-PUR-001] Test purchasing: konversi, last_cost, idempotensi, permission' --body '**Context**
Test purchasing: konversi, last_cost, idempotensi, permission. Rujukan: FR-PUR-001…004.

**Acceptance Criteria**
- [ ] Semua BR-045…050 punya test
- [ ] balance = SUM(movements) tetap benar setelah receive

**Depends on:** BE-PUR-003
**References:** FR-PUR-001…004
**Estimate:** M
**Branch:** `feature/test-pur-001-test-purchasing-konversi-last-cost-idemp`' --label 'testing,pur,core' --assignee 'naufaldi'
gh issue create --title '[FE-RPT-001] Halaman laporan sales & payment' --body '**Context**
Halaman laporan sales & payment. Rujukan: SCR-RPT-01.

**Acceptance Criteria**
- [ ] Filter tanggal/outlet/kasir/metode
- [ ] State empty dan loading ada

**Depends on:** BE-RPT-003, FE-AUTH-003
**References:** SCR-RPT-01
**Estimate:** M
**Branch:** `feature/fe-rpt-001-halaman-laporan-sales-payment`' --label 'frontend,rpt,core' --assignee 'naufaldi'
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
