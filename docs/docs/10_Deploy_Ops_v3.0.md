# Deployment & Operations — F&B POS & Inventory

> **Versi:** 3.0.1 | **Tanggal:** 2026-10-27 | **Tier:** Standard
> 

## 1. CI/CD (`SETUP-004`)

| Tahap | Pemicu | Isi |
| --- | --- | --- |
| CI | Pull request ke `develop`/`main` | Pint, Larastan, Pest (PostgreSQL service), arch test, Oxlint, ESLint, Prettier (`--check`), `vite build`; gagal = merge diblok |
| CD staging | Merge ke `develop` | Build image, `migrate --force` via direct connection, `queue:restart`, smoke test otomatis |
| CD production | Tag `v*` dari `main` + persetujuan Ongki | Sama seperti staging + backup sebelum migrasi |

Branch: `main` (production), `develop` (staging), `feature/<id>-<slug>`. Commit: `feat(BE-ORD-002): ...`. PR memuat ID tiket dan minimal 1 reviewer (lihat DoR/DoD).

## 2. Release, Smoke Test, Rollback (`DEPLOY-002`)

**Checklist rilis**

- [ ]  Semua tiket rilis Done; CI hijau di `main`; UAT disetujui
- [ ]  Backup database terbaru terverifikasi (§3)
- [ ]  Migrasi **backward compatible** (tambah kolom nullable dulu, hapus di rilis berikutnya)
- [ ]  `.env` production diperiksa (`DB_*` bukan transaction pooler; `APP_DEBUG=false`)
- [ ]  Deploy → migrasi → `queue:restart` → cache clear

**Smoke test (≤ 10 menit):** login Owner → buka shift → buat order → bayar tunai → stok bahan turun sesuai recipe → terima 1 purchase → tutup shift → laporan sales menampilkan order.

**Rollback**
| Situasi | Tindakan |
|———|———-|
| Bug aplikasi tanpa perubahan skema | Deploy ulang image tag sebelumnya |
| Migrasi bermasalah | Restore ke backup pra-rilis (hanya bila ada data hilang/rusak; catat transaksi yang masuk sejak backup) |
| Data stok tidak konsisten | Jalankan query verifikasi `balance = SUM(movements)`; koreksi lewat adjustment, **bukan** edit langsung (trigger append-only menolak) |
Aturan: rollback skema ke bawah tidak dipakai di production; koreksi maju (forward fix).

## 3. Backup dan Restore (`DEPLOY-001`)

- Supabase backup harian aktif; tambahan `pg_dump` terjadwal harian dari VPS ke penyimpanan terpisah (retensi 14 hari).
- **RPO 24 jam, RTO 2 jam** `[ASUMSI]`.
- Uji restore ke project staging **sekali sebelum go-live** dan tiap kuartal; catat durasi.
- Gambar produk: bucket Supabase Storage dengan versioning/backup mingguan.

## 4. Monitoring dan Logging

| Hal | Alat | Ambang |
| --- | --- | --- |
| Uptime FE dan API | Uptime monitor eksternal (ping `/up` dan halaman login) | Alert bila gagal 2× berturut |
| Error aplikasi | Sentry (BE + FE) | Alert untuk error baru dan lonjakan |
| Queue | Pantau `jobs` menumpuk dan `failed_jobs` | Alert bila > 50 job tertunda > 5 menit |
| Database | Dasbor Supabase (koneksi, CPU, disk) | Disk > 80% |
| Integritas stok | Query terjadwal harian: `balance != SUM(movements)` | Hasil > 0 = insiden S2 |

Log terstruktur tanpa password/token/data kartu; level `warning` ke atas di production.

## 5. Insiden

| Level | Contoh | Respons |
| --- | --- | --- |
| S1 | Tidak bisa login/bayar di semua outlet; kebocoran data tenant | Ongki langsung; komunikasikan ke pilot dalam 30 menit; rollback bila perlu |
| S2 | Stok tidak konsisten; laporan salah | Perbaikan ≤ 1 hari kerja; koreksi lewat adjustment |
| S3 | Bug kosmetik/UX | Masuk backlog sprint berikutnya |

Postmortem blameless untuk S1/S2: tulis timeline, akar masalah, tiket perbaikan, dan test yang menutup celahnya.

## 6. Strategi Migrasi

- Satu migrasi = satu perubahan logis; wajib bisa jalan di database berisi data.
- Perubahan trigger/constraint diuji di staging dengan salinan data.
- Data koreksi yang menyentuh tabel append-only memakai jalur khusus (nonaktifkan trigger oleh pemilik DB, dicatat di audit manual).

## 7. Handover dan Operasi Berkelanjutan

- Runbook (`DOC-002`, R1.1): menjalankan lokal, deploy, restore, antrean worker.
- Tinjau mingguan: error Sentry teratas, tiket bug, kepatuhan estimasi vs aktual (masukan ke backlog PRD).

## 8. Changelog

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 3.0 | 2026-10-25 | Versi pertama |
| 3.0.1 | 2026-10-27 | §1 CI menambah Oxlint dan Prettier check (standar scaffold Vue) |