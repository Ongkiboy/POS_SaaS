# Modul BIZ — Business, Outlet, Pengaturan

> Sumber kebenaran tetap `docs/` v3.0; file ini hanya **kumpulan per modul** supaya developer cukup membuka satu folder. Bila ada beda, `docs/` menang.

| | |
|---|---|
| Owner | Naufaldi (3 tiket) |
| Tiket | 3 core (1.75 hari) + 0 rilis 1.1 (0.00 hari) |

## 1. Ringkasan buat developer

Profil business, daftar outlet, dan pengaturan per outlet (pajak, service charge, stok minus, batas diskon kasir, pembulatan, teks struk). Pengaturan ini dibaca oleh POS dan Inventory.

**Hati-hati**
- Manager hanya boleh ubah pajak, service, struk; `rounding_increment` hanya Owner.
- Kode outlet tidak boleh berubah setelah ada order.
- Perubahan setting wajib audit before/after.

**Dianggap selesai kalau:** Owner/Manager dapat mengelola outlet dan setting sesuai hak; setting terbaca oleh `PriceCalculator`.

## 2. Kebutuhan Fungsional (dari SRS v3.0)

### Modul BIZ — Business, Outlet, Pengaturan

#### FR-BIZ-001: Profil Business & License
**Input:** nama, alamat (opsional), `license_plan` (teks), `license_valid_until` (tanggal, opsional).
**Aturan:** hanya Owner. License hanya disimpan dan ditampilkan, **tanpa enforcement** (tidak memblokir fitur). Perubahan tercatat audit.

#### FR-BIZ-002: CRUD Outlet
**Input:** kode (unik per business, tidak berubah setelah ada order), nama, alamat, timezone, status.
**Aturan:** `outlets.manage` (Owner). Outlet yang pernah punya transaksi tidak dihapus permanen, hanya INACTIVE (BR-018). Outlet INACTIVE tidak bisa dipilih untuk shift baru.
| Kondisi | Pesan Error |
|---------|-------------|
| Kode outlet sudah dipakai | "Kode outlet sudah digunakan." |
| Ubah kode outlet yang sudah punya order | "Kode outlet tidak dapat diubah karena sudah ada transaksi." |

#### FR-BIZ-003: Pengaturan Outlet
**Input:** `tax_rate` % (0–100, maks 2 desimal), `service_charge_rate` % (0–100, maks 2 desimal), `allow_negative_stock` (bool, default `true`), `cashier_max_discount_percent` (0–100, default 10), `rounding_increment` (0, 100, 500, atau 1000; default 100; 0 = nonaktif; hanya Owner), `receipt_header`, `receipt_footer` (≤ 255).
**Aturan:** Owner dapat mengubah semua. Manager hanya `tax_rate`, `service_charge_rate`, `receipt_*` di outlet yang ia akses (BR-024). Perubahan pajak/service/`allow_negative_stock`/`rounding_increment` tercatat audit dengan before/after. Perubahan tidak mengubah order lama (snapshot tarif di order).
| Kondisi | Pesan Error |
|---------|-------------|
| Tarif di luar 0–100 | "Tarif harus antara 0 dan 100." |
| Increment bukan 0/100/500/1000 | "Pembulatan harus 0, 100, 500, atau 1000." |
| Manager mengubah field di luar haknya | `403` |

#### FR-BIZ-004: Konfigurasi Payment Provider Midtrans [P2]
**Input:** `environment` (`SANDBOX`/`PRODUCTION`), `merchant_id`, `client_key`, `server_key` (write-only), outlet (opsional; kosong = default business, terisi = override outlet), `enabled_payment_types` (default `["qris"]`), `expiry_minutes` (1–1440, default 15), `is_active`.
**Aturan:** hanya `payment_config.manage` (Owner) (BR-037). Server key disimpan terenkripsi; respons hanya memuat 4 karakter terakhir. Konfigurasi outlet menimpa konfigurasi business. Beralih `SANDBOX → PRODUCTION` butuh konfirmasi eksplisit. Audit `GATEWAY_CONFIG_CHANGED` tanpa nilai key. Tanpa konfigurasi aktif, opsi Midtrans tidak muncul di POS.
| Kondisi | Pesan Error |
|---------|-------------|
| Server key kosong saat pertama kali menyimpan | "Server key wajib diisi." |
| `expiry_minutes` di luar 1–1440 | "Masa berlaku harus antara 1 dan 1440 menit." |
| Tipe pembayaran tidak didukung sistem | "Tipe pembayaran tidak tersedia." |
| Selain Owner | `403` |

---

## 3. Aturan Bisnis terkait

| ID | Aturan | Konsekuensi bila dilanggar |
|----|--------|---------------------------|
| BR-018 | Entitas yang pernah dipakai transaksi tidak dihapus permanen: INACTIVE atau soft delete | Laporan historis rusak |
| BR-024 | Manager hanya mengelola Cashier/Inventory/Kitchen di outlet yang ia akses, dan hanya mengubah pajak, service, struk | `403` |
| BR-031 | SKU produk dan ingredient unik per business; kode outlet unik per business | `422` |
| BR-036 | Pembulatan total: ke kelipatan `rounding_increment` outlet (0/100/500/1000), half-up, selisih boleh negatif, berlaku semua metode pembayaran; total > 0 tidak boleh menjadi 0; selisih disimpan di `orders.rounding_amount`; total setelah pembulatan yang dibayar, di-refund, dan dilaporkan | Kas dan struk tidak sinkron |
| BR-037 | [P2] Provider gateway = Midtrans. Kredensial per business, override per outlet; server key terenkripsi; hanya Owner (`payment_config.manage`) yang mengelola | Kebocoran kredensial |

## 4. Use Case

| ID | Nama | Aktor | Prioritas | FR | Proses |
|----|------|-------|-----------|----|--------|
| UC-BIZ-01 | Kelola Business & Outlet | Owner | Must | FR-BIZ-001, 002 | |
| UC-BIZ-02 | Atur Pengaturan Outlet | Owner, Manager | Must | FR-BIZ-003 | |

**Spesifikasi ringkas**

| ID | Prakondisi | Alur utama | Pascakondisi sukses | Alternatif / gagal | FR / BR |
|----|-----------|------------|---------------------|--------------------|---------|
| UC-BIZ-01 | Owner | Ubah profil/license; tambah, ubah, nonaktifkan outlet | Tersimpan + audit | Kode outlet ganda atau diubah setelah ada order → `422`; outlet bertransaksi tidak dihapus | FR-BIZ-001, 002 / BR-018, 031 |
| UC-BIZ-02 | Owner/Manager | Ubah pajak, service, stok minus, batas diskon, pembulatan, struk | Tersimpan; berlaku order baru; audit `SETTING_CHANGED` | Tarif di luar 0–100 → `422`; Manager mengubah field di luar haknya → `403` | FR-BIZ-003 / BR-024 |
| UC-BIZ-03 | Owner | Akun Midtrans milik business | Isi server key, client key, mode; simpan (write-only, terenkripsi) | Provider aktif; audit tanpa nilai kunci | Kunci tidak valid → `422` | FR-BIZ-004 / BR-037 |

## 5. Tabel Database (ERD v3.0)

**`businesses`**
| Kolom | Tipe | Null | Constraint | Keterangan |
|-------|------|------|-----------|------------|
| name | V(100) | NN | | |
| slug | V(120) | NN | UNIQUE | |
| status | V(20) | NN | CHECK `ACTIVE`,`INACTIVE` | |
| address | V(255) | N | | |
| license_plan | V(50) | N | | Tanpa enforcement |
| license_valid_until | DATE | N | | Tanpa enforcement |

**`outlets`**
| Kolom | Tipe | Null | Constraint | Keterangan |
|-------|------|------|-----------|------------|
| code | V(6) | NN | UNIQUE(business_id, code) | Dipakai nomor order; tidak berubah setelah ada order |
| name | V(100) | NN | | |
| address | V(255) | N | | |
| timezone | V(50) | NN | DEFAULT `Asia/Jakarta` | |
| tax_rate | DEC(5,2) | NN | DEFAULT 0, CHECK 0–100 | Exclusive |
| service_charge_rate | DEC(5,2) | NN | DEFAULT 0, CHECK 0–100 | |
| allow_negative_stock | BOOLEAN | NN | DEFAULT true | BR-011 |
| cashier_max_discount_percent | DEC(5,2) | NN | DEFAULT 10, CHECK 0–100 | |
| rounding_increment | INT | NN | DEFAULT 100, CHECK IN (0,100,500,1000) | BR-036 |
| receipt_header, receipt_footer | V(255) | N | | |
| status | V(20) | NN | CHECK | |
| deleted_at | TIMESTAMP | N | | |

## 6. Behavior (Sequence / State / Activity)

Tidak ada diagram khusus; alur sederhana (CRUD).

## 7. Endpoint (SDD v3.0 §4)

| ID | Method | Endpoint | Permission | UC | FR | Deskripsi |
|----|--------|----------|-----------|----|----|-----------|
| API-BIZ-01 | GET | `/business` | `settings.view` | UC-BIZ-01 | FR-BIZ-001 | Profil + license |
| API-BIZ-02 | PUT | `/business` | `settings.update` (Owner) | UC-BIZ-01 | FR-BIZ-001 | Ubah profil + license |
| API-BIZ-03 | GET | `/outlets` | `outlets.view` | UC-BIZ-01 | FR-BIZ-002 | Daftar outlet yang diakses |
| API-BIZ-04 | POST | `/outlets` | `outlets.manage` | UC-BIZ-01 | FR-BIZ-002 | Tambah outlet |
| API-BIZ-05 | PUT | `/outlets/{id}` | `outlets.manage` | UC-BIZ-01 | FR-BIZ-002 | Ubah/nonaktifkan outlet |
| API-BIZ-06 | GET | `/outlets/{id}/settings` | `settings.view` | UC-BIZ-02 | FR-BIZ-003 | Baca pengaturan |
| API-BIZ-07 | PUT | `/outlets/{id}/settings` | `settings.update` (Manager Limited) | UC-BIZ-02 | FR-BIZ-003 | Ubah pengaturan |

## 8. Tiket

Detail AC lengkap ada di `tickets/tickets.md` (ID sama).

| ID | Task | Est | Owner | Sprint | Depends on | Test |
|----|------|-----|-------|--------|-----------|------|
| BE-USER-002 | CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk) | M | Naufaldi | S2 | BE-RBAC-001, BE-AUD-001 | TC-USER-003, TC-USER-004, TC-USER-005 |
| BE-USER-003 | GET/PUT /business (profil + license tanpa enforcement) | XS | Naufaldi | S5 | BE-RBAC-001 | TC-USER-003 |
| FE-USER-002 | Halaman pengaturan outlet dan profil business | S | Naufaldi | S5 | BE-USER-002, BE-USER-003, FE-AUTH-003 | TC-USER-003 |

**Acceptance Criteria ringkas**

- **BE-USER-002** 
  - [ ] Manager hanya boleh ubah pajak, service, struk; rounding_increment hanya Owner
  - [ ] Kode outlet tidak bisa diubah setelah ada order; perubahan setting tercatat audit before/after
- **BE-USER-003** 
  - [ ] Hanya Owner yang dapat mengubah; perubahan tercatat audit
  - [ ] license_plan dan license_valid_until tersimpan tanpa memblokir fitur
- **FE-USER-002** 
  - [ ] Field di luar hak Manager tampil nonaktif
  - [ ] Tarif di luar 0–100 menampilkan pesan SRS

## 9. Test Case (Test Plan v3.0)

| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-USER-003 | Pengaturan outlet: Manager hanya pajak/service/struk; tarif 0–100; license tersimpan | FR-BIZ-001; FR-BIZ-003 | Feature | Core |
| TC-USER-004 | rounding_increment hanya Owner dan hanya {0,100,500,1000} | FR-BIZ-003; BR-036 | Feature | Core |
| TC-USER-005 | Kode outlet tidak berubah setelah ada order; outlet bertransaksi tidak dihapus | FR-BIZ-002; BR-031 | Feature | Core |
