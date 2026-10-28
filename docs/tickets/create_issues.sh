#!/usr/bin/env bash
# Dibuat otomatis oleh generate_github_issues.py dari tickets.md. Jangan diedit tangan; jalankan ulang generatornya.
set -euo pipefail
# Butuh: gh auth login. Jalankan dari dalam repo.
#
# Pakai:
#   DRY_RUN=1 bash create_issues.sh                     # hanya tampilkan apa yang akan dilakukan
#   bash create_issues.sh                               # buat issue yang BELUM ada (yang sudah ada dilewati)
#   UPDATE_EXISTING=1 bash create_issues.sh             # + sunting issue yang judul/isinya beda dari tickets.md
#   ONLY="SETUP-003,SETUP-008" bash create_issues.sh    # batasi ke ID tertentu
#   WITH_MILESTONES=1 bash create_issues.sh             # buat milestone S1..S5, R1.1 dan pasang ke issue
#   DELETE_ISSUES=1 bash create_issues.sh               # hapus issue yang dihasilkan oleh script ini
# Assignee: isi username GitHub (bukan @handle dokumen), mis. GH_ONGKI=usernameku bash create_issues.sh

DRY_RUN="${DRY_RUN:-0}"
UPDATE_EXISTING="${UPDATE_EXISTING:-0}"
WITH_MILESTONES="${WITH_MILESTONES:-0}"
ONLY="${ONLY:-}"
# GH_ONGKI="${GH_ONGKI:-}"
# GH_AKMAL="${GH_AKMAL:-}"
# GH_NAUFALDI="${GH_NAUFALDI:-}"
GH_ONGKI="${GH_ONGKI:-username_ongki}"
GH_AKMAL="${GH_AKMAL:-username_akmal}"
GH_NAUFALDI="${GH_NAUFALDI:-username_naufaldi}"
DELETE_ISSUES="${DELETE_ISSUES:-0}"

LABELS=(
  "setup" "core" "database" "ten" "rbac" "aud" "backend" "auth" "user" "frontend" "testing" "cat" "ing" "rec" "inv" "shf" "ord" "pay" "eng" "integration" "pur" "rpt" "pwa" "seed" "e2e" "sec" "perf" "devops" "r1.1" "kit" "docs"
)
# Pastikan assignment tidak diam-diam dilewati.
for key in GH_ONGKI GH_AKMAL GH_NAUFALDI; do
  if [ -z "${!key}" ] || [ "${!key}" = "username_ongki" ] \
     || [ "${!key}" = "username_akmal" ] \
     || [ "${!key}" = "username_naufaldi" ]; then
    echo "ERROR: Isi username GitHub yang valid untuk $key."
    exit 1
  fi
done
run() { if [ "$DRY_RUN" = "1" ]; then echo "  [dry-run] $*"; else "$@"; fi; }
in_scope() { [ -z "$ONLY" ] && return 0; case ",$ONLY," in *",$1,"*) return 0 ;; esac; return 1; }
assignee_of() { case "$1" in ongki) echo "$GH_ONGKI" ;; akmal) echo "$GH_AKMAL" ;; naufaldi) echo "$GH_NAUFALDI" ;; esac; }

declare -A NUM
load_existing() {
  local num title id
  while IFS=$'\t' read -r num title; do
    id="$(printf '%s' "$title" | sed -n 's/^\[\([^]]*\)\].*/\1/p')"
    if [ -n "$id" ]; then NUM["$id"]="$num"; fi
  done < <(gh issue list --state all --limit 1000 --json number,title --jq '.[] | "\(.number)\t\(.title)"')
}


delete_generated_issues() {
  local repo
  repo="$(gh repo view --json nameWithOwner --jq '.nameWithOwner')"

  echo "Repository: $repo"
  echo "Mencari issue hasil generate dengan ID tiket..."

  local issues
  issues="$(gh issue list \
    --repo "$repo" \
    --state all \
    --limit 1000 \
    --json id,number,title \
    --jq '.[] |
      select(.title | test("^\\[[A-Z][A-Z0-9.-]*-[0-9]{3}\\]")) |
      [.id, .number, .title] | @tsv')"

  if [[ -z "$issues" ]]; then
    echo "Tidak ada issue dengan pola ID tiket yang cocok."
    return 0
  fi

  printf '%s\n' "$issues" |
    cut -f2,3

  local count
  count="$(printf '%s\n' "$issues" | wc -l | tr -d ' ')"
  echo
  echo "Total target: $count issue"

  read -r -p "Ketik DELETE $repo untuk menghapus: " confirm
  if [[ "$confirm" != "DELETE $repo" ]]; then
    echo "Dibatalkan."
    return 1
  fi

  local node_id number title
  while IFS=$'\t' read -r node_id number title; do
    [[ -n "$node_id" ]] || continue

    echo "Menghapus #$number: $title"

    gh api graphql \
      -f query='mutation($id: ID!) {
        deleteIssue(input: {issueId: $id}) {
          clientMutationId
        }
      }' \
      -f id="$node_id" >/dev/null

  done <<< "$issues"

  echo "Proses penghapusan selesai."
}

if [[ "$DELETE_ISSUES" == "1" ]]; then
  delete_generated_issues
  exit $?
fi

ensure_milestones() {
  local pair
  for pair in 'S1|26 Okt–6 Nov 2026' 'S2|9–20 Nov 2026' 'S3|23 Nov–4 Des 2026' 'S4|7–18 Des 2026' 'S5|21–30 Des 2026' 'R1.1|Rilis 1.1 (Januari 2027)'; do
    run gh api "repos/{owner}/{repo}/milestones" -f title="${pair%%|*}" -f description="${pair#*|}" --silent || true
  done
}

issue() {  # id judul label milestone kunci-assignee ; isi issue dari stdin
  local id="$1" title="$2" labels="$3" milestone="$4" akey="$5" body full tmp assignee cur_title cur_body
  body="$(cat)"
  in_scope "$id" || return 0
  full="[$id] $title"
  assignee="$(assignee_of "$akey")"
  # if [ -n "${NUM[$id]:-}" ]; then
  #   if [ "$UPDATE_EXISTING" != "1" ]; then 
  #   echo "ADA    #${NUM[$id]} $id (dilewati)"; return 0; 
  #   fi

  #   cur_title="$(gh issue view "${NUM[$id]}" --json title --jq .title | tr -d '\r')"
  #   cur_body="$(gh issue view "${NUM[$id]}" --json body --jq .body | tr -d '\r')"
    
  #   if [ "$cur_title" = "$full" ] && [ "$cur_body" = "$(printf '%s' "$body" | tr -d '\r')" ]; then 
  #   echo "SAMA   #${NUM[$id]} $id"; return 0; 
  #   fi
  #   echo "SUNTING #${NUM[$id]} $id"
  #   tmp="$(mktemp)"; printf '%s\n' "$body" > "$tmp"
  #   local eargs=(issue edit "${NUM[$id]}" --title "$full" --body-file "$tmp" --add-label "$labels")
  #   if [ -n "$assignee" ]; then eargs+=(--add-assignee "$assignee"); fi
  #   run gh "${eargs[@]}"
  #   rm -f "$tmp"
  #   return 0
  # fi
  # echo "BUAT   $id"
  # tmp="$(mktemp)"; printf '%s\n' "$body" > "$tmp"
  # local cargs=(issue create --title "$full" --body-file "$tmp" --label "$labels")
  # if [ -n "$assignee" ]; then cargs+=(--assignee "$assignee"); fi
  # if [ "$WITH_MILESTONES" = "1" ]; then cargs+=(--milestone "$milestone"); fi
  # run gh "${cargs[@]}"
  # rm -f "$tmp"
  # if [ "$DRY_RUN" != "1" ]; then sleep 1; fi   # hindari batas laju sekunder GitHub
  if [ -n "${NUM[$id]:-}" ]; then
  # Assignment tetap diproses meskipun issue tidak perlu diedit.
  if [ -n "$assignee" ]; then
    run gh issue edit "${NUM[$id]}" --add-assignee "$assignee"
  fi

  if [ "$UPDATE_EXISTING" != "1" ]; then
    echo "ADA    #${NUM[$id]} $id (assignment diperiksa)"
    return 0
  fi

  cur_title="$(gh issue view "${NUM[$id]}" --json title --jq .title | tr -d '\r')"
  cur_body="$(gh issue view "${NUM[$id]}" --json body --jq .body | tr -d '\r')"

  if [ "$cur_title" = "$full" ] &&
     [ "$cur_body" = "$(printf '%s' "$body" | tr -d '\r')" ]; then
    echo "SAMA   #${NUM[$id]} $id (assignment diperiksa)"
    return 0
  fi

  echo "SUNTING #${NUM[$id]} $id"
  tmp="$(mktemp)"
  printf '%s\n' "$body" > "$tmp"

  local eargs=(issue edit "${NUM[$id]}" \
    --title "$full" \
    --body-file "$tmp" \
    --add-label "$labels")

  if [ -n "$assignee" ]; then
    eargs+=(--add-assignee "$assignee")
  fi

  run gh "${eargs[@]}"
  rm -f "$tmp"
  return 0
fi
}

echo "Memastikan label tersedia di repository..."
for label in "${LABELS[@]}"; do
  if [ "$DRY_RUN" = "1" ]; then echo "  [dry-run] gh label create $label --force"; else gh label create "$label" --force >/dev/null 2>&1 || true; fi
done
if [ "$WITH_MILESTONES" = "1" ]; then echo "Memastikan milestone tersedia..."; ensure_milestones; fi
echo "Membaca issue yang sudah ada..."
load_existing
echo "Mulai memproses 148 tiket..."

issue 'SETUP-001' 'Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template' 'setup,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Init monorepo (/backend Laravel 13, /frontend Vue 3 + TS + Vite), branching, PR template. Rujukan: ADR-001; TD §1, §7.

**Acceptance Criteria**
- [ ] Repo punya /backend dan /frontend, `php artisan serve` dan `npm run dev` jalan di Laragon (PHP 8.4)
- [ ] Branch main/develop/feature dan PR template (memuat ID tiket) tersedia
- [ ] Pest (+ pest-plugin-arch) terpasang dan satu test contoh lulus lewat `./vendor/bin/pest`

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'SETUP-002' 'Konfigurasi PostgreSQL lokal (Laragon) + .env.example per environment' 'setup,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Konfigurasi PostgreSQL lokal (Laragon) + .env.example per environment. Supabase hanya untuk staging/production dan diverifikasi di SETUP-008 (keputusan 2026-10-26). Rujukan: ADR-006; TD §6.

**Acceptance Criteria**
- [ ] Koneksi PostgreSQL lokal berfungsi dan `SELECT ... FOR UPDATE` bekerja dalam transaksi
- [ ] `.env.example` memuat semua variabel (DB, CACHE_STORE=database, QUEUE_CONNECTION=database, Supabase Storage) tanpa rahasia dan menyebut larangan transaction pooler

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'SETUP-003' 'Pint, Larastan, Oxlint, ESLint, Prettier, pre-commit hook' 'setup,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Pint, Larastan, Oxlint, ESLint, Prettier, pre-commit hook. Lint frontend memakai standar scaffold Vue (Oxlint + ESLint + Prettier); hook dan CI tanpa `--fix` (keputusan 2026-10-27). Dikerjakan Ongki sebagai pengecualian satu kali karena WIP sudah ada (2026-10-27). Rujukan: TD §7.

**Acceptance Criteria**
- [ ] Commit gagal bila Pint/Oxlint/ESLint melaporkan error
- [ ] Larastan level yang disepakati lulus pada skeleton

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'SETUP-004' 'CI GitHub Actions: lint + Pest + build frontend' 'setup,core' 'S1' 'akmal' <<'__BODY__'
**Context**
CI GitHub Actions: lint + Pest + build frontend. Rujukan: DEPLOY ops §1.

**Acceptance Criteria**
- [ ] PR memicu lint, Pest, dan `npm run build` (Vite); status check; gagal salah satu = merge diblok
- [ ] Pest memakai database PostgreSQL service container (bukan SQLite)

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'SETUP-005' 'Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1' 'setup,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Skeleton backend: app/Modules/*, pola Action/Service/DTO, response envelope, exception handler, prefix /api/v1. Rujukan: TD §2, §2.4; SDD §4.2.

**Acceptance Criteria**
- [ ] Respons sukses dan error mengikuti envelope SDD §4.2 (`success`, `data`, `error.code`)
- [ ] Exception 404/403/409/422 dipetakan ke kode error SDD §4.3
- [ ] Modul baru cukup didaftarkan di `config/modules.php`; routes, policy, dan event-nya termuat lewat `ModuleServiceProvider` tanpa mengubah `routes/api.php` (TD §2.4)
- [ ] `route:cache` dan `config:cache` berjalan tanpa error; satu modul contoh (`Platform/Tenant`) dengan route ping termuat lewat `ModuleServiceProvider`

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'SETUP-006' 'Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog' 'setup,core' 'S1' 'naufaldi' <<'__BODY__'
**Context**
Skeleton frontend: Vue 3 + Vite + TS, Vue Router, API client (CSRF, 401/419/422), Pinia, Tailwind, toast/dialog. Rujukan: TD §3; ADR-001.

**Acceptance Criteria**
- [ ] API client otomatis mengambil `/sanctum/csrf-cookie` dan menyertakan `X-Outlet-Id`
- [ ] 401 mengarahkan ke login; 422 memetakan `details` ke field form

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'SETUP-007' 'Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test' 'setup,core' 'S2' 'ongki' <<'__BODY__'
**Context**
Helper Money & Quantity (BIGINT rupiah, DECIMAL qty, half-up) + enum bersama + unit test. Rujukan: ADR-004; SRS §1.4.

**Acceptance Criteria**
- [ ] `Money::halfUp()` memberi hasil benar untuk kasus .5 positif dan nol
- [ ] Enum OrderStatus, MovementType, PaymentMethod, ShiftStatus tersedia dan sama dengan CHECK di ERD

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
TC-ORD-001
__BODY__

issue 'SETUP-008' 'Staging (VPS + Docker + Nginx) + Supabase staging (DB dan Storage) + HTTPS; FE dan API satu parent domain (atau proxy rewrite)' 'setup,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Staging (VPS + Docker + Nginx) + Supabase staging (project, DB, Storage) + HTTPS; FE dan API satu parent domain (atau proxy rewrite). Seluruh pekerjaan Supabase (project staging, migrasi lewat direct connection, verifikasi R-B, Storage) ada di tiket ini; dev lokal memakai PostgreSQL Laragon (keputusan 2026-10-26). Rujukan: NFR-SEC-003; TD §5, §6; ADR-006.

**Acceptance Criteria**
- [ ] Login lewat FE staging berhasil dengan cookie Sanctum (Secure, HttpOnly, SameSite)
- [ ] Sertifikat HTTPS aktif dan HTTP dialihkan
- [ ] Koneksi staging ke Supabase memakai direct atau session pooler (bukan transaction pooler) dan `SELECT ... FOR UPDATE` bekerja dalam transaksi (R-B; dipindah dari SETUP-002)
- [ ] Project Supabase staging (terpisah dari production) dibuat dan seluruh migrasi (termasuk trigger append-only) berhasil dijalankan lewat direct connection
- [ ] Upload dan baca gambar produk lewat disk `s3` ke bucket Supabase Storage staging berhasil (`FILESYSTEM_DISK=s3`, endpoint Supabase, paket Flysystem S3 terpasang)

**Technical Notes**
- Rujuk Technical Design §1, §5, dan §6. Dev lokal: disk lokal untuk gambar (`FILESYSTEM_DISK=local`); disk `s3` hanya diverifikasi di staging

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'SETUP-009' 'Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi' 'setup,core' 'S1' 'akmal' <<'__BODY__'
**Context**
Arch test Pest: dependensi modul (peta panah), registrasi modul, dan pola nama migrasi. Rujukan: TD §2.3, §2.4.

**Acceptance Criteria**
- [ ] Arch test gagal bila Recipe memanggil POS/Inventory atau Inventory memanggil POS; modul hanya bicara lewat Contract/Facade yang terdaftar
- [ ] Arch test gagal bila ada class `XxxModule` yang belum terdaftar di `config/modules.php` (kecuali `Payments\Gateway`) atau nama migrasi tidak mengikuti pola `modul_aksi_tabel`

**Technical Notes**
- Rujuk Technical Design §1 dan §6

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'DB-TEN-001' 'Migrasi businesses, outlets, users, user_outlets' 'database,ten,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Migrasi businesses, outlets, users, user_outlets. Rujukan: ERD §3.1; BR-022.

**Acceptance Criteria**
- [ ] Semua kolom, CHECK, dan UNIQUE sesuai ERD §3.1
- [ ] Rollback migrasi bersih
- [ ] Migrasi `users` bawaan Laravel dihapus (diganti skema ERD); migrasi framework (sessions, cache, jobs, failed_jobs) dan Spatie diberi nama berprefix modul (`platform_`, `rbac_`), dan tabel framework dihitung terpisah dari 40 tabel domain

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-TEN-003
__BODY__

issue 'DB-TEN-002' 'Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet' 'database,ten,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Trait BelongsToBusiness (global scope + auto-fill business_id) + helper scope outlet. Rujukan: ADR-003; FR-USER-002.

**Acceptance Criteria**
- [ ] Query model tenant otomatis terfilter business_id
- [ ] Insert otomatis mengisi business_id; data business lain mengembalikan 404

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-TEN-001, TC-TEN-002
__BODY__

issue 'DB-RBAC-001' 'Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own' 'database,rbac,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Install Spatie Permission (teams = business_id), seeder 5 role × matriks, config scope Limited/Own. Rujukan: ADR-005; SRS §2.1.

**Acceptance Criteria**
- [ ] 5 role dan semua permission SRS §2.1 ter-seed per business saat register
- [ ] Config scope (`OWN_ONLY`, `LIMITED`) terbaca untuk /me

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-RBAC-001
__BODY__

issue 'DB-AUD-001' 'Migrasi audit_logs + trigger append-only' 'database,aud,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Migrasi audit_logs + trigger append-only. Rujukan: ADR-007; BR-020.

**Acceptance Criteria**
- [ ] UPDATE dan DELETE pada audit_logs ditolak trigger DB
- [ ] Index sesuai ERD §4

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-AUD-002
__BODY__

issue 'BE-AUD-001' 'AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue' 'backend,aud,core' 'S1' 'ongki' <<'__BODY__'
**Context**
AuditLogger::log(action, resource, before, after, reason) dipanggil setelah commit via queue. Rujukan: FR-AUD-001.

**Acceptance Criteria**
- [ ] Pemanggilan di dalam transaksi baru tertulis setelah commit (afterCommit)
- [ ] Payload memuat before/after JSON, user, outlet, IP

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUD-001
__BODY__

issue 'BE-RBAC-001' 'Middleware permission + outlet.context, Policy aturan Limited/Own' 'backend,rbac,core' 'S1' 'ongki' <<'__BODY__'
**Context**
Middleware permission + outlet.context, Policy aturan Limited/Own. Rujukan: FR-USER-002; BR-024, BR-025.

**Acceptance Criteria**
- [ ] Tanpa permission = 403; X-Outlet-Id di luar user_outlets = 403
- [ ] Policy Cashier hanya order sendiri; Manager hanya mengelola Cashier/Inventory/Kitchen

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RBAC-002, TC-RBAC-003
__BODY__

issue 'BE-AUTH-001' 'POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi)' 'backend,auth,core' 'S1' 'ongki' <<'__BODY__'
**Context**
POST /auth/register-business (business + outlet + Owner + seed role, satu transaksi). Rujukan: FR-AUTH-001; UC-AUTH-01; SEQ-AUTH-01.

**Acceptance Criteria**
- [ ] Data valid mengembalikan 201 dan membuat business, outlet, Owner, user_outlets
- [ ] Email ganda dan password lemah mengembalikan 422 dengan pesan SRS FR-AUTH-001

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-001, TC-AUTH-002, TC-AUTH-003
__BODY__

issue 'BE-AUTH-002' 'Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED' 'backend,auth,core' 'S2' 'ongki' <<'__BODY__'
**Context**
Login/logout Sanctum SPA, throttle, lockout 5×/15 menit, audit LOGIN dan LOGIN_FAILED. Rujukan: FR-AUTH-002; UC-AUTH-02; BR-023.

**Acceptance Criteria**
- [ ] Gagal ke-5 mengunci akun 15 menit (423 ACCOUNT_LOCKED)
- [ ] Akun INACTIVE 403; login gagal email tak dikenal tercatat dengan user_id NULL

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-004, TC-AUTH-005, TC-AUTH-006
__BODY__

issue 'BE-AUTH-003' 'GET/PUT /me dan PUT /me/password' 'backend,auth,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
GET/PUT /me dan PUT /me/password. Rujukan: FR-AUTH-003; UC-AUTH-03.

**Acceptance Criteria**
- [ ] Password lama salah mengembalikan 422 "Password lama tidak sesuai."
- [ ] Ganti password mengakhiri sesi lain milik user

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-009
__BODY__

issue 'BE-AUTH-004' '/me mengembalikan outlet + permission dengan scope' 'backend,auth,core' 'S2' 'ongki' <<'__BODY__'
**Context**
/me mengembalikan outlet + permission dengan scope. Rujukan: FR-AUTH-004.

**Acceptance Criteria**
- [ ] Respons memuat role, outlets, permissions[{code, scope}] sesuai SDD §4.5
- [ ] Owner otomatis punya semua outlet business

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUTH-007, TC-AUTH-008
__BODY__

issue 'BE-USER-001' 'CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit' 'backend,user,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
CRUD user + assign role & outlet + nonaktifkan (aturan Manager limited), audit. Rujukan: FR-USER-001; UC-USER-01; BR-024.

**Acceptance Criteria**
- [ ] Manager yang memilih role di luar Cashier/Inventory/Kitchen mendapat 403
- [ ] Owner terakhir tidak bisa dinonaktifkan atau diganti role

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-USER-001, TC-USER-002
__BODY__

issue 'BE-USER-002' 'CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk)' 'backend,user,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
CRUD outlet + pengaturan (pajak, service, minus, diskon kasir, rounding, struk). Rujukan: FR-BIZ-002, FR-BIZ-003; UC-BIZ-02.

**Acceptance Criteria**
- [ ] Manager hanya boleh ubah pajak, service, struk; rounding_increment hanya Owner
- [ ] Kode outlet tidak bisa diubah setelah ada order; perubahan setting tercatat audit before/after

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-USER-003, TC-USER-004, TC-USER-005
__BODY__

issue 'BE-USER-003' 'GET/PUT /business (profil + license tanpa enforcement)' 'backend,user,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
GET/PUT /business (profil + license tanpa enforcement). Rujukan: FR-BIZ-001; UC-BIZ-01.

**Acceptance Criteria**
- [ ] Hanya Owner yang dapat mengubah; perubahan tercatat audit
- [ ] license_plan dan license_valid_until tersimpan tanpa memblokir fitur

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-USER-003
__BODY__

issue 'FE-AUTH-001' 'Halaman Login' 'frontend,auth,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
Halaman Login. Rujukan: UC-AUTH-02; SCR-AUTH-01.

**Acceptance Criteria**
- [ ] Pesan error mengikuti SRS dan tidak membocorkan email atau password yang salah
- [ ] Akun terkunci menampilkan pesan 423 dan state loading/error ada

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUTH-004
__BODY__

issue 'FE-AUTH-002' 'Halaman Register Business' 'frontend,auth,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
Halaman Register Business. Rujukan: UC-AUTH-01; SCR-AUTH-02.

**Acceptance Criteria**
- [ ] Validasi per field menampilkan pesan SRS
- [ ] Sukses mengarahkan ke login/onboarding

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUTH-001
__BODY__

issue 'FE-AUTH-003' 'Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher' 'frontend,auth,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
Auth store (Pinia), route guard (Vue Router), direktif v-can + composable useCan, outlet switcher. Rujukan: FR-AUTH-004; ADR-001.

**Acceptance Criteria**
- [ ] Halaman terproteksi mengarahkan ke login bila 401
- [ ] v-can menyembunyikan UI sesuai permission dan scope; backend tetap menegakkan

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUTH-007
__BODY__

issue 'FE-USER-001' 'Halaman manajemen user (list, form, role, outlet)' 'frontend,user,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
Halaman manajemen user (list, form, role, outlet). Rujukan: UC-USER-01; SCR-USER-01.

**Acceptance Criteria**
- [ ] Dropdown role hanya menampilkan role yang boleh dipilih pelaku (GET /roles)
- [ ] State empty, loading, error tersedia

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-USER-001
__BODY__

issue 'FE-USER-002' 'Halaman pengaturan outlet dan profil business' 'frontend,user,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
Halaman pengaturan outlet dan profil business. Rujukan: UC-BIZ-02; SCR-BIZ-01.

**Acceptance Criteria**
- [ ] Field di luar hak Manager tampil nonaktif
- [ ] Tarif di luar 0–100 menampilkan pesan SRS

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-USER-003
__BODY__

issue 'TEST-AUTH-001' 'Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint)' 'testing,auth,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Suite isolasi tenant dataset-driven (Business A tidak bisa membaca/ubah B di tiap endpoint). Rujukan: NFR-SEC-001; ADR-003.

**Acceptance Criteria**
- [ ] Setiap endpoint terdaftar dites lintas tenant dan mengembalikan 404
- [ ] Dataset otomatis bertambah saat endpoint baru didaftarkan (DoD tiap modul)

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-TEN-001
__BODY__

issue 'TEST-RBAC-001' 'Test matriks role × endpoint' 'testing,rbac,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Test matriks role × endpoint. Rujukan: SRS §2.1.

**Acceptance Criteria**
- [ ] Tiap sel matriks permission diuji (ijin/tolak)
- [ ] Aturan Own/Limited diuji untuk Cashier, Manager, Inventory

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-RBAC-001
__BODY__

issue 'DB-CAT-001' 'Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups' 'database,cat,core' 'S1' 'naufaldi' <<'__BODY__'
**Context**
Migrasi categories, products, modifier_groups, modifier_options, product_modifier_groups. Rujukan: ERD §3.2.

**Acceptance Criteria**
- [ ] UNIQUE parsial SKU dan nama sesuai ERD
- [ ] products tidak punya kolom cost_price

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-CAT-001
__BODY__

issue 'BE-CAT-001' 'CRUD category (status, sort order)' 'backend,cat,core' 'S1' 'naufaldi' <<'__BODY__'
**Context**
CRUD category (status, sort order). Rujukan: FR-CAT-001; UC-CAT-01.

**Acceptance Criteria**
- [ ] Nama unik per business; INACTIVE menyembunyikan produknya dari POS
- [ ] Soft delete, bukan hard delete

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-002
__BODY__

issue 'BE-CAT-002' 'CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp)' 'backend,cat,core' 'S1' 'naufaldi' <<'__BODY__'
**Context**
CRUD product + upload gambar ke Supabase Storage (maks 2 MB, jpg/png/webp). Rujukan: FR-CAT-002; UC-CAT-01; SEQ-CAT-01.

**Acceptance Criteria**
- [ ] SKU ganda 422 "SKU sudah digunakan."; harga non-bulat/negatif 422
- [ ] Gambar >2 MB atau format salah 422 dengan pesan SRS

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD
- Dev: `FILESYSTEM_DISK=local` (disk lokal); verifikasi upload ke Supabase Storage (disk `s3`) dilakukan di SETUP-008

**Test Reference**
TC-CAT-001, TC-CAT-006
__BODY__

issue 'BE-CAT-003' 'Audit perubahan harga + guard produk INACTIVE + soft delete' 'backend,cat,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
Audit perubahan harga + guard produk INACTIVE + soft delete. Rujukan: FR-CAT-002; BR-018, BR-029.

**Acceptance Criteria**
- [ ] Perubahan selling_price menulis PRODUCT_PRICE_CHANGED (before/after)
- [ ] Produk yang pernah dipakai transaksi tidak bisa dihapus permanen

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-004
__BODY__

issue 'BE-CAT-004' 'CRUD modifier group & option + pasang ke produk' 'backend,cat,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
CRUD modifier group & option + pasang ke produk. Rujukan: FR-CAT-003; UC-CAT-02; BR-030.

**Acceptance Criteria**
- [ ] min_select > max_select ditolak dengan pesan SRS
- [ ] SINGLE hanya memperbolehkan satu pilihan; option INACTIVE tidak bisa dipilih

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-003
__BODY__

issue 'BE-CAT-005' 'GET /pos/catalog dengan cache + invalidasi saat katalog berubah' 'backend,cat,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
GET /pos/catalog dengan cache + invalidasi saat katalog berubah. Rujukan: FR-CAT-004; SEQ-CAT-01.

**Acceptance Criteria**
- [ ] Respons tidak memuat HPP
- [ ] Perubahan produk/kategori/modifier membatalkan cache

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-CAT-005
__BODY__

issue 'FE-CAT-001' 'Halaman kategori' 'frontend,cat,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
Halaman kategori. Rujukan: SCR-CAT-01.

**Acceptance Criteria**
- [ ] CRUD kategori jalan dengan state loading/empty/error
- [ ] Aksi dibatasi v-can products.*

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-CAT-002
__BODY__

issue 'FE-CAT-002' 'Halaman list + form produk (upload gambar)' 'frontend,cat,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
Halaman list + form produk (upload gambar). Rujukan: SCR-CAT-02.

**Acceptance Criteria**
- [ ] Error 422 tampil per field
- [ ] Preview gambar dan batas 2 MB divalidasi sebelum upload

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-CAT-001
__BODY__

issue 'FE-CAT-003' 'UI manajemen modifier' 'frontend,cat,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
UI manajemen modifier. Rujukan: SCR-CAT-03.

**Acceptance Criteria**
- [ ] Group, option, dan pemasangan ke produk dapat dikelola
- [ ] Aturan min/max tampil jelas

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-CAT-003
__BODY__

issue 'TEST-CAT-001' 'Feature test katalog (tenant, SKU unik, inactive, cache)' 'testing,cat,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Feature test katalog (tenant, SKU unik, inactive, cache). Rujukan: FR-CAT-001…004.

**Acceptance Criteria**
- [ ] Semua baris tabel validasi FR-CAT-002/003 punya test
- [ ] Test cache invalidasi lulus

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-CAT-001…006
__BODY__

issue 'DB-ING-001' 'Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000)' 'database,ing,core' 'S1' 'akmal' <<'__BODY__'
**Context**
Migrasi units, ingredients, ingredient_unit_conversions (seed kg→g, L→ml = 1000). Rujukan: ERD §3.2; BR-046.

**Acceptance Criteria**
- [ ] Unit g, kg, ml, L, pcs ter-seed; faktor kg→g dan L→ml = 1000
- [ ] last_cost DECIMAL(18,4), CHECK ≥ 0

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-ING-001
__BODY__

issue 'BE-ING-001' 'CRUD ingredient (permission inventory.adjust, baca inventory.view)' 'backend,ing,core' 'S1' 'akmal' <<'__BODY__'
**Context**
CRUD ingredient (permission inventory.adjust, baca inventory.view). Rujukan: FR-ING-001; UC-ING-01.

**Acceptance Criteria**
- [ ] SKU ganda 422; base unit tidak bisa diubah bila sudah ada movement
- [ ] last_cost negatif ditolak; perubahan tidak mengubah movement/order lama

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ING-001, TC-ING-002, TC-ING-003
__BODY__

issue 'BE-ING-002' 'Seeder unit + endpoint GET /units' 'backend,ing,core' 'S1' 'akmal' <<'__BODY__'
**Context**
Seeder unit + endpoint GET /units. Rujukan: FR-ING-001.

**Acceptance Criteria**
- [ ] GET /units mengembalikan 5 satuan
- [ ] Seeder idempoten (aman dijalankan ulang)

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ING-001
__BODY__

issue 'DB-REC-001' 'Migrasi recipes (versi), recipe_items, modifier_option_recipe_items' 'database,rec,core' 'S1' 'akmal' <<'__BODY__'
**Context**
Migrasi recipes (versi), recipe_items, modifier_option_recipe_items. Rujukan: ERD §3.2; BR-013.

**Acceptance Criteria**
- [ ] UNIQUE parsial 1 recipe aktif per produk
- [ ] UNIQUE (recipe_id, ingredient_id)

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-REC-001
__BODY__

issue 'BE-REC-001' 'Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP' 'backend,rec,core' 'S2' 'akmal' <<'__BODY__'
**Context**
Recipe per produk: edit = versi baru, 1 aktif; role view tanpa HPP. Rujukan: FR-REC-001; UC-REC-01; SEQ-REC-01.

**Acceptance Criteria**
- [ ] Simpan membuat version+1 dan menonaktifkan versi lama dalam satu transaksi
- [ ] Qty ≤ 0, bahan ganda, recipe kosong ditolak dengan pesan SRS

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-001, TC-REC-002, TC-REC-004
__BODY__

issue 'BE-REC-002' 'Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price' 'backend,rec,core' 'S2' 'akmal' <<'__BODY__'
**Context**
Kalkulator HPP (qty × last_cost, half-up) tanpa kolom cost_price. Rujukan: FR-REC-003; BR-014.

**Acceptance Criteria**
- [ ] HPP produk tanpa recipe = 0 dan ditandai no recipe
- [ ] HPP hanya tampil untuk Owner dan Manager

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-003
__BODY__

issue 'FE-ING-001' 'Halaman ingredient' 'frontend,ing,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Halaman ingredient. Rujukan: SCR-ING-01.

**Acceptance Criteria**
- [ ] CRUD ingredient dengan pesan error SRS
- [ ] Base unit terkunci bila sudah ada movement

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ING-002
__BODY__

issue 'FE-REC-001' 'Editor recipe di form produk + preview HPP' 'frontend,rec,core' 'S4' 'akmal' <<'__BODY__'
**Context**
Editor recipe di form produk + preview HPP. Rujukan: SCR-CAT-02; UC-REC-01.

**Acceptance Criteria**
- [ ] Satuan input memakai base unit bahan
- [ ] Preview HPP disembunyikan untuk non-Owner/Manager

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-003, TC-REC-004
__BODY__

issue 'TEST-REC-001' 'Test recipe (versioning, validasi, HPP)' 'testing,rec,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Test recipe (versioning, validasi, HPP). Rujukan: FR-REC-001, FR-REC-003.

**Acceptance Criteria**
- [ ] Versioning dan 1 aktif teruji (termasuk constraint DB)
- [ ] Perhitungan HPP half-up sesuai vektor uji

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-REC-001…004
__BODY__

issue 'DB-INV-001' 'Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches' 'database,inv,core' 'S2' 'akmal' <<'__BODY__'
**Context**
Migrasi stock_balances, stock_movements (trigger append-only), stock_adjustments, waste_records, stock_opening_batches. Rujukan: ERD §3.3; ADR-007.

**Acceptance Criteria**
- [ ] UNIQUE idempotensi movement dan CHECK arah delta sesuai ERD
- [ ] UPDATE/DELETE movement ditolak trigger

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-INV-011
__BODY__

issue 'BE-INV-001' 'StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative)' 'backend,inv,core' 'S2' 'akmal' <<'__BODY__'
**Context**
StockService::apply() (lock FOR UPDATE, before/after, allow_negative_stock, forceNegative). Rujukan: ADR-002; ACT-INV-01.

**Acceptance Criteria**
- [ ] Stok kurang dan minus dilarang melempar InsufficientStock
- [ ] forceNegative hanya dipakai jalur settlement gateway (default false)

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-002
__BODY__

issue 'BE-INV-002' 'Idempotensi movement (referensi sama = movement lama) + helper reverse()' 'backend,inv,core' 'S2' 'akmal' <<'__BODY__'
**Context**
Idempotensi movement (referensi sama = movement lama) + helper reverse(). Rujukan: BR-009; NFR-DATA-003.

**Acceptance Criteria**
- [ ] Pemanggilan ulang dengan referensi sama mengembalikan movement lama tanpa baris baru
- [ ] reverse() membuat movement lawan dengan referensi baru

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-001
__BODY__

issue 'BE-INV-003' 'POST /inventory/opening-stock (batch + movement OPENING)' 'backend,inv,core' 'S2' 'akmal' <<'__BODY__'
**Context**
POST /inventory/opening-stock (batch + movement OPENING). Rujukan: FR-INV-001; UC-INV-01; SEQ-INV-01.

**Acceptance Criteria**
- [ ] Satu batch menghasilkan satu movement OPENING per bahan dengan reference_id = id batch
- [ ] Qty ≤ 0 dan bahan ganda dalam batch ditolak

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-004
__BODY__

issue 'BE-INV-004' 'POST/GET /inventory/adjustments (record dibuat sebelum movement)' 'backend,inv,core' 'S2' 'akmal' <<'__BODY__'
**Context**
POST/GET /inventory/adjustments (record dibuat sebelum movement). Rujukan: FR-INV-003; UC-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib dan qty > 0; before/after tersimpan
- [ ] Hasil minus saat minus dilarang 422 INSUFFICIENT_STOCK

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-005
__BODY__

issue 'BE-INV-005' 'POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya)' 'backend,inv,core' 'S3' 'akmal' <<'__BODY__'
**Context**
POST/GET /inventory/waste (alasan enum, snapshot biaya, Kitchen tanpa biaya). Rujukan: FR-INV-004; UC-INV-04; BR-034.

**Acceptance Criteria**
- [ ] Alasan OTHER tanpa catatan 422
- [ ] total_cost tidak ada di respons untuk role Kitchen

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-006, TC-INV-007
__BODY__

issue 'BE-INV-006' 'GET stok, riwayat movement, low stock + stok minus' 'backend,inv,core' 'S3' 'akmal' <<'__BODY__'
**Context**
GET stok, riwayat movement, low stock + stok minus. Rujukan: FR-INV-002, FR-INV-005; UC-INV-02.

**Acceptance Criteria**
- [ ] Filter ingredient, tipe, tanggal dengan paginasi
- [ ] Low stock memakai min_stock override outlet; stok minus tampil terpisah

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-008
__BODY__

issue 'FE-INV-001' 'Halaman stok + badge low stock/minus' 'frontend,inv,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Halaman stok + badge low stock/minus. Rujukan: SCR-INV-01.

**Acceptance Criteria**
- [ ] Badge low stock dan minus berbeda dan menonjol
- [ ] State empty/loading/error ada

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-008
__BODY__

issue 'FE-INV-002' 'Halaman riwayat movement' 'frontend,inv,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Halaman riwayat movement. Rujukan: SCR-INV-02.

**Acceptance Criteria**
- [ ] Filter tipe/tanggal bekerja
- [ ] Tidak ada aksi ubah/hapus movement

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-011
__BODY__

issue 'FE-INV-003' 'Form opening stock & adjustment' 'frontend,inv,core' 'S4' 'akmal' <<'__BODY__'
**Context**
Form opening stock & adjustment. Rujukan: SCR-INV-03.

**Acceptance Criteria**
- [ ] Alasan wajib tampil pada adjustment
- [ ] Error stok kurang menampilkan pesan SRS

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-004, TC-INV-005
__BODY__

issue 'FE-INV-004' 'Form & daftar waste' 'frontend,inv,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Form & daftar waste. Rujukan: SCR-INV-04.

**Acceptance Criteria**
- [ ] Catatan wajib saat alasan OTHER
- [ ] Nilai biaya disembunyikan untuk Kitchen

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-006, TC-INV-007
__BODY__

issue 'TEST-INV-001' 'Unit test StockService: konkurensi, minus, idempotensi, presisi desimal' 'testing,inv,core' 'S4' 'akmal' <<'__BODY__'
**Context**
Unit test StockService: konkurensi, minus, idempotensi, presisi desimal. Rujukan: NFR-DATA-001.

**Acceptance Criteria**
- [ ] 20 apply paralel pada bahan sama: balance = SUM(movements)
- [ ] Presisi DECIMAL(18,4) terjaga

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-001, TC-INV-002, TC-INV-003
__BODY__

issue 'TEST-INV-002' 'Feature test opening, adjustment, waste, stok' 'testing,inv,core' 'S4' 'akmal' <<'__BODY__'
**Context**
Feature test opening, adjustment, waste, stok. Rujukan: FR-INV-001…005.

**Acceptance Criteria**
- [ ] Semua baris tabel validasi FR-INV-003/004 diuji
- [ ] Permission Kitchen/Inventory sesuai matriks

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-004…008
__BODY__

issue 'DB-SHF-001' 'Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet)' 'database,shf,core' 'S1' 'naufaldi' <<'__BODY__'
**Context**
Migrasi shifts, cash_movements (+ UNIQUE parsial 1 OPEN per kasir per outlet). Rujukan: ERD §3.4; BR-002.

**Acceptance Criteria**
- [ ] Shift OPEN kedua milik kasir yang sama di outlet sama ditolak DB
- [ ] cash_movements append-only (trigger)

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-SHF-001
__BODY__

issue 'BE-SHF-001' 'Open shift (opening cash, business_date menurut timezone outlet)' 'backend,shf,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
Open shift (opening cash, business_date menurut timezone outlet). Rujukan: FR-SHF-001; UC-SHF-01; SEQ-SHF-01.

**Acceptance Criteria**
- [ ] Sudah ada shift OPEN 409 SHIFT_ALREADY_OPEN
- [ ] Opening cash negatif 422; audit SHIFT_OPENED

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-001
__BODY__

issue 'BE-SHF-002' 'Cash in / cash out (alasan wajib, cash.manage)' 'backend,shf,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
Cash in / cash out (alasan wajib, cash.manage). Rujukan: FR-SHF-002; UC-SHF-02.

**Acceptance Criteria**
- [ ] Alasan kosong 422; tanpa shift OPEN 409
- [ ] Tercatat append-only dan masuk audit

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-002
__BODY__

issue 'BE-SHF-003' 'ShiftSummaryService: Expected Cash + non_cash per metode' 'backend,shf,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
ShiftSummaryService: Expected Cash + non_cash per metode. Rujukan: FR-SHF-003; BR-004, BR-042.

**Acceptance Criteria**
- [ ] Expected = opening + cash sales (bersih kembalian) + in − out − cash refund
- [ ] Pembayaran non-tunai tampil di non_cash dan tidak masuk Expected Cash

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-003, TC-SHF-007
__BODY__

issue 'BE-SHF-004' 'Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit' 'backend,shf,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
Close shift: tolak bila ada order DRAFT/PENDING, hitung selisih, audit. Rujukan: FR-SHF-003; UC-SHF-03; BR-003.

**Acceptance Criteria**
- [ ] Ada order pending 409 SHIFT_HAS_PENDING_ORDERS dengan jumlah order
- [ ] Sukses menyimpan expected, actual, difference; shift CLOSED tidak menerima transaksi

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-004, TC-SHF-005
__BODY__

issue 'BE-SHF-005' 'GET /shifts/current + open_hours_exceeded + middleware shift.open' 'backend,shf,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
GET /shifts/current + open_hours_exceeded + middleware shift.open. Rujukan: FR-SHF-004; BR-026.

**Acceptance Criteria**
- [ ] open_hours_exceeded true bila terbuka > 24 jam
- [ ] Order tanpa shift OPEN ditolak 409 NO_OPEN_SHIFT

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-SHF-006
__BODY__

issue 'FE-SHF-001' 'Dialog open shift (+ banner > 24 jam)' 'frontend,shf,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
Dialog open shift (+ banner > 24 jam). Rujukan: SCR-SHF-01.

**Acceptance Criteria**
- [ ] Banner peringatan tampil saat open_hours_exceeded
- [ ] Tidak bisa masuk layar POS tanpa shift OPEN

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-SHF-006
__BODY__

issue 'FE-SHF-002' 'Dialog cash in / cash out' 'frontend,shf,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
Dialog cash in / cash out. Rujukan: SCR-SHF-02.

**Acceptance Criteria**
- [ ] Alasan wajib divalidasi
- [ ] Saldo ringkasan ter-refresh setelah simpan

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-SHF-002
__BODY__

issue 'FE-SHF-003' 'Wizard close shift + ringkasan (cetak)' 'frontend,shf,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
Wizard close shift + ringkasan (cetak). Rujukan: SCR-SHF-03.

**Acceptance Criteria**
- [ ] Expected Cash tampil sebelum input uang fisik
- [ ] Pesan error order pending menampilkan jumlah

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-SHF-004, TC-SHF-005
__BODY__

issue 'TEST-SHF-001' 'Test shift: open ganda, selisih kas, close dengan order pending, efek refund' 'testing,shf,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
Test shift: open ganda, selisih kas, close dengan order pending, efek refund. Rujukan: FR-SHF-001…004.

**Acceptance Criteria**
- [ ] Formula Expected Cash diuji dengan data campuran (cash, QRIS, refund)
- [ ] Konkurensi open shift ganda lulus

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-SHF-001…007
__BODY__

issue 'DB-ORD-001' 'Migrasi orders, order_items, order_item_modifiers, order_sequences' 'database,ord,core' 'S2' 'ongki' <<'__BODY__'
**Context**
Migrasi orders, order_items, order_item_modifiers, order_sequences. Rujukan: ERD §3.4.

**Acceptance Criteria**
- [ ] Kolom snapshot, CHECK table_number ⇒ DINE_IN, rounding_amount boleh negatif
- [ ] FK recipe_id ke recipes (NULL = tanpa recipe)

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-ORD-009
__BODY__

issue 'BE-ORD-001' 'OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}' 'backend,ord,core' 'S2' 'ongki' <<'__BODY__'
**Context**
OrderNumberGenerator aman konkurensi {OUTLET}-{YYMMDD}-{urut}. Rujukan: FR-ORD-007; BR-019.

**Acceptance Criteria**
- [ ] Nomor reset per outlet per business date
- [ ] 50 order paralel tidak menghasilkan nomor ganda

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-005
__BODY__

issue 'BE-ORD-002' 'PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total' 'backend,ord,core' 'S2' 'ongki' <<'__BODY__'
**Context**
PriceCalculator: subtotal, diskon, service, pajak exclusive, pembulatan total. Rujukan: FR-ORD-002; BR-005, BR-036; ACT-ORD-02.

**Acceptance Criteria**
- [ ] Vektor uji: subtotal 54.000 → total 62.400 (rounding +30); diskon 10% → 56.100 (−33)
- [ ] Total > 0 tidak boleh menjadi 0 setelah pembulatan

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-001, TC-ORD-002
__BODY__

issue 'BE-ORD-003' 'Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier' 'backend,ord,core' 'S2' 'ongki' <<'__BODY__'
**Context**
Create/replace draft: snapshot nama, SKU, harga, HPP, recipe_id, modifier. Rujukan: FR-ORD-001; UC-ORD-01; SEQ-ORD-01.

**Acceptance Criteria**
- [ ] Produk INACTIVE 422 PRODUCT_UNAVAILABLE; qty di luar 1–999 422
- [ ] Mengubah harga produk setelah order dibuat tidak mengubah order

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-006, TC-ORD-009
__BODY__

issue 'BE-ORD-004' 'Diskon order-level (persen/nominal), batas Cashier per outlet, audit' 'backend,ord,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Diskon order-level (persen/nominal), batas Cashier per outlet, audit. Rujukan: FR-ORD-003; UC-ORD-02.

**Acceptance Criteria**
- [ ] Diskon > subtotal 422 DISCOUNT_INVALID
- [ ] Cashier melebihi batas outlet 403 DISCOUNT_LIMIT_EXCEEDED

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-003, TC-ORD-004
__BODY__

issue 'BE-ORD-005' 'State machine order: confirm, reopen (guard gateway PENDING)' 'backend,ord,core' 'S2' 'ongki' <<'__BODY__'
**Context**
State machine order: confirm, reopen (guard gateway PENDING). Rujukan: FR-ORD-004; STM-ORDER; BR-028.

**Acceptance Criteria**
- [ ] Transisi tidak terdaftar 409 INVALID_STATE_TRANSITION
- [ ] Reopen ditolak bila ada payment SUCCESS; titik ekstensi guard gateway PENDING tersedia

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-007, TC-ORD-010
__BODY__

issue 'BE-ORD-006' 'Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit)' 'backend,ord,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Cancel order (DRAFT/PENDING, alasan wajib, aturan Cashier limited, audit). Rujukan: FR-ORD-005; UC-ORD-03; BR-025.

**Acceptance Criteria**
- [ ] Order sudah dibayar 409 "harus melalui refund"
- [ ] Cashier membatalkan order orang lain 403

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-008
__BODY__

issue 'BE-ORD-007' 'Riwayat & detail order (Cashier hanya miliknya)' 'backend,ord,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Riwayat & detail order (Cashier hanya miliknya). Rujukan: FR-ORD-006; UC-ORD-04.

**Acceptance Criteria**
- [ ] Filter tanggal (business date), status, kasir, nomor order
- [ ] Order tenant lain 404

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-008
__BODY__

issue 'FE-ORD-001' 'Layout layar POS responsif (grid kategori/produk + cart)' 'frontend,ord,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Layout layar POS responsif (grid kategori/produk + cart). Rujukan: SCR-POS-01.

**Acceptance Criteria**
- [ ] Nyaman di HP ≥ 360 px dengan target sentuh ≥ 44 px
- [ ] Load awal < 3 detik (4G, cache dingin)

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-001
__BODY__

issue 'FE-ORD-002' 'Cart store (Pinia), modifier picker, qty' 'frontend,ord,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Cart store (Pinia), modifier picker, qty. Rujukan: SCR-POS-01, SCR-POS-02.

**Acceptance Criteria**
- [ ] Group modifier wajib memblokir tombol simpan dengan pesan SRS
- [ ] Cart bertahan saat berpindah layar dalam sesi

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-006
__BODY__

issue 'FE-ORD-003' 'Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server)' 'frontend,ord,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Panel ringkasan: tipe order, nomor meja, diskon, total (estimator = vektor uji server). Rujukan: SCR-POS-03.

**Acceptance Criteria**
- [ ] Estimasi lokal memakai vektor uji yang sama dengan backend; angka server menjadi final
- [ ] Nomor meja hanya aktif untuk Dine In

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-001, TC-ORD-002, TC-ORD-009
__BODY__

issue 'FE-ORD-004' 'Halaman riwayat & detail order' 'frontend,ord,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Halaman riwayat & detail order. Rujukan: SCR-POS-05.

**Acceptance Criteria**
- [ ] Cashier hanya melihat order miliknya
- [ ] Tombol cancel/refund tampil sesuai status dan permission

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-008
__BODY__

issue 'TEST-ORD-001' 'Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan)' 'testing,ord,core' 'S5' 'ongki' <<'__BODY__'
**Context**
Unit test PriceCalculator table-driven (diskon, service, pajak, pembulatan). Rujukan: BR-005, BR-036.

**Acceptance Criteria**
- [ ] Vektor uji ACT-ORD-02 tersimpan sebagai fixture bersama BE dan FE
- [ ] Kasus increment 0/100/500/1000 dan batas .5 diuji

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ORD-001…003
__BODY__

issue 'TEST-ORD-002' 'Feature test lifecycle order & cancel' 'testing,ord,core' 'S5' 'ongki' <<'__BODY__'
**Context**
Feature test lifecycle order & cancel. Rujukan: STM-ORDER.

**Acceptance Criteria**
- [ ] Semua transisi STM-ORDER diuji (sah dan tidak sah)
- [ ] Cancel Cashier Own/Limited diuji

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ORD-007, TC-ORD-008
__BODY__

issue 'DB-PAY-001' 'Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments)' 'database,pay,core' 'S2' 'ongki' <<'__BODY__'
**Context**
Migrasi payments, refunds (+ kolom gateway nullable, trigger append-only payments). Rujukan: ERD §3.4; Lampiran SDD B.

**Acceptance Criteria**
- [ ] UNIQUE (business_id, idempotency_key) dan UNIQUE parsial SUCCESS per order
- [ ] refunds: UNIQUE(order_id), CHECK CASH ⇒ shift_id NOT NULL; created_by payments nullable

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-PAY-004
__BODY__

issue 'BE-PAY-001' 'Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif' 'backend,pay,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Interface PaymentMethod (strategy): Cash, QRIS manual; CARD/GATEWAY stub nonaktif. Rujukan: FR-PAY-001; BR-007.

**Acceptance Criteria**
- [ ] Cash tendered < total 422; QRIS nominal ≠ total 422
- [ ] CARD/GATEWAY 422 PAYMENT_METHOD_UNAVAILABLE

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-001, TC-PAY-002, TC-PAY-003
__BODY__

issue 'BE-PAY-002' 'POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik' 'backend,pay,core' 'S3' 'ongki' <<'__BODY__'
**Context**
POST /orders/{id}/pay: Idempotency-Key, lock order, PAID→COMPLETED atomik. Rujukan: FR-PAY-001; UC-PAY-01; SEQ-PAY-01.

**Acceptance Criteria**
- [ ] Key sama untuk order sama = 200 + Idempotent-Replay; untuk order lain 409
- [ ] Order bukan PENDING_PAYMENT 409; header kosong 422

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-004, TC-PAY-005, TC-PAY-007
__BODY__

issue 'BE-PAY-003' 'Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit' 'backend,pay,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Panggil DeductStockForOrder dalam transaksi yang sama + event SaleCompleted after-commit. Rujukan: ADR-002; FR-ENG-001.

**Acceptance Criteria**
- [ ] Stok kurang dan minus dilarang: rollback total, payment tidak tersimpan
- [ ] Double submit paralel menghasilkan 1 payment dan 1 set movement

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-006, TC-PAY-008
__BODY__

issue 'BE-PAY-004' 'Builder data struk (pembulatan, metode, kembalian)' 'backend,pay,core' 'S3' 'ongki' <<'__BODY__'
**Context**
Builder data struk (pembulatan, metode, kembalian). Rujukan: FR-PAY-002; UC-PAY-02.

**Acceptance Criteria**
- [ ] Struk memuat baris pembulatan bila ada
- [ ] Cetak ulang dari riwayat memakai data tersimpan

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-014
__BODY__

issue 'BE-PAY-005' 'Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit' 'backend,pay,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Refund penuh: permission, alasan, kas ke shift OPEN pelaku, restock opsional, audit. Rujukan: FR-RFD-001; UC-RFD-01; SEQ-RFD-01.

**Acceptance Criteria**
- [ ] Refund tunai tanpa shift OPEN pelaku 409 NO_OPEN_SHIFT; QRIS shift_id NULL
- [ ] Refund kedua 409 ORDER_ALREADY_REFUNDED

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-009, TC-PAY-010, TC-PAY-011, TC-PAY-012
__BODY__

issue 'FE-PAY-001' 'Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key)' 'frontend,pay,core' 'S5' 'ongki' <<'__BODY__'
**Context**
Dialog pembayaran (numpad, nominal cepat, kembalian, ref QRIS, Idempotency-Key). Rujukan: SCR-POS-04.

**Acceptance Criteria**
- [ ] Key dibuat sekali per dialog dan dipakai ulang saat retry
- [ ] Error stok kurang menampilkan daftar bahan

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-004, TC-PAY-008
__BODY__

issue 'FE-PAY-002' 'Tampilan struk + CSS print 58/80 mm' 'frontend,pay,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Tampilan struk + CSS print 58/80 mm. Rujukan: SCR-POS-06.

**Acceptance Criteria**
- [ ] Print browser 58 dan 80 mm rapi (uji printer thermal)
- [ ] Cetak ulang dari riwayat

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-014
__BODY__

issue 'FE-PAY-003' 'Dialog refund (alasan, restock)' 'frontend,pay,core' 'S5' 'ongki' <<'__BODY__'
**Context**
Dialog refund (alasan, restock). Rujukan: SCR-POS-07.

**Acceptance Criteria**
- [ ] Alasan wajib; restock default tidak dicentang
- [ ] Pesan 409 NO_OPEN_SHIFT jelas

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PAY-009, TC-PAY-010
__BODY__

issue 'TEST-PAY-001' 'Test pembayaran: double submit, overpay, status invalid, kembalian, rollback' 'testing,pay,core' 'S5' 'ongki' <<'__BODY__'
**Context**
Test pembayaran: double submit, overpay, status invalid, kembalian, rollback. Rujukan: FR-PAY-001; NFR-DATA-002/003.

**Acceptance Criteria**
- [ ] 0 payment tanpa potong stok pada test kegagalan
- [ ] Retry dengan key sama tidak menggandakan payment/movement

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PAY-001…008
__BODY__

issue 'TEST-PAY-002' 'Test refund (kas, status, permission, restock)' 'testing,pay,core' 'S5' 'ongki' <<'__BODY__'
**Context**
Test refund (kas, status, permission, restock). Rujukan: FR-RFD-001.

**Acceptance Criteria**
- [ ] Efek ke Expected Cash shift pelaku diuji
- [ ] RETURN movement terbentuk hanya bila restock

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PAY-009…013
__BODY__

issue 'BE-ENG-001' 'RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan' 'backend,eng,core' 'S2' 'akmal' <<'__BODY__'
**Context**
RecipeUsageCalculator: item × recipe (versi snapshot), agregat per bahan. Rujukan: FR-ENG-001; BR-009, BR-010.

**Acceptance Criteria**
- [ ] Bahan sama di beberapa item diagregat menjadi satu angka
- [ ] Produk tanpa recipe dilewati

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-001, TC-ENG-002, TC-ENG-003
__BODY__

issue 'BE-ENG-002' 'DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten' 'backend,eng,core' 'S2' 'akmal' <<'__BODY__'
**Context**
DeductStockForOrder: lock urut ingredient_id, movement SALE idempoten. Rujukan: ADR-002; SEQ-PAY-01.

**Acceptance Criteria**
- [ ] Dua order paralel dengan bahan sama tidak deadlock dan stok benar
- [ ] Movement SALE unit_cost memakai last_cost saat kejadian

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-005
__BODY__

issue 'BE-ENG-003' 'Kebijakan stok minus: stock_shortage di respons dan audit' 'backend,eng,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Kebijakan stok minus: stock_shortage di respons dan audit. Rujukan: FR-ENG-002; BR-011.

**Acceptance Criteria**
- [ ] allow_negative_stock = true: bayar sukses dan stock_shortage terisi
- [ ] false: 422 INSUFFICIENT_STOCK dengan details.ingredients

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-004
__BODY__

issue 'BE-ENG-004' 'Jalur restock refund (movement RETURN dari movement SALE order)' 'backend,eng,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Jalur restock refund (movement RETURN dari movement SALE order). Rujukan: FR-RFD-001; BR-012.

**Acceptance Criteria**
- [ ] Restock membuat RETURN positif per bahan dengan reference REFUND:id
- [ ] Tanpa restock tidak ada movement baru

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PAY-013
__BODY__

issue 'BE-ENG-005' 'GET /orders/{id}/stock-trace' 'backend,eng,core' 'S5' 'akmal' <<'__BODY__'
**Context**
GET /orders/{id}/stock-trace. Rujukan: FR-ENG-003.

**Acceptance Criteria**
- [ ] Menampilkan movement SALE dan RETURN milik order
- [ ] Butuh inventory.view; order tenant lain 404

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-006
__BODY__

issue 'INT-ENG-001' 'Integration test Sale → Payment → Recipe → Movement → Balance' 'integration,eng,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Integration test Sale → Payment → Recipe → Movement → Balance. Rujukan: NFR-DATA-001/002.

**Acceptance Criteria**
- [ ] balance = SUM(movements) untuk semua bahan pasca skenario
- [ ] Skenario lengkap order→bayar→refund restock lulus

**Technical Notes**
- Rujuk Test Plan §4

**Test Reference**
TC-ENG-001…005
__BODY__

issue 'TEST-ENG-001' 'Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel' 'testing,eng,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Edge case: tanpa recipe, recipe diubah setelah jual, 2 order paralel. Rujukan: BR-013.

**Acceptance Criteria**
- [ ] Recipe versi baru tidak mengubah movement order lama
- [ ] Produk tanpa recipe tampil di laporan no recipe

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-ENG-002, TC-ENG-003, TC-ENG-005
__BODY__

issue 'FE-ENG-001' 'Indikator stok menipis/habis di layar POS' 'frontend,eng,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Indikator stok menipis/habis di layar POS. Rujukan: SCR-POS-01.

**Acceptance Criteria**
- [ ] Indikator tampil tanpa memblokir penjualan
- [ ] Data berasal dari endpoint stok (tanpa HPP)

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ENG-004
__BODY__

issue 'DB-PUR-001' 'Migrasi suppliers, purchases, purchase_items, document_sequences' 'database,pur,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
Migrasi suppliers, purchases, purchase_items, document_sequences. Rujukan: ERD §3.6; BR-045…050.

**Acceptance Criteria**
- [ ] purchases UNIQUE (outlet_id, code); status CHECK DRAFT/RECEIVED/CANCELLED
- [ ] document_sequences PK (outlet_id, doc_type, seq_date) dengan lock FOR UPDATE

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-PUR-002
__BODY__

issue 'BE-PUR-001' 'CRUD supplier (soft delete, INACTIVE tidak bisa dipilih)' 'backend,pur,core' 'S2' 'naufaldi' <<'__BODY__'
**Context**
CRUD supplier (soft delete, INACTIVE tidak bisa dipilih). Rujukan: FR-PUR-001; UC-PUR-01.

**Acceptance Criteria**
- [ ] Supplier INACTIVE ditolak saat membuat purchase
- [ ] Permission suppliers.* sesuai matriks (Owner, Manager, Inventory)

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-001, TC-PUR-006
__BODY__

issue 'BE-PUR-002' 'Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO' 'backend,pur,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
Purchase DRAFT: buat/ubah item (satuan beli + faktor konversi), nomor PO. Rujukan: FR-PUR-002; UC-PUR-02; BR-046, BR-049.

**Acceptance Criteria**
- [ ] quantity_base = quantity × faktor (kg→g, L→ml = 1000)
- [ ] Purchase RECEIVED tidak bisa diubah 409 INVALID_STATE_TRANSITION

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-002, TC-PUR-005
__BODY__

issue 'BE-PUR-003' 'Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten' 'backend,pur,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
Receive purchase: movement PURCHASE, update last_cost, atomik dan idempoten. Rujukan: FR-PUR-003; UC-PUR-03; SEQ-PUR-01; BR-045, BR-047.

**Acceptance Criteria**
- [ ] Receive menambah stok base unit dan last_cost = harga per satuan beli ÷ faktor
- [ ] Receive ganda tidak menggandakan movement (409 atau replay)

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-003, TC-PUR-004, TC-PUR-005
__BODY__

issue 'BE-PUR-004' 'GET daftar & detail purchase (filter status, supplier, tanggal)' 'backend,pur,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
GET daftar & detail purchase (filter status, supplier, tanggal). Rujukan: FR-PUR-004; UC-PUR-04.

**Acceptance Criteria**
- [ ] Filter dan paginasi bekerja; data tenant lain 404
- [ ] Permission purchases.view

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-006
__BODY__

issue 'FE-PUR-001' 'Halaman supplier' 'frontend,pur,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
Halaman supplier. Rujukan: SCR-PUR-01.

**Acceptance Criteria**
- [ ] CRUD supplier dengan state loading/empty/error
- [ ] Aksi dibatasi v-can suppliers.*

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PUR-001
__BODY__

issue 'FE-PUR-002' 'Form & daftar purchase (satuan beli, konversi tampil)' 'frontend,pur,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
Form & daftar purchase (satuan beli, konversi tampil). Rujukan: SCR-PUR-02.

**Acceptance Criteria**
- [ ] Konversi ke base unit tampil sebelum simpan
- [ ] Purchase RECEIVED tampil read-only

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PUR-002
__BODY__

issue 'FE-PUR-003' 'Alur terima barang (konfirmasi, hasil stok baru)' 'frontend,pur,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
Alur terima barang (konfirmasi, hasil stok baru). Rujukan: SCR-PUR-03.

**Acceptance Criteria**
- [ ] Konfirmasi menampilkan ringkasan stok sebelum dan sesudah
- [ ] Klik ganda aman

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PUR-003, TC-PUR-004
__BODY__

issue 'TEST-PUR-001' 'Test purchasing: konversi, last_cost, idempotensi, permission' 'testing,pur,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
Test purchasing: konversi, last_cost, idempotensi, permission. Rujukan: FR-PUR-001…004.

**Acceptance Criteria**
- [ ] Semua BR-045…050 punya test
- [ ] balance = SUM(movements) tetap benar setelah receive

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PUR-001…006
__BODY__

issue 'BE-RPT-001' 'Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund)' 'backend,rpt,core' 'S3' 'naufaldi' <<'__BODY__'
**Context**
Laporan sales (gross, diskon, service, pajak, pembulatan, net, COGS, gross profit, refund). Rujukan: FR-RPT-001; UC-RPT-01.

**Acceptance Criteria**
- [ ] Angka cocok dengan fixture; REFUNDED dipisah; pembulatan baris terpisah
- [ ] Periode memakai business_date; outlet di luar akses 403

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-001, TC-RPT-002, TC-RPT-003, TC-RPT-004, TC-RPT-005
__BODY__

issue 'BE-RPT-003' 'Laporan payment per metode' 'backend,rpt,core' 'S4' 'naufaldi' <<'__BODY__'
**Context**
Laporan payment per metode. Rujukan: FR-RPT-003.

**Acceptance Criteria**
- [ ] Total Cash dan QRIS cocok dengan payments
- [ ] Cashier hanya ringkasan miliknya

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-001
__BODY__

issue 'FE-RPT-001' 'Halaman laporan sales & payment' 'frontend,rpt,core' 'S5' 'naufaldi' <<'__BODY__'
**Context**
Halaman laporan sales & payment. Rujukan: SCR-RPT-01.

**Acceptance Criteria**
- [ ] Filter tanggal/outlet/kasir/metode
- [ ] State empty dan loading ada

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-RPT-001
__BODY__

issue 'TEST-RPT-001' 'Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone' 'testing,rpt,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Test laporan sales & payment: angka vs fixture, isolasi tenant, timezone. Rujukan: FR-RPT-001…005.

**Acceptance Criteria**
- [ ] Business date lintas tengah malam benar
- [ ] Isolasi tenant pada semua endpoint laporan

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-RPT-001…005
__BODY__

issue 'FE-PWA-001' 'Manifest + service worker (vite-plugin-pwa), cache aset statis saja' 'frontend,pwa,core' 'S3' 'akmal' <<'__BODY__'
**Context**
Manifest + service worker (vite-plugin-pwa), cache aset statis saja. Rujukan: FR-PWA-001; F-17.

**Acceptance Criteria**
- [ ] Installable (Lighthouse PWA lulus)
- [ ] Respons API tidak pernah di-cache service worker

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PWA-001, TC-PWA-002
__BODY__

issue 'FE-PWA-002' 'Banner offline + aksi tulis ditolak + retry aman (key sama)' 'frontend,pwa,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Banner offline + aksi tulis ditolak + retry aman (key sama). Rujukan: FR-PWA-001.

**Acceptance Criteria**
- [ ] Offline menampilkan banner dan menolak aksi tulis
- [ ] Retry bayar memakai Idempotency-Key yang sama

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-PWA-003
__BODY__

issue 'DB-SEED-001' 'Seeder demo (business, outlet, menu, recipe, stok, supplier)' 'database,seed,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Seeder demo (business, outlet, menu, recipe, stok, supplier). Rujukan: TD §8.

**Acceptance Criteria**
- [ ] Satu perintah menghasilkan data demo siap dipakai UAT
- [ ] Aman dijalankan ulang di staging

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'TEST-E2E-001' 'E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift' 'testing,e2e,core' 'S4' 'akmal' <<'__BODY__'
**Context**
E2E Playwright: register → menu → recipe → shift → order → bayar → stok turun → close shift. Rujukan: F-01…F-10.

**Acceptance Criteria**
- [ ] Alur utama lulus di staging dan CI
- [ ] Stok setelah bayar sesuai recipe

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-E2E-001
__BODY__

issue 'TEST-E2E-002' 'E2E inventory: opening → purchase receive → adjustment → waste' 'testing,e2e,core' 'S4' 'akmal' <<'__BODY__'
**Context**
E2E inventory: opening → purchase receive → adjustment → waste. Rujukan: F-11, F-12, F-20.

**Acceptance Criteria**
- [ ] Stok dan last_cost berubah sesuai purchase
- [ ] Movement tercatat di riwayat

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-E2E-002
__BODY__

issue 'TEST-SEC-001' 'Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload' 'testing,sec,core' 'S4' 'akmal' <<'__BODY__'
**Context**
Checklist keamanan: tenant, throttling, CORS/CSRF, mass assignment, upload. Rujukan: NFR-SEC-001…007.

**Acceptance Criteria**
- [ ] Semua butir checklist lulus atau punya tiket perbaikan
- [ ] Webhook/gateway ditandai out of scope Phase 1

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-SEC-001
__BODY__

issue 'TEST-PERF-001' 'Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements)' 'testing,perf,core' 'S5' 'akmal' <<'__BODY__'
**Context**
Load test 20 pembayaran paralel bahan sama + cek balance = SUM(movements). Rujukan: NFR-PERF-003, NFR-PERF-004.

**Acceptance Criteria**
- [ ] p95 bayar + potong stok < 800 ms (order ≤ 10 item)
- [ ] 0 deadlock dan balance = SUM(movements)

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-PERF-001
__BODY__

issue 'DEPLOY-001' 'Environment production, HTTPS, backup harian + uji restore' 'devops,setup,core' 'S4' 'ongki' <<'__BODY__'
**Context**
Environment production, HTTPS, backup harian + uji restore. Rujukan: NFR-REL-002; DO §3.

**Acceptance Criteria**
- [ ] Backup harian berjalan dan restore berhasil diuji sekali
- [ ] Variabel rahasia hanya di environment server

**Technical Notes**
- Rujuk Deploy & Ops v3.0

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'DEPLOY-002' 'Deploy production + smoke test' 'devops,setup,core' 'S5' 'ongki' <<'__BODY__'
**Context**
Deploy production + smoke test. Rujukan: DO §2.

**Acceptance Criteria**
- [ ] Smoke test (login, buka shift, order, bayar, tutup shift) lulus
- [ ] Rollback ke rilis sebelumnya terdokumentasi dan teruji di staging

**Technical Notes**
- Rujuk Deploy & Ops v3.0

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'BE-AUD-002' 'GET /audit-logs + filter (tanggal, user, aksi, resource)' 'backend,aud,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
GET /audit-logs + filter (tanggal, user, aksi, resource). Rujukan: FR-AUD-002; UC-AUD-01.

**Acceptance Criteria**
- [ ] Filter tanggal, user, aksi, resource bekerja
- [ ] Manager hanya melihat outlet miliknya

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-AUD-003
__BODY__

issue 'FE-AUD-001' 'Halaman audit log' 'frontend,aud,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Halaman audit log. Rujukan: UC-AUD-01; SCR-AUD-01.

**Acceptance Criteria**
- [ ] Daftar memakai paginasi dan filter
- [ ] Order/aksi sensitif menampilkan before/after

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-AUD-003
__BODY__

issue 'BE-REC-003' 'CRUD recipe per modifier option (F-18)' 'backend,rec,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
CRUD recipe per modifier option (F-18). Rujukan: FR-REC-002; UC-REC-02.

**Acceptance Criteria**
- [ ] Qty > 0 dalam base unit; perubahan tercatat audit
- [ ] Hanya recipes.manage yang boleh mengubah

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-REC-005
__BODY__

issue 'FE-REC-002' 'Editor recipe modifier option (F-18)' 'frontend,rec,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Editor recipe modifier option (F-18). Rujukan: SCR-CAT-03.

**Acceptance Criteria**
- [ ] Editor muncul di UI modifier option
- [ ] Validasi qty dan bahan ganda sama dengan recipe produk

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-005
__BODY__

issue 'FE-REC-003' 'Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP' 'frontend,rec,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Tampilan recipe read-only untuk Inventory/Kitchen tanpa HPP. Rujukan: UC-REC-03.

**Acceptance Criteria**
- [ ] Role tanpa recipes.manage tidak melihat tombol ubah
- [ ] HPP tidak ada di respons maupun UI

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-REC-004
__BODY__

issue 'DB-INV-002' 'Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS)' 'database,inv,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Migrasi stock_opnames, stock_opname_items (UNIQUE parsial IN_PROGRESS). Rujukan: ERD §3.3; BR-017.

**Acceptance Criteria**
- [ ] Hanya 1 opname IN_PROGRESS per outlet di level DB
- [ ] UNIQUE (opname_id, ingredient_id)

**Technical Notes**
- Ikuti ERD v3.0; migrasi dijalankan di PostgreSQL lokal saat dev dan lewat direct connection Supabase di staging/production; CHECK/UNIQUE parsial wajib ada di migrasi

**Test Reference**
TC-INV-010
__BODY__

issue 'BE-INV-007' 'Opname: mulai (snapshot), input hitungan, batalkan' 'backend,inv,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Opname: mulai (snapshot), input hitungan, batalkan. Rujukan: FR-INV-006; UC-INV-05; SEQ-INV-02.

**Acceptance Criteria**
- [ ] Mulai saat ada opname berjalan 409 OPNAME_IN_PROGRESS
- [ ] Hitungan negatif 422

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-009, TC-INV-010
__BODY__

issue 'BE-INV-008' 'Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id)' 'backend,inv,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Opname submit: selisih vs snapshot diterapkan ke stok live (urut ingredient_id). Rujukan: BR-017; STM-OPNAME.

**Acceptance Criteria**
- [ ] Penjualan selama penghitungan tidak hilang (selisih dihitung terhadap snapshot)
- [ ] Submit pada opname bukan IN_PROGRESS 409 OPNAME_NOT_ACTIVE

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-INV-009
__BODY__

issue 'FE-INV-005' 'UI opname (mulai, input hitungan, review selisih, submit)' 'frontend,inv,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
UI opname (mulai, input hitungan, review selisih, submit). Rujukan: SCR-INV-05.

**Acceptance Criteria**
- [ ] Layar nyaman di HP (target sentuh ≥ 44 px)
- [ ] Review selisih tampil sebelum submit

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-INV-009
__BODY__

issue 'TEST-INV-003' 'Test opname (snapshot, selisih, konkurensi dengan penjualan)' 'testing,inv,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Test opname (snapshot, selisih, konkurensi dengan penjualan). Rujukan: FR-INV-006.

**Acceptance Criteria**
- [ ] Skenario penjualan selama opname lulus
- [ ] Submit ganda ditolak

**Technical Notes**
- Pest (BE) / Playwright (E2E); data uji memakai factory, tanpa data produksi

**Test Reference**
TC-INV-009, TC-INV-010
__BODY__

issue 'BE-KIT-001' 'Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready' 'backend,kit,r1.1' 'R1.1' 'ongki' <<'__BODY__'
**Context**
Antrian dapur: GET order COMPLETED kitchen_status NEW (tanpa harga), POST ready. Rujukan: FR-KIT-001; UC-KIT-01; BR-033.

**Acceptance Criteria**
- [ ] Respons tanpa harga dan data pembayaran
- [ ] Order REFUNDED otomatis hilang dari antrian

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ORD-011
__BODY__

issue 'FE-KIT-001' 'Halaman antrian dapur (polling 10 detik)' 'frontend,kit,r1.1' 'R1.1' 'ongki' <<'__BODY__'
**Context**
Halaman antrian dapur (polling 10 detik). Rujukan: SCR-KIT-01.

**Acceptance Criteria**
- [ ] Tombol Siap mengubah NEW menjadi READY
- [ ] State empty antrian tampil

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-ORD-011
__BODY__

issue 'BE-ENG-006' 'Pemakaian bahan modifier option ditambahkan ke agregat (F-18)' 'backend,eng,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Pemakaian bahan modifier option ditambahkan ke agregat (F-18). Rujukan: FR-REC-002; FR-ENG-001.

**Acceptance Criteria**
- [ ] Pemakaian modifier dihitung dari recipe option aktif saat pembayaran
- [ ] Modifier tanpa recipe tidak memotong stok

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-ENG-007
__BODY__

issue 'BE-PUR-005' 'Cancel purchase DRAFT + partial receive (purchase_receipts)' 'backend,pur,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Cancel purchase DRAFT + partial receive (purchase_receipts). Rujukan: FR-PUR-005; UC-PUR-05.

**Acceptance Criteria**
- [ ] Cancel hanya DRAFT dengan alasan wajib
- [ ] Partial receive memotong sisa dan status mengikuti jumlah diterima

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-PUR-007
__BODY__

issue 'BE-RPT-002' 'Laporan produk + daftar no recipe' 'backend,rpt,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Laporan produk + daftar no recipe. Rujukan: FR-RPT-002; BR-010.

**Acceptance Criteria**
- [ ] Produk tanpa recipe aktif tampil di daftar
- [ ] Revenue dan qty cocok dengan fixture

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-006
__BODY__

issue 'BE-RPT-004' 'Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian' 'backend,rpt,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Laporan inventory (stok, movement per tipe, low stock, minus) + waste + pembelian. Rujukan: FR-RPT-004.

**Acceptance Criteria**
- [ ] Stok minus muncul pada laporan
- [ ] Waste menampilkan biaya (Kitchen tanpa nilai)

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-008
__BODY__

issue 'BE-RPT-005' 'Dashboard permission-aware (timezone outlet, cache TTL pendek)' 'backend,rpt,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Dashboard permission-aware (timezone outlet, cache TTL pendek). Rujukan: FR-RPT-005; UC-RPT-02.

**Acceptance Criteria**
- [ ] Widget sesuai permission; Cashier hanya data shift sendiri
- [ ] Rata-rata order = net sales ÷ jumlah order

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-007
__BODY__

issue 'FE-RPT-002' 'Halaman laporan produk, inventory, waste' 'frontend,rpt,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Halaman laporan produk, inventory, waste. Rujukan: SCR-RPT-02.

**Acceptance Criteria**
- [ ] Daftar no recipe dan stok minus menonjol
- [ ] Nilai biaya disembunyikan sesuai role

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-RPT-006, TC-RPT-008
__BODY__

issue 'FE-RPT-003' 'Dashboard' 'frontend,rpt,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Dashboard. Rujukan: SCR-RPT-03.

**Acceptance Criteria**
- [ ] Widget mengikuti respons permission
- [ ] Polling/refresh tidak membebani (cache TTL)

**Technical Notes**
- Ikuti Technical Design §3: Vue 3 `<script setup lang="ts">`, Vue Router, Pinia hanya untuk state klien (auth, outlet, cart); `v-can`/`useCan` untuk UI; backend tetap menegakkan izin

**Test Reference**
TC-RPT-007
__BODY__

issue 'BE-RPT-006' 'Export CSV sales dan stock movement (stream, reports.export)' 'backend,rpt,r1.1' 'R1.1' 'naufaldi' <<'__BODY__'
**Context**
Export CSV sales dan stock movement (stream, reports.export). Rujukan: FR-RPT-006; UC-RPT-03.

**Acceptance Criteria**
- [ ] Filter sama dengan layar; hanya business/outlet user
- [ ] Tanpa permission 403

**Technical Notes**
- Ikuti Technical Design §2: Controller tipis → Action/Service → Model; Form Request, Policy, API Resource; transaksi dan `afterCommit` sesuai SDD

**Test Reference**
TC-RPT-009
__BODY__

issue 'DOC-001' 'Dokumentasi API (OpenAPI/Scribe)' 'docs,setup,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
Dokumentasi API (OpenAPI/Scribe). Rujukan: SDD §4.

**Acceptance Criteria**
- [ ] Semua endpoint Phase 1 terdokumentasi dengan contoh
- [ ] Dokumen dapat diakses di staging

**Technical Notes**
- Rujuk SDD §4

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

issue 'DOC-002' 'README + runbook dev (Laragon, Supabase, queue worker)' 'docs,setup,r1.1' 'R1.1' 'akmal' <<'__BODY__'
**Context**
README + runbook dev (Laragon, Supabase, queue worker). Rujukan: TD §6.

**Acceptance Criteria**
- [ ] Developer baru bisa menjalankan proyek dari README tanpa bertanya
- [ ] Runbook queue worker dan scheduler tersedia

**Technical Notes**
- Rujuk SDD §4

**Test Reference**
— (tiket non-fitur; AC dapat diperiksa langsung)
__BODY__

echo "Selesai."
