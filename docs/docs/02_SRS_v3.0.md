# Software Requirements Specification (SRS) — F&B POS & Inventory
> **Versi:** 3.0 (Baseline final untuk implementasi) | **Tanggal:** 2026-10-25 | **Status:** Disetujui
> **Referensi PRD:** PRD v3.0 · **Skema data:** ERD v3.0 · **Perilaku & API:** SDD v3.0
> **Dokumen turunan:** Process & Use Case v3.0 · SDD v3.0 · ERD v3.0 · UI/UX v3.0 · Technical Design v3.0 · Task Breakdown v3.0 · Test Plan v3.0 · Deploy & Ops v3.0 · folder `/modules`
> **Tier:** Standard + pengecualian Full (pembayaran, stok, shift, tenant isolation, RBAC)
> **Penanda:** `[R1.1]` rilis Januari 2027 · `[P2]` Phase 2 · `[ASUMSI]` belum divalidasi · 🔶 menunggu keputusan

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen
Menjabarkan kebutuhan fungsional dan non-fungsional Phase 1 sebagai acuan developer, QA, dan desainer. AC tiket dan test case diturunkan dari tabel validasi, aturan, dan BR di dokumen ini.

### 1.2 Lingkup Sistem
Web PWA (Vue 3) dengan backend Laravel API (`/api/v1`), PostgreSQL, Cache/Queue (driver database), object storage. Modul: AUTH, BIZ, USER, CAT, ING, REC, SHF, ORD, PAY, ENG, RFD, INV, PUR, KIT, RPT, AUD, PWA. Purchasing (PUR) masuk Phase 1 versi inti. Payment gateway **Midtrans** dispesifikasikan di dokumen ini (FR bertanda **[P2]**, BR-037…044) tetapi dikerjakan di Phase 2. Split payment ditunda.

### 1.3 Definisi & Singkatan
| Istilah | Definisi |
|---------|----------|
| Business | Tenant. Satu pemilik usaha |
| Outlet | Lokasi operasional di bawah business |
| Base unit | Satuan terkecil ingredient (`g`, `ml`, `pcs`); semua stok dan recipe memakai ini |
| Movement | Catatan perubahan stok (ledger), append-only |
| Balance | Saldo stok per outlet per ingredient, cache dari ledger |
| Business date | Tanggal shift dibuka menurut timezone outlet |
| HPP | Harga pokok per item, dihitung dari recipe × `last_cost` |
| Last Cost | Biaya per base unit terakhir; diinput manual atau diperbarui otomatis saat purchase diterima (BR-047) |
| Idempotency-Key | UUID per permintaan bayar agar retry tidak membuat payment ganda |
| Snapshot | Salinan nama, harga, HPP, versi recipe saat transaksi |
| Pembulatan total | Pembulatan total order ke kelipatan `rounding_increment` outlet; berlaku untuk semua metode pembayaran |
| Midtrans | Provider payment gateway (Phase 2) |
| Gateway transaction | Satu percobaan pembayaran lewat Midtrans untuk sebuah order; status `PENDING`, `SETTLED`, `FAILED`, `EXPIRED`, `CANCELLED`, `CHALLENGE` |
| Provider order id | `order_id` yang dikirim ke Midtrans: `{order_number}-{attempt_no}`, unik per percobaan, maks 50 karakter |
| Webhook (HTTP notification) | Notifikasi status transaksi dari Midtrans ke endpoint kami; dapat dikirim berulang |
| Settlement | Status Midtrans yang menandakan dana diterima; satu-satunya pemicu order dibayar untuk pembayaran gateway |
| Server key | Kunci rahasia Midtrans; disimpan terenkripsi, tidak pernah ditampilkan ulang atau dicatat di log |

### 1.4 Konvensi Respons
`401` belum login · `403` tanpa permission/outlet · `404` data bukan milik tenant (tidak membocorkan keberadaan) · `409` konflik state · `422` validasi · `423` akun terkunci · `502` gateway tidak dapat dihubungkan. Uang = bilangan bulat rupiah. Qty stok = `DECIMAL(18,4)`. Waktu = UTC di DB, ditampilkan menurut timezone outlet.

---


### 1.5 Penanda Rilis
`[R1.1]` = spesifikasi sudah final tetapi dikerjakan di rilis 1.1 (Januari 2027). `[P2]` = Phase 2. Tanpa penanda = rilis core (30 Des 2026).

| FR / bagian | Rilis | Catatan |
|-------------|-------|---------|
| FR-REC-002 (recipe per modifier option) | R1.1 | Sampai rilis ini bahan modifier tidak memotong stok |
| FR-INV-006 (opname) | R1.1 | Tabel opname dibuat di tiket R1.1 |
| FR-KIT-001 (antrian dapur) | R1.1 | `kitchen_status` tetap terisi `NEW` saat bayar |
| FR-AUD-002 (baca audit log) | R1.1 | Pencatatan (FR-AUD-001) tetap core |
| FR-RPT-002, FR-RPT-004, FR-RPT-005 | R1.1 | Core: FR-RPT-001 (sales), FR-RPT-003 (payment) |
| FR-RPT-006 (export CSV) | R1.1 | |
| FR-PUR-005 (cancel, partial receive) | R1.1 | Core: FR-PUR-001…004 |
| FR-BIZ-004, FR-PAY-003…005, FR-RFD-002, FR-RPT-007 | P2 | Midtrans |

---

## 2. Kebutuhan Fungsional

### Modul AUTH — Autentikasi

#### FR-AUTH-001: Register Business
**Deskripsi:** Calon pemilik membuat business, outlet default, dan akun Owner dalam satu proses atomik.
**Input:** nama business (wajib, ≤ 100), nama outlet (wajib, ≤ 100), kode outlet (wajib, 2–6 huruf/angka, unik per business), timezone (wajib, default `Asia/Jakarta`), nama owner (wajib, ≤ 100), email (wajib), password (wajib).
**Aturan:** membuat `businesses`, `outlets`, `users` (role OWNER), `user_outlets` dalam satu transaksi. Audit `BUSINESS_REGISTERED` (BR-022).
| Kondisi | Pesan Error |
|---------|-------------|
| Email sudah terdaftar | "Email sudah digunakan, silakan gunakan email lain." |
| Password < 8 karakter atau tanpa huruf besar dan angka | "Password minimal 8 karakter, mengandung huruf besar dan angka." |
| Konfirmasi password tidak sama | "Konfirmasi password tidak cocok." |
| Kode outlet tidak valid | "Kode outlet 2–6 karakter huruf atau angka." |
**Output:** `201` business + user; gagal: `422` per field.

#### FR-AUTH-002: Login & Logout
**Deskripsi:** User masuk dan keluar dengan sesi Sanctum SPA.
**Input:** email, password.
**Aturan:** gagal 5× berturut-turut → `locked_until = now + 15 menit` (BR-023). Login sukses mereset hitungan, menyimpan `last_login_at`. User INACTIVE tidak bisa login. Login sukses **dan gagal** tercatat di audit (`LOGIN`, `LOGIN_FAILED`). Sesi berakhir setelah 120 menit tidak aktif `[ASUMSI]`.
| Kondisi | Pesan Error |
|---------|-------------|
| Email tidak ada atau password salah | "Email atau password salah." (`422`) |
| Akun terkunci | "Akun terkunci sementara. Coba lagi dalam 15 menit." (`423`) |
| Akun nonaktif | "Akun nonaktif. Hubungi pemilik." (`403`) |
**Output:** `200` + cookie sesi; logout `204`.

#### FR-AUTH-003: Profil & Ganti Password
**Input:** nama, password lama, password baru.
**Aturan:** password baru wajib sesuai FR-AUTH-001; mengganti password mengakhiri sesi lain milik user.
| Kondisi | Pesan Error |
|---------|-------------|
| Password lama salah | "Password lama tidak sesuai." |

#### FR-AUTH-004: Konteks Outlet
**Deskripsi:** `GET /me` mengembalikan user, role, permission, dan outlet yang boleh diakses. Semua endpoint outlet-scoped menerima header `X-Outlet-Id`.
**Aturan:** `X-Outlet-Id` yang tidak ada di `user_outlets` user → `403`. Owner otomatis punya akses semua outlet business.

---

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

### Modul USER — User & RBAC

#### FR-USER-001: CRUD User
**Input:** nama, email (unik global di seluruh sistem), password awal, role (satu), daftar outlet (≥ 1), status.
**Aturan:** satu user satu role yang berlaku di semua outlet yang ia akses. Manager hanya boleh membuat/mengubah CASHIER, INVENTORY, KITCHEN di outlet yang ia akses, tidak boleh menyentuh Owner atau Manager lain dan tidak mengubah permission (BR-024). Menonaktifkan user mengakhiri sesinya. Tidak ada hard delete (BR-018). Audit `USER_CREATED`, `USER_UPDATED`, `ROLE_CHANGED`. Owner terakhir tidak boleh dinonaktifkan atau diganti role-nya.
| Kondisi | Pesan Error |
|---------|-------------|
| Role target di luar hak Manager | "Anda tidak dapat mengelola role ini." (`403`) |
| Outlet target di luar akses pembuat | "Outlet tidak termasuk akses Anda." (`403`) |
| Menonaktifkan Owner terakhir | "Business harus memiliki minimal satu Owner aktif." |

#### FR-USER-002: Penegakan Akses
**Aturan:** setiap endpoint melewati rantai `auth → outlet context → permission → [shift OPEN] → tenant scope otomatis`. Semua tabel tenant memiliki `business_id` dengan global scope (BR-022). Data business lain → `404`. Pengecekan di backend, bukan hanya UI.

---

### Modul CAT — Katalog

#### FR-CAT-001: Category
**Input:** nama (wajib, ≤ 100, unik per business), deskripsi, status, sort order.
**Aturan:** category INACTIVE menyembunyikan produknya dari POS. Soft delete (BR-018).

#### FR-CAT-002: Product
**Input:** category (wajib), SKU (wajib, ≤ 50, unik per business), nama (wajib, ≤ 150), deskripsi, harga jual (wajib, bilangan bulat ≥ 0), status, gambar (jpg/png/webp, ≤ 2 MB).
**Aturan:** produk INACTIVE tidak muncul di POS dan tidak bisa ditambahkan ke order baru (BR-029). Tidak ada kolom harga pokok yang diinput; HPP hasil hitungan (FR-REC-003). Perubahan harga → audit `PRODUCT_PRICE_CHANGED`. Harga sama untuk semua outlet.
| Kondisi | Pesan Error |
|---------|-------------|
| SKU sudah ada | "SKU sudah digunakan." |
| Harga negatif atau bukan angka bulat | "Harga harus bilangan bulat ≥ 0." |
| Gambar > 2 MB atau format salah | "Gambar maksimal 2 MB (jpg, png, webp)." |

#### FR-CAT-003: Modifier
**Input group:** nama, tipe (`SINGLE`/`MULTI`), wajib (bool), `min_select`, `max_select`. **Option:** nama, `price_delta` (bilangan bulat ≥ 0 `[ASUMSI]`), status. Group dihubungkan ke produk.
**Aturan:** group wajib → kasir harus memilih sesuai `min_select`/`max_select` (BR-030). `SINGLE` → maks 1 pilihan. Option INACTIVE tidak bisa dipilih.
| Kondisi | Pesan Error |
|---------|-------------|
| `min_select` > `max_select` | "Minimal pilihan tidak boleh melebihi maksimal." |
| Group wajib tidak dipilih saat order | "Pilih opsi untuk {nama group}." |

#### FR-CAT-004: Katalog POS
`GET /pos/catalog` mengembalikan kategori + produk ACTIVE + modifier. Di-cache, dibatalkan saat katalog berubah. Respons memuat harga jual, tanpa HPP.

---

### Modul ING — Ingredient

#### FR-ING-001: Ingredient & Unit
**Input:** SKU (wajib, unik per business), nama, base unit (`g`/`ml`/`pcs`, wajib dan tidak dapat diubah setelah ada movement), `last_cost` (rupiah per base unit, `DECIMAL(18,4)` ≥ 0), minimum stock (≥ 0), status.
**Aturan:** CRUD ingredient memakai permission `inventory.adjust` (Owner, Manager, Inventory); membaca memakai `inventory.view`. `last_cost` dapat diinput manual dan diperbarui otomatis saat purchase diterima (FR-PUR-003, BR-047). Perubahan `last_cost` tidak mengubah movement dan order lama (snapshot). `ingredient_unit_conversions` dipakai oleh purchase (BR-046).
| Kondisi | Pesan Error |
|---------|-------------|
| SKU ganda | "SKU bahan sudah digunakan." |
| Ubah base unit bahan yang sudah punya movement | "Satuan dasar tidak dapat diubah karena stok sudah tercatat." |
| `last_cost` negatif | "Biaya per satuan tidak boleh negatif." |

---

### Modul REC — Recipe

#### FR-REC-001: Recipe per Product (versi)
**Input:** daftar item (ingredient, qty > 0 dalam base unit).
**Aturan:** setiap simpan membuat **versi baru** (`version + 1`) dan menonaktifkan versi lama; hanya 1 versi aktif per produk (BR-013). Hanya `recipes.manage` (Owner, Manager) (BR-032). Ingredient harus milik business yang sama. Audit `RECIPE_UPDATED` (versi lama, baru). Satu bahan tidak boleh muncul dua kali di satu recipe. Role dengan `recipes.view` (Inventory, Kitchen) hanya membaca recipe **tanpa HPP**.
| Kondisi | Pesan Error |
|---------|-------------|
| Qty ≤ 0 | "Jumlah bahan harus lebih dari 0." |
| Bahan ganda | "Bahan {nama} sudah ada di recipe." |
| Recipe kosong | "Recipe minimal memiliki satu bahan." |

#### FR-REC-002: Recipe per Modifier Option (F-18) `[R1.1]`
**Input:** daftar (ingredient, qty > 0 base unit) per modifier option.
**Aturan:** pemakaian ditambahkan ke agregat bahan saat order dibayar (FR-ENG-001). Tidak berversi di Phase 1; perubahan dicatat audit dan hanya berlaku untuk order baru karena item order menyimpan modifier yang dipilih, sedangkan pemakaian modifier dihitung dari recipe aktif **saat pembayaran** `[ASUMSI]` 🔶 (lihat §9, R-01).

#### FR-REC-003: Kalkulator HPP
**Aturan:** HPP per unit produk = Σ(qty bahan × `last_cost`) dari recipe aktif, dibulatkan half-up ke rupiah bulat; HPP modifier = Σ dari recipe option terpilih. Produk tanpa recipe memiliki HPP 0 dan ditandai "no recipe" di daftar produk (laporan khusus no recipe di R1.1). Nilai ditampilkan di form recipe hanya untuk Owner dan Manager.

---

### Modul SHF — Shift & Kas

#### FR-SHF-001: Open Shift
**Input:** opening cash (bilangan bulat ≥ 0).
**Aturan:** satu kasir hanya boleh satu shift OPEN per outlet (BR-002), dijamin constraint DB. `business_date` = tanggal saat dibuka menurut timezone outlet (BR-027). Beberapa kasir boleh punya shift OPEN paralel di outlet yang sama. Audit `SHIFT_OPENED`.
| Kondisi | Pesan Error |
|---------|-------------|
| Sudah ada shift OPEN | "Anda masih memiliki shift yang terbuka." (`409`) |
| Opening cash negatif | "Kas awal tidak boleh negatif." |

#### FR-SHF-002: Cash In / Cash Out
**Input:** tipe (`IN`/`OUT`), nominal (> 0), alasan (wajib).
**Aturan:** butuh `cash.manage` dan shift OPEN milik user. `cash_movements` append-only. Audit.
| Kondisi | Pesan Error |
|---------|-------------|
| Alasan kosong | "Alasan wajib diisi." |
| Tidak ada shift OPEN | "Buka shift terlebih dahulu." (`409`) |

#### FR-SHF-003: Ringkasan & Close Shift
**Input:** actual cash (≥ 0).
**Aturan:** Expected Cash = opening + cash sales (bersih kembalian) + cash in − cash out − cash refund; selisih = actual − expected (BR-004). Ditolak jika masih ada order DRAFT/PENDING_PAYMENT di shift itu (BR-003). Setelah CLOSED tidak ada transaksi baru ke shift tersebut. Pembayaran gateway (non-tunai) tidak masuk Expected Cash dan ditampilkan di `non_cash` per metode (BR-042). Audit `SHIFT_CLOSED` memuat selisih.
| Kondisi | Pesan Error |
|---------|-------------|
| Ada order DRAFT/PENDING_PAYMENT | "Selesaikan atau batalkan {n} order sebelum menutup shift." (`409`) |
| Shift sudah CLOSED | "Shift sudah ditutup." (`409`) |

#### FR-SHF-004: Shift Saat Ini & Peringatan
`GET /shifts/current` mengembalikan shift OPEN user + `open_hours_exceeded` (true jika terbuka > 24 jam, BR-026). Sistem tidak menutup shift otomatis. Pembuatan order memakai guard `shift.open` (BR-001). Pengguna dengan `shifts.view_all` dapat melihat semua shift outlet.

---

### Modul ORD — Order

#### FR-ORD-001: Buat & Ubah Draft
**Input:** `order_type` (`DINE_IN`/`TAKEAWAY`), nomor meja (opsional, hanya Dine In, ≤ 20), item (produk, qty integer 1–999 `[ASUMSI]`, modifier terpilih, catatan ≤ 255), catatan order.
**Aturan:** wajib shift OPEN milik kasir (BR-001). Tiap item menyimpan snapshot nama, SKU, harga, HPP, `recipe_id` aktif (BR-014). Modifier menyimpan nama dan `price_delta` saat itu. Total dihitung ulang tiap perubahan (FR-ORD-002). Selama DRAFT, perubahan item dilakukan dengan mengganti seluruh daftar item (`PUT /orders/{id}`); item yang tidak berubah mempertahankan snapshot lama, item baru mengambil snapshot saat ditambahkan.
| Kondisi | Pesan Error |
|---------|-------------|
| Tidak ada shift OPEN | "Buka shift terlebih dahulu." (`409`) |
| Produk INACTIVE / bukan milik tenant | "Produk tidak tersedia." (`422`) |
| Qty di luar 1–999 | "Jumlah harus antara 1 dan 999." |
| Modifier group wajib kosong atau di luar min/max | "Pilih opsi untuk {nama group}." |
| Nomor meja pada order Takeaway | "Nomor meja hanya untuk Dine In." |

#### FR-ORD-002: Kalkulasi Harga
**Aturan (BR-005):**
```
line_total   = (harga + Σ price_delta modifier) × qty
subtotal     = Σ line_total
diskon       = order-level (persen atau nominal), hasil dibulatkan half-up ke rupiah
dpp          = subtotal − diskon
service      = round_half_up(dpp × service_rate / 100)
pajak        = round_half_up((dpp + service) × tax_rate / 100)
sebelum      = dpp + service + pajak
rounding_amount = lihat BR-036 (di bawah)
total        = sebelum + rounding_amount
```
Pajak exclusive saja. Tarif disalin ke order (`tax_rate_snapshot`, `service_rate_snapshot`) saat order dibuat.

**Pembulatan total (BR-036):** langkah terakhir perhitungan, berlaku untuk **semua metode pembayaran**:
```
increment = outlets.rounding_increment   (0 = nonaktif)
rounded   = round_half_up(sebelum / increment) × increment
IF sebelum > 0 AND rounded = 0 THEN rounded = increment
rounding_amount = rounded − sebelum      (boleh negatif)
total     = rounded
```
Contoh (increment 100): sebelum 24.350 → total 24.400 (rounding +50); sebelum 24.340 → total 24.300 (rounding −40). `orders.rounding_amount` menyimpan selisih; `orders.total` adalah angka yang dibayar, di-refund, dan dilaporkan.

#### FR-ORD-003: Diskon Order
**Input:** tipe (`PERCENT`/`AMOUNT`), nilai.
**Aturan:** hanya level order (BR-006). Butuh `discounts.apply`. Persen 0–100; nominal ≤ subtotal. Untuk Cashier, diskon efektif (% dari subtotal) tidak melebihi `cashier_max_discount_percent` outlet; Manager dan Owner tanpa batas. Audit `DISCOUNT_APPLIED`. Hanya pada status DRAFT.
| Kondisi | Pesan Error |
|---------|-------------|
| Persen > 100 atau nominal > subtotal | "Diskon melebihi nilai order." |
| Cashier melebihi batas | "Diskon melebihi batas {x}% untuk kasir." (`403`) |

#### FR-ORD-004: Konfirmasi & Edit Ulang
**Aturan:** `DRAFT → PENDING_PAYMENT` mengunci item dan memvalidasi ulang produk/modifier masih ACTIVE (BR-029). `PENDING_PAYMENT → DRAFT` diizinkan sebelum ada pembayaran sukses (BR-028) dan selama tidak ada transaksi gateway `PENDING` (BR-038). Order kosong tidak bisa dikonfirmasi.
| Kondisi | Pesan Error |
|---------|-------------|
| Order tanpa item | "Tambahkan minimal satu item." |
| Status tidak valid untuk transisi | "Transisi status tidak valid." (`409`) |
| Ada transaksi gateway PENDING [P2] | "Batalkan pembayaran QRIS terlebih dahulu." (`409`) |

#### FR-ORD-005: Cancel Order
**Input:** alasan (wajib).
**Aturan:** hanya DRAFT dan PENDING_PAYMENT (BR-025). Order PAID/COMPLETED hanya lewat refund. Cashier hanya order miliknya di shift yang masih OPEN; Manager/Owner semua order outlet. Tidak ada movement stok (stok belum dipotong). Order dengan transaksi gateway `PENDING` tidak dapat dibatalkan sebelum transaksinya dibatalkan (BR-038). Audit `ORDER_CANCELLED`.
| Kondisi | Pesan Error |
|---------|-------------|
| Order sudah dibayar | "Order yang sudah dibayar harus melalui refund." (`409`) |
| Cashier membatalkan order orang lain | `403` |
| Ada transaksi gateway PENDING [P2] | "Batalkan pembayaran QRIS terlebih dahulu." (`409`) |
| Alasan kosong | "Alasan wajib diisi." |

#### FR-ORD-006: Riwayat & Detail
Cashier hanya melihat order miliknya (`sales.view` = Own); Manager/Owner semua order di outlet yang diakses. Filter: tanggal (business date), status, kasir, nomor order. Paginasi.

#### FR-ORD-007: Penomoran Order
Format `{KODE_OUTLET}-{YYMMDD}-{urut 4 digit}`, `YYMMDD` dari business date, urutan reset per outlet per hari, dijamin unik walau order dibuat bersamaan (BR-019).

---

### Modul PAY — Pembayaran & Struk

#### FR-PAY-001: Bayar Order
**Input:** metode (`CASH`/`QRIS`), `tendered_amount` (Cash), `reference_no` (QRIS, opsional ≤ 50), header `Idempotency-Key` (wajib, UUID).
**Aturan:**
- Hanya order PENDING_PAYMENT; butuh `sales.pay` dan shift OPEN milik kasir.
- Cash: `tendered ≥ total` (total sudah termasuk pembulatan), `change = tendered − total`. QRIS: nominal = total; kasir mengonfirmasi pembayaran diterima. Tanpa split payment (BR-007).
- Key yang sudah dipakai mengembalikan hasil sebelumnya tanpa memproses ulang (BR-008).
- Dalam **satu transaksi**: kunci order → buat payment → order `PAID` lalu `COMPLETED` (BR-021) → potong stok (FR-ENG-001) → set `kitchen_status = NEW`. Gagal di langkah mana pun → rollback semuanya.
- Audit `PAYMENT_CREATED` setelah commit.
| Kondisi | Pesan Error |
|---------|-------------|
| Order bukan PENDING_PAYMENT | "Order tidak dapat dibayar." (`409`) |
| Cash `tendered` < total | "Uang diterima kurang dari total." (`422`) |
| QRIS dengan nominal ≠ total | "Nominal QRIS harus sama dengan total." (`422`) |
| Metode CARD/GATEWAY | "Metode pembayaran belum tersedia." (`422`) |
| `Idempotency-Key` hilang | "Idempotency-Key wajib." (`422`) |
| `Idempotency-Key` sudah dipakai untuk order lain | "Idempotency-Key sudah dipakai untuk order lain." (`409`) |
| Stok kurang dan `allow_negative_stock` = false | "Stok tidak cukup: {daftar bahan}." (`422`) |
**Output:** `200` order COMPLETED, `change_amount`, `stock_shortage` (daftar bahan yang menjadi minus, jika ada).
**Catatan:** endpoint ini hanya untuk **Cash dan QRIS manual**. Pembayaran lewat Midtrans memakai FR-PAY-003 s.d. FR-PAY-005 [P2]; `CARD` tetap tidak tersedia.

#### FR-PAY-002: Struk
Data struk: header outlet, nomor order, tanggal (timezone outlet), kasir, tipe order (+meja), item + modifier, subtotal, diskon, service, pajak, pembulatan (bila ada), total, metode, tunai/kembalian, footer. Tampil di layar dengan CSS print 58/80 mm (browser print). Dapat dicetak ulang dari riwayat order. Untuk pembayaran gateway, struk menampilkan tipe pembayaran dan 6 karakter terakhir id transaksi Midtrans. Kirim struk ke WhatsApp/email = Phase 2.

#### FR-PAY-003: Mulai Pembayaran Midtrans [P2]
**Input:** `payment_type` (salah satu `enabled_payment_types`; pilot: `qris`).
**Aturan:**
1. Order `PENDING_PAYMENT`; butuh `sales.pay` dan shift OPEN milik kasir; konfigurasi Midtrans aktif untuk outlet (override outlet, jika tidak ada pakai business).
2. Bila `allow_negative_stock = false`, stok dicek lebih dulu (tanpa memotong); kurang → ditolak sebelum QR dibuat (BR-041).
3. Bila order sudah punya transaksi `PENDING` yang belum kedaluwarsa dengan tipe sama, kembalikan transaksi itu (klik ganda aman); tipe berbeda → `409` (BR-038).
4. Buat transaksi `PENDING` (`attempt_no` berikutnya, `provider_order_id = {order_number}-{attempt_no}`, `gross_amount = orders.total`, `expires_at = now + expiry_minutes`), commit, **lalu** panggil Midtrans (timeout 10 detik). Rincian item dikirim sehingga jumlahnya sama dengan `gross_amount` (item, diskon sebagai nilai negatif, service, pajak, pembulatan).
5. Midtrans gagal atau menolak → transaksi `FAILED`, order tetap `PENDING_PAYMENT`.
6. Audit `GATEWAY_PAYMENT_INITIATED`.
| Kondisi | Pesan Error |
|---------|-------------|
| Order bukan PENDING_PAYMENT | "Order tidak dapat dibayar." (`409`) |
| Tidak ada shift OPEN | "Buka shift terlebih dahulu." (`409`) |
| Provider belum dikonfigurasi atau nonaktif | "Pembayaran QRIS Midtrans belum diaktifkan di outlet ini." (`422` `GATEWAY_NOT_CONFIGURED`) |
| Tipe tidak diaktifkan | "Metode pembayaran belum tersedia." (`422`) |
| Stok kurang dan minus dilarang | "Stok tidak cukup: {daftar bahan}." (`422`) |
| Transaksi PENDING dengan tipe lain | "Batalkan pembayaran yang sedang berjalan terlebih dahulu." (`409`) |
| Midtrans tidak merespons atau menolak | "Layanan pembayaran sedang tidak tersedia. Coba lagi atau gunakan tunai." (`502` `GATEWAY_UNAVAILABLE`) |
**Output:** `201` transaksi (`id`, `status`, `qr_string`/`redirect_url`, `expires_at`, `provider_order_id`).

#### FR-PAY-004: Webhook Midtrans [P2]
**Endpoint:** publik (tanpa sesi/CSRF), throttle, hanya HTTPS.
**Aturan:**
1. Hitung signature = `SHA512(order_id + status_code + gross_amount + server_key)` dengan server key milik konfigurasi transaksi terkait; tidak cocok → simpan event `signature_valid = false`, balas `403`, **tidak diproses** (BR-040).
2. Hitung `event_key`; sudah ada → balas `200` tanpa memproses ulang (BR-040).
3. Simpan event (`RECEIVED`), antrekan job, balas `200` (target p95 < 300 ms, NFR-PERF-007).
4. Job: cari transaksi lewat `provider_order_id` (tak dikenal → event `IGNORED`); **verifikasi status ke Midtrans** (Get Status) dan pakai hasilnya, bukan isi webhook saja.
5. `settlement` (atau `capture` dengan `fraud_status = accept`): dalam **satu transaksi DB** kunci order lalu cek (a) order masih `PENDING_PAYMENT`, (b) `gross_amount` = `orders.total`. Lolos → transaksi `SETTLED`, buat `payments` `SUCCESS` (`method` `QRIS`/`GATEWAY`, `provider = MIDTRANS`, `shift_id` = shift order, `created_by` NULL, `idempotency_key = transaction_id`), order `PAID → COMPLETED`, `kitchen_status = NEW`, potong stok **tanpa menolak** bila minus (BR-041). Setelah commit: audit `GATEWAY_PAYMENT_SETTLED` (+ `GATEWAY_STOCK_OVERRIDE` bila ada kekurangan), event `SaleCompleted`.
6. Tidak lolos (a) atau (b): order **tidak berubah**, transaksi tetap tercatat, event `FAILED` dengan alasan `ORDER_NOT_PAYABLE` atau `AMOUNT_MISMATCH`; muncul di FR-RPT-007 (BR-039).
7. `deny`/`failure` → `FAILED`; `cancel` → `CANCELLED`; `expire` → `EXPIRED`; `capture` + `challenge` → `CHALLENGE`; `refund`/`partial_refund`/`chargeback` hanya dicatat. Order tetap `PENDING_PAYMENT`. Audit `GATEWAY_PAYMENT_FAILED`.
8. Kegagalan pemrosesan diulang otomatis (3 kali, backoff); setelah itu event `FAILED` dan tampil di FR-RPT-007.

#### FR-PAY-005: Status, Pembatalan, dan Kedaluwarsa [P2]
**Aturan:**
- `GET` status transaksi (butuh `sales.pay` dan order milik kasir, atau `sales.view` penuh): FE polling tiap 3 detik selama dialog QR terbuka sampai status final atau `expires_at`.
- Batalkan transaksi `PENDING` (kasir pemilik order, Manager, Owner): panggil API cancel Midtrans; sukses → `CANCELLED` dan audit `GATEWAY_PAYMENT_CANCELLED`; gagal → tetap `PENDING`. Order baru dapat dibuka ulang, dibatalkan, atau dibayar tunai setelah transaksi tidak lagi `PENDING` (BR-038).
- Penyapu terjadwal tiap menit: transaksi `PENDING` yang lewat `expires_at` dicek ke Midtrans; `settlement` diproses seperti FR-PAY-004, selain itu `EXPIRED` (BR-044).
| Kondisi | Pesan Error |
|---------|-------------|
| Transaksi sudah final | "Transaksi sudah selesai." (`409`) |
| Cancel ditolak Midtrans karena sudah dibayar | "Pembayaran sudah diterima. Menunggu konfirmasi." (`409`); settlement diproses oleh FR-PAY-004 |

---

### Modul ENG — Core Engine: Sale → Inventory

#### FR-ENG-001: Potong Stok Saat Pembayaran
**Aturan (BR-009, BR-010):**
1. Hitung kebutuhan: tiap item × recipe (`recipe_id` di snapshot) + recipe modifier option terpilih × qty item.
2. Agregat per ingredient untuk seluruh order.
3. Urutkan by `ingredient_id`, kunci balance (`FOR UPDATE`), lalu insert 1 movement `SALE` per ingredient (delta negatif, `unit_cost = last_cost` saat kejadian) dan update balance.
4. Produk tanpa recipe dilewati dan tampil di laporan "no recipe".
5. Idempoten: unique `(outlet_id, ingredient_id, type, reference_type, reference_id)`.

#### FR-ENG-002: Kebijakan Stok Minus
`allow_negative_stock = true` (default): pembayaran tetap sukses walau stok menjadi minus; respons memuat `stock_shortage`, audit mencatatnya, dan stok minus tampil di laporan inventory. `false`: seluruh pembayaran ditolak dan di-rollback dengan daftar bahan kurang (BR-011).

#### FR-ENG-003: Stock Trace
`GET /orders/:id/stock-trace` menampilkan movement yang dihasilkan sebuah order (termasuk `RETURN` bila ada refund). Butuh `inventory.view`.

---

### Modul RFD — Refund

#### FR-RFD-001: Refund Penuh
**Input:** alasan (wajib), `restock` (bool, default `false`).
**Aturan (BR-012, BR-035):**
- Order harus PAID atau COMPLETED; satu refund per order; seluruh nilai order (`total`, termasuk pembulatan). Butuh `sales.refund`.
- Metode refund mengikuti metode pembayaran asli.
- **Tunai:** dicatat sebagai kas keluar di shift OPEN milik **pelaku refund**, bukan shift asal order; ditolak jika pelaku tidak punya shift OPEN (`409`).
- **QRIS:** hanya dicatat di sistem; pengembalian dana manual di luar sistem; tidak butuh shift (`refunds.shift_id` nullable).
- `restock = true` → movement `RETURN` (positif) per ingredient dari movement `SALE` order tersebut, dalam transaksi yang sama.
- Order → `REFUNDED`. Audit `REFUND_CREATED`.
| Kondisi | Pesan Error |
|---------|-------------|
| Order bukan PAID/COMPLETED | "Order tidak dapat direfund." (`409`) |
| Sudah pernah direfund | "Order sudah direfund." (`409`) |
| Refund tunai tanpa shift OPEN | "Buka shift terlebih dahulu untuk refund tunai." (`409`) |
| Alasan kosong | "Alasan wajib diisi." |

#### FR-RFD-002: Refund Pembayaran Gateway [P2]
**Input:** sama dengan FR-RFD-001.
**Aturan (BR-043):** berlaku untuk order yang dibayar lewat Midtrans. Sistem membuat `gateway_refund_key` unik, memanggil API refund Midtrans, dan hanya bila diterima: refund tercatat (`gateway_status` `PENDING`/`SUCCESS`), order `REFUNDED`, restock bila dipilih (satu transaksi DB). Refund ditolak atau tipe pembayaran tidak mendukung refund via API (`[ASUMSI]` tergantung tipe; verifikasi ke Midtrans) → `422 GATEWAY_REFUND_FAILED`; Manager/Owner dapat mengulang dengan **refund manual** (`gateway_refund_key` NULL, dana dikembalikan di luar sistem, seperti QRIS manual). `shift_id` NULL (non-tunai). Audit `GATEWAY_REFUND`. Key refund yang sama tidak diproses dua kali.
| Kondisi | Pesan Error |
|---------|-------------|
| Midtrans menolak refund | "Refund ke Midtrans gagal. Anda dapat mencatat refund manual." (`422`) |
| Midtrans tidak merespons | "Layanan pembayaran sedang tidak tersedia." (`502`) |

---

### Modul INV — Inventory

#### FR-INV-001: Opening Stock
**Input:** per bahan: qty > 0 (base unit).
**Aturan:** membuat movement `OPENING` dan balance. `reference_id` berisi id batch agar idempotensi unik (BR-009). Butuh `inventory.adjust`. Audit.

#### FR-INV-002: Stok & Riwayat Movement
Daftar stok per outlet (qty, minimum, status low) dan riwayat movement (filter ingredient, tipe, tanggal; paginasi). Movement tidak punya endpoint ubah/hapus (BR-020).

#### FR-INV-003: Stock Adjustment
**Input:** ingredient, jenis (`TAMBAH`/`KURANGI`), qty (> 0), alasan (wajib).
**Aturan:** menyimpan before/after, user, waktu (BR-016). Movement `ADJUSTMENT` bertanda sesuai jenis. Berlaku `allow_negative_stock`. Butuh `inventory.adjust`. Audit `STOCK_ADJUSTMENT`.
| Kondisi | Pesan Error |
|---------|-------------|
| Qty ≤ 0 | "Jumlah harus lebih dari 0." |
| Alasan kosong | "Alasan wajib diisi." |
| Hasil minus dan minus dilarang | "Stok tidak mencukupi untuk pengurangan ini." |

#### FR-INV-004: Waste
**Input:** ingredient, qty (> 0), alasan enum (`EXPIRED`, `SPOILED`, `SPILLED`, `PRODUCTION_ERROR`, `OTHER`), catatan (wajib bila `OTHER`).
**Aturan:** selalu mengurangi stok (BR-016). Biaya = qty × `last_cost` saat kejadian, disimpan sebagai snapshot. Butuh `inventory.waste`. Kitchen tidak melihat nilai biaya (BR-034). Audit `WASTE`.

#### FR-INV-005: Low Stock
Bahan dengan `quantity ≤ minimum` (override per outlet bila ada, jika tidak memakai default ingredient) tampil sebagai low stock, termasuk di dashboard. Stok minus tampil terpisah dan menonjol.

#### FR-INV-006: Stock Opname (F-13) `[R1.1]`
**Alur:** mulai → snapshot stok sistem semua bahan aktif → input hitungan fisik → review selisih → submit atau batalkan.
**Aturan (BR-017):** maks 1 opname `IN_PROGRESS` per outlet. Selisih = hitungan − snapshot. Saat submit, selisih diterapkan ke stok live lewat movement `STOCK_OPNAME` (urut `ingredient_id`), sehingga penjualan selama penghitungan tidak hilang. Butuh `inventory.count`. Audit ringkasan selisih.
| Kondisi | Pesan Error |
|---------|-------------|
| Sudah ada opname IN_PROGRESS | "Masih ada opname yang berjalan di outlet ini." (`409`) |
| Hitungan negatif | "Hitungan tidak boleh negatif." |
| Submit opname yang bukan IN_PROGRESS | "Opname sudah selesai atau dibatalkan." (`409`) |

---

### Modul PUR — Supplier & Purchasing (F-20)

#### FR-PUR-001: Supplier
**Input:** nama (wajib, ≤ 150, unik per business tanpa membedakan huruf besar/kecil), kontak (≤ 100), telepon (≤ 30), email, alamat (≤ 255), status.
**Aturan:** permission `suppliers.*` (Owner, Manager, Inventory). Supplier INACTIVE tidak bisa dipilih untuk purchase baru; supplier yang pernah dipakai tidak dihapus permanen (BR-018, BR-048).
| Kondisi | Pesan Error |
|---------|-------------|
| Nama supplier sudah ada | "Nama supplier sudah digunakan." |
| Nama kosong | "Nama supplier wajib diisi." |
| Email tidak valid | "Format email tidak valid." |

#### FR-PUR-002: Buat & Ubah Purchase (DRAFT)
**Input:** supplier (wajib, ACTIVE), nomor invoice supplier (opsional, ≤ 50), catatan, item: bahan (wajib), satuan beli (`g`, `kg`, `ml`, `L`, `pcs`), qty (> 0, maks 4 desimal), harga per satuan beli (rupiah bulat ≥ 0).
**Aturan:** butuh `purchases.create`. Satuan beli harus punya faktor konversi ke base unit bahan (BR-046); sistem menghitung `quantity_base`, `unit_cost_base`, dan `line_total` serta menampilkannya sebelum simpan. Satu bahan hanya sekali per purchase. Nomor `PO-{KODE_OUTLET}-{YYMMDD}-{urut 3 digit}` (BR-049). Hanya status DRAFT yang dapat diubah (BR-048).
| Kondisi | Pesan Error |
|---------|-------------|
| Supplier INACTIVE atau bukan milik tenant | "Supplier tidak tersedia." |
| Item kosong | "Tambahkan minimal satu item." |
| Qty ≤ 0 | "Jumlah harus lebih dari 0." |
| Harga negatif atau bukan bilangan bulat | "Harga harus bilangan bulat ≥ 0." |
| Satuan beli tidak punya konversi untuk bahan | "Satuan tidak dapat dikonversi untuk bahan ini." |
| Bahan ganda | "Bahan {nama} sudah ada di purchase." |
| Purchase bukan DRAFT | "Purchase sudah diterima dan tidak dapat diubah." (`409`) |

#### FR-PUR-003: Terima Purchase
**Input:** — (opsional `note`).
**Aturan (BR-045, BR-047, BR-050):** butuh `purchases.receive`. Dalam **satu transaksi**: kunci purchase → pastikan DRAFT → untuk tiap bahan berurutan `ingredient_id`: `StockService::apply(PURCHASE, +quantity_base, PURCHASE:{id}, unit_cost = unit_cost_base)` → perbarui `ingredients.last_cost = unit_cost_base` → purchase `RECEIVED` (`received_by`, `received_at`). Gagal di langkah mana pun → rollback total. Penerimaan kedua tidak menambah stok. Tidak tunduk pada `allow_negative_stock`. Audit `PURCHASE_RECEIVED` memuat last_cost sebelum/sesudah per bahan.
| Kondisi | Pesan Error |
|---------|-------------|
| Purchase bukan DRAFT | "Purchase tidak dapat diterima." (`409` `INVALID_STATE_TRANSITION`) |
| Outlet tidak aktif | "Outlet tidak aktif." |
| Tanpa permission | `403` |
**Output:** `200` purchase RECEIVED + ringkasan `items[{ingredient_id, quantity_after, last_cost_before, last_cost_after}]`.

#### FR-PUR-004: Daftar & Detail Purchase
Filter status, supplier, tanggal; paginasi. Butuh `purchases.view`. Data tenant lain `404`.

#### FR-PUR-005: Cancel & Partial Receive `[R1.1]`
**Aturan:** cancel hanya DRAFT dengan alasan wajib (`purchases.cancel`), tanpa movement. Partial receive menerima sebagian qty per item lewat `purchase_receipts`; status tetap DRAFT sampai seluruh qty diterima. Detail ditulis menjelang R1.1.

---

### Modul KIT — Dapur

#### FR-KIT-001: Antrian Dapur (F-14) `[R1.1]`
Daftar order COMPLETED dengan `kitchen_status = NEW` di outlet aktif: nomor order, tipe + meja, item + modifier + qty, catatan. **Tanpa harga dan data pembayaran** (BR-033). Tombol "Siap" mengubah menjadi `READY`. Polling 10 detik `[ASUMSI]`, tanpa realtime di Phase 1. Permission `orders.kitchen_view`, `orders.kitchen_update`.

---

### Modul RPT — Laporan & Dashboard

Semua laporan: filter per outlet harus subset outlet user; periode memakai **business date** (BR-027); order `PAID`/`COMPLETED` dihitung penjualan, `REFUNDED` dipisah.

| FR | Isi |
|----|-----|
| **FR-RPT-001** Sales | Filter tanggal/outlet/kasir/metode. Gross sales, diskon, pajak, service, net sales, jumlah order, pembulatan (baris terpisah, di luar net sales), **COGS** (Σ HPP snapshot × qty), **gross profit**, total refund |
| **FR-RPT-002** Product `[R1.1]` | Produk terjual, qty, revenue; daftar "no recipe" (BR-010) |
| **FR-RPT-003** Payment | Total per metode (Cash, QRIS) dan, di Phase 2, per provider dan tipe pembayaran gateway |
| **FR-RPT-004** Inventory & Waste `[R1.1]` | Stok saat ini, ringkasan movement per tipe, low stock, **stok minus**; waste per bahan, qty, alasan, biaya |
| **FR-RPT-005** Dashboard `[R1.1]` | Penjualan hari ini, jumlah order, rata-rata order (net sales ÷ jumlah order), low stock, top product (hari ini by qty), shift saat ini. Widget menyesuaikan permission; Cashier hanya data shift sendiri; cache TTL pendek |
| **FR-RPT-006** Export CSV (F-19) `[R1.1]` | Sales dan stock movement, filter sama dengan layar, butuh `reports.export`, stream |
| **FR-RPT-007** Rekonsiliasi Gateway **[P2]** | Butuh `reports.view` (Owner, Manager). Daftar transaksi Midtrans per status; tiga daftar tindak lanjut: (1) event `FAILED` (`ORDER_NOT_PAYABLE`, `AMOUNT_MISMATCH`, gagal proses) = dana mungkin diterima tanpa payment, (2) `PENDING` lewat `expires_at`, (3) `SETTLED` tanpa payment. Total settlement per hari dibandingkan total `payments` provider |

Cashier pada `reports.view` hanya melihat ringkasan shift dan transaksi miliknya.

---

### Modul AUD — Audit Trail

#### FR-AUD-001: Pencatatan
Aksi wajib: `LOGIN`, `LOGIN_FAILED`, `BUSINESS_REGISTERED`, `USER_CREATED/UPDATED`, `ROLE_CHANGED`, `PRODUCT_PRICE_CHANGED`, `RECIPE_UPDATED`, `SETTING_CHANGED` (pajak, service, stok minus, diskon kasir, pembulatan), `SHIFT_OPENED/CLOSED`, `CASH_IN/CASH_OUT`, `DISCOUNT_APPLIED`, `ORDER_CANCELLED`, `PAYMENT_CREATED`, `REFUND_CREATED`, `STOCK_ADJUSTMENT`, `WASTE`, `STOCK_OPNAME_SUBMITTED`, `OPENING_STOCK`, `INGREDIENT_COST_CHANGED`, `SUPPLIER_CREATED/UPDATED`, `PURCHASE_CREATED`, `PURCHASE_RECEIVED`; Phase 2: `GATEWAY_CONFIG_CHANGED`, `GATEWAY_PAYMENT_INITIATED`, `GATEWAY_PAYMENT_SETTLED`, `GATEWAY_PAYMENT_FAILED`, `GATEWAY_PAYMENT_CANCELLED`, `GATEWAY_STOCK_OVERRIDE`, `GATEWAY_REFUND`. Field: business, outlet, user, action, resource, before/after JSON, alasan, IP, waktu. Ditulis setelah commit. Append-only (BR-020). Aksi yang dipotong dari scope (UI audit) tetap dicatat.

**Katalog kode aksi per modul** (sumber tunggal; SDD, tiket, dan modul README mengikuti ini):
| Modul | Aksi |
|-------|------|
| AUTH | `LOGIN`, `LOGIN_FAILED`, `BUSINESS_REGISTERED` |
| USER | `USER_CREATED`, `USER_UPDATED`, `ROLE_CHANGED` |
| BIZ | `SETTING_CHANGED` |
| CAT | `PRODUCT_PRICE_CHANGED` |
| ING-REC | `RECIPE_UPDATED`, `INGREDIENT_COST_CHANGED` |
| SHF | `SHIFT_OPENED`, `SHIFT_CLOSED`, `CASH_IN`, `CASH_OUT` |
| ORD | `DISCOUNT_APPLIED`, `ORDER_CANCELLED` |
| PAY-RFD | `PAYMENT_CREATED`, `REFUND_CREATED` |
| ENG-INV | `OPENING_STOCK`, `STOCK_ADJUSTMENT`, `WASTE`, `STOCK_OPNAME_SUBMITTED` |
| PUR | `SUPPLIER_CREATED`, `SUPPLIER_UPDATED`, `PURCHASE_CREATED`, `PURCHASE_RECEIVED` |
| GW [P2] | `GATEWAY_CONFIG_CHANGED`, `GATEWAY_PAYMENT_INITIATED/SETTLED/FAILED/CANCELLED`, `GATEWAY_STOCK_OVERRIDE`, `GATEWAY_REFUND` |

`INGREDIENT_COST_CHANGED` dicatat saat `last_cost` bahan berubah: diubah manual (FR-ING-001) atau otomatis saat purchase diterima. Saat receive, boleh menjadi bagian payload `PURCHASE_RECEIVED` (before/after last_cost per bahan) tanpa baris terpisah; perubahan manual memakai `INGREDIENT_COST_CHANGED`.

#### FR-AUD-002: Baca Audit Log (Should) `[R1.1]`
Filter tanggal, user, aksi, resource. Butuh `audit.view` (Owner; Manager hanya outlet miliknya).

---

### Modul PWA

#### FR-PWA-001: PWA
Manifest + service worker; installable; HTTPS. Service worker hanya cache aset statis, **tidak** cache respons API. Offline: banner dan semua aksi tulis ditolak; retry pembayaran memakai `Idempotency-Key` yang sama selama dialog terbuka.

---


## 2.1 Matriks Permission Final

Nama kanonik mengikuti PRD v1.1 §24. Tambahan baru ditandai ➕. Satu role per user.

| Permission | Owner | Manager | Cashier | Inventory | Kitchen |
|------------|:----:|:-------:|:-------:|:---------:|:-------:|
| `sales.view` | ✓ | ✓ | Own | – | – |
| `sales.create`, `sales.update` | ✓ | ✓ | ✓ | – | – |
| `sales.cancel` | ✓ | ✓ | Limited | – | – |
| `sales.pay` ➕ | ✓ | ✓ | ✓ | – | – |
| `sales.refund` | ✓ | ✓ | – | – | – |
| `discounts.apply` | ✓ | ✓ | ✓ (batas %) | – | – |
| `shifts.open`, `shifts.close` | ✓ | ✓ | ✓ | – | – |
| `shifts.view_all` | ✓ | ✓ | – | – | – |
| `cash.manage` | ✓ | ✓ | ✓ | – | – |
| `orders.kitchen_view`, `orders.kitchen_update` | ✓ | ✓ | – | – | ✓ |
| `products.view` | ✓ | ✓ | ✓ | ✓ | ✓ |
| `products.create/update/delete` | ✓ | ✓ | – | – | – |
| `recipes.view` | ✓ | ✓ | – | ✓ | ✓ |
| `recipes.manage` | ✓ | ✓ | – | – | – |
| `inventory.view` | ✓ | ✓ | – | ✓ | ✓ |
| `inventory.adjust`, `inventory.count` | ✓ | ✓ | – | ✓ | – |
| `inventory.waste` | ✓ | ✓ | – | ✓ | ✓ (tanpa nilai biaya) |
| `reports.view` | ✓ | ✓ | Limited | ✓ | – |
| `reports.export` ➕ | ✓ | ✓ | – | – | – |
| `dashboard.view` ➕ | ✓ | ✓ | Limited | Limited | – |
| `users.view/create/update/delete` | ✓ | Limited | – | – | – |
| `settings.view/update` | ✓ | Limited | – | – | – |
| `payment_config.manage` ➕ (Phase 2) | ✓ | – | – | – | – |
| `outlets.view` ➕ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `outlets.manage` ➕ | ✓ | – | – | – | – |
| `audit.view` ➕ | ✓ | Limited | – | – | – |
| `purchases.view`, `purchases.create`, `purchases.receive` | ✓ | ✓ | – | ✓ | – |
| `purchases.cancel` `[R1.1]` | ✓ | ✓ | – | ✓ | – |
| `suppliers.view/create/update/delete` | ✓ | ✓ | – | ✓ | – |

**Definisi "Limited" / "Own":**
- `sales.view` Cashier: hanya order miliknya.
- `sales.cancel` Cashier: hanya order miliknya, status DRAFT/PENDING_PAYMENT, shift masih OPEN.
- `reports.view` Cashier: hanya ringkasan shift dan transaksi miliknya.
- `dashboard.view` Cashier: shift sendiri; Inventory: widget stok saja.
- `users.*` Manager: lihat Bagian FR-USER-001.
- `settings.*` Manager: pajak, service charge, struk di outlet miliknya.
- `audit.view` Manager: outlet miliknya.

Catatan: `sales.complete` dari audit delta **dihapus** (COMPLETED otomatis, BR-021). CRUD ingredient memakai `inventory.adjust`. Pembayaran dan pembatalan transaksi Midtrans memakai `sales.pay` (Cashier: order sendiri; Manager/Owner: semua); rekonsiliasi gateway memakai `reports.view`; webhook tidak memakai permission (diamankan signature, FR-PAY-004).

---


**Implementasi RBAC (Spatie):** satu role per user (`syncRoles`), `teams` aktif dengan `business_id` sebagai team key; 5 role dan seluruh permission di-seed per business saat register. Scope `Own`/`Limited` tidak disimpan di Spatie, tetapi di `config/rbac_scopes.php` dan dieksekusi oleh Policy; `/me` menggabungkan keduanya (SDD §4.5).

## 3. Kebutuhan Non-Fungsional

### 3.1 Performa
| ID | Requirement | Target |
|----|-------------|--------|
| NFR-PERF-001 | Load awal POS (4G, cache dingin) | < 3 detik |
| NFR-PERF-002 | Response API endpoint tulis umum | p95 < 500 ms |
| NFR-PERF-003 | Pembayaran + potong stok (order ≤ 10 item) | p95 < 800 ms |
| NFR-PERF-004 | Pembayaran simultan per outlet | ≥ 20 tanpa deadlock atau stok salah |
| NFR-PERF-005 | Laporan sales satu bulan satu outlet | < 3 detik `[ASUMSI]` |
| NFR-PERF-006 | Beban acuan | 5 outlet × 300 order/hari ≈ 1.500 order/hari ≈ ±15.000 movement/hari; tanpa partisi tabel sampai ±3 tahun data `[ASUMSI]` |
| NFR-PERF-007 | Webhook Midtrans [P2] | Persist + respons `200` p95 < 300 ms; settlement (payment + potong stok) selesai p95 ≤ 5 detik sejak diterima, di luar latensi Midtrans |
| NFR-PERF-008 | Index query hot path | `EXPLAIN` tanpa sequential scan pada tabel > 10.000 baris untuk: lock `stock_balances`, idempotensi `payments`/`stock_movements`, antrian dapur, close shift, lookup `provider_order_id`, dedup `event_key`. Daftar index mengikuti ERD v3.0 §4 |

### 3.2 Keamanan
| ID | Requirement |
|----|-------------|
| NFR-SEC-001 | Isolasi tenant: 0 kebocoran lintas business di test suite; data business lain → `404` |
| NFR-SEC-002 | Password dengan bcrypt/argon2; lockout 5×/15 menit |
| NFR-SEC-003 | HTTPS/TLS 1.2+; cookie `Secure`, `HttpOnly`, `SameSite` |
| NFR-SEC-004 | CSRF untuk Sanctum SPA; CORS dibatasi origin frontend |
| NFR-SEC-005 | Validasi input via Form Request; mass assignment dibatasi; upload divalidasi tipe dan ukuran |
| NFR-SEC-006 | Throttling endpoint login dan pembayaran |
| NFR-SEC-007 | Respons POS tidak memuat HPP; antrian dapur tanpa harga dan pembayaran |
| NFR-SEC-008 | [P2] Webhook Midtrans wajib melewati verifikasi signature dan cek status ke Midtrans sebelum mengubah order; payload tanpa signature valid tidak pernah memengaruhi data |
| NFR-SEC-009 | [P2] Server key terenkripsi di database, tidak masuk respons API, log, atau audit; hanya Owner dapat mengubah |
| NFR-SEC-010 | [P2] Data kartu tidak pernah diterima atau disimpan server; endpoint webhook ber-throttle dan hanya HTTPS |

### 3.3 Reliabilitas & Integritas
| ID | Requirement | Target |
|----|-------------|--------|
| NFR-REL-001 | Uptime | 99,5% per bulan (±3,6 jam) |
| NFR-REL-002 | Backup harian, uji restore minimal sekali sebelum production | RPO ≤ 24 jam, RTO ≤ 2 jam `[ASUMSI]` |
| NFR-DATA-001 | `balance = SUM(movements)` | 100% ingredient |
| NFR-DATA-002 | Payment + status order + movement atomik | 0 payment tanpa potong stok pada test kegagalan |
| NFR-DATA-003 | Retry dengan `Idempotency-Key` sama | 0 payment atau movement ganda |
| NFR-DATA-004 | [P2] Notifikasi Midtrans berulang atau bersamaan | 0 payment atau movement ganda; setiap transaksi `SETTLED` punya tepat 1 `payments` |
| NFR-REL-003 | [P2] Pemrosesan webhook gagal | Retry otomatis 3 kali; sisa kegagalan terlihat di FR-RPT-007; 0 notifikasi hilang (event selalu tersimpan) |

### 3.4 Skalabilitas
Mendukung 5 outlet dalam 12 bulan pada satu VPS tanpa perubahan arsitektur. Monolith modular; Cache/Queue (driver database) untuk cache dan queue.

### 3.5 Kompatibilitas
Chrome 90+, Firefox 88+, Safari 14+; Android 8+ dan iOS 14+ via browser/PWA. Layar POS responsif ≥ 360 px.

### 3.6 Aksesibilitas
Kontras teks WCAG AA, label pada semua field, target sentuh ≥ 44 px di layar POS.

---


## 4. Aturan Bisnis (Business Rules)

> Total 51 BR (BR-037…044 = Midtrans `[P2]`, BR-045…050 = Purchase, BR-051 = biaya movement SALE). `BR-001…BR-020` sama dengan `BR-01…BR-20` di Audit 01 §8 (revisi sesuai PRD v1.1 dan G0). `BR-021…` baru.

| ID | Aturan | Dampak jika dilanggar |
|----|--------|-----------------------|
| BR-001 | Order hanya dibuat jika kasir punya shift OPEN di outlet | `409` |
| BR-002 | Satu kasir maksimal 1 shift OPEN per outlet; outlet boleh punya beberapa shift OPEN paralel | `409`; dijamin partial unique index |
| BR-003 | Close shift ditolak jika masih ada order DRAFT/PENDING_PAYMENT di shift itu | `409` |
| BR-004 | Expected Cash = Opening + Cash Sales (bersih kembalian) + Cash In − Cash Out − Cash Refund | Selisih kas salah |
| BR-005 | Urutan hitung: subtotal → diskon order → service (dari subtotal setelah diskon) → pajak (dari subtotal setelah diskon + service). Pajak exclusive. Half-up ke rupiah per komponen. Pembulatan total ke kelipatan increment outlet sebagai langkah terakhir (BR-036) | Total salah |
| BR-006 | Diskon hanya level order, persen atau nominal, tidak melebihi subtotal; butuh `discounts.apply`; batas % kasir per outlet | `403`/`422` |
| BR-007 | Cash: `tendered ≥ total`, kembalian dihitung. QRIS manual: kasir konfirmasi, `reference_no` opsional, nominal = total. Tanpa split payment | `422` |
| BR-008 | Endpoint bayar wajib `Idempotency-Key`; key yang sama mengembalikan hasil sebelumnya | Payment ganda |
| BR-009 | Saat order dibayar, dalam transaksi yang sama: 1 movement `SALE` per ingredient (agregat seluruh item) | Stok tidak akurat |
| BR-010 | Produk tanpa recipe aktif boleh dijual, tidak memotong stok, ditandai "no recipe" di daftar produk dan (R1.1) laporan "no recipe" | Kasir terblokir |
| BR-011 | `allow_negative_stock = true`: tetap jual, stok boleh minus, ditandai di laporan. `false`: bayar ditolak dengan daftar bahan kurang. Pembayaran gateway: lihat BR-041 | Sesuai setting |
| BR-012 | Refund penuh saja, butuh `sales.refund`, alasan wajib; tunai dicatat di shift OPEN pelaku dan ditolak bila tidak ada; restock dipilih per refund (default tidak) | Selisih kas/stok |
| BR-013 | Edit recipe membuat versi baru; order lama merujuk versi lamanya | HPP historis berubah |
| BR-014 | Perubahan harga, nama, recipe tidak mengubah order lama (snapshot) | Laporan historis berubah |
| BR-051 | `unit_cost` pada movement `SALE` memakai biaya bahan dari **snapshot order** (HPP saat item ditambahkan), bukan `last_cost` saat pembayaran, agar konsisten dengan COGS di laporan | HPP laporan ≠ biaya movement |
| BR-015 | Semua qty stok dan recipe dalam base unit | Salah hitung stok |
| BR-016 | Adjustment dan waste wajib alasan, qty > 0 (arah ditentukan jenis aksi); waste selalu mengurangi stok | `422` |
| BR-017 | Opname: selisih = hitungan − snapshot awal, diterapkan ke stok live; maks 1 opname IN_PROGRESS per outlet | Penjualan selama opname hilang |
| BR-018 | Entitas yang pernah dipakai transaksi tidak dihapus permanen: INACTIVE atau soft delete | Laporan historis rusak |
| BR-019 | Nomor order `{KODE_OUTLET}-{YYMMDD}-{urut 4 digit}`, reset harian menurut timezone outlet, aman konkurensi | Nomor ganda |
| BR-020 | `audit_logs`, `stock_movements`, `payments`, `cash_movements` append-only; tidak ada endpoint ubah/hapus | Jejak audit rusak |
| BR-021 | Pembayaran sukses menjadikan order `PAID` lalu `COMPLETED` dalam transaksi yang sama; status dapur terpisah di `kitchen_status`. Untuk pembayaran gateway pemicunya settlement terverifikasi (BR-039) | State ambigu |
| BR-022 | Semua tabel tenant memiliki `business_id` dengan global scope; data business lain mengembalikan `404` | Kebocoran data |
| BR-023 | Login gagal 5× berturut-turut mengunci akun 15 menit | Brute force |
| BR-024 | Manager hanya mengelola Cashier/Inventory/Kitchen di outlet yang ia akses, dan hanya mengubah pajak, service, struk | `403` |
| BR-025 | Cashier hanya membatalkan order miliknya, DRAFT/PENDING_PAYMENT, shift OPEN | `403` |
| BR-026 | Shift terbuka > 24 jam menampilkan peringatan; tidak ditutup otomatis | Shift terlupa |
| BR-027 | Business date = tanggal shift dibuka (timezone outlet); laporan harian memakai business date | Laporan salah hari |
| BR-028 | `PENDING_PAYMENT → DRAFT` diizinkan sebelum ada pembayaran sukses | Kasir tidak bisa koreksi |
| BR-029 | Produk, modifier, kategori INACTIVE tidak bisa masuk order baru; divalidasi saat tambah item dan saat konfirmasi | Produk nonaktif terjual |
| BR-030 | Modifier group wajib harus dipilih sesuai `min_select`/`max_select`; `SINGLE` maks 1 | `422` |
| BR-031 | SKU produk dan ingredient unik per business; kode outlet unik per business | `422` |
| BR-032 | `recipes.manage` hanya Owner dan Manager | `403` |
| BR-033 | Antrian dapur tidak menampilkan harga dan data pembayaran | Kebocoran data sensitif |
| BR-034 | Kitchen tidak melihat nilai biaya waste | Kebocoran data sensitif |
| BR-035 | Satu refund per order | Refund ganda |
| BR-036 | Pembulatan total: ke kelipatan `rounding_increment` outlet (0/100/500/1000), half-up, selisih boleh negatif, berlaku semua metode pembayaran; total > 0 tidak boleh menjadi 0; selisih disimpan di `orders.rounding_amount`; total setelah pembulatan yang dibayar, di-refund, dan dilaporkan | Kas dan struk tidak sinkron |
| BR-037 | [P2] Provider gateway = Midtrans. Kredensial per business, override per outlet; server key terenkripsi; hanya Owner (`payment_config.manage`) yang mengelola | Kebocoran kredensial |
| BR-038 | [P2] Satu transaksi gateway `PENDING` per order. Selama ada, order tidak dapat `reopen` atau cancel; transaksi harus dibatalkan atau berakhir lebih dulu | Order dibayar setelah dibatalkan |
| BR-039 | [P2] Settlement mengubah order hanya bila order `PENDING_PAYMENT` dan `gross_amount` = `orders.total`. Selain itu order tidak berubah dan event ditandai untuk tinjauan (FR-RPT-007) | Payment pada order salah status |
| BR-040 | [P2] Webhook wajib signature valid, status diverifikasi ke Midtrans, dan idempoten lewat `event_key` unik; pembuatan `payments` memakai `idempotency_key = transaction_id` | Payment ganda atau data palsu |
| BR-041 | [P2] Pembayaran gateway tidak boleh di-rollback karena stok (dana sudah diterima). Bila `allow_negative_stock = false`, stok dicek saat transaksi dimulai; saat settlement stok tetap dipotong walau menjadi minus, ditandai `GATEWAY_STOCK_OVERRIDE` dan muncul di laporan stok minus 🔶 | Uang diterima tanpa order selesai |
| BR-042 | [P2] Pembayaran gateway non-tunai: tidak masuk Expected Cash; `payments.shift_id` = shift order; masuk laporan payment per provider | Selisih kas salah |
| BR-043 | [P2] Refund gateway lewat API refund Midtrans dengan `gateway_refund_key` unik; ditolak → pilihan refund manual oleh Manager/Owner; tetap satu refund per order (BR-035) | Refund ganda atau dana tidak kembali |
| BR-044 | [P2] Transaksi `PENDING` yang lewat `expires_at` dicek ke Midtrans lalu `EXPIRED` bila tidak settlement; percobaan baru memakai `attempt_no` + 1 dan `provider_order_id` baru | Transaksi menggantung |

---
| BR-045 | Receive purchase hanya dari DRAFT, satu transaksi: 1 movement `PURCHASE` per bahan, purchase `RECEIVED`, `last_cost` diperbarui; gagal → rollback; receive kedua tidak menambah stok | Stok ganda atau setengah jadi |
| BR-046 | Satuan beli dikonversi ke base unit memakai `ingredient_unit_conversions`: `kg→g` = 1000, `L→ml` = 1000, satuan sama dengan base unit = 1; kombinasi lain ditolak | Salah hitung stok |
| BR-047 | `last_cost` bahan = harga per satuan beli ÷ faktor konversi (`DECIMAL(18,4)`) dari purchase RECEIVED terbaru (Last Cost, bukan rata-rata); tidak mengubah movement/order lama | HPP historis berubah |
| BR-048 | Purchase RECEIVED/CANCELLED tidak dapat diubah; supplier INACTIVE tidak bisa dipilih | `409` / `422` |
| BR-049 | Nomor purchase `PO-{KODE_OUTLET}-{YYMMDD}-{urut 3 digit}` dari `document_sequences`, reset harian menurut timezone outlet, aman konkurensi | Nomor ganda |
| BR-050 | Receive tidak tunduk pada `allow_negative_stock` (delta positif); menerima bahan pada stok minus tetap boleh | Penerimaan terblokir tanpa alasan |

## 5. Batasan Sistem (Constraints)
- **Teknologi:** Laravel terbaru, PostgreSQL, Cache/Queue (driver database), Sanctum SPA, Vue 3 + TypeScript + Vite, Tailwind, Supabase Storage, Midtrans untuk payment gateway Phase 2 (ADR-001…006)
- **Regulasi:** UU PDP untuk data user dan karyawan; pajak mengikuti PB1 setempat (🔶 validasi lapangan); data kartu tidak pernah menyentuh server kami (ditangani Midtrans)
- **Data model:** ERD v3.0 adalah sumber kebenaran skema (tabel, constraint, index); perubahan skema wajib memperbarui ERD
- **Waktu:** Core selesai 29–30 Des 2026; rilis 1.1 Januari 2027; margin kapasitas core ±4,75 hari-orang (101 dari ±106)
- **Platform:** online-only; tanpa offline POS dan tanpa integrasi printer native
- **Tim:** 3 fullstack; kapasitas efektif 75%


## 6. Antarmuka Eksternal
| Layanan | Fungsi | Fase |
|---------|--------|------|
| Supabase Storage | Gambar produk | Phase 1 |
| Supabase (PostgreSQL + Storage) | Database dan gambar produk | Phase 1 |
| Email (SMTP) | Notifikasi akun `[ASUMSI]` opsional | Phase 1 |
| Midtrans (Core API + HTTP notification) | QRIS dinamis dan metode lain: charge, cek status, cancel, refund, webhook | Phase 2 |
| WhatsApp/email struk | Kirim receipt | Phase 2 |

---


## 7. Matriks Ketertelusuran
> Kolom UC, SEQ/STM/ACT, dan API terisi penuh. Kolom UI (wireframe, tahap 9) dan TC (test plan, tahap 12) diisi di dokumen masing-masing dan bukan bagian baseline ini.

| Fitur | FR | BR | UC | SEQ / STM / ACT | API |
|-------|----|----|----|-----------------|-----|
| F-01 | FR-AUTH-001…004 | BR-022, BR-023 | UC-AUTH-01…03 | SEQ-AUTH-01 | API-AUTH-01…06 |
| F-02 | FR-BIZ-001…003 | BR-018, BR-024, BR-031 | UC-BIZ-01, 02 | — | API-BIZ-01…07 |
| F-03 | FR-USER-001, 002 | BR-022, BR-024 | UC-USER-01 | SEQ-USER-01 | API-USER-01…06 |
| F-04 | FR-CAT-001…004 | BR-014, BR-018, BR-029, BR-030, BR-031 | UC-CAT-01, 02 | SEQ-CAT-01 | API-CAT-01…18 |
| F-05 | FR-ING-001, FR-REC-001, 003 | BR-013, BR-015, BR-032 | UC-ING-01, UC-REC-01, 03 | SEQ-REC-01 | API-ING-01…06, API-REC-01, 02 |
| F-06 | FR-SHF-001…004 | BR-001…004, BR-026, BR-027 | UC-SHF-01…03 | SEQ-SHF-01, STM-SHIFT | API-SHF-01…07 |
| F-07 | FR-ORD-001…007 | BR-001, BR-005, BR-006, BR-019, BR-025, BR-028, BR-029, BR-036 | UC-ORD-01…04 | SEQ-ORD-01, SEQ-ORD-02, ACT-ORD-02, STM-ORDER | API-ORD-01…08 |
| F-08 | FR-PAY-001, 002 | BR-007, BR-008, BR-021, BR-036 | UC-PAY-01, 02 | SEQ-PAY-01 | API-PAY-01, 02 |
| F-09 | FR-ENG-001…003 | BR-009…011 | UC-ENG-01 | SEQ-PAY-01, ACT-INV-01 | API-PAY-01, API-ENG-01 |
| F-10 | FR-RFD-001, FR-RFD-002 [P2] | BR-012, BR-035, BR-043 | UC-RFD-01 | SEQ-RFD-01 | API-RFD-01 |
| F-11 | FR-INV-001, 002, 005 | BR-015, BR-020 | UC-INV-01, 02 | SEQ-INV-01, ACT-INV-01 | API-INV-01…04 |
| F-12 | FR-INV-003, 004 | BR-016 | UC-INV-03, 04 | SEQ-INV-01 | API-INV-05…08 |
| F-13 | FR-INV-006 | BR-017 | UC-INV-05 | SEQ-INV-02, STM-OPNAME | API-INV-09…14 |
| F-14 | FR-KIT-001 | BR-033 | UC-KIT-01 | SEQ-KIT-01 | API-KIT-01, 02 |
| F-15 | FR-RPT-001…005 | BR-027 | UC-RPT-01, 02 | SEQ-RPT-01 | API-RPT-01…06 |
| F-16 | FR-AUD-001, 002 | BR-020 | UC-AUD-01 | — (perilaku lintas UC) | API-AUD-01 |
| F-17 | FR-PWA-001 | — | — (NFR) | — | — |
| F-18 | FR-REC-002 | BR-009 | UC-REC-02 | SEQ-PAY-01 | API-REC-03, 04 |
| F-19 | FR-RPT-006 | BR-022 | UC-RPT-03 | SEQ-RPT-01 | API-RPT-07, 08 |
| F-20 | FR-PUR-001…004, FR-PUR-005 `[R1.1]`, FR-ING-001 | BR-045…050 | UC-PUR-01…04, UC-PUR-05 `[R1.1]` | SEQ-PUR-01 | API-PUR-01…09, API-PUR-10 `[R1.1]` |
| F-21 [P2] | FR-BIZ-004, FR-PAY-003…005, FR-RFD-002, FR-RPT-007 | BR-037…044 | UC-PAY-03…05, UC-SYS-01 (ditulis di dokumen 03 v3.0) | SEQ-GW-01…03 (belum ada di SDD) | API gateway (belum ada di SDD §4) |

**Hasil pemeriksaan celah:** setiap fitur Must punya UC; setiap UC punya minimal satu endpoint; setiap endpoint menunjuk UC dan FR (SDD §4.4); nilai status di STM sama dengan enum ERD. Entitas `Payment` tidak punya STM di Phase 1 karena hanya status `SUCCESS` yang tersimpan (dibuat saat gateway masuk di Phase 2).

---


## 8. Dampak ke Tiket
Perubahan dari baseline sebelumnya sudah diserap di **Task Breakdown v3.0** (`docs/08_Task_Breakdown_v3.0.md`): 148 tiket, ID `[TIPE]-[MODUL]-[NOMOR]`, kolom referensi memuat FR/UC/SEQ/SCR. Tidak ada lagi daftar re-baseline terpisah.

---

## 9. Risiko & Asumsi 🔶
| ID | Hal | Dampak |
|----|-----|--------|
| R-01 | **Ditutup:** pemakaian bahan modifier dihitung dari recipe option saat pembayaran (A4); recipe modifier masuk R1.1 | Selisih stok modifier di pilot sampai F-18 |
| R-02 | Harga menu eksklusif (dikunci A1); bila lapangan inklusif | `PriceCalculator` (BE-ORD-002) berubah |
| R-03 | **Ditutup:** email login unik global | — |
| R-04 | **Ditutup:** 1 business banyak outlet | — |
| R-05 | Increment pembulatan default 100 | Rendah: setting per outlet |
| R-06 | BR-041: settlement tetap memotong stok walau minus **(disetujui B4)** | Phase 2 |
| R-07 | **Ditutup:** akun Midtrans per business (B2) | Phase 2 |
| R-08 | Dukungan refund API berbeda per tipe pembayaran `[ASUMSI]` | Jalur refund manual menutupnya |
| R-09 | Webhook terlambat atau tidak sampai | Rendah: penyapu + rekonsiliasi |
| R-10 | **Jadwal core 30 Des tanpa buffer 20%** (101 vs ±106 hari-orang) | Tinggi: potong sesuai cut list PRD §4.1 bila S2 terlambat > 2 hari |
| R-11 | Supabase transaction pooler memutus `SELECT … FOR UPDATE` | Tinggi bila salah konfigurasi: wajib direct/session pooler (TD §6) |
| R-12 | Cookie Sanctum lintas domain FE-API | Sedang: FE dan API satu parent domain atau proxy rewrite |

---

## 10. Changelog
| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| 1.0 | 2026-10-07 | SRS awal Phase 1: 50 FR, 35 BR, NFR terukur, matriks permission final, traceability awal | Claude |
| 1.1 | 2026-10-07 | Cash rounding (tunai saja) masuk: BR-036, setting, perubahan FR dan BR terkait. Total 36 BR | Claude |
| 1.2 | 2026-10-07 | Pembulatan berlaku untuk **semua metode**: pindah ke level order (`orders.total` setelah pembulatan), setting jadi `rounding_increment`, hapus preview payable | Claude |
| 1.3 | 2026-10-07 | Hasil G2: kolom UC di traceability terisi; aturan baca-saja recipe tanpa HPP (FR-REC-001); `409` untuk Idempotency-Key lintas order (FR-PAY-001) | Claude |
| 2.0 | 2026-10-12 | Versi final: traceability UC/SEQ/API terisi, ingredient memakai `inventory.adjust`, email unik global, penegasan `PUT /orders/{id}`, default setting dikunci | Claude |
| 2.1 | 2026-10-12 | Midtrans [P2]: FR-BIZ-004, FR-PAY-003…005, FR-RFD-002, FR-RPT-007, BR-037…044 (total 44 BR), permission `payment_config.manage`, NFR-PERF-007/008, NFR-SEC-008…010, NFR-DATA-004, NFR-REL-003, penyesuaian FR-ORD-004/005, FR-SHF-003, FR-PAY-002, FR-RPT-003, FR-AUD-001; antarmuka Midtrans; ERD v3.0 jadi sumber skema; traceability F-21; risiko R-06…R-10 | Claude |
| 3.0 | 2026-10-25 | Baseline final: modul PUR (FR-PUR-001…005, BR-045…050), penanda rilis R1.1, Vue 3/Spatie/Supabase, matriks permission purchase aktif, risiko diperbarui, tanpa daftar re-baseline terpisah | Claude |
| 3.0.1 | 2026-10-26 | Kontrak audit: katalog kode aksi FR-AUD-001, INGREDIENT_COST_CHANGED, PURCHASE/SUPPLIER aksi, BR-051 (unit_cost SALE = snapshot order) | Claude |
