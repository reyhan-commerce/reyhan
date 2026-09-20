# EasyShop E-Commerce — Backend Core (Laravel 13)

Production-grade, high-concurrency RESTful backend API for the EasyShop cosmetics and personal care e-commerce platform. Built on **Laravel 13**, **PostgreSQL 18/17**, and **Redis**.

---

## 🛠️ Technology Stack & Environment

| Layer | Component / Specification |
| :--- | :--- |
| **Framework** | Laravel 13.x (PHP 8.5+ with strict typing) |
| **High-Performance Runtime** | Laravel Octane + FrankenPHP binary |
| **Primary Database** | PostgreSQL 18.4 (Database: `shop_db` on port `30102`) |
| **DB Extensions** | `pg_trgm` (trigram fuzzy search), `cube`, `earthdistance` (geodistance) |
| **In-Memory Store** | Redis 8.6 (Port `30101`, partitioned into DB 0, 1, 2) |
| **Queue & Dashboard** | Laravel Horizon (Redis DB 2) |
| **Performance Monitor**| Laravel Pulse (Redis DB 0) |
| **Authentication** | Dual-guard: Laravel Sanctum (Mobile OTP Users) & Session (Filament Admins) |
| **Authorization / RBAC** | Spatie Laravel Permission (scoped to `admin` guard) |
| **Testing & Quality** | Pest PHP 4.x, PHPStan / Larastan Level 8, Laravel Pint (PSR-12) |

---

## 🚀 Quickstart & Setup

### 1. Prerequisites
- PHP 8.3+ (PHP 8.5 recommended) with `pdo_pgsql`, `redis`, `bcmath`, `curl`, `intl`, `mbstring`
- Composer 2.x
- Docker engine with running PostgreSQL (`:30102`) and Redis (`:30101`)

### 2. Environment Configuration
Ensure `.env` contains the required infrastructure connections:
```env
APP_NAME="Jafari Shop"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=30102
DB_DATABASE=shop_db
DB_USERNAME=postgres
DB_PASSWORD=password

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=30101
REDIS_DB=0
REDIS_CACHE_DB=0
REDIS_SESSION_DB=1
REDIS_QUEUE_DB=2
```

### 3. Database Migration
Run all pending migrations, including PostgreSQL extensions:
```bash
php artisan migrate
```

### 4. Running Development Server
Run via standard Artisan server:
```bash
php artisan serve
```
Or run with high-concurrency FrankenPHP Octane server:
```bash
php artisan octane:start --server=frankenphp --port=8000
```

---

## 📡 Core API v1 Endpoints (Phase 1)

| Method | Endpoint | Auth Guard | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/captcha/generate` | Public | Generates SVG visual/math captcha and UUID key |
| `POST` | `/api/v1/auth/otp/request` | Public | Validates captcha and dispatches 5-digit OTP via SMS |
| `POST` | `/api/v1/auth/otp/verify` | Public | Verifies OTP code and returns Sanctum access token |
| `GET` | `/api/v1/auth/me` | `auth:sanctum` | Returns authenticated customer profile |
| `POST` | `/api/v1/auth/logout` | `auth:sanctum` | Revokes active Sanctum device access token |

---

## 🧪 Quality Assurance & Test Commands

Every contribution must pass all three quality gates:

```bash
# 1. Run unit and feature test suite (Pest)
./vendor/bin/pest

# 2. Run static analysis at Level 8 (Larastan)
./vendor/bin/phpstan analyse

# 3. Check code style standards (Laravel Pint)
./vendor/bin/pint --test

# 4. Auto-fix code style issues
./vendor/bin/pint
```

---

## 📂 Architecture Reference

For in-depth domain architecture, multi-database Redis schema, text normalization pipeline, and AI agent operational guidelines, see [`ARCHITECTURE.md`](./ARCHITECTURE.md).
