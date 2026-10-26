# UI/UX — F&B POS & Inventory (versi teks)
> **Versi:** 3.0 | **Tanggal:** 2026-10-25 | **Tier:** Lite–Standard (site map, flow, daftar layar, state). Wireframe visual (Figma) **dilewati** atas kesepakatan E2; dokumen ini cukup untuk implementasi.
> **Frontend:** Vue 3 + Vite + Vue Router + Tailwind. SPA; semua halaman terautentikasi client-rendered.

## 1. Prinsip UI
- **POS dulu, mobile dulu:** target 360 px; area sentuh ≥ 44 px; satu tangan; tombol aksi utama di bawah.
- Angka rupiah selalu `Rp 12.500` (titik ribuan, tanpa desimal); qty bahan maksimal 4 desimal dengan satuan.
- Konfirmasi untuk aksi tak terbalik (bayar, refund, terima purchase, tutup shift); tombol dinonaktifkan saat request berjalan (cegah klik ganda).
- Setiap halaman wajib punya **loading** (skeleton), **empty** (ajakan aksi), dan **error** (pesan dari server + tombol coba lagi).
- Menu dan tombol memakai `v-can`/`useCan`; menyembunyikan UI bukan pengganti izin di backend.
- Pesan error mengikuti teks di SRS (Bahasa Indonesia, tanpa istilah teknis).

## 2. Site Map dan Navigasi per Role
```
/login · /register
/pos ........................ Cashier, Manager, Owner (butuh shift OPEN)
  /pos/orders · /pos/shift/close
/menu ....................... Manager, Owner
  /menu/categories · /menu/products · /menu/modifiers
/inventory .................. Inventory, Manager, Owner (Kitchen: stok baca + waste)
  /inventory/stock · /movements · /adjustments · /waste · /ingredients · /opname (R1.1)
/purchasing ................. Inventory, Manager, Owner
  /purchasing/suppliers · /purchasing/orders
/reports · /dashboard (R1.1)  Owner, Manager (Inventory: stok; Cashier: ringkasan sendiri)
/kitchen (R1.1) ............. Kitchen, Manager, Owner
/settings ................... Owner (Manager terbatas)
  /settings/business · /settings/outlets/:id · /settings/users
/audit (R1.1)
```
| Role | Beranda setelah login |
|------|-----------------------|
| Cashier | `/pos` (bila belum ada shift OPEN: dialog Buka Shift) |
| Manager, Owner | `/reports/sales` (dashboard di R1.1) |
| Inventory | `/inventory/stock` |
| Kitchen | `/inventory/stock` (R1.1: `/kitchen`) |

## 3. Daftar Layar
| ID | Layar | Route | UC | API | Rilis |
|----|-------|-------|----|-----|-------|
| SCR-AUTH-01 | Login | `/login` | UC-AUTH-02 | POST /auth/login | core |
| SCR-AUTH-02 | Register Business | `/register` | UC-AUTH-01 | POST /auth/register-business | core |
| SCR-BIZ-01 | Pengaturan Business & Outlet | `/settings/business · /settings/outlets/:id` | UC-BIZ-01, UC-BIZ-02 | API-BIZ-01…08 | core |
| SCR-USER-01 | Manajemen User | `/settings/users` | UC-USER-01 | API-USER-*, GET /roles | core |
| SCR-AUD-01 | Audit Log | `/audit` | UC-AUD-01 | GET /audit-logs | R1.1 |
| SCR-CAT-01 | Kategori | `/menu/categories` | UC-CAT-01 | API-CAT-* | core |
| SCR-CAT-02 | Produk (form + editor recipe) | `/menu/products · /menu/products/:id` | UC-CAT-01, UC-REC-01 | API-CAT-*, API-REC-01/02 | core |
| SCR-CAT-03 | Modifier (+ recipe option R1.1) | `/menu/modifiers` | UC-CAT-02, UC-REC-02 | API-CAT-*, API-REC-03/04 | core |
| SCR-ING-01 | Ingredient | `/inventory/ingredients` | UC-ING-01 | API-ING-* | core |
| SCR-INV-01 | Stok & low stock | `/inventory/stock` | UC-INV-02 | GET /inventory/stock | core |
| SCR-INV-02 | Riwayat movement | `/inventory/movements` | UC-INV-02 | GET /inventory/movements | core |
| SCR-INV-03 | Opening stock & adjustment | `/inventory/adjustments` | UC-INV-01, UC-INV-03 | API-INV-04/05 | core |
| SCR-INV-04 | Waste | `/inventory/waste` | UC-INV-04 | API-INV-07/08 | core |
| SCR-INV-05 | Stock Opname | `/inventory/opname` | UC-INV-05 | API-INV-09…14 | R1.1 |
| SCR-SHF-01 | Dialog Buka Shift | `dialog di /pos` | UC-SHF-01 | POST /shifts/open | core |
| SCR-SHF-02 | Dialog Kas Masuk/Keluar | `dialog di /pos` | UC-SHF-02 | POST /shifts/current/cash | core |
| SCR-SHF-03 | Tutup Shift | `/pos/shift/close` | UC-SHF-03 | API-SHF-03/05 | core |
| SCR-POS-01 | Layar POS (grid + cart) | `/pos` | UC-ORD-01 | GET /pos/catalog, POST /orders | core |
| SCR-POS-02 | Pemilih Modifier | `bottom sheet di /pos` | UC-ORD-01 | — | core |
| SCR-POS-03 | Ringkasan Order (tipe, meja, diskon, total) | `panel di /pos` | UC-ORD-01, UC-ORD-02 | PUT /orders/{id}, /discount | core |
| SCR-POS-04 | Dialog Pembayaran | `dialog di /pos` | UC-PAY-01 | POST /orders/{id}/pay | core |
| SCR-POS-05 | Riwayat & Detail Order | `/pos/orders · /pos/orders/:id` | UC-ORD-04, UC-ORD-03 | API-ORD-* | core |
| SCR-POS-06 | Struk | `/pos/orders/:id/receipt` | UC-PAY-02 | GET /orders/{id}/receipt | core |
| SCR-POS-07 | Dialog Refund | `dialog di detail order` | UC-RFD-01 | POST /orders/{id}/refund | core |
| SCR-KIT-01 | Antrian Dapur | `/kitchen` | UC-KIT-01 | API-KIT-01/02 | R1.1 |
| SCR-PUR-01 | Supplier | `/purchasing/suppliers` | UC-PUR-01 | API-PUR-01…04 | core |
| SCR-PUR-02 | Daftar & Form Purchase | `/purchasing/orders · /purchasing/orders/:id` | UC-PUR-02, UC-PUR-04 | API-PUR-05…08 | core |
| SCR-PUR-03 | Terima Purchase | `dialog di detail purchase` | UC-PUR-03 | API-PUR-09 | core |
| SCR-RPT-01 | Laporan Sales & Payment | `/reports/sales` | UC-RPT-01 | API-RPT-01/03 | core |
| SCR-RPT-02 | Laporan Produk, Inventory, Waste | `/reports/products · /reports/inventory` | UC-RPT-01 | API-RPT-02/04/05 | R1.1 |
| SCR-RPT-03 | Dashboard | `/dashboard` | UC-RPT-02 | API-RPT-06 | R1.1 |

## 4. Flow Utama
Format: `[S]` layar · `[A]` aksi user · `[R]` respons sistem.

### 4.1 Order sampai bayar tunai (UC-ORD-01, UC-PAY-01)
```
[S] SCR-POS-01 Layar POS
[A] Pilih kategori → ketuk produk
[R] Produk punya modifier wajib? Ya → [S] SCR-POS-02 (tombol Simpan nonaktif sampai min_select terpenuhi) / Tidak → masuk cart
[A] Atur qty, tipe order (Dine In/Takeaway), nomor meja (hanya Dine In), diskon (jika diizinkan)
[R] SCR-POS-03: estimasi total lokal; angka final dari server setelah Simpan (POST /orders → draft)
[A] Konfirmasi → status PENDING_PAYMENT
[S] SCR-POS-04 Dialog Pembayaran → pilih Cash / QRIS
[A] Cash: ketik atau pilih nominal cepat → [R] kembalian tampil
[A] Bayar (Idempotency-Key dibuat sekali untuk dialog ini)
[R] 200 → order COMPLETED → [S] SCR-POS-06 Struk (cetak) → kembali ke POS dengan cart kosong
[R] 422 INSUFFICIENT_STOCK → daftar bahan kurang ditampilkan; order tetap PENDING_PAYMENT
[R] Jaringan putus → pesan "Koneksi terputus"; tombol Coba lagi memakai key yang sama
```
### 4.2 Tutup shift (UC-SHF-03)
```
[S] SCR-SHF-03 → [R] ringkasan: kas awal, penjualan tunai, kas masuk/keluar, refund tunai, Expected Cash, non-tunai
[A] Input uang fisik → [R] selisih tampil (hijau 0, merah ≠ 0)
[A] Tutup → [R] 409 SHIFT_HAS_PENDING_ORDERS: tampilkan jumlah + tautan ke order terkait
[R] Sukses → shift CLOSED, ringkasan dapat dicetak, logout dari POS
```
### 4.3 Terima purchase (UC-PUR-03)
```
[S] SCR-PUR-02 detail DRAFT → [A] Terima
[S] SCR-PUR-03 konfirmasi: tiap bahan menampilkan qty diterima (base unit), stok sebelum → sesudah, last_cost lama → baru
[A] Konfirmasi → [R] 200 status RECEIVED, tombol ubah hilang
[R] 409 → "Purchase tidak dapat diterima." (misal sudah diterima di tab lain)
```
### 4.4 Refund (UC-RFD-01)
```
[S] SCR-POS-05 detail order COMPLETED → [A] Refund (hanya Manager/Owner)
[S] SCR-POS-07 → isi alasan, centang restock (default tidak) → [A] Konfirmasi
[R] Tunai tanpa shift OPEN pelaku → 409 NO_OPEN_SHIFT "Buka shift terlebih dahulu."
[R] Sukses → order REFUNDED; struk refund
```
### 4.5 Opname `[R1.1]` (UC-INV-05)
```
[S] SCR-INV-05 → [A] Mulai (snapshot stok) → [A] input hitungan per bahan (simpan otomatis per baris)
[R] Review selisih terhadap snapshot → [A] Submit → [R] penyesuaian diterapkan; opname SUBMITTED
```
## 5. State Layar Penting
| Layar | Empty | Error khusus |
|-------|-------|--------------|
| POS-01 | "Belum ada menu. Tambahkan produk di Menu." (Manager) / "Menu belum tersedia." (Cashier) | Katalog gagal dimuat → tombol Muat ulang |
| POS-05 | "Belum ada order pada rentang ini." | — |
| INV-01 | "Belum ada bahan. Tambahkan ingredient lalu isi stok awal." | — |
| PUR-02 | "Belum ada purchase. Buat purchase pertama." | Supplier INACTIVE di draft lama → tandai dan minta ganti |
| RPT-01 | "Tidak ada transaksi pada periode ini." | Outlet di luar akses → pesan 403 |

## 6. Changelog
| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 3.0 | 2026-10-25 | Versi teks pertama: site map, 31 layar, 5 flow, state |
