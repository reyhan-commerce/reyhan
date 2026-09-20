# Backend Architectural Manual & Context (AI & Human Guide)

This document provides a comprehensive blueprint of the backend architecture, coding conventions, domain rules, and developer tooling for the EasyShop cosmetics platform. **All AI agents and engineers working on this repository must strictly adhere to the patterns defined here.**

---

## 1. Architectural Philosophy & Principles

1. **Decoupled Headless Model**:
   - The backend is strictly an API provider. It renders no Blade views for end-users.
   - All client responses must follow RFC 7807 Problem Details for errors and standardized JSON structures for data:
     ```json
     {
       "success": true,
       "data": { ... },
       "meta": { ... }
     }
     ```
2. **Strict Multi-Auth Isolation**:
   - **`users` Table**: End-customers who interact with the Nuxt frontend. They authenticate solely via mobile phone number + SMS OTP through Laravel Sanctum. No passwords or Spatie roles are assigned to `users`.
   - **`admins` Table**: Store administrators and staff who access the Filament 5 admin panel. Authenticated via session cookies on the `admin` guard, governed by Spatie Permissions and Filament Shield.
3. **Database-Level Data Integrity**:
   - Primary database is PostgreSQL 18/17 (`shop_db`).
   - Extensions enabled:
     - `pg_trgm`: Used for GIN-indexed trigram fuzzy search across Persian and English product titles and brand names.
     - `cube` & `earthdistance`: Used for spatial distance calculation (e.g., shipping hubs to delivery addresses).
4. **Redis Database Partitioning**:
   - Redis instances must strictly use assigned database indexes:
     - **DB 0**: Application Cache, Rate Limiting, and Laravel Pulse metrics.
     - **DB 1**: User & Admin HTTP Sessions.
     - **DB 2**: Asynchronous Queues, Scheduled Jobs, and Laravel Horizon.

---

## 2. Directory Structure & Domain Layering

```
backend/
├── app/
│   ├── Actions/            # Single-responsibility command actions (e.g., VerifyOtpAction)
│   ├── Enums/              # PHP 8.1+ backed enums (OrderStatus, PaymentStatus, etc.)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/     # RESTful controllers with apiResource mapping
│   │   ├── Middleware/     # Route and pipeline middleware
│   │   ├── Requests/       # Form requests with Persian text normalization
│   │   └── Resources/      # JsonResource transformers
│   ├── Models/             # Eloquent domain models with strict typing
│   ├── Pipelines/
│   │   └── Normalizer/     # Persian text normalization pipes (Digits, Characters, ZWNJ)
│   ├── Services/           # Domain business logic (SmsManager, PaymentManager, StockService)
│   └── ValueObjects/       # Immutable value objects
├── config/
├── database/
│   ├── migrations/         # PostgreSQL schema definitions
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── api/
│   │   └── v1.php          # RESTful v1 endpoints
│   ├── api.php
│   └── web.php
└── tests/
    ├── Feature/            # Pest functional API tests
    └── Unit/               # Pest isolated unit tests
```

---

## 3. Mandatory Persian Text Normalization Pipeline

All incoming text payloads from both users and administrators must pass through the `PersianNormalizer` pipeline before validation and persistence:
- **Title / Description Pipes**:
  - Unify Arabic Kaf (`ك`) to Persian Keh (`ک`).
  - Unify Arabic Yeh (`ي`, `ى`) to Persian Yeh (`ی`).
  - Convert Latin (`0-9`) and Arabic-Indic (`٠-٩`) digits into Persian digits (`۰-۹`).
  - Clean and normalize Zero-Width Non-Joiner (ZWNJ / `\x{200C}`).
- **Identifier / Phone Pipes**:
  - Convert Persian and Arabic digits into standard ASCII (`0-9`) for mobile numbers and Iranian National Code (`national_code`).

---

## 4. Stock Reservation & Concurrency Defense

To prevent overselling during high-traffic flash sales:
1. **Tier 1 (Checkout)**: Temporary atomic reservation via Redis Lock (`atomicLock('stock:variant:{id}', 15 minutes)`).
2. **Tier 2 (Payment Verification)**: In a PostgreSQL transaction, lock inventory row using `lockForUpdate()`, verify actual stock, decrement stock, and commit.

---

## 5. Coding Standards & AI Agent Guidelines

- **Strict Typing**: Always add `declare(strict_types=1);` at the top of every PHP file.
- **RESTful Routing**: Strictly use `Route::apiResource(...)` inside `routes/api/v1.php`.
- **Quality Gates**:
  - Run `./vendor/bin/pint --test` before committing any code.
  - Run `./vendor/bin/phpstan analyse` to verify Larastan Level 8 compliance.
  - Run `./vendor/bin/pest` to verify all functional tests pass.
