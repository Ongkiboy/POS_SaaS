# Log Keputusan dan Open Items
> **Versi:** 3.0 | **Tanggal:** 2026-10-25 | Semua keputusan di bawah **dikunci** oleh Ongki (24–25 Okt 2026) kecuali bagian "Open Items".

## 1. Keputusan yang dikunci
| Kode | Keputusan | Dampak ke dokumen |
|------|-----------|-------------------|
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
| "Tetap Naufaldi" | Purchase (BE, FE, test) tetap di Naufaldi | Task Breakdown §2 |

## 2. Risiko utama
| ID | Risiko | Mitigasi |
|----|--------|----------|
| R-10 | Core 101 hari-orang vs kapasitas 105,75 (margin ±4,5%, tanpa buffer 20%) | Cut list PRD §4.1; prioritas jalur kritis; kalibrasi estimasi akhir S1; 30 Des cadangan |
| R-A | Bahan modifier tidak memotong stok sampai rilis 1.1 | Pilot diberi tahu; opsi menarik F-18 ke core (+±1,5 hari) diputuskan di CP3 (4 Des) |
| R-B | Transaction pooler Supabase memutus `FOR UPDATE` | Wajib direct/session pooler (TD §6); dicek di SETUP-002 |
| R-C | Cookie Sanctum lintas domain FE–API | Satu parent domain atau proxy rewrite; diuji di SETUP-008 |
| R-D | Harga eksklusif ternyata tidak sesuai kebiasaan kafe pilot | Validasi lapangan sebelum UAT; ubah `PriceCalculator` bila perlu |

## 3. Open Items (tidak memblokir pengembangan)
| # | Item | Owner | Tenggat |
|---|------|-------|---------|
| 1 | Validasi lapangan harga eksklusif, tarif PB1, service charge | Ongki + 1 pemilik kafe | Sebelum UAT (21 Des) |
| 2 | Uji printer thermal 58/80 mm dari browser | Ongki | Sebelum UAT |
| 3 | Keputusan menarik F-18 ke core | Ongki | 4 Des (CP3) |
| 4 | Akun Midtrans sandbox + Redis untuk Phase 2 | Ongki | Sebelum Phase 2 |
| 5 | Kalibrasi estimasi tiket (aktual vs rencana) | Semua | Akhir S1 (6 Nov) |
| 6 | Dokumen yang ditunda ke 1.1: OpenAPI (DOC-001), runbook (DOC-002) | Akmal | Jan 2027 |
| 7 | Jenis VPS (Hostinger atau Rumahweb; unmanaged Ubuntu atau berpanel), RAM/vCPU, domain, DNS lewat Cloudflare atau langsung | Ongki | Ditanyakan lagi menjelang staging (`SETUP-008`) |
