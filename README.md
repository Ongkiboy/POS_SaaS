# F&B POS & Inventory

Monorepo: `backend/` (Laravel 13, API) dan `frontend/` (Vue 3 + TypeScript + Vite).

## Sekali per clone

```bash
git config core.hooksPath .githooks
```

Hook `.githooks/pre-commit` menjalankan Pint (backend) serta Oxlint, ESLint, dan Prettier (frontend) **tanpa `--fix`** pada file yang di-stage. Jangan memakai `--no-verify`.

## Menjalankan

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
cd frontend && npm ci && npm run dev
```

## Database
Database dev: PostgreSQL lokal `pos_inventory`; test: `pos_inventory_test`. Supabase hanya staging/production.