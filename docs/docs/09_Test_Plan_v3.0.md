# Test Plan — F&B POS & Inventory
> **Versi:** 3.0 | **Tanggal:** 2026-10-25 | **Tier:** Standard + Full untuk pembayaran, stok, shift, tenant
> Test case diturunkan dari spesifikasi (SRS, UC, STM), bukan dari kode. Total **111 TC** (100 core, 11 R1.1).

## 1. Strategi dan Level
| Level | Alat | Cakupan | Dijalankan |
|-------|------|---------|------------|
| Unit | Pest | `PriceCalculator`, `StockService`, `Money/Quantity`, HPP | Tiap commit (CI) |
| Feature | Pest + PostgreSQL | Endpoint, validasi tabel SRS, permission, tenant | Tiap PR (CI) |
| Integration | Pest | Sale → Payment → Recipe → Movement → Balance; konkurensi | Tiap PR untuk modul engine/payment |
| E2E | Playwright | Alur utama di staging | Harian + sebelum rilis |
| Manual/UAT | Checklist | PWA, printer, UX kasir | Sebelum rilis (21–24 Des) |
| Performa | Skrip load sederhana | 20 pembayaran paralel | Sebelum rilis |
Aturan: tiap FR Must punya ≥ 1 TC; tiap BR punya ≥ 1 TC; tiap baris tabel validasi SRS = 1 asersi; tiap role pada tiap endpoint diuji (matriks); setiap endpoint baru masuk dataset isolasi tenant.

## 2. Lingkungan dan Data
- CI: PostgreSQL service container (bukan SQLite, karena partial unique index, trigger, `FOR UPDATE`).
- Factory untuk business/outlet/user per role; fixture bersama `price-vectors.json` (BE dan FE) dan `report-fixture.sql` (laporan).
- Staging memakai project Supabase terpisah dan `DemoSeeder`.

## 3. Daftar Test Case

### Autentikasi (AUTH)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-AUTH-001 | Register business sukses: business, outlet, Owner, 5 role ter-seed dalam 1 transaksi | FR-AUTH-001 | Feature | Core |
| TC-AUTH-002 | Email sudah terdaftar → 422 "Email sudah terdaftar." | FR-AUTH-001; BR-022 | Feature | Core |
| TC-AUTH-003 | Password lemah / konfirmasi tidak cocok → 422 per field | FR-AUTH-001 | Feature | Core |
| TC-AUTH-004 | Login sukses + audit LOGIN; pesan error tidak membocorkan email/password | FR-AUTH-002 | Feature | Core |
| TC-AUTH-005 | 5 kali gagal → 423 ACCOUNT_LOCKED 15 menit; sukses setelah kedaluwarsa | FR-AUTH-002; BR-023 | Feature | Core |
| TC-AUTH-006 | Akun INACTIVE 403; email tak dikenal tercatat LOGIN_FAILED user_id NULL | FR-AUTH-002 | Feature | Core |
| TC-AUTH-007 | /me memuat role, outlet, permission dengan scope | FR-AUTH-004 | Feature | Core |
| TC-AUTH-008 | X-Outlet-Id di luar akses → 403 | FR-AUTH-004; BR-024 | Feature | Core |
| TC-AUTH-009 | Ganti password: lama salah 422; sukses mengakhiri sesi lain | FR-AUTH-003 | Feature | Core |

### Isolasi Tenant (TEN)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-TEN-001 | Isolasi tenant: tiap endpoint terdaftar mengembalikan 404 untuk data business lain (dataset-driven) | NFR-SEC-001; ADR-003 | Feature | Core |
| TC-TEN-002 | Arch test: query model tenant tanpa scope / DB::table tanpa helper gagal | NFR-SEC-001 | Unit | Core |
| TC-TEN-003 | UNIQUE komposit per business (SKU, kode outlet, nama) benar di DB | BR-022; BR-031 | Unit | Core |

### RBAC (RBAC)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-RBAC-001 | Matriks role × endpoint sesuai SRS §2.1 (ijin dan tolak) | FR-USER-002 | Feature | Core |
| TC-RBAC-002 | Cashier: Own/Limited (order sendiri, cancel milik sendiri) | FR-USER-002; BR-025 | Feature | Core |
| TC-RBAC-003 | Manager hanya mengelola Cashier/Inventory/Kitchen; tidak bisa ubah Owner | FR-USER-001; BR-024 | Feature | Core |
| TC-RBAC-004 | Kitchen/Inventory tanpa HPP dan nilai biaya di respons manapun | BR-032; BR-034 | Feature | Core |

### User, Outlet, Business (USER)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-USER-001 | CRUD user: role/outlet di luar hak Manager → 403; nonaktif mengakhiri sesi | FR-USER-001 | Feature | Core |
| TC-USER-002 | Owner terakhir tidak bisa dinonaktifkan/diganti role | FR-USER-001 | Feature | Core |
| TC-USER-003 | Pengaturan outlet: Manager hanya pajak/service/struk; tarif 0–100; license tersimpan | FR-BIZ-001; FR-BIZ-003 | Feature | Core |
| TC-USER-004 | rounding_increment hanya Owner dan hanya {0,100,500,1000} | FR-BIZ-003; BR-036 | Feature | Core |
| TC-USER-005 | Kode outlet tidak berubah setelah ada order; outlet bertransaksi tidak dihapus | FR-BIZ-002; BR-031 | Feature | Core |

### Audit (AUD)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-AUD-001 | Semua aksi wajib FR-AUD-001 tercatat dengan before/after dan after-commit | FR-AUD-001 | Feature | Core |
| TC-AUD-002 | UPDATE/DELETE audit_logs ditolak trigger DB | BR-020; ADR-007 | Unit | Core |
| TC-AUD-003 | Filter audit log tanggal/user/aksi/resource; Manager hanya outlet miliknya | FR-AUD-002 | Feature | R1.1 |

### Katalog (CAT)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-CAT-001 | SKU produk ganda 422; harga non-bulat/negatif 422 | FR-CAT-002 | Feature | Core |
| TC-CAT-002 | Nama kategori unik; INACTIVE menyembunyikan produk dari POS | FR-CAT-001 | Feature | Core |
| TC-CAT-003 | Modifier: min>max 422; SINGLE satu pilihan; option INACTIVE tak bisa dipilih | FR-CAT-003; BR-030 | Feature | Core |
| TC-CAT-004 | Ubah harga menulis PRODUCT_PRICE_CHANGED; produk bertransaksi tidak dihapus permanen | FR-CAT-002; BR-018; BR-029 | Feature | Core |
| TC-CAT-005 | Cache /pos/catalog dibatalkan saat katalog berubah; tanpa HPP | FR-CAT-004 | Feature | Core |
| TC-CAT-006 | Upload gambar >2 MB atau format salah → 422 | FR-CAT-002 | Feature | Core |

### Ingredient (ING)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-ING-001 | SKU ingredient ganda 422; unit & konversi ter-seed | FR-ING-001 | Feature | Core |
| TC-ING-002 | Base unit terkunci setelah ada movement | FR-ING-001; BR-015 | Feature | Core |
| TC-ING-003 | last_cost negatif ditolak; perubahan tak mengubah movement/order lama | FR-ING-001; BR-014 | Feature | Core |

### Recipe (REC)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-REC-001 | Simpan recipe = versi baru, hanya 1 aktif (termasuk constraint DB) | FR-REC-001; BR-013 | Feature | Core |
| TC-REC-002 | Qty ≤ 0, bahan ganda, recipe kosong → 422 | FR-REC-001 | Feature | Core |
| TC-REC-003 | HPP half-up; produk tanpa recipe HPP 0 + penanda no recipe | FR-REC-003; BR-010; BR-014 | Unit | Core |
| TC-REC-004 | Inventory/Kitchen melihat recipe tanpa HPP; tanpa recipes.manage tak bisa ubah | FR-REC-001; BR-032 | Feature | Core |
| TC-REC-005 | Recipe modifier option: qty > 0, audit, permission | FR-REC-002 | Feature | R1.1 |

### Inventory (INV)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-INV-001 | StockService idempoten: referensi sama tidak membuat movement baru | NFR-DATA-003; BR-009 | Unit | Core |
| TC-INV-002 | Stok kurang + minus dilarang → InsufficientStock; minus diizinkan → saldo negatif | FR-INV-003; BR-011 | Unit | Core |
| TC-INV-003 | 20 apply paralel: balance = SUM(movements), tanpa deadlock | NFR-DATA-001 | Unit | Core |
| TC-INV-004 | Opening stock: 1 batch = 1 movement OPENING per bahan; qty ≤ 0 ditolak | FR-INV-001 | Feature | Core |
| TC-INV-005 | Adjustment: alasan wajib; minus dilarang → 422 | FR-INV-003; BR-016 | Feature | Core |
| TC-INV-006 | Waste: alasan OTHER tanpa catatan 422; total_cost snapshot | FR-INV-004; BR-034 | Feature | Core |
| TC-INV-007 | Kitchen tidak melihat total_cost/biaya | FR-INV-004; BR-034 | Feature | Core |
| TC-INV-008 | Low stock memakai min_stock override outlet; stok minus terpisah | FR-INV-002; FR-INV-005 | Feature | Core |
| TC-INV-009 | Opname: selisih terhadap snapshot, penjualan selama opname tidak hilang | FR-INV-006; BR-017 | Feature | R1.1 |
| TC-INV-010 | Hanya 1 opname IN_PROGRESS per outlet (409); hitungan negatif 422 | FR-INV-006 | Feature | R1.1 |
| TC-INV-011 | UPDATE/DELETE stock_movements ditolak trigger DB | BR-020; ADR-007 | Unit | Core |

### Shift & Kas (SHF)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-SHF-001 | Open shift ganda 409 SHIFT_ALREADY_OPEN; kas awal negatif 422 | FR-SHF-001; BR-002 | Feature | Core |
| TC-SHF-002 | Cash in/out: alasan wajib; tanpa shift OPEN 409 | FR-SHF-002 | Feature | Core |
| TC-SHF-003 | Expected Cash = opening + cash sales − kembalian + in − out − cash refund | FR-SHF-003; BR-004 | Unit | Core |
| TC-SHF-004 | Close shift ditolak bila ada order DRAFT/PENDING (409 + jumlah) | FR-SHF-003; BR-003 | Feature | Core |
| TC-SHF-005 | Close sukses: expected, actual, difference tersimpan; shift CLOSED tak menerima transaksi | FR-SHF-003 | Feature | Core |
| TC-SHF-006 | open_hours_exceeded > 24 jam; order tanpa shift OPEN 409 NO_OPEN_SHIFT | FR-SHF-004; BR-026 | Feature | Core |
| TC-SHF-007 | Pembayaran non-tunai tidak masuk Expected Cash; tampil di non_cash | BR-042 | Feature | Core |

### Order POS (ORD)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-ORD-001 | Vektor: subtotal 54.000, service 5%, PB1 10% → total 62.400 (rounding +30) | FR-ORD-002; BR-036 | Unit | Core |
| TC-ORD-002 | Diskon 10% → total 56.100 (rounding −33); batas .5 dan increment 0/100/500/1000 | FR-ORD-002; BR-005 | Unit | Core |
| TC-ORD-003 | Diskon > subtotal 422 DISCOUNT_INVALID | FR-ORD-003 | Feature | Core |
| TC-ORD-004 | Cashier melebihi batas diskon outlet 403 DISCOUNT_LIMIT_EXCEEDED | FR-ORD-003; BR-006 | Feature | Core |
| TC-ORD-005 | 50 order paralel: nomor order unik, reset harian per outlet | FR-ORD-007; BR-019 | Integration | Core |
| TC-ORD-006 | Snapshot: ubah harga/nama produk setelah order tidak mengubah order | FR-ORD-001; BR-008 | Feature | Core |
| TC-ORD-007 | State machine: transisi sah dan tidak sah (409 INVALID_STATE_TRANSITION) | FR-ORD-004; STM-ORDER | Feature | Core |
| TC-ORD-008 | Cancel: alasan wajib; order dibayar 409; Cashier hanya miliknya; riwayat sesuai role | FR-ORD-005; FR-ORD-006; BR-025 | Feature | Core |
| TC-ORD-009 | table_number hanya DINE_IN; qty 1–999; item minimal 1 | FR-ORD-001 | Feature | Core |
| TC-ORD-010 | Produk INACTIVE saat confirm 422 PRODUCT_UNAVAILABLE; reopen ditolak jika ada payment | FR-ORD-004; BR-028 | Feature | Core |
| TC-ORD-011 | Antrian dapur: tanpa harga; READY; REFUNDED hilang | FR-KIT-001; BR-033 | Feature | R1.1 |

### Pembayaran, Struk, Refund (PAY)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-PAY-001 | Tunai: kembalian benar; nominal kurang 422 | FR-PAY-001; BR-007 | Feature | Core |
| TC-PAY-002 | QRIS manual: nominal ≠ total 422; referensi opsional | FR-PAY-001 | Feature | Core |
| TC-PAY-003 | Metode CARD/GATEWAY → 422 PAYMENT_METHOD_UNAVAILABLE | FR-PAY-001 | Feature | Core |
| TC-PAY-004 | Idempotency-Key sama + order sama → replay tanpa payment/movement baru | FR-PAY-001; BR-007 | Feature | Core |
| TC-PAY-005 | Key yang sama untuk order lain → 409; header kosong 422 | FR-PAY-001 | Feature | Core |
| TC-PAY-006 | Double submit paralel → 1 payment, 1 set movement | NFR-DATA-002; NFR-DATA-003 | Integration | Core |
| TC-PAY-007 | Order bukan PENDING_PAYMENT → 409 | FR-PAY-001 | Feature | Core |
| TC-PAY-008 | Stok kurang & minus dilarang → rollback total (0 payment, 0 movement, status tetap) | FR-ENG-002; BR-011 | Integration | Core |
| TC-PAY-009 | Refund tunai masuk shift OPEN pelaku; mengurangi Expected Cash | FR-RFD-001; BR-004 | Feature | Core |
| TC-PAY-010 | Refund tunai tanpa shift OPEN pelaku → 409 NO_OPEN_SHIFT | FR-RFD-001 | Feature | Core |
| TC-PAY-011 | Refund QRIS manual: shift_id NULL | FR-RFD-001 | Feature | Core |
| TC-PAY-012 | Refund kedua 409 ORDER_ALREADY_REFUNDED; hanya Manager/Owner | FR-RFD-001; BR-012 | Feature | Core |
| TC-PAY-013 | Refund restock: movement RETURN per bahan; tanpa restock tanpa movement | FR-RFD-001; BR-012 | Feature | Core |
| TC-PAY-014 | Struk memuat baris pembulatan, metode, kembalian; cetak ulang dari data tersimpan | FR-PAY-002 | Feature | Core |

### Engine Sale→Inventory (ENG)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-ENG-001 | Agregat bahan lintas item dan qty | FR-ENG-001; BR-009 | Integration | Core |
| TC-ENG-002 | Produk tanpa recipe dilewati, tidak ada movement | FR-ENG-001; BR-010 | Integration | Core |
| TC-ENG-003 | Recipe diubah setelah jual: order lama memakai versi snapshot | FR-ENG-001; BR-013 | Integration | Core |
| TC-ENG-004 | stock_shortage terisi saat minus diizinkan; 422 details.ingredients saat dilarang | FR-ENG-002; BR-011 | Feature | Core |
| TC-ENG-005 | 2 order paralel bahan sama: tanpa deadlock, stok benar | NFR-DATA-001 | Integration | Core |
| TC-ENG-006 | stock-trace menampilkan movement SALE dan RETURN order | FR-ENG-003 | Feature | Core |
| TC-ENG-007 | Pemakaian bahan modifier option ikut dipotong saat bayar | FR-REC-002; FR-ENG-001 | Integration | R1.1 |

### Purchasing (PUR)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-PUR-001 | Supplier CRUD; nama unik; INACTIVE tak bisa dipilih | FR-PUR-001; BR-048 | Feature | Core |
| TC-PUR-002 | Purchase DRAFT: konversi kg→g dan L→ml, total, nomor PO unik; bahan ganda 422 | FR-PUR-002; BR-046; BR-049 | Feature | Core |
| TC-PUR-003 | Receive: stok bertambah per bahan, last_cost = harga ÷ faktor, status RECEIVED | FR-PUR-003; BR-045; BR-047 | Feature | Core |
| TC-PUR-004 | Receive ganda/klik ganda: tidak ada movement baru | FR-PUR-003; BR-045 | Integration | Core |
| TC-PUR-005 | Purchase RECEIVED tak bisa diubah/diterima ulang (409) | BR-048 | Feature | Core |
| TC-PUR-006 | Permission suppliers.*/purchases.* sesuai matriks; tenant lain 404 | FR-PUR-004 | Feature | Core |
| TC-PUR-007 | Cancel DRAFT dengan alasan; partial receive | FR-PUR-005 | Feature | R1.1 |

### Laporan (RPT)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-RPT-001 | Laporan sales cocok dengan fixture; payment per metode | FR-RPT-001; FR-RPT-003 | Feature | Core |
| TC-RPT-002 | COGS dan gross profit dari snapshot HPP | FR-RPT-001 | Feature | Core |
| TC-RPT-003 | REFUNDED dipisah dari net sales; pembulatan baris terpisah | FR-RPT-001 | Feature | Core |
| TC-RPT-004 | Business date menurut timezone outlet (lintas tengah malam) | FR-RPT-001; BR-027 | Feature | Core |
| TC-RPT-005 | Outlet di luar akses → 403; Cashier hanya ringkasan miliknya | FR-RPT-001 | Feature | Core |
| TC-RPT-006 | Laporan produk + daftar no recipe | FR-RPT-002 | Feature | R1.1 |
| TC-RPT-007 | Dashboard sesuai permission, timezone, cache | FR-RPT-005 | Feature | R1.1 |
| TC-RPT-008 | Laporan inventory (minus) dan waste (biaya disembunyikan untuk Kitchen) | FR-RPT-004 | Feature | R1.1 |
| TC-RPT-009 | Export CSV: filter sama dengan layar; tanpa izin 403 | FR-RPT-006 | Feature | R1.1 |

### PWA (PWA)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-PWA-001 | PWA installable (Lighthouse) | FR-PWA-001 | Manual | Core |
| TC-PWA-002 | Service worker tidak meng-cache respons API | FR-PWA-001 | Manual | Core |
| TC-PWA-003 | Offline: banner tampil, aksi tulis ditolak, retry bayar memakai key sama | FR-PWA-001 | Manual | Core |

### Keamanan (SEC)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-SEC-001 | Checklist keamanan (tenant, throttling, CORS/CSRF, mass assignment, upload) | NFR-SEC-001…007 | Manual | Core |

### End-to-End (E2E)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-E2E-001 | E2E: register → menu → recipe → shift → order → bayar → stok turun → close shift | F-01…F-10 | E2E | Core |
| TC-E2E-002 | E2E: opening stock → purchase receive → adjustment → waste | F-11; F-12; F-20 | E2E | Core |

### Performa (PERF)
| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-PERF-001 | 20 pembayaran paralel bahan sama: p95 < 800 ms, balance = SUM(movements) | NFR-PERF-003; NFR-PERF-004 | Performa | Core |

## 4. Integrasi, Konkurensi, dan E2E
**INT-ENG-001 (skenario wajib):** (1) opening stock; (2) order dengan 3 item + recipe; (3) bayar tunai; (4) cek movement `SALE` per bahan dan `balance = SUM(movements)`; (5) refund dengan restock → `RETURN`; (6) purchase receive → `PURCHASE` + `last_cost`; (7) ulangi (3) dengan retry key sama → tidak ada duplikasi.
**Konkurensi:** 20 pembayaran paralel pada bahan sama; 2 order paralel bahan sama (urutan lock `ingredient_id`); double submit bayar; receive purchase ganda; open shift ganda.
**E2E (Playwright):** `TC-E2E-001` dan `TC-E2E-002` di staging; dijalankan juga pasca-deploy sebagai smoke test.

## 5. Matriks FR → TC
| FR | TC |
|----|----|
| FR-AUD-001 | TC-AUD-001 |
| FR-AUD-002 | TC-AUD-003 |
| FR-AUTH-001 | TC-AUTH-001, TC-AUTH-002, TC-AUTH-003 |
| FR-AUTH-002 | TC-AUTH-004, TC-AUTH-005, TC-AUTH-006 |
| FR-AUTH-003 | TC-AUTH-009 |
| FR-AUTH-004 | TC-AUTH-007, TC-AUTH-008 |
| FR-BIZ-001 | TC-USER-003 |
| FR-BIZ-002 | TC-USER-005 |
| FR-BIZ-003 | TC-USER-003, TC-USER-004 |
| FR-BIZ-004 | [P2] ditulis menjelang Phase 2 |
| FR-CAT-001 | TC-CAT-002 |
| FR-CAT-002 | TC-CAT-001, TC-CAT-004, TC-CAT-006 |
| FR-CAT-003 | TC-CAT-003 |
| FR-CAT-004 | TC-CAT-005 |
| FR-ENG-001 | TC-ENG-001, TC-ENG-002, TC-ENG-003, TC-ENG-007 |
| FR-ENG-002 | TC-ENG-004, TC-PAY-008 |
| FR-ENG-003 | TC-ENG-006 |
| FR-ING-001 | TC-ING-001, TC-ING-002, TC-ING-003 |
| FR-INV-001 | TC-INV-004 |
| FR-INV-002 | TC-INV-008 |
| FR-INV-003 | TC-INV-002, TC-INV-005 |
| FR-INV-004 | TC-INV-006, TC-INV-007 |
| FR-INV-005 | TC-INV-008 |
| FR-INV-006 | TC-INV-009, TC-INV-010 |
| FR-KIT-001 | TC-ORD-011 |
| FR-ORD-001 | TC-ORD-006, TC-ORD-009 |
| FR-ORD-002 | TC-ORD-001, TC-ORD-002 |
| FR-ORD-003 | TC-ORD-003, TC-ORD-004 |
| FR-ORD-004 | TC-ORD-007, TC-ORD-010 |
| FR-ORD-005 | TC-ORD-008 |
| FR-ORD-006 | TC-ORD-008 |
| FR-ORD-007 | TC-ORD-005 |
| FR-PAY-001 | TC-PAY-001, TC-PAY-002, TC-PAY-003, TC-PAY-004, TC-PAY-005, TC-PAY-007 |
| FR-PAY-002 | TC-PAY-014 |
| FR-PAY-003 | [P2] ditulis menjelang Phase 2 |
| FR-PAY-004 | [P2] ditulis menjelang Phase 2 |
| FR-PAY-005 | [P2] ditulis menjelang Phase 2 |
| FR-PUR-001 | TC-PUR-001 |
| FR-PUR-002 | TC-PUR-002 |
| FR-PUR-003 | TC-PUR-003, TC-PUR-004 |
| FR-PUR-004 | TC-PUR-006 |
| FR-PUR-005 | TC-PUR-007 |
| FR-PWA-001 | TC-PWA-001, TC-PWA-002, TC-PWA-003 |
| FR-REC-001 | TC-REC-001, TC-REC-002, TC-REC-004 |
| FR-REC-002 | TC-ENG-007, TC-REC-005 |
| FR-REC-003 | TC-REC-003 |
| FR-RFD-001 | TC-PAY-009, TC-PAY-010, TC-PAY-011, TC-PAY-012, TC-PAY-013 |
| FR-RFD-002 | [P2] ditulis menjelang Phase 2 |
| FR-RPT-001 | TC-RPT-001, TC-RPT-002, TC-RPT-003, TC-RPT-004, TC-RPT-005 |
| FR-RPT-002 | TC-RPT-006 |
| FR-RPT-003 | TC-RPT-001 |
| FR-RPT-004 | TC-RPT-008 |
| FR-RPT-005 | TC-RPT-007 |
| FR-RPT-006 | TC-RPT-009 |
| FR-RPT-007 | [P2] ditulis menjelang Phase 2 |
| FR-SHF-001 | TC-SHF-001 |
| FR-SHF-002 | TC-SHF-002 |
| FR-SHF-003 | TC-SHF-003, TC-SHF-004, TC-SHF-005 |
| FR-SHF-004 | TC-SHF-006 |
| FR-USER-001 | TC-RBAC-003, TC-USER-001, TC-USER-002 |
| FR-USER-002 | TC-RBAC-001, TC-RBAC-002 |

**FR tanpa TC (celah, tidak ditambal diam-diam):** tidak ada.

## 6. Test NFR
| NFR | Cara uji | Kriteria |
|-----|----------|----------|
| NFR-PERF (bayar + potong stok) | `TC-PERF-001` | p95 < 800 ms (order ≤ 10 item), 0 deadlock |
| NFR-PERF (muat POS) | Lighthouse 4G cache dingin | < 3 detik |
| NFR-SEC-001 (tenant) | `TC-TEN-001` dataset-driven | 100% endpoint 404 lintas tenant |
| NFR-SEC (throttle, CORS/CSRF, upload) | `TC-SEC-001` | Semua butir lulus |
| NFR-DATA (konsistensi stok) | `TC-INV-003`, `TC-ENG-005`, `TC-PAY-006` | `balance = SUM(movements)` 100% |

## 7. Kriteria Keluar dan UAT
**Rilis core (29 Des) boleh jalan bila:** semua TC core lulus di CI/staging; `TC-E2E-001/002` hijau; `TC-SEC-001` lulus; `TC-PERF-001` lulus; tidak ada bug S1/S2 terbuka; UAT disetujui.
**Skrip UAT (21–24 Des, 1 pemilik kafe + 1 kasir):** (1) setup menu dan recipe; (2) isi stok awal dan terima 1 purchase; (3) satu shift penuh dengan ≥ 20 order campuran Cash/QRIS dan 1 refund; (4) tutup shift dan cocokkan kas; (5) cek laporan sales dan stok; (6) cetak struk di printer thermal; (7) pakai dari HP.

## 8. Changelog
| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 3.0 | 2026-10-25 | Versi pertama: 111 TC, matriks FR→TC, kriteria keluar |
