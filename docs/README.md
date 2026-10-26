# F&B POS & Inventory — Paket Dokumen Final v3.0
> **Tanggal:** 2026-10-25 | **Tim:** Ongki (platform + POS), Akmal (recipe, inventory, engine, PWA/test), Naufaldi (katalog, user/outlet, shift, purchase, laporan)
> **Stack:** Laravel 13 (API) · Vue 3 (PWA) · PostgreSQL Supabase · Spatie Permission · cache/queue driver database
> **Target:** core production 29 Des 2026 (cadangan 30 Des) · rilis 1.1 Januari 2027 · Midtrans Phase 2

## Mulai dari mana
| Kamu | Baca urut |
|------|-----------|
| Semua dev (hari pertama) | `docs/11_DoR_DoD.md` → `docs/07_Technical_Design_v3.0.md` → `docs/08_Task_Breakdown_v3.0.md` → folder modulmu |
| Mengerjakan tiket | `tickets/by-owner/<namamu>/tickets.md` (AC lengkap, urut sprint) → README modul → SRS/SDD bagian terkait |
| Menulis test | `docs/09_Test_Plan_v3.0.md` |
| Deploy/ops | `docs/10_Deploy_Ops_v3.0.md` |

## Isi paket
```
docs/
  01_PRD_v3.0.md                 produk, scope core vs R1.1 vs P2, timeline, keputusan
  02_SRS_v3.0.md                 FR, NFR, BR-001…050, matriks permission, traceability
  03_Process_UseCase_v3.0.md     proses, use case, UC Purchase dan gateway
  04_SDD_v3.0.md                 arsitektur + ADR, behavior (sequence/state), API
  05_ERD_v3.0.md                 40 tabel Phase 1 + 3 Midtrans, index, relasi, Eloquent
  06_UIUX_v3.0.md                site map, 31 layar, flow, state (versi teks)
  07_Technical_Design_v3.0.md    struktur kode, aturan modul, konvensi, environment
  08_Task_Breakdown_v3.0.md      148 tiket, kapasitas, jadwal sprint, jalur kritis, cut list
  09_Test_Plan_v3.0.md           111 test case, matriks FR→TC, kriteria keluar, skrip UAT
  10_Deploy_Ops_v3.0.md          CI/CD, rilis, rollback, backup, monitoring, insiden
  11_DoR_DoD.md                  Definition of Ready/Done, konvensi
  12_Keputusan_dan_Open_Items.md log keputusan, risiko, open items
modules/                         AUTH BIZ USER CAT ING-REC SHF ORD PAY-RFD ENG-INV PUR KIT RPT AUD-PWA GW-P2
tickets/                         tickets.md (sumber), tickets.csv (Jira/Linear/Notion), create_issues.sh (GitHub Issues)
  by-owner/                      ongki/ akmal/ naufaldi/ — tiket per orang (tickets.md, tickets.csv, create_issues.sh), urut per sprint
```
Sumber kebenaran adalah `docs/`. Folder `modules/` dan `tickets/` adalah turunan; bila ada beda, `docs/` menang dan perbaiki turunannya.

## Penanda
`[R1.1]` rilis Januari 2027 · `[P2]` Phase 2 · `BR-xxx` aturan bisnis · `FR-xxx` kebutuhan fungsional · tiket = ID task (`BE-ORD-002`).

## Minggu pertama (S1, 26 Okt–6 Nov)
| Dev | Tiket awal |
|-----|-----------|
| Ongki | SETUP-001, 002, 005, 007 → DB-TEN-001/002 → DB-RBAC-001, DB-AUD-001 → BE-AUTH-001 |
| Akmal | SETUP-003, 004 (CI) → DB-ING-001 → BE-ING-001/002 |
| Naufaldi | SETUP-006 (skeleton FE) → DB-CAT-001 → BE-CAT-001/002 setelah DB-TEN-002 siap |

## Cara impor tiket
- CSV: impor `tickets/tickets.csv` ke Jira/Linear/Notion/Trello.
- GitHub: `gh auth login` lalu `bash tickets/create_issues.sh` (semua) atau `bash tickets/by-owner/<nama>/create_issues.sh` (satu orang) dari dalam repo.
- Pola struktur backend dan registrasi modul: `docs/07_Technical_Design_v3.0.md` §2.1 dan §2.4.
