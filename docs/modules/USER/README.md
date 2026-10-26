# Modul USER — Manajemen User

> Sumber kebenaran tetap `docs/` v3.0; file ini hanya **kumpulan per modul** supaya developer cukup membuka satu folder. Bila ada beda, `docs/` menang.

| | |
|---|---|
| Owner | Naufaldi (2 tiket) |
| Tiket | 2 core (2.00 hari) + 0 rilis 1.1 (0.00 hari) |

## 1. Ringkasan buat developer

CRUD user, memilih role dan outlet. Manager hanya boleh mengelola Cashier, Inventory, Kitchen.

**Hati-hati**
- Owner terakhir tidak boleh dinonaktifkan.
- Nonaktifkan user = akhiri semua sesinya.
- Dropdown role di FE berasal dari `GET /roles` (sudah difilter hak pelaku).

**Dianggap selesai kalau:** User dikelola sesuai matriks permission; test TC-USER-001/002 hijau.

## 2. Kebutuhan Fungsional (dari SRS v3.0)

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

## 3. Aturan Bisnis terkait

| ID | Aturan | Konsekuensi bila dilanggar |
|----|--------|---------------------------|
| BR-018 | Entitas yang pernah dipakai transaksi tidak dihapus permanen: INACTIVE atau soft delete | Laporan historis rusak |
| BR-022 | Semua tabel tenant memiliki `business_id` dengan global scope; data business lain mengembalikan `404` | Kebocoran data |
| BR-024 | Manager hanya mengelola Cashier/Inventory/Kitchen di outlet yang ia akses, dan hanya mengubah pajak, service, struk | `403` |
| BR-031 | SKU produk dan ingredient unik per business; kode outlet unik per business | `422` |
| BR-036 | Pembulatan total: ke kelipatan `rounding_increment` outlet (0/100/500/1000), half-up, selisih boleh negatif, berlaku semua metode pembayaran; total > 0 tidak boleh menjadi 0; selisih disimpan di `orders.rounding_amount`; total setelah pembulatan yang dibayar, di-refund, dan dilaporkan | Kas dan struk tidak sinkron |

## 4. Use Case

| ID | Nama | Aktor | Prioritas | FR | Proses |
|----|------|-------|-----------|----|--------|
| UC-USER-01 | Kelola User | Owner, Manager | Must | FR-USER-001, 002 | |

**Spesifikasi ringkas**

| ID | Prakondisi | Alur utama | Pascakondisi sukses | Alternatif / gagal | FR / BR |
|----|-----------|------------|---------------------|--------------------|---------|
| UC-USER-01 | Owner/Manager | Tambah/ubah user: role, outlet, status | User tersimpan; audit; nonaktif → sesi berakhir | Manager memilih role/outlet di luar hak → `403`; Owner terakhir tidak bisa dinonaktifkan | FR-USER-001, 002 / BR-024 |

## 5. Tabel Database (ERD v3.0)

**`users`**
| Kolom | Tipe | Null | Constraint | Keterangan |
|-------|------|------|-----------|------------|
| name | V(100) | NN | | |
| email | V(255) | NN | UNIQUE `lower(email)` | Unik global, case-insensitive |
| password | V(255) | NN | | Hash |
| status | V(20) | NN | CHECK | |
| failed_login_count | SMALLINT | NN | DEFAULT 0 | BR-023 |
| locked_until | TIMESTAMP | N | | |
| last_login_at | TIMESTAMP | N | | |
| remember_token | V(100) | N | | |
| deleted_at | TIMESTAMP | N | | |

**`user_outlets`**: PK `(user_id, outlet_id)`; `user_id` FK users NN; `outlet_id` FK outlets NN; `business_id` NN. Tanpa `id`/`updated_at`.

## 6. Behavior (Sequence / State / Activity)

Tidak ada diagram khusus; alur sederhana (CRUD).

## 7. Endpoint (SDD v3.0 §4)

| ID | Method | Endpoint | Permission | UC | FR | Deskripsi |
|----|--------|----------|-----------|----|----|-----------|
| API-USER-01 | GET | `/users` | `users.view` | UC-USER-01 | FR-USER-001 | Daftar user |
| API-USER-02 | POST | `/users` | `users.create` | UC-USER-01 | FR-USER-001 | Tambah user |
| API-USER-03 | GET | `/users/{id}` | `users.view` | UC-USER-01 | FR-USER-001 | Detail |
| API-USER-04 | PUT | `/users/{id}` | `users.update` | UC-USER-01 | FR-USER-001 | Ubah role/outlet/profil |
| API-USER-05 | PATCH | `/users/{id}/status` | `users.delete` | UC-USER-01 | FR-USER-001 | Aktif/nonaktif |
| API-USER-06 | GET | `/roles` | `users.view` | UC-USER-01 | FR-USER-001 | Role yang boleh dipilih pelaku |

## 8. Tiket

Detail AC lengkap ada di `tickets/tickets.md` (ID sama).

| ID | Task | Est | Owner | Sprint | Depends on | Test |
|----|------|-----|-------|--------|-----------|------|
| BE-USER-001 | CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit | M | Naufaldi | S3 | BE-RBAC-001, BE-AUD-001 | TC-USER-001, TC-USER-002 |
| FE-USER-001 | Halaman manajemen user (list, form, role, outlet) | M | Naufaldi | S4 | BE-USER-001, FE-AUTH-003 | TC-USER-001 |

**Acceptance Criteria ringkas**

- **BE-USER-001** 
  - [ ] Manager yang memilih role di luar Cashier/Inventory/Kitchen mendapat 403
  - [ ] Owner terakhir tidak bisa dinonaktifkan atau diganti role
  - [ ] Audit `USER_CREATED`/`USER_UPDATED`/`ROLE_CHANGED` tercatat
- **FE-USER-001** 
  - [ ] Dropdown role hanya menampilkan role yang boleh dipilih pelaku (GET /roles)
  - [ ] State empty, loading, error tersedia

## 9. Test Case (Test Plan v3.0)

| ID | Skenario | FR / BR | Level | Rilis |
|----|----------|---------|-------|-------|
| TC-USER-001 | CRUD user: role/outlet di luar hak Manager → 403; nonaktif mengakhiri sesi | FR-USER-001 | Feature | Core |
| TC-USER-002 | Owner terakhir tidak bisa dinonaktifkan/diganti role | FR-USER-001 | Feature | Core |
