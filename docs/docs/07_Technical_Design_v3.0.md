# Technical Design — F&B POS & Inventory
> **Versi:** 3.0 | **Tanggal:** 2026-10-25 | **Tier:** Standard | Disepakati sebelum `SETUP-001`.
> Referensi: SDD v3.0 (ADR), ERD v3.0, SRS v3.0.

## 1. Struktur Repo dan Package
```
pos-inventory/
├── backend/            # Laravel 13 (API-only), PHP 8.4
├── frontend/           # Vue 3 + TypeScript + Vite (SPA)
├── docs/               # dokumen ini (baseline v3.0)
└── .github/workflows/  # CI
```
**Backend (Composer):** `laravel/sanctum`, `spatie/laravel-permission`, `league/flysystem-aws-s3-v3` (Supabase Storage via S3-compatible), `pestphp/pest` + `pest-plugin-arch`, `larastan/larastan`, `laravel/pint`, `knuckleberry/scribe` (OpenAPI di R1.1).
**Frontend (npm):** `vue`, `vue-router`, `pinia`, `typescript`, `vite`, `tailwindcss`, `zod`, `vite-plugin-pwa`, `vitest` (dev), `playwright` (dev). Opsional bila tim sepakat di `SETUP-006`: `@tanstack/vue-query` untuk state server.
Versi dikunci di `composer.lock` dan `package-lock.json` pada `SETUP-001`; upgrade mayor hanya lewat tiket.

## 2. Backend
### 2.1 Struktur folder backend
```
backend/
├── app/
│   ├── Modules/
│   │   ├── Platform/
│   │   │   ├── Tenant/            # BelongsToBusiness, TenantScope, OutletContext
│   │   │   ├── Auth/              # register, login, lockout, /me
│   │   │   ├── Rbac/              # middleware permission, Policy Own/Limited
│   │   │   ├── Audit/             # AuditLogger, WriteAuditLogJob
│   │   │   ├── Business/          # profil business + license
│   │   │   ├── Outlet/            # outlet + pengaturan outlet
│   │   │   └── User/              # CRUD user, GET /roles
│   │   ├── Catalog/
│   │   │   ├── Category/
│   │   │   ├── Product/           # + upload gambar (Supabase Storage)
│   │   │   └── Modifier/
│   │   ├── Inventory/
│   │   │   ├── Ingredient/        # + Unit, konversi satuan
│   │   │   ├── Recipe/            # versi, HPP; recipe modifier [R1.1]
│   │   │   ├── Stock/             # StockService, balance, movement, opening, adjustment
│   │   │   ├── Waste/
│   │   │   └── Opname/            # [R1.1]
│   │   ├── Shift/                 # shift, cash movement, ShiftSummaryService
│   │   ├── Sales/
│   │   │   ├── Order/             # PriceCalculator, OrderNumberGenerator, state machine
│   │   │   ├── Payment/           # PaymentMethod (Cash, QRIS manual), idempotensi
│   │   │   ├── Refund/
│   │   │   ├── Receipt/
│   │   │   └── Kitchen/           # [R1.1]
│   │   ├── Engine/                # RecipeUsageCalculator, DeductStockForOrder, RestockForRefund
│   │   ├── Purchasing/
│   │   │   ├── Supplier/
│   │   │   └── Purchase/          # ReceivePurchase, penomoran dokumen
│   │   ├── Reporting/             # sales, payment, stok; sisanya [R1.1]
│   │   └── Payments/Gateway/      # [P2] adapter Midtrans (belum didaftarkan)
│   ├── Support/                   # lintas modul, tanpa logika bisnis
│   │   ├── Money.php · Quantity.php
│   │   ├── Enums/                 # OrderStatus, MovementType, PaymentMethod, ShiftStatus, ...
│   │   ├── Http/ (ApiResponse.php, Middleware/: EnsureOutletContext, EnsureOpenShift, SetPermissionTeam)
│   │   ├── Exceptions/            # InsufficientStock, InvalidStateTransition, ...
│   │   ├── Contracts/             # Module.php + interface lintas modul (mis. StockDeductor)
│   │   └── Module/BaseModule.php
│   ├── Providers/ (AppServiceProvider.php, ModuleServiceProvider.php)
│   └── Console/                   # cek integritas stok (balance vs SUM movements)
├── bootstrap/ (app.php, providers.php)
├── config/ (modules.php, rbac_scopes.php, permission.php [teams=true], sanctum.php, cors.php, filesystems.php)
├── database/
│   ├── migrations/                # SATU folder, nama berprefix modul (lihat §2.4)
│   ├── factories/
│   └── seeders/ (DatabaseSeeder, UnitSeeder, RoleAndPermissionSeeder, DemoSeeder)
├── routes/ (api.php dikosongkan/tidak dipakai, console.php)
├── tests/
│   ├── Unit/ · Feature/<Modul>/ · Integration/
│   ├── Arch/                      # dependensi modul, registrasi modul, pola migrasi
│   ├── Tenant/                    # suite isolasi tenant dataset-driven
│   └── Fixtures/                  # price-vectors.json (dipakai juga FE), fixture laporan
└── .env.example · composer.json · pint.json · phpstan.neon
```
Isi tiap submodul (folder yang kosong tidak dibuat):
```
Sales/Order/
├── OrderModule.php         # registrasi: routes, policy, event (§2.4)
├── routes.php
├── Http/ (Controllers/, Requests/, Resources/)
├── Actions/                # CreateDraftOrder, ConfirmOrder, CancelOrder
├── Services/               # PriceCalculator, OrderNumberGenerator
├── Models/ · Policies/ · Events/
```

### 2.2 Lapisan dan aturan
| Lapisan | Tugas | Larangan |
|---------|-------|----------|
| Controller | Terima request, panggil Action, kembalikan Resource | Tidak ada logika bisnis atau query |
| Form Request | Validasi + pesan error sesuai SRS | — |
| Action | Satu use case (mis. `ReceivePurchase`, `PayOrder`), pemilik transaksi DB | Tidak memanggil Controller |
| Service | Logika bersama (`PriceCalculator`, `StockService`, `AuditLogger`, `OrderNumberGenerator`) | Tidak membuka transaksi sendiri kecuali `StockService` memakai transaksi bersarang |
| Model | Relasi, scope, cast (`Money`, `Quantity`) | Tidak ada logika alur |
| Policy | Aturan Own/Limited | — |
- Semua model tenant memakai `BelongsToBusiness`. **Dilarang** `DB::table()` untuk tabel tenant tanpa helper scope (dijaga arch test).
- Uang `int` rupiah (`Money`), qty `string/decimal` (`Quantity`); jangan `float`.
- Aksi sensitif memanggil `AuditLogger::log()` setelah commit.
- `Idempotency-Key` di endpoint bayar disimpan di `payments.idempotency_key` (UNIQUE per business).
- RBAC: middleware `outlet.context` memanggil `setPermissionsTeamId(business_id)` sebelum `permission:*`.

### 2.3 Aturan dependensi modul (dijaga arch test `SETUP-009`)
```
Platform  ←  Catalog  ←  Inventory(Recipe, Ingredient)
Platform  ←  Shift
Platform, Catalog, Inventory, Shift  ←  Sales
Inventory  ←  Engine  ←  Sales (Sales memanggil Engine lewat Contract, bukan sebaliknya)
Inventory  ←  Purchasing
semua  ←  Reporting (read-only)
```
Panah = "boleh bergantung pada". Contoh larangan: `Inventory` tidak boleh memanggil `Sales`; `Catalog` tidak boleh memanggil `Inventory\Stock`. Komunikasi balik lewat Event.

### 2.4 Registrasi Modul dan Migrasi
**Prinsip:** modul didaftarkan **eksplisit** di `config/modules.php` (bukan scan folder), supaya jelas modul mana yang aktif, aman untuk `config:cache`/`route:cache`, dan modul Phase 2 bisa ada di repo tanpa aktif. Tanpa package tambahan.

| File | Isi |
|------|-----|
| `app/Support/Contracts/Module.php` | Interface: `routes(): ?string`, `policies(): array` (Model ⇒ Policy), `events(): array` (Event ⇒ listeners) |
| `app/Support/Module/BaseModule.php` | Implementasi default (semua kosong) supaya tiap modul hanya mengisi yang perlu |
| `app/Modules/<Grup>/<Sub>/<Sub>Module.php` | Satu class per submodul, `extends BaseModule` |
| `config/modules.php` | Daftar class modul yang aktif |
| `app/Providers/ModuleServiceProvider.php` | Loop daftar: `Gate::policy()`, `Event::listen()`, load `routes.php` di bawah `Route::prefix('api/v1')->middleware('api')` |
| `bootstrap/providers.php` | Daftarkan `ModuleServiceProvider` |

Aturan:
- Middleware per route ditentukan di `routes.php` masing-masing modul (publik, `auth:sanctum`, `outlet.context`, `shift.open`, `permission:*`).
- Pakai **satu** jalur routing: hapus `api:` dari `withRouting()` di `bootstrap/app.php` atau biarkan `routes/api.php` kosong; jangan dua-duanya aktif.
- **Modul baru = 3 langkah:** (1) salin folder submodul, (2) isi class `XxxModule`, (3) tambah satu baris di `config/modules.php`.
- Verifikasi di `SETUP-001`/`SETUP-005` bahwa `bootstrap/providers.php` dan `withRouting()` sesuai versi Laravel yang terpasang (kerangka di dokumen ini mengikuti pola Laravel 11/12).

**Migrasi:** semua di `database/migrations/`, nama `tanggal_urutGlobal_modul_aksi_tabel`, mis. `2026_10_27_000010_tenant_create_businesses_table.php`. Satu folder karena FK lintas modul butuh urutan pasti. Blok nomor urut per developer untuk mengurangi tabrakan saat merge: Ongki `0xxxxx`, Akmal `1xxxxx`, Naufaldi `2xxxxx` (kolom urutGlobal 6 digit).

**Test penjaga (dikerjakan di `SETUP-009`, Akmal):** `tests/Arch/ModulesTest.php` memeriksa (a) semua class `XxxModule` terdaftar kecuali `Payments\Gateway` (Phase 2) dan (b) nama migrasi cocok `^\d{4}_\d{2}_\d{2}_\d{6}_[a-z]+_(create|alter|add|drop)_[a-z_]+\.php$`; `tests/Arch/DependencyTest.php` memeriksa (c) aturan dependensi §2.3 (mis. `Inventory` tidak memakai `Sales`). Pest dipasang di `SETUP-001`.

**Migrasi bawaan Laravel:** hapus migrasi `users`; migrasi `cache`, `jobs`, dan `sessions` diganti namanya jadi `..._platform_create_..._table`; migrasi Spatie jadi `..._rbac_create_permission_tables`. Tabel framework tidak dihitung dalam 40 tabel domain (ERD v3.0).

**Generator opsional:** command `make:module Sales/Order` (±40 baris) hanya dibuat bila tim memintanya di retro S1; tidak ada di tiket.

---

## 3. Frontend
```
frontend/src/
  main.ts · App.vue
  router/               # Vue Router: guard auth + permission (meta.permission)
  features/<modul>/     # views/, components/, composables/, api.ts, schemas.ts per modul
  lib/api.ts            # fetch wrapper: CSRF, X-Outlet-Id, Idempotency-Key, error mapping
  lib/auth.ts           # useAuth (Pinia), useCan, direktif v-can
  stores/               # Pinia: auth, outlet, cart (hanya state klien)
  components/ui/        # tombol, dialog, tabel, toast (Tailwind)
```
- Composition API dengan `<script setup lang="ts">`. **State server** (data dari API) diambil lewat composable per fitur; **state klien** (auth, outlet aktif, cart) di Pinia. Jangan menyalin data server ke Pinia.
- SPA murni: tidak ada data tenant di build statis. Guard di `router/` memeriksa sesi dan `meta.permission`.
- Estimator total di FE memakai **vektor uji yang sama** dengan backend (`fixtures/price-vectors.json`); angka server menjadi final.
- PWA: `vite-plugin-pwa`, hanya precache aset statis; **tidak** meng-cache respons `/api`.
- API client memakai **path relatif** (`/api/v1/...`), tidak ada URL API di kode FE.
- Formatter rupiah/qty di satu modul (`lib/format.ts`).

## 4. Konvensi API dan Kontrak
- Prefix `/api/v1`; cookie Sanctum SPA; header `X-Outlet-Id`; envelope respons dan kode error mengikuti SDD §4.2–4.3.
- Perubahan kontrak: update SDD §4 dan tiket FE terkait pada PR yang sama.
- Paginasi: `page`, `per_page` (maks 100). Waktu UTC ISO-8601; tampilan memakai timezone outlet.

## 5. Topologi Deployment
```
Browser/PWA ── HTTPS ──▶ pos.<domain>  (Nginx, satu origin)
                           ├─ /api, /sanctum, /up ─▶ php-fpm (Laravel)
                           │                          ├─ queue worker (driver database)
                           │                          ├─ scheduler (cron)
                           │                          └─▶ Supabase: PostgreSQL + Storage
                           └─ lainnya ─▶ file statis frontend/dist (SPA, fallback ke index.html)
```
- **Satu origin:** tidak ada CORS; cookie sesi `SameSite=Lax; Secure; HttpOnly`; `SESSION_DOMAIN=null`; `SANCTUM_STATEFUL_DOMAINS=pos.<domain>`.
- **Dev lokal:** Vite dev server (`localhost:5173`) dengan `server.proxy` untuk `/api` dan `/sanctum` ke `localhost:8000`; selalu buka `http://localhost:5173`; `SANCTUM_STATEFUL_DOMAINS=localhost:5173`. `changeOrigin` dibiarkan `false`.
- **Production:** `npm run build` menghasilkan `frontend/dist`, disajikan Nginx host; tanpa container Node. Docker Compose di VPS: `app` (php-fpm), `queue`, `scheduler`. Database dan storage di Supabase (tidak di Compose). Container hanya dibuka ke `127.0.0.1`.
- Staging dan production terpisah; staging memakai project Supabase terpisah.
- Klien non-browser (Flutter, nanti): token Sanctum lewat path yang sama; endpoint token dikerjakan setelah core (tidak di Phase 1).

## 6. Environment, Database, Queue
- Variabel utama: `APP_KEY`, `DB_*` (host **direct** atau **session pooler**; **bukan** transaction pooler), `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `SESSION_DRIVER=database`, `FILESYSTEM_DISK=s3` (endpoint Supabase), `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`.
- Migrasi dijalankan lewat **direct connection** (butuh hak DDL dan trigger).
- Trigger append-only (`audit_logs`, `stock_movements`, `payments`, `cash_movements`) dibuat di migrasi; test memverifikasinya.
- Queue worker: `php artisan queue:work --tries=3 --backoff=5`; dipantau supervisor; deploy me-restart worker (`queue:restart`).
- Dev lokal: Laragon (PHP 8.4) + Supabase project dev atau PostgreSQL lokal; disk lokal untuk gambar.
- Kunci Supabase `service_role` hanya di server; tidak pernah masuk FE.

## 7. Kualitas
| Alat | Aturan |
|------|--------|
| Pint | Preset Laravel, wajib lulus di pre-commit dan CI |
| Larastan | Level 6 (naik bertahap) |
| ESLint + Prettier | `eslint-plugin-vue` + TypeScript strict |
| Pest | Unit + feature; database PostgreSQL di CI |
| Pest arch | Aturan §2.3 dan larangan `DB::table` tenant |
| Playwright | E2E alur utama di staging dan CI |
Cakupan: setiap BR punya minimal satu test (Test Plan §5). Target cakupan baris `StockService`, `PriceCalculator`, `PayOrder`, `ReceivePurchase` ≥ 90%.

## 8. Seeder dan Data Demo
- `RoleAndPermissionSeeder`: 5 role × permission SRS §2.1 (dipanggil saat register business).
- `UnitSeeder`: `g`, `kg`, `ml`, `L`, `pcs` + konversi kg→g, L→ml = 1000.
- `DemoSeeder` (`DB-SEED-001`): 1 business, 2 outlet, 4 user (satu per role), 20 produk, 15 bahan + recipe, stok awal, 2 supplier. Idempoten; hanya staging/lokal.

## 9. Changelog
| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 3.0 | 2026-10-25 | Versi pertama: Spatie, Supabase, queue database |
| 3.0.1 | 2026-10-25 | §2.1 pohon folder lengkap; §2.4 registrasi modul eksplisit dan aturan migrasi |
| 3.0.2 | 2026-10-25 | Frontend kembali ke Vue 3 + Vite + Pinia; §3 dan §5 ditulis ulang (satu origin, FE statis di Nginx, tanpa container Node) |
