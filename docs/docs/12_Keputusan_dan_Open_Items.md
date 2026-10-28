# Log Keputusan dan Open Items

> **Versi:** 3.0.2 | **Tanggal:** 2026-10-27 | Semua keputusan di bawah **dikunci** oleh Ongki (24–25 Okt 2026) kecuali bagian “Open Items”.
> 

## 1. Keputusan yang dikunci

| Kode | Keputusan | Dampak ke dokumen |
| --- | --- | --- |
| A1 | Harga menu **eksklusif** pajak | `PriceCalculator` (BE-ORD-002), BR-005 |
| A2 | 1 business, banyak outlet | NFR skala, UX pilih outlet |
| A3 | 5 role terpisah (Owner, Manager, Cashier, Inventory, Kitchen) | Seeder permission |
| A4 | Bahan modifier dihitung saat bayar | BE-ENG-001/006, R-01 ditutup |
| A5 | Tidak ada cuti bersama Desember | Kapasitas 47 hari kerja |
| B1–B6 | Midtrans Phase 2; akun per business; QRIS via Core API; settlement tetap potong stok (BR-041); fee tidak dicatat; refund API + fallback manual | Modul GW-P2 |
| C | Belum ada tiket/kode dikerjakan; tidak perlu migrasi koreksi | Jadwal mulai 26 Okt |
| D | Frontend **Vue 3** (Next.js sempat dipilih 24 Okt, dibatalkan 25 Okt); Laravel API; default D lain dipakai (sesi idle 120 menit, qty 1–999, polling dapur 10 dtk / QR 3 dtk, rate limit login 5/menit, RPO 24 jam / RTO 2 jam, diskon kasir 10%, increment pembulatan 100, email login unik global, ID BIGINT identity) | ADR-001, ADR-014 |
| E | Struktur `/docs` + `/modules`; dokumen pendukung dibuat; satu baseline v3.0; wireframe versi teks | Paket ini |
| F1 c + J3 | Core 30 Des; fitur Should ke rilis 1.1; laporan core minimal (sales, payment, stok); dashboard dan laporan lain ke 1.1 | PRD §3, SRS §1.5 |
| F2 + P1 | Purchase masuk Phase 1 versi inti; cancel dan partial receive ke 1.1 | FR-PUR, ADR-015 |
| F3 | Pembagian per modul dengan penyeimbangan beban | Task Breakdown §2 |
| 1–4 | Dokumen lama (PRD/SRS/SDD kanonik v2.1) diganti v3.0; cache/queue driver database; PostgreSQL Supabase; Spatie Permission | ADR-005/006/014 |
| Struktur modul | Konvensi eksplisit tanpa package (`config/modules.php` + `ModuleServiceProvider`), migrasi satu folder berprefix modul; `nwidart/laravel-modules` tidak dipakai | TD §2.1, §2.4; `SETUP-005` |
| Audit 25 Okt | P1 Pest di SETUP-001; P2 migrasi framework dan Spatie berprefix modul + hapus migrasi `users` bawaan (DB-TEN-001); P3 arch test registrasi/pola migrasi pindah ke SETUP-009 (S→M, Akmal); P4 CP1 direvisi (login pindah ke CP2) | PRD §7, tiket |
| PWA | Tetap seperti rencana (`FE-PWA-001` dengan service worker, `FE-PWA-002` banner offline) | Tidak ada perubahan |
| “Tetap Naufaldi” | Purchase (BE, FE, test) tetap di Naufaldi | Task Breakdown §2 |

## 2. Risiko utama

| ID | Risiko | Mitigasi |
| --- | --- | --- |
| R-10 | Core 101 hari-orang vs kapasitas 105,75 (margin ±4,5%, tanpa buffer 20%) | Cut list PRD §4.1; prioritas jalur kritis; kalibrasi estimasi akhir S1; 30 Des cadangan |
| G1 (26 Okt) | Repo implementasi dibangun ulang dari nol di `C:\laragon\www\pos-inventory` (monorepo `backend/` + `frontend/`); repo lama (frontend Next.js, file `Modul*`) tidak dipakai. Nama class mengikuti dokumen (`Module`, `BaseModule`, `ModuleServiceProvider`) | `00_STATUS.md`; SETUP-001, SETUP-005 |
| G2 (26 Okt) | Database dev = PostgreSQL lokal Laragon (`pos_inventory`). Supabase hanya staging dan production. SETUP-002 dipersempit ke lokal. **Seluruh pekerjaan Supabase dimiliki SETUP-008** (project staging, migrasi lewat direct connection, verifikasi R-B, Storage); Technical Notes di 14 tiket DB dan BE-CAT-002 disesuaikan | TD §6; tiket SETUP-002, SETUP-008; Task Breakdown 3.0.2 |
| G3 (27 Okt) | Lint frontend memakai standar scaffold Vue tanpa modifikasi: Oxlint + ESLint + Prettier. Hook pre-commit dan CI menjalankannya tanpa `--fix` | TD §7; DoD; Deploy Ops §1; tiket SETUP-003 |
| G4 (27 Okt) | Pembagian tiket kembali ke F3 (Ongki tidak mengambil tiket teman). **Pengecualian satu kali:** Ongki menyelesaikan SETUP-003 karena WIP sudah ada; assignee `@ongki`, reviewer `@akmal`. Akibatnya beban core Ongki 36,00 (kapasitas 35,25) dan Akmal 32,75. SETUP-004, SETUP-006, SETUP-009 tetap milik pemilik semula | Task Breakdown 3.0.2 §2; `tickets.md` SETUP-003 |
| G5 (26 Okt) | Node LTS (v24) untuk frontend; Node non-LTS tidak dipakai | `00_STATUS.md` |
| R-A | Bahan modifier tidak memotong stok sampai rilis 1.1 | Pilot diberi tahu; opsi menarik F-18 ke core (+±1,5 hari) diputuskan di CP3 (4 Des) |
| R-B | Transaction pooler Supabase memutus `FOR UPDATE` | Wajib direct/session pooler (TD §6); lokal dicek di SETUP-002 (lulus 2026-10-26), staging dicek di SETUP-008 |
| R-C | Cookie Sanctum lintas domain FE–API | Satu parent domain atau proxy rewrite; diuji di SETUP-008 |
| R-D | Harga eksklusif ternyata tidak sesuai kebiasaan kafe pilot | Validasi lapangan sebelum UAT; ubah `PriceCalculator` bila perlu |

## 3. Open Items (tidak memblokir pengembangan)

| # | Item | Owner | Tenggat |
| --- | --- | --- | --- |
| 1 | Validasi lapangan harga eksklusif, tarif PB1, service charge | Ongki + 1 pemilik kafe | Sebelum UAT (21 Des) |
| 2 | Uji printer thermal 58/80 mm dari browser | Ongki | Sebelum UAT |
| 3 | Keputusan menarik F-18 ke core | Ongki | 4 Des (CP3) |
| 4 | Akun Midtrans sandbox + Redis untuk Phase 2 | Ongki | Sebelum Phase 2 |
| 5 | Kalibrasi estimasi tiket (aktual vs rencana) | Semua | Akhir S1 (6 Nov) |
| 6 | Dokumen yang ditunda ke 1.1: OpenAPI (DOC-001), runbook (DOC-002) | Akmal | Jan 2027 |
| 7 | Jenis VPS (Hostinger atau Rumahweb; unmanaged Ubuntu atau berpanel), RAM/vCPU, domain, DNS lewat Cloudflare atau langsung | Ongki | Ditanyakan lagi menjelang staging (`SETUP-008`) |
| 8 | **Lokasi enum PHP:** ERD §7.2 menulis `app/Enums`, TD §2.1 menulis `app/Support/Enums` | Ongki | Sebelum SETUP-007 (S2) |
| 9 | **Rename migrasi bawaan Laravel** ada di AC DB-TEN-001 (Ongki), sedangkan test pola nama migrasi ada di SETUP-009 (Akmal). Test itu baru hijau setelah DB-TEN-001 merge: atur urutan merge | Ongki + Akmal | S1 |
| 10 | **Kode dan pesan error global yang belum ada di SDD §4.3:** `500 INTERNAL_ERROR` (cadangan, tanpa bocor detail), perlakuan `419` (CSRF), teks baku 401/403/404/429 | Ongki | SETUP-005 |
| 11 | **Lokasi middleware:** TD §2.1 menaruh `EnsureOutletContext`/`SetPermissionTeam` di `Support/Http/Middleware`, padahal middleware itu memakai model Platform (Outlet, User); bertentangan dengan aturan “Support tanpa logika bisnis” dan arch test dependensi. Usul: pindah ke `Platform/Tenant` dan `Platform/Rbac` | Ongki | BE-RBAC-001 |
| 12 | **Nilai CHECK `status`** di `outlets` dan `users` tidak dirinci ERD §3.1. Usul: `ACTIVE`,`INACTIVE` (sama dengan `businesses`) | Ongki | DB-TEN-001 |
| 13 | **Pesan email ganda:** Test Plan TC-AUTH-002 “Email sudah terdaftar.” berbeda dari SRS FR-AUTH-001 “Email sudah digunakan, silakan gunakan email lain.” Implementasi ikut SRS; patch Test Plan | Ongki | BE-AUTH-001 |
| 14 | **Aturan password:** SRS “huruf besar dan angka” vs SDD API-AUTH-01 “mixed case, numbers” (huruf kecil juga wajib) | Ongki | BE-AUTH-001 |
| 15 | **`X-Outlet-Id` tidak dikirim** pada endpoint outlet-scoped: kode dan pesan tidak ditentukan. Usul: `403 FORBIDDEN`, sama dengan outlet di luar akses | Ongki | BE-RBAC-001 |
| 16 | **Tabel Spatie vs konvensi ERD:** ERD meminta `id` identity, panjang kolom `V(30)`/`V(60)`, CHECK nama role, FK `business_id`. Usul: tambahkan CHECK, FK, dan NOT NULL di migrasi `rbac_`; biarkan tipe id bawaan Spatie (bigserial) dan catat sebagai pengecualian | Ongki | DB-RBAC-001 |
| 17 | **Policy Own/Limited untuk entitas yang belum ada** (Order di S2, User CRUD milik Naufaldi): BE-RBAC-001 menyediakan aturan yang dapat dipakai ulang; `OrderPolicy` dan `UserPolicy` wajib memakainya di tiket masing-masing | Ongki, Naufaldi | BE-RBAC-001, BE-USER-001 |
| 18 | **SETUP-008:** dependensi nyata (BE-AUTH-002, FE-AUTH-001), bunyi AC cookie (`XSRF-TOKEN` memang tidak HttpOnly), CD staging di Deploy Ops §1 belum punya tiket | Ongki | Sebelum S3 |
| 19 | **SETUP-009:** AC “Recipe memanggil POS/Inventory” ambigu karena Recipe berada di grup Inventory (TD §2.1) | Akmal | S1 |
| 20 | **Mekanisme pre-commit hook** di SETUP-003 (`.githooks/pre-commit` + `core.hooksPath` + `.gitattributes` LF): finalisasi dan dokumentasikan cara aktivasi per clone | Ongki (SETUP-003) | S1 |
| 21 | **`LOGIN_FAILED` untuk email tak dikenal:** ERD membolehkan `audit_logs.user_id` NULL, tetapi `business_id` NOT NULL, padahal email tak dikenal tidak punya business. Perlu keputusan (mis. tidak dicatat di `audit_logs`, cukup log aplikasi; atau `business_id` nullable khusus aksi ini) | Ongki | Sebelum BE-AUTH-002 (S2) |
| 22 | **Konfigurasi DB test** (`phpunit.xml` ke PostgreSQL `pos_inventory_test` + `RefreshDatabase`) dibutuhkan Ongki sejak DB-TEN-001, tumpang tindih dengan AC2 SETUP-004 (Akmal). Ongki menerapkan perubahan minimal yang identik; Akmal tinggal memverifikasi di CI | Ongki + Akmal | S1 |
| 23 | **Cakupan dan estimasi SETUP-008** membesar (VPS + Docker + Nginx + project Supabase + migrasi + R-B + Storage + HTTPS), padahal estimasi M (batas DoR ≤ 1 hari). Paket Flysystem S3 (`league/flysystem-aws-s3-v3`) dan kebijakan akses bucket (publik/privat) belum diputuskan di tiket mana pun. Kalibrasi di akhir S1; bila > M, pecah (usul tiket baru `SETUP-010`: Supabase staging + Storage, S) | Ongki | Akhir S1 (6 Nov), sebelum S3 |
| 24 | **Beban core Ongki 36,00 > kapasitas 35,25** (Akmal 32,75, Naufaldi 32,25 masih longgar). Usul: pindahkan satu tiket Ongki berestimasi S/M di S3–S5 ke Akmal | Ongki | Sprint planning S2 |
| 25 | Teks lama “migrasi lewat direct connection Supabase” masih ada di `docs/tickets/create_issues.sh` dan di issue GitHub yang sudah dibuat; sinkronkan dengan `tickets.md` | Ongki | Sebelum membuat/menyunting issue |

## 4. Changelog

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 3.0 | 2026-10-25 | Versi pertama |
| 3.0.1 | 2026-10-27 | Keputusan G1–G5; R-B dipecah lokal/staging; open items 8–22 dari penyusunan panduan implementasi |
| 3.0.2 | 2026-10-27 | G2 diperjelas (pemilik pekerjaan Supabase = SETUP-008); G4 diubah (SETUP-003 dikerjakan Ongki, pengecualian); open items 23–25 |