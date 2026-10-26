# Peta Modul

Tiap folder berisi README: ringkasan (bahasa mudah), FR, BR, UC, tabel, diagram, endpoint+contoh, tiket+AC, test case. Sumber kebenaran: `docs/`.

| Folder | Modul | Owner | Core | R1.1 |
|--------|-------|-------|------|------|
| [AUTH](AUTH/README.md) | Autentikasi, Tenant, RBAC | Ongki (9 tiket), Naufaldi (4 tiket) | 13 | 0 |
| [BIZ](BIZ/README.md) | Business, Outlet, Pengaturan | Naufaldi (3 tiket) | 3 | 0 |
| [USER](USER/README.md) | Manajemen User | Naufaldi (2 tiket) | 2 | 0 |
| [CAT](CAT/README.md) | Katalog (Category, Product, Modifier) | Naufaldi (9 tiket), Akmal (1 tiket) | 10 | 0 |
| [ING-REC](ING-REC/README.md) | Ingredient & Recipe | Akmal (12 tiket) | 9 | 3 |
| [SHF](SHF/README.md) | Shift & Kas | Naufaldi (10 tiket) | 10 | 0 |
| [ORD](ORD/README.md) | Order POS | Ongki (14 tiket) | 14 | 0 |
| [PAY-RFD](PAY-RFD/README.md) | Pembayaran, Struk, Refund | Ongki (11 tiket) | 11 | 0 |
| [ENG-INV](ENG-INV/README.md) | Engine Sale→Inventory & Inventory | Akmal (27 tiket) | 21 | 6 |
| [PUR](PUR/README.md) | Supplier & Purchasing | Naufaldi (10 tiket) | 9 | 1 |
| [KIT](KIT/README.md) | Antrian Dapur [R1.1] | Ongki (2 tiket) | 0 | 2 |
| [RPT](RPT/README.md) | Laporan & Dashboard | Naufaldi (9 tiket), Akmal (1 tiket) | 4 | 6 |
| [AUD-PWA](AUD-PWA/README.md) | Audit & PWA | Ongki (2 tiket), Naufaldi (2 tiket), Akmal (2 tiket) | 4 | 2 |
| [GW-P2](GW-P2/README.md) | Payment Gateway Midtrans | — (Phase 2) | — | — |

## Tiket lintas modul (bukan fitur)

| ID | Task | Owner | Sprint |
|----|------|-------|--------|
| DB-SEED-001 | Seeder demo (business, outlet, menu, recipe, stok, supplier) | Akmal | S5 |
| DEPLOY-001 | Environment production, HTTPS, backup harian + uji restore | Ongki | S4 |
| DEPLOY-002 | Deploy production + smoke test | Ongki | S5 (cadangan 30 Des) |
| SETUP-001 | Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template | Ongki | S1 |
| SETUP-002 | Konfigurasi PostgreSQL Supabase (direct/session pooler) + .env.example per environment | Ongki | S4 |
| SETUP-003 | Pint, Larastan, ESLint, Prettier, pre-commit hook | Akmal | S1 |
| SETUP-004 | CI GitHub Actions: lint + Pest + build frontend | Akmal | S1 |
| SETUP-005 | Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1 | Ongki | S1 |
| SETUP-006 | Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog | Naufaldi | S1 |
| SETUP-007 | Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test | Ongki | S2 |
| SETUP-008 | Staging + HTTPS; FE dan API satu parent domain (atau proxy rewrite) | Ongki | S3 |
| SETUP-009 | Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi | Akmal | S1 |
| TEST-E2E-001 | E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift | Akmal | S4 |
| TEST-E2E-002 | E2E inventory: opening → purchase receive → adjustment → waste | Akmal | S4 |
| TEST-PERF-001 | Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements) | Akmal | S5 |
| TEST-SEC-001 | Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload | Akmal | S4 |
| DOC-001 | Dokumentasi API (OpenAPI/Scribe) | Akmal | R1.1 |
| DOC-002 | README + runbook dev (Laragon, Supabase, queue worker) | Akmal | R1.1 |
