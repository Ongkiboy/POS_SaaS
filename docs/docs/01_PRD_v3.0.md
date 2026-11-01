# Product Requirements Document (PRD) — F&B POS & Inventory
> **Versi:** 3.0 (Baseline final untuk implementasi) | **Tanggal:** 2026-10-25 | **Status:** Disetujui untuk dikerjakan (G0–G6)
> **Tier dokumentasi:** Standard + pengecualian Full (pembayaran, stok, shift, tenant isolation, RBAC) | **Mode tiket:** Tim (3 developer)
> **Stakeholder:** Iki/Ongki (product owner, tech lead), Akmal, Naufaldi
> **Menggantikan:** PRD v1.x, v2.0, v2.1. **Dokumen turunan:** SRS v3.0, Process & Use Case v3.0, SDD v3.0, ERD v3.0, UI/UX v3.0, Technical Design v3.0, Task Breakdown v3.0, Test Plan v3.0, Deploy & Ops v3.0, folder `/modules`.
> **Legenda:** `[ASUMSI]` belum divalidasi · 🔶 menunggu keputusan · `[R1.1]` rilis lanjutan Januari 2027 · `[P2]` Phase 2

## Perubahan inti v3.0 dibanding v2.1
| # | Perubahan | Dasar |
|---|-----------|-------|
| 1 | Frontend **Vue 3** (stack awal); Laravel tetap API-only | Keputusan 2026-10-25 |
| 2 | **Purchase masuk Phase 1** versi inti (supplier, purchase DRAFT → RECEIVED, update `last_cost`, konversi kg→g dan L→ml) | F2 + P1 |
| 3 | Dibagi dua rilis: **core 30 Des 2026** dan **rilis 1.1 Januari 2027** (opname, dapur, export CSV, UI audit log, recipe modifier, cancel/partial purchase, laporan lanjutan, dashboard) | F1 c + J3 |
| 4 | Laporan core hanya sales, payment, dan stok; dashboard dan laporan lain ke rilis 1.1 | J3 |
| 5 | RBAC memakai **Spatie Permission** (teams = business); aturan "Limited/Own" di Policy | Konfirmasi 1–4 |
| 6 | Database **PostgreSQL di Supabase** (direct/session pooler); cache dan queue **driver database** dulu | Konfirmasi 1–4 |
| 7 | Midtrans tetap **Phase 2** (QRIS Core API, akun per business) | B1–B6 |
| 8 | Harga menu **eksklusif** pajak, 1 business banyak outlet, 5 role terpisah, bahan modifier dihitung saat bayar | A1–A4 |

---

## 1. Overview Produk

### 1.1 Nama Produk
F&B POS & Inventory Management (PWA). Nama komersial belum ditetapkan.

### 1.2 Latar Belakang
Bisnis F&B kecil-menengah umumnya mencatat penjualan di satu alat dan stok bahan di catatan terpisah. Stok baru dikoreksi saat opname, sehingga selisih antara bahan terpakai dan bahan terjual tidak terlihat sampai terlambat. Produk ini menyatukan penjualan dan persediaan dengan satu alur: **Sale → Payment → Recipe → Inventory → Reporting**.

### 1.3 Problem Statement
> **Masalah yang diselesaikan:** "Pemilik dan manajer kafe/restoran kecil kesulitan mengetahui stok bahan baku dan kas yang sebenarnya ketika transaksi terjadi cepat dan dikelola banyak orang, yang berdampak pada selisih stok, selisih kas, dan keputusan pembelian yang meleset."

### 1.4 Tujuan Produk
| # | Tujuan | Indikator Keberhasilan |
|---|--------|------------------------|
| 1 | Kasir menyelesaikan transaksi dengan cepat | Rata-rata waktu dari item pertama ditambahkan sampai pembayaran sukses ≤ 30 detik untuk order ≤ 5 item `[ASUMSI]` |
| 2 | Stok sistem mencerminkan stok fisik | Selisih stok sistem vs opname ≤ 3% per bahan pada 2 minggu pertama pilot `[ASUMSI]` |
| 3 | Kas shift dapat direkonsiliasi | 100% shift tertutup dengan Expected Cash terhitung otomatis |
| 4 | Data tenant terisolasi | 0 kebocoran lintas business di test suite dan UAT |
| 5 | Setiap pemotongan stok bisa ditelusuri | `balance = SUM(movements)` untuk 100% ingredient |
| 6 | Pembayaran non-tunai terekonsiliasi (Phase 2) | 100% transaksi Midtrans berstatus settlement memiliki tepat 1 payment dan movement stok; 0 payment ganda dari notifikasi berulang |

### 1.5 Skala & Peluncuran
- **Peluncuran:** pilot 1 business dengan 1 outlet, lalu bertahap.
- **Target 12 bulan:** 5 outlet di bawah 1 business (dikunci).
- Desain tetap multi-tenant dan multi-outlet sejak awal agar tidak ada refactor saat skala naik.

---


## 2. Target User

### 2.1 User Persona
**Persona 1: Pemilik / Manajer Kafe**
- **Role:** Owner atau Manager
- **Goals:** Melihat penjualan, stok, dan selisih kas tanpa menunggu laporan manual
- **Pain Points:** Selisih stok tidak jelas penyebabnya; kasir dan gudang saling menyalahkan
- **Tech Literacy:** Menengah

**Persona 2: Kasir**
- **Role:** Cashier
- **Goals:** Input order cepat, terima pembayaran, tutup shift tanpa menghitung manual
- **Pain Points:** Antrean panjang, salah hitung kembalian, selisih kas di akhir shift
- **Tech Literacy:** Rendah-menengah

**Persona 3: Staf Gudang / Dapur**
- **Role:** Inventory, Kitchen
- **Goals:** Catat stok masuk, waste, opname; lihat antrian order
- **Pain Points:** Pencatatan manual di kertas, bahan habis tanpa peringatan
- **Tech Literacy:** Rendah-menengah

### 2.2 User Journey (High-Level)
```
[Pagi: buka outlet] → [Kasir buka shift + kas awal] → [Order, bayar, struk]
  → [Stok bahan otomatis berkurang sesuai recipe] → [Dapur melihat antrian]
  → [Sore: kasir tutup shift, sistem hitung selisih] → [Owner lihat laporan & stok menipis]
```

---


## 3. Scope Produk

### 3.1 Phase 1 — rilis core (target production 29 Des 2026, cadangan 30 Des)
- Autentikasi, business, outlet, user, RBAC 5 role (Spatie), audit trail (pencatatan)
- Category, product, modifier, ingredient, recipe (versi)
- Shift & kas: open, cash in/out, close, rekonsiliasi
- POS: order, diskon order-level, pajak/service charge (exclusive), Dine In (nomor meja opsional) & Takeaway, cancel, refund penuh
- Pembayaran: Cash dan QRIS manual, pembulatan total per outlet, idempotency, struk (browser print)
- Inventory: stok per outlet, opening stock, adjustment, waste, movement, low stock, stok minus (setting per outlet)
- **Purchasing inti:** supplier, purchase DRAFT → RECEIVED, konversi satuan beli (kg→g, L→ml), update `last_cost`
- Laporan core: sales (+COGS, gross profit), payment, stok (halaman stok, movement, low stock, stok minus)
- PWA installable, online-only

### 3.2 Rilis 1.1 (Januari 2027, indikatif 4–15 Jan)
- Stock opname (F-13), antrian dapur (F-14), recipe per modifier option (F-18), export CSV (F-19)
- Layar baca audit log (F-16 bagian baca)
- Cancel purchase dan partial receive (F-20 bagian lanjut)
- Laporan produk (+no recipe), laporan inventory/waste, dashboard
- Dokumentasi API (OpenAPI) dan runbook lengkap

> **Dampak yang harus dipahami tim:** sebelum F-18 masuk, pilihan modifier (extra shot, oat milk) menambah harga tetapi **tidak** memotong bahan. Selisih stok kecil di pilot adalah konsekuensi yang diterima. Produk tanpa recipe ditandai di daftar produk (HPP = 0, penanda "no recipe") sampai laporan khususnya hadir di 1.1.

### 3.3 Phase 2 (struktur sudah disiapkan, belum dibangun)
- Payment gateway **Midtrans** (QRIS dinamis Core API, webhook, refund via API, rekonsiliasi gateway, F-21)
- Split payment ditunda, kirim receipt (WhatsApp/email), KDS penuh, harga produk per outlet, job rekonsiliasi stok otomatis, Redis untuk queue/cache

**Di luar MVP:** Loyalty, CRM, payroll, accounting, advanced warehouse, AI forecasting, franchise, online ordering, delivery, marketplace, promotion engine, advanced table management, multi-currency, multi-language, partial refund order, open bill Dine In, mode pajak inclusive, offline POS, transfer stok antar outlet, billing/subscription SaaS.

### 3.4 Asumsi
- Satu mata uang (IDR), satu bahasa (Indonesia)
- Internet tersedia di outlet; tidak ada offline queue
- Satu kasir bekerja di satu perangkat pada satu waktu
- Harga menu **belum termasuk pajak** (eksklusif; dikunci A1). Bila di lapangan ternyata inklusif, `PriceCalculator` (BE-ORD-002) berubah
- Harga produk sama di semua outlet satu business
- Printer: browser print 58/80 mm tanpa integrasi printer native
- Pemakaian bahan modifier dihitung dari recipe aktif **saat pembayaran** (dikunci A4)
- Phase 2: tiap business memakai akun Midtrans sendiri; data kartu tidak pernah melewati server kami

### 3.5 Ketergantungan
- VPS + domain + HTTPS (FE dan API satu origin lewat Nginx), akun Supabase (PostgreSQL + Storage)
- Akses 1 pemilik kafe untuk validasi lapangan sebelum UAT (21 Des)
- Perangkat uji: 1 HP Android, 1 browser desktop, 1 printer thermal (uji print browser)
- Phase 2: akun merchant Midtrans, endpoint webhook HTTPS publik, Redis dan queue worker aktif

### 3.6 Aturan Bisnis Awal (ringkas)
Aturan lengkap dan bernomor ada di SRS §4 (`BR-001` s.d. `BR-050`). Prinsip utama: order butuh shift OPEN; bayar, status, movement, dan saldo satu transaksi atomik; pembayaran idempoten; snapshot harga/HPP/recipe; stok boleh minus sesuai setting; diskon order-level; pajak exclusive; pembulatan total per outlet; COMPLETED otomatis saat bayar; refund penuh satu kali; purchase RECEIVED menambah stok dan memperbarui `last_cost`; tenant terisolasi.

### 3.7 Daftar Aktor
| Aktor | Tipe | Deskripsi |
|-------|------|-----------|
| Owner | Primary | Pemilik business; akses penuh semua outlet |
| Manager | Primary | Pengelola operasional; refund, user terbatas, setting outlet, purchase |
| Cashier | Primary | Order, pembayaran, shift, kas |
| Inventory Staff | Primary | Stok, adjustment, waste, purchase, opname (1.1) |
| Kitchen Staff | Primary | Antrian order (1.1), waste |
| Sistem Penjadwal | System | Phase 2: penyapu transaksi gateway, retry webhook, rekonsiliasi stok. Tidak dipakai di core |
| Midtrans | System (eksternal) | Phase 2: payment gateway |

---

## 4. Fitur Produk

### 4.1 Daftar Fitur
| ID | Nama Fitur | Prioritas | Rilis |
|----|-----------|-----------|-------|
| F-01 | Autentikasi & Sesi | Must | Core |
| F-02 | Business, Outlet & Pengaturan | Must | Core |
| F-03 | User, Role & Akses Outlet (RBAC) | Must | Core |
| F-04 | Katalog: Category, Product, Modifier | Must | Core |
| F-05 | Ingredient & Recipe (versi) | Must | Core |
| F-06 | Shift & Kas | Must | Core |
| F-07 | Order POS (draft, diskon, pajak, cancel) | Must | Core |
| F-08 | Pembayaran (Cash, QRIS manual) & Struk | Must | Core |
| F-09 | Pemotongan Stok Otomatis (core engine) | Must | Core |
| F-10 | Refund Penuh | Must | Core |
| F-11 | Stok, Opening Stock & Movement | Must | Core |
| F-12 | Stock Adjustment & Waste | Must | Core |
| F-13 | Stock Opname | Should | **R1.1** |
| F-14 | Antrian Dapur | Should | **R1.1** |
| F-15 | Laporan & Dashboard | Must | Core (sales, payment, stok); R1.1 (produk, inventory/waste, dashboard) |
| F-16 | Audit Trail | Must | Core (pencatatan); R1.1 (layar baca) |
| F-17 | PWA | Must | Core |
| F-18 | Recipe per Modifier Option | Should | **R1.1** |
| F-19 | Export CSV | Should | **R1.1** |
| F-20 | Supplier & Purchasing | Should | Core (inti); R1.1 (cancel, partial receive) |
| F-21 | Payment Gateway Midtrans | Should | Phase 2 |
| F-22 | Kirim Receipt | Nice to Have | Phase 2 |

> **Urutan pemotongan bila core terlambat (cut list core):** indikator stok di POS (FE-ENG-001) → banner offline (FE-PWA-002) → E2E inventory/purchase (TEST-E2E-002) → FE halaman laporan payment → FE Purchase dipersingkat (satu layar). Core engine (F-06 s.d. F-12) tidak dipotong.

### 4.2 Detail Fitur
Format: user story singkat + acceptance criteria. Spesifikasi lengkap ada di SRS (FR-xxx).

#### F-01 Autentikasi & Sesi
> Sebagai pengguna, saya ingin masuk dengan aman agar hanya saya yang mengakses data bisnis saya.
- [ ] Register business membuat business, outlet default, dan akun Owner dalam satu proses
- [ ] Login gagal 5 kali mengunci akun 15 menit dan tidak membocorkan apakah email atau password yang salah
- [ ] Logout mengakhiri sesi; sesi tidak aktif melewati batas waktu harus login ulang

#### F-02 Business, Outlet & Pengaturan
> Sebagai Owner, saya ingin mengatur outlet dan aturan pajak agar struk dan total sesuai kebijakan kami.
- [ ] Outlet punya timezone, kode unik, tarif pajak %, service charge %, `allow_negative_stock`, batas diskon kasir, pembulatan total, header/footer struk
- [ ] Manager hanya dapat mengubah pajak, service charge, dan struk di outlet miliknya, tidak profil business
- [ ] Setiap perubahan pajak/service charge tercatat di audit trail
- [ ] Business menyimpan data license dasar (plan, berlaku sampai) tanpa enforcement

#### F-03 User, Role & Akses Outlet
> Sebagai Owner, saya ingin memberi karyawan akses sesuai tugasnya agar data sensitif tidak terbuka.
- [ ] Satu user punya satu role dan daftar outlet yang boleh diakses
- [ ] Pengecekan akses dilakukan di backend; mengakses data business lain mengembalikan 404
- [ ] Manager hanya boleh membuat dan mengubah Cashier, Inventory, Kitchen di outlet yang ia akses

#### F-04 Katalog
> Sebagai Manager, saya ingin mengelola menu agar kasir hanya melihat produk yang tersedia.
- [ ] SKU produk unik per business; produk INACTIVE tidak muncul di POS dan tidak bisa dipesan
- [ ] Modifier group mendukung tipe single/multi, wajib/opsional, dan selisih harga per option
- [ ] Perubahan harga produk tercatat di audit trail dan tidak mengubah order lama

#### F-05 Ingredient & Recipe
> Sebagai Manager, saya ingin menghubungkan menu dengan bahan agar stok berkurang sesuai resep.
- [ ] Qty recipe dalam base unit ingredient; edit recipe membuat versi baru dengan 1 versi aktif
- [ ] HPP produk dihitung dari recipe × `last_cost`, ditampilkan sebagai nilai hitungan, tidak diinput manual
- [ ] Hanya Owner dan Manager yang dapat mengubah recipe; Inventory dan Kitchen hanya melihat recipe tanpa HPP
- [ ] Owner, Manager, dan Inventory Staff dapat mengelola ingredient (permission `inventory.adjust`)

#### F-06 Shift & Kas
> Sebagai Kasir, saya ingin membuka dan menutup shift agar kas harian terekonsiliasi.
- [ ] Satu kasir hanya punya satu shift OPEN per outlet; order wajib terhubung ke shift OPEN
- [ ] Expected Cash = opening + cash sales + cash in − cash out − cash refund; selisih = actual − expected
- [ ] Shift tidak dapat ditutup selama ada order DRAFT atau PENDING_PAYMENT; shift terbuka > 24 jam menampilkan peringatan

#### F-07 Order POS
> Sebagai Kasir, saya ingin membuat order dengan modifier dan diskon agar total benar sebelum dibayar.
- [ ] Total = subtotal − diskon + service charge + pajak + pembulatan total ke increment outlet, mengikuti urutan pada BR-005 dan BR-036
- [ ] Cancel hanya untuk DRAFT/PENDING_PAYMENT dengan alasan wajib; Kasir hanya bisa membatalkan order miliknya
- [ ] Nomor order unik per outlet dengan format `{KODE_OUTLET}-{YYMMDD}-{urut}` dan aman terhadap order bersamaan
- [ ] Dine In dapat menyimpan nomor meja (opsional)

#### F-08 Pembayaran & Struk
> Sebagai Kasir, saya ingin menerima pembayaran tunai atau QRIS dan mencetak struk.
- [ ] Cash menghitung kembalian dari total (sudah termasuk pembulatan); QRIS menyimpan nomor referensi opsional; tanpa split payment
- [ ] Klik ganda atau retry dengan `Idempotency-Key` sama tidak membuat payment kedua
- [ ] Struk memuat header outlet, item, diskon, pajak, service, total, pembayaran, dan dapat dicetak (browser print) ulang dari riwayat

#### F-09 Pemotongan Stok Otomatis
> Sebagai Owner, saya ingin stok berkurang otomatis saat produk terjual agar stok akurat tanpa input manual.
- [ ] Saat order dibayar, stok dipotong sesuai recipe (versi saat transaksi) dan recipe modifier option dalam transaksi yang sama
- [ ] Produk tanpa recipe boleh dijual dan tidak memotong stok, serta muncul di laporan "no recipe"
- [ ] Jika stok kurang: boleh jual (stok minus) saat `allow_negative_stock` aktif; jika tidak aktif, pembayaran ditolak dengan daftar bahan kurang

#### F-10 Refund Penuh
> Sebagai Manager, saya ingin merefund order yang sudah dibayar dengan jejak yang jelas.
- [ ] Refund penuh, satu kali per order, butuh permission `sales.refund` dan alasan wajib
- [ ] Refund tunai dicatat sebagai kas keluar di shift OPEN milik pelaku; ditolak jika pelaku tidak punya shift OPEN
- [ ] Restock dipilih per refund (default tidak); jika dipilih membuat movement `RETURN`
- [ ] (Phase 2) Refund pembayaran Midtrans memanggil API refund; bila ditolak atau tidak didukung, Manager dapat memilih refund manual (lihat F-21)

#### F-11 Stok, Opening Stock & Movement
> Sebagai Inventory Staff, saya ingin melihat dan mengisi stok awal agar data siap dipakai.
- [ ] Opening stock membuat movement `OPENING` per bahan per outlet
- [ ] Setiap movement menyimpan stok sebelum, sesudah, dan biaya per unit saat kejadian; tidak bisa diubah atau dihapus
- [ ] Bahan dengan stok ≤ minimum tampil sebagai low stock

#### F-12 Adjustment & Waste
> Sebagai Inventory Staff, saya ingin mencatat koreksi dan bahan terbuang dengan alasan jelas.
- [ ] Adjustment dan waste wajib punya alasan dan qty > 0
- [ ] Waste selalu mengurangi stok dan menyimpan biaya (qty × `last_cost` saat kejadian); Kitchen tidak melihat nilai biaya
- [ ] Setiap adjustment dan waste tercatat di audit trail dengan before/after

#### F-13 Stock Opname [Rilis 1.1]
> Sebagai Inventory Staff, saya ingin menghitung stok fisik dan menyesuaikan sistem.
- [ ] Hanya 1 opname IN_PROGRESS per outlet; stok sistem di-snapshot saat mulai
- [ ] Selisih = hitungan − snapshot, diterapkan ke stok live saat submit sehingga penjualan selama penghitungan tidak hilang
- [ ] Submit membuat movement `STOCK_OPNAME` dan audit ringkasan selisih

#### F-14 Antrian Dapur [Rilis 1.1]
> Sebagai Kitchen Staff, saya ingin melihat order yang harus disiapkan.
- [ ] Antrian menampilkan item, qty, catatan, nomor order, tanpa harga dan data pembayaran
- [ ] Order baru dibayar muncul dengan status dapur NEW; tombol "Siap" mengubah menjadi READY
- [ ] Status dapur terpisah dari status order

#### F-15 Laporan & Dashboard (core: sales, payment, stok; laporan produk/inventory/waste dan dashboard di rilis 1.1)
> Sebagai Owner, saya ingin melihat penjualan, stok, dan selisih dalam satu tempat.
- [ ] Laporan harian memakai business date, bukan waktu pembuatan data
- [ ] Sales report memuat gross, diskon, pajak, service, net, jumlah order, COGS, gross profit; REFUNDED dipisah
- [ ] Dashboard hanya menampilkan widget sesuai permission; Kasir hanya melihat data shift sendiri

#### F-16 Audit Trail (pencatatan di core; layar baca audit log di rilis 1.1)
> Sebagai Owner, saya ingin tahu siapa mengubah apa.
- [ ] Aksi di PRD §28 (login sukses/gagal, cancel, refund, shift, kas, diskon, adjustment, waste, recipe, user/role, harga, setting pajak) tercatat dengan before/after JSON
- [ ] Audit log tidak dapat diubah atau dihapus dari aplikasi
- [ ] (Rilis 1.1) Owner dan Manager dapat memfilter audit log (tanggal, user, aksi, resource)

#### F-17 PWA
> Sebagai pengguna, saya ingin memasang aplikasi di HP atau desktop.
- [ ] Aplikasi installable (manifest + HTTPS) dan responsif di desktop dan mobile
- [ ] Service worker hanya meng-cache aset statis, tidak respons API
- [ ] Saat offline, tampil banner dan aksi tulis ditolak dengan pesan jelas

#### F-18 Recipe per Modifier Option [Rilis 1.1]
> Sebagai Manager, saya ingin extra shot atau oat milk ikut memotong stok.
- [ ] Modifier option dapat punya daftar bahan tambahan dalam base unit
- [ ] Saat dibayar, pemakaian modifier ditambahkan ke agregat bahan per order
- [ ] Perubahan recipe modifier tercatat di audit trail

#### F-19 Export CSV [Rilis 1.1]
> Sebagai Owner, saya ingin mengunduh data penjualan dan movement.
- [ ] Export CSV tersedia untuk sales dan stock movement dengan filter yang sama dengan layar
- [ ] Hanya permission `reports.export` yang dapat mengunduh
- [ ] Data yang diekspor hanya milik business dan outlet yang diakses user

#### F-20 Supplier & Purchasing (inti di Phase 1; cancel dan partial receive di rilis 1.1)
> Sebagai Inventory Staff atau Manager, saya ingin mencatat pembelian bahan dari supplier agar stok masuk dan biaya bahan terbarui otomatis.
- [ ] Supplier dapat dikelola (nama unik per business, status ACTIVE/INACTIVE); supplier INACTIVE tidak bisa dipilih di purchase baru
- [ ] Purchase DRAFT berisi item dengan satuan beli (g, kg, ml, L, pcs); sistem menampilkan konversi ke base unit (kg→g dan L→ml = 1000) sebelum disimpan
- [ ] Menerima purchase (DRAFT → RECEIVED) menambah stok dalam satu transaksi: 1 movement `PURCHASE` per bahan, `last_cost` bahan diperbarui, dan klik ganda tidak menggandakan stok
- [ ] Purchase RECEIVED tidak dapat diubah; nomor purchase unik per outlet `PO-{KODE_OUTLET}-{YYMMDD}-{urut}`
- [ ] (Rilis 1.1) Cancel purchase DRAFT dengan alasan dan partial receive

#### F-21 Payment Gateway Midtrans (Phase 2)
> Sebagai Kasir, saya ingin menerima pembayaran QRIS dinamis lewat Midtrans agar pembayaran terverifikasi otomatis tanpa konfirmasi manual.
- [ ] Owner menyimpan kredensial Midtrans (sandbox/production) per business, dengan override per outlet; server key terenkripsi dan tidak pernah ditampilkan ulang
- [ ] Kasir memulai pembayaran gateway dari order `PENDING_PAYMENT`; sistem menampilkan QR dan status ter-update otomatis; hanya satu transaksi aktif per order
- [ ] Order menjadi `COMPLETED` dan stok terpotong hanya setelah settlement terverifikasi (signature valid + cek status ke Midtrans); notifikasi berulang tidak membuat payment ganda
- [ ] Transaksi kedaluwarsa, gagal, atau dibatalkan tidak mengubah order; kasir dapat mencoba lagi atau beralih ke tunai
- [ ] Settlement untuk order yang sudah tidak dapat dibayar, atau nominal yang tidak cocok, tidak mengubah order dan muncul di laporan rekonsiliasi untuk ditindaklanjuti Manager
- [ ] Pembayaran gateway tidak masuk Expected Cash shift; muncul di laporan payment per metode dan provider
- [ ] Setiap aksi gateway (konfigurasi, inisiasi, settlement, gagal, batal, refund) tercatat di audit trail

*F-22 (Phase 2): acceptance criteria ditulis menjelang Phase 2 dimulai.*

---


---

## 5. User Flow (High-Level)

### Flow: Penjualan
```
[Kasir login] → [Pilih outlet] → [Buka shift + kas awal]
  → [Pilih produk + modifier + qty] → [Diskon (opsional, butuh izin)]
  → [Konfirmasi order → PENDING_PAYMENT, item terkunci]
  → [Pilih Cash/QRIS] 
      → Nominal kurang/tidak valid → pesan error, tetap PENDING_PAYMENT
      → Stok kurang & stok minus dilarang → pembayaran ditolak + daftar bahan
      → Sukses → [Order COMPLETED, stok terpotong, struk tampil, antrian dapur NEW]
```

### Flow: Tutup Shift
```
[Kasir tekan Tutup Shift]
  → Ada order DRAFT/PENDING → ditolak, selesaikan atau batalkan dulu
  → Tidak ada → [Sistem tampilkan Expected Cash] → [Kasir input uang fisik]
  → [Selisih dihitung, shift CLOSED, ringkasan dapat dicetak]
```

### Flow: Refund
```
[Manager buka order] → [Refund: alasan + pilih restock]
  → Tunai & Manager tanpa shift OPEN → ditolak
  → Order bukan PAID/COMPLETED atau sudah pernah direfund → ditolak
  → Sukses → [Order REFUNDED, kas keluar tercatat di shift Manager, RETURN movement jika restock]
```

### Flow: Pembayaran Midtrans (Phase 2)
```
[Order PENDING_PAYMENT] → [Pilih QRIS Midtrans] → [Sistem membuat transaksi PENDING + tampilkan QR]
  → Stok kurang & stok minus dilarang → ditolak sebelum QR dibuat
  → Pelanggan scan dan bayar → [Midtrans kirim notifikasi settlement]
      → Signature valid, status terverifikasi, order masih PENDING_PAYMENT, nominal cocok
          → [Order COMPLETED, stok terpotong, struk, antrian dapur NEW]
      → Order sudah tidak dapat dibayar atau nominal berbeda
          → order tidak berubah, event ditandai untuk tinjauan Manager
  → QR kedaluwarsa / gagal / dibatalkan kasir → [Order tetap PENDING_PAYMENT: coba lagi atau bayar tunai]
```

### Flow: Pembelian Bahan (Purchase)
```
[Inventory/Manager buka Purchase] → [Pilih supplier ACTIVE] → [Tambah item: bahan, satuan beli, qty, harga per satuan beli]
  → [Sistem tampilkan konversi ke base unit + total] → [Simpan DRAFT, nomor PO terbit]
  → [Barang datang: Terima] → Bukan DRAFT → ditolak
  → Sukses → [Stok bertambah (movement PURCHASE), last_cost bahan diperbarui, purchase RECEIVED, tidak bisa diubah]
```


## 6. Non-Functional Requirements (Ringkasan)
| Aspek | Kebutuhan |
|-------|-----------|
| Performa | Load awal POS < 3 detik (4G); pembayaran + potong stok p95 < 800 ms |
| Konkurensi | ≥ 20 pembayaran simultan per outlet tanpa deadlock atau stok salah |
| Ketersediaan | Uptime 99,5% per bulan |
| Keamanan | Isolasi tenant 0 kebocoran; password hash; HTTPS |
| Platform | Chrome 90+, Safari 14+; Android 8+ dan iOS 14+ via browser/PWA |
| Bahasa & mata uang | Indonesia, IDR bilangan bulat |
| Skala | 5 outlet dalam 12 bulan tanpa perubahan arsitektur |
| Database | PostgreSQL (Supabase, direct/session pooler); cache dan queue driver database di core |
| Gateway (Phase 2) | Notifikasi settlement diproses p95 ≤ 5 detik sejak diterima; webhook idempoten; server key terenkripsi; signature diverifikasi |
| Database | Skema dan index mengikuti ERD v3.0; query hot path tanpa sequential scan pada tabel besar |

Detail terukur di SRS §3.

---


## 7. Timeline & Milestone
Kapasitas dihitung dari **Senin 26 Okt 2026** (47 hari kerja sampai 30 Des; 25 Des libur) × 3 dev × 75% = ±106 hari-orang. Kebutuhan core hasil re-baseline: **101 hari-orang** (margin ±4,75 hari-orang, ±4,5%, tanpa buffer 20%). Dengan margin setipis ini jadwal 30 Des **rawan**; urutan potong di §4.1 dan prioritas jalur kritis (Task Breakdown §3) dipakai bila ada keterlambatan.

| Sprint | Tanggal | Fokus |
|--------|---------|-------|
| S1 | 26 Okt–6 Nov | Setup, tenant, RBAC, audit, register business, katalog, ingredient, skema DB inti |
| S2 | 9–20 Nov | Recipe, StockService, shift, kalkulator order, draft order |
| S3 | 23 Nov–4 Des | Bayar + potong stok end-to-end, refund, POS FE |
| S4 | 7–18 Des | Purchase, laporan core, PWA, close shift, integration test, feature freeze 18 Des |
| S5 | 21–30 Des | UAT 21–24 Des, hardening, deploy 29 Des, 30 Des cadangan |
| Rilis 1.1 | 4–15 Jan 2027 (indikatif) | Item `[R1.1]` |

| Milestone | Target | Deskripsi |
|-----------|--------|-----------|
| CP1 | 6 Nov | Register business, tenant scope + RBAC + audit siap, CRUD katalog dan ingredient jalan, CI + staging |
| CP2 | 20 Nov | Login + `/me` (FE login dapat dipakai), `StockService` teruji, recipe versi, shift bisa dibuka, order draft + kalkulator |
| CP3 | 4 Des | Order → bayar tunai → stok terpotong end-to-end |
| CP4 | 18 Des | Feature freeze core: refund, close shift, purchase receive, laporan sales/payment, E2E |
| UAT | 21–24 Des | Uji dengan pengguna nyata |
| Production | 29 Des | 30 Des cadangan |

---

## 8. Keputusan & Open Questions

### 8.1 Keputusan yang dikunci
| # | Keputusan | Nilai |
|---|-----------|-------|
| 1 | Harga menu | Eksklusif pajak (A1) |
| 2 | Skala | 1 business, banyak outlet (A2) |
| 3 | Role | 5 role terpisah: Owner, Manager, Cashier, Inventory, Kitchen (A3) |
| 4 | Bahan modifier | Dihitung saat bayar (A4) |
| 5 | Cuti bersama Desember | Tidak ada (A5) |
| 6 | Midtrans | Phase 2; akun per business; QRIS via Core API; settlement tetap memotong stok walau minus (BR-041); fee tidak dicatat; refund API dengan fallback manual (B1–B6) |
| 7 | Frontend | Vue 3 + TypeScript + Vite + Pinia + Tailwind; PWA via vite-plugin-pwa; disajikan sebagai file statis oleh Nginx |
| 8 | Jadwal | Core 30 Des; rilis 1.1 Januari 2027 (F1 c + J3) |
| 9 | Purchase | Phase 1 versi inti (F2 + P1) |
| 10 | Pembagian dev | Ongki: platform + POS; Akmal: recipe/BOM + inventory + engine + PWA/E2E; Naufaldi: katalog, user/outlet, shift, purchase, laporan (F3, dengan penyesuaian di Task Breakdown §2) |
| 11 | Stack lain | Laravel 13, PostgreSQL Supabase, Spatie Permission, cache/queue driver database, Supabase Storage |
| 12 | Default | Increment pembulatan 100; diskon Cashier 10%; email login unik global; sesi idle 120 menit; qty 1–999 |

### 8.2 Masih terbuka (tidak memblokir pengembangan)
| # | Pertanyaan | Owner | Deadline | Dampak |
|---|-----------|-------|----------|--------|
| 1 | Validasi lapangan harga eksklusif + tarif PB1 dan service charge | Iki + 1 pemilik kafe | Sebelum UAT (21 Des) | Bila inklusif: ubah `PriceCalculator` |
| 2 | Uji printer thermal 58/80 mm dari browser | Iki | Sebelum UAT | Penyesuaian CSS cetak |
| 3 | Akun Midtrans sandbox untuk uji Phase 2 | Iki | Sebelum Phase 2 | Phase 2 |
| 4 | Keputusan penarikan recipe modifier (F-18) ke core bila pilot butuh akurasi stok modifier | Iki | 4 Des (CP3) | +±1,5 hari |
| 5 | Redis untuk queue saat Midtrans masuk | Ongki | Sebelum Phase 2 | Konfigurasi deploy |
| 6 | Jenis VPS (Hostinger atau Rumahweb, unmanaged atau berpanel), RAM/vCPU, domain, dan apakah DNS lewat Cloudflare | Ongki | Ditanyakan lagi menjelang staging (`SETUP-008`) | Konfigurasi Nginx/Compose, ukuran build FE |

---

## 9. Changelog
| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| 1.0 | 2026-10 | Draft awal | |
| 1.1 | 2026-10 | Refund, last cost, stok minus, Business Rules, Open Items | |
| 1.2 | 2026-10-07 | Format template, fitur ber-ID + AC, split Phase 1/2, keputusan G0 (diskon order-level, pembulatan, COMPLETED otomatis, license kolom, `last_cost`), hapus mode pajak inclusive, pilot 1 outlet + target 5 outlet | Claude |
| 1.3 | 2026-10-07 | Cash rounding (tunai saja) masuk scope, menggantikan keputusan G0 "tanpa cash rounding" | Claude |
| 1.4 | 2026-10-07 | Pembulatan berlaku untuk semua metode pembayaran (level order) | Claude |
| 1.5 | 2026-10-07 | Aktor Sistem Penjadwal dinyatakan tidak dipakai di Phase 1 (temuan G2 U-02) | Claude |
| 2.0 | 2026-10-12 | Versi final: keputusan terbuka ditutup (§8.1), permission ingredient, sisa open question dipisah (§8.2). Konsisten dengan SRS v2.0 dan SDD v2.0 | Claude |
| 2.1 | 2026-10-12 | Midtrans ditetapkan sebagai provider gateway (Phase 2): F-21 + AC, aktor Midtrans, BR draft 11–13, flow Midtrans, NFR gateway, estimasi Phase 2, open question gateway; split payment dipisah dan ditunda; referensi ERD v3.0 | Claude |
| 3.0 | 2026-10-25 | Baseline final: Vue 3 (stack awal), Purchase inti Phase 1, pembagian core/rilis 1.1, laporan minimal core, Spatie, Supabase, cache/queue database, jadwal mulai 26 Okt, keputusan A–J dikunci | Claude |
