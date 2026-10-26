# Modul AUD-PWA — Audit & PWA

> Sumber kebenaran tetap `docs/` v3.0; file ini hanya **kumpulan per modul** supaya developer cukup membuka satu folder. Bila ada beda, `docs/` menang.

| | |
|---|---|
| Owner | Ongki (2 tiket), Naufaldi (2 tiket), Akmal (2 tiket) |
| Tiket | 4 core (2.50 hari) + 2 rilis 1.1 (1.00 hari) |

## 1. Ringkasan buat developer

Audit: setiap aksi sensitif dicatat (append-only). PWA: aplikasi bisa di-install dan aset statis di-cache, tetapi respons API tidak.

**Hati-hati**
- `audit_logs` dilindungi trigger DB (tidak bisa UPDATE/DELETE).
- Pencatatan audit setelah commit via queue.
- Service worker hanya precache aset statis.
- Layar baca audit log masuk rilis 1.1.

**Dianggap selesai kalau:** Aksi wajib tercatat; PWA installable; test AUD/PWA lulus.

## 2. Kebutuhan Fungsional (dari SRS v3.0)

### Modul AUD — Audit Trail

#### FR-AUD-001: Pencatatan
Aksi wajib: `LOGIN`, `LOGIN_FAILED`, `BUSINESS_REGISTERED`, `USER_CREATED/UPDATED`, `ROLE_CHANGED`, `PRODUCT_PRICE_CHANGED`, `RECIPE_UPDATED`, `SETTING_CHANGED` (pajak, service, stok minus, diskon kasir, pembulatan), `SHIFT_OPENED/CLOSED`, `CASH_IN/CASH_OUT`, `DISCOUNT_APPLIED`, `ORDER_CANCELLED`, `PAYMENT_CREATED`, `REFUND_CREATED`, `STOCK_ADJUSTMENT`, `WASTE`, `STOCK_OPNAME_SUBMITTED`, `OPENING_STOCK`; Phase 2: `GATEWAY_CONFIG_CHANGED`, `GATEWAY_PAYMENT_INITIATED`, `GATEWAY_PAYMENT_SETTLED`, `GATEWAY_PAYMENT_FAILED`, `GATEWAY_PAYMENT_CANCELLED`, `GATEWAY_STOCK_OVERRIDE`, `GATEWAY_REFUND`. Field: business, outlet, user, action, resource, before/after JSON, alasan, IP, waktu. Ditulis setelah commit. Append-only (BR-020). Aksi yang dipotong dari scope (UI audit) tetap dicatat.

#### FR-AUD-002: Baca Audit Log (Should) `[R1.1]`
Filter tanggal, user, aksi, resource. Butuh `audit.view` (Owner; Manager hanya outlet miliknya).

---



## 3. Aturan Bisnis terkait

| ID | Aturan | Konsekuensi bila dilanggar |
|----|--------|---------------------------|
| BR-020 | `audit_logs`, `stock_movements`, `payments`, `cash_movements` append-only; tidak ada endpoint ubah/hapus | Jejak audit rusak |

## 4. Use Case

| ID | Nama | Aktor | Prioritas | FR | Proses |
|----|------|-------|-----------|----|--------|
| UC-AUD-01 | Lihat Audit Log `[R1.1]` | Owner, Manager | Should | FR-AUD-002 | |

**Spesifikasi ringkas**

| ID | Prakondisi | Alur utama | Pascakondisi sukses | Alternatif / gagal | FR / BR |
|----|-----------|------------|---------------------|--------------------|---------|
| UC-AUD-01 | `audit.view` | Filter tanggal, user, aksi, resource | Daftar audit (Manager: outlet miliknya) | — | FR-AUD-002 / BR-020 |

## 5. Tabel Database (ERD v3.0)

**`audit_logs`** 🔒
| Kolom | Tipe | Null | Constraint | Keterangan |
|-------|------|------|-----------|------------|
| outlet_id | BIGINT | N | FK outlets | |
| user_id | BIGINT | N | FK users | NULL untuk `LOGIN_FAILED` email tak dikenal |
| action | V(50) | NN | | Daftar di SRS FR-AUD-001 |
| resource_type | V(50) | NN | | |
| resource_id | BIGINT | N | | |
| before_data, after_data | JSONB | N | | |
| reason | V(255) | N | | |
| ip_address | V(45) | N | | |

## 6. Behavior (Sequence / State / Activity)

Tidak ada diagram khusus; alur sederhana (CRUD).

## 7. Endpoint (SDD v3.0 §4)

| ID | Method | Endpoint | Permission | UC | FR | Deskripsi |
|----|--------|----------|-----------|----|----|-----------|
| API-AUD-01 | GET | `/audit-logs` | `audit.view` | UC-AUD-01 | FR-AUD-002 | Audit log `[R1.1]` |

## 8. Tiket

Detail AC lengkap ada di `tickets/tickets.md` (ID sama).

| ID | Task | Est | Owner | Sprint | Depends on | Test |
|----|------|-----|-------|--------|-----------|------|
| BE-AUD-001 | AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue | S | Ongki | S1 | DB-AUD-001, SETUP-005 | TC-AUD-001 |
| DB-AUD-001 | Migrasi audit_logs + trigger append-only | S | Ongki | S1 | DB-TEN-001 | TC-AUD-002 |
| FE-PWA-001 | Manifest + service worker (vite-plugin-pwa), cache aset statis saja | M | Akmal | S3 | FE-ORD-001 | TC-PWA-001, TC-PWA-002 |
| FE-PWA-002 | Banner offline + aksi tulis ditolak + retry aman (key sama) | S | Akmal | S5 | FE-PWA-001, BE-PAY-002 | TC-PWA-003 |
| BE-AUD-002 | GET /audit-logs + filter (tanggal, user, aksi, resource) | S | Naufaldi | R1.1 | BE-AUD-001, BE-RBAC-001 | TC-AUD-003 |
| FE-AUD-001 | Halaman audit log | S | Naufaldi | R1.1 | BE-AUD-002, FE-AUTH-003 | TC-AUD-003 |

**Acceptance Criteria ringkas**

- **BE-AUD-001** 
  - [ ] Pemanggilan di dalam transaksi baru tertulis setelah commit (afterCommit)
  - [ ] Payload memuat before/after JSON, user, outlet, IP
- **DB-AUD-001** 
  - [ ] UPDATE dan DELETE pada audit_logs ditolak trigger DB
  - [ ] Index sesuai ERD §4
- **FE-PWA-001** 
  - [ ] Installable (Lighthouse PWA lulus)
  - [ ] Respons API tidak pernah di-cache service worker
- **FE-PWA-002** 
  - [ ] Offline menampilkan banner dan menolak aksi tulis
  - [ ] Retry bayar memakai Idempotency-Key yang sama
- **BE-AUD-002** `[R1.1]`
  - [ ] Filter tanggal, user, aksi, resource bekerja
  - [ ] Manager hanya melihat outlet miliknya
- **FE-AUD-001** `[R1.1]`
  - [ ] Daftar memakai paginasi dan filter
  - [ ] Order/aksi sensitif menampilkan before/after

## 9. Test Case (Test Plan v3.0)

| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-AUD-001 | Semua aksi wajib FR-AUD-001 tercatat dengan before/after dan after-commit | FR-AUD-001 | Feature | Core |
| TC-AUD-002 | UPDATE/DELETE audit_logs ditolak trigger DB | BR-020; ADR-007 | Unit | Core |
| TC-AUD-003 | Filter audit log tanggal/user/aksi/resource; Manager hanya outlet miliknya | FR-AUD-002 | Feature | R1.1 |
| TC-PWA-001 | PWA installable (Lighthouse) | FR-PWA-001 | Manual | Core |
| TC-PWA-002 | Service worker tidak meng-cache respons API | FR-PWA-001 | Manual | Core |
| TC-PWA-003 | Offline: banner tampil, aksi tulis ditolak, retry bayar memakai key sama | FR-PWA-001 | Manual | Core |
