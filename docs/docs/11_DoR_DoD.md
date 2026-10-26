# Definition of Ready & Done — Tim 3 Developer
> **Versi:** 3.0 | Berlaku untuk seluruh tiket di `tickets/tickets.md`

## Alur status
`todo → doing → review → done` (+ `blocked` dengan penyebab dan pihak yang ditunggu). **WIP limit: 1 tiket `doing` per orang.**

## Definition of Ready (masuk sprint)
- [ ] Judul spesifik, estimasi ≤ M (1 hari)
- [ ] AC ≥ 2, dapat diuji, referensi FR/UC/SEQ/SCR terisi
- [ ] Dependency sudah Done atau ada mock yang disepakati
- [ ] Endpoint/skema yang dibutuhkan sudah ada di SDD/ERD
- [ ] Assignee dan reviewer ditentukan (reviewer silang: Ongki ← Akmal ← Naufaldi ← Ongki)

## Definition of Done (per tiket)
- [ ] Semua AC terpenuhi dan diperiksa reviewer
- [ ] Test terkait ditulis dan lulus (Pest/Playwright); Pint, Larastan, ESLint bersih; CI hijau
- [ ] Untuk endpoint baru: masuk dataset isolasi tenant (`TEST-AUTH-001`) dan matriks RBAC (`TEST-RBAC-001`)
- [ ] Aksi sensitif tercatat di audit (jika tercantum di FR-AUD-001)
- [ ] Dokumen/kontrak API diperbarui bila berubah (SDD §4) pada PR yang sama
- [ ] PR di-review minimal 1 orang lain dan disetujui; commit memuat ID tiket
- [ ] Dicek di staging untuk tiket FE/integrasi

## Konvensi
| Hal | Aturan |
|-----|--------|
| Branch | `feature/<id-huruf-kecil>-<slug>`; perbaikan `fix/<id>-<slug>` |
| Commit | `feat(BE-AUTH-001): tambah endpoint register` |
| PR | Judul `[BE-AUTH-001] Endpoint POST /auth/register`; deskripsi memuat `Closes #<issue>` |
| Bug | Tiket baru `BUG-[MODUL]-nnn` merujuk TC yang gagal |
| Perubahan scope | Tiket baru atau update dokumen hulu (naikkan versi, catat di changelog); jangan menambah isi diam-diam ke tiket berjalan |

## Ritme tim
- Standup singkat harian (async boleh): kemarin, hari ini, blocker.
- **Akhir S1: kalibrasi estimasi** (bandingkan aktual vs rencana, laporkan ke Ongki).
- Sprint review + planning tiap 2 minggu (Jumat); feature freeze core 18 Des.
