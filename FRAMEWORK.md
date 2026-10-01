# 🌿 Reyhan Commerce — Framework Architecture & Extensibility Specification

This document is the official architectural specification for the **Reyhan Commerce Framework** — an enterprise-grade, full-stack, headless, and modular e-commerce engine designed for high concurrency, sovereign customizability, and seamless zero-breaking updates.

---

## 🏛️ 1. Framework Monorepo & Package Architecture

Reyhan is organized as a modular monorepo distributing standalone, reusable packages alongside reference implementations:

```text
reyhan/
├── packages/
│   ├── core/                        # 🟢 Laravel Engine: reyhan-commerce/core
│   │   ├── src/
│   │   │   ├── Actions/             # Domain actions (Checkout, Payment, Orders, Wishlist)
│   │   │   ├── Contracts/Models/    # Domain interfaces (OrderContract, ProductContract, ...)
│   │   │   ├── Data/                # Strongly-typed DTOs (Spatie Laravel Data)
│   │   │   ├── Models/              # Native Eloquent entities (swappable via Reyhan::model())
│   │   │   ├── Services/            # Inventory, Pricing, Otp, Sms, Payment Manager
│   │   │   └── Support/Modules/     # Dynamic PSR-4 extension loader
│   │   ├── database/migrations/     # PostgreSQL 17 JSONB schemas & GIN indices
│   │   └── composer.json            # Package metadata & provider auto-discovery
│   │
│   ├── storefront/                  # 🎨 Nuxt 4 Layer: @reyhan-commerce/storefront
│   │   ├── app/
│   │   │   ├── components/          # Cascading e-commerce UI components (Nuxt UI + Tailwind 4)
│   │   │   ├── composables/         # Reactive hooks (useShopLocale, useApi, usePersian)
│   │   │   ├── layouts/             # Default, checkout, invoice layouts
│   │   │   ├── pages/               # Catalog, PDP, Checkout, Profile, Wishlist, RMA
│   │   │   └── stores/              # Pinia state stores (cart, auth, checkout, catalog)
│   │   ├── nuxt.config.ts           # Storefront layer configuration
│   │   └── package.json             # NPM package specification
│   │
│   └── create-reyhan/               # 🛠️ CLI Scaffolder: create-reyhan
│       ├── bin/index.js             # Interactive Clack-powered CLI wizard
│       └── package.json             # NPM executable package
│
├── backend/                         # Reference Backend Application (Laravel 13 + Filament 5)
├── frontend/                        # Reference Storefront Application (Nuxt 4 + Vue 3)
└── docs/                            # Official Documentation Portal (VitePress)
```

---

## 🔒 2. Concurrency, Stock Locking & Financial Integrity

Reyhan implements a battle-tested **Two-Tier Concurrency Architecture**:

```mermaid
sequenceDiagram
    autonumber
    actor Customer
    participant Storefront as Nuxt 4 Storefront
    participant API as Laravel 13 Core
    participant Redis as Redis 7 (ZSET)
    participant DB as PostgreSQL 17

    Customer->>Storefront: Click "Place Order & Pay"
    Storefront->>API: POST /api/v1/checkout/create-order
    API->>Redis: Atomic Lua: ZADD inventory:reservations:{id} (15m TTL)
    API->>DB: DB::transaction -> Create Order & OrderItems (PendingPayment)
    API-->>Storefront: Gateway Redirect URL
    Customer->>Storefront: Complete Bank Payment & Return
    Storefront->>API: POST /api/v1/payment/verify
    Note over API,DB: lockForUpdate() on Payment & Order
    API->>DB: DB::transaction -> Decrement DB Stock & Set Order Processing
    API->>Redis: Atomic commit() -> Release Redis ZSET Reservation
    API-->>Storefront: Payment Verified & Order Confirmed
```

1. **Tier 1 (Redis Sorted Sets with Self-Purging Expiration):**
   - Stock reservations are stored as `member = "reservationId:quantity"` with `score = expireTimestamp`.
   - Expired reservations are automatically pruned in every check and reservation call via atomic Lua scripts without leaving ghost locks.
2. **Tier 2 (PostgreSQL Pessimistic Locking):**
   - In `VerifyPaymentAction` and `ApproveCardTransferReceiptAction`, rows are locked using `lockForUpdate()` to eliminate race conditions, double-decrements, or duplicate verification webhooks.
3. **Anti-Brute-Force OTP Protection:**
   - OTP codes enforce a maximum of 5 verification attempts. Exceeding the threshold immediately invalidates the OTP token and triggers a 15-minute lockout period.

---

## 🧩 3. Sovereign Extensibility: Zero Core Modification

### A. Dynamic Model Swapping (`Reyhan::model()`)
Core actions and services interact with Eloquent entities through contracts and the `Reyhan::model()` resolver. To override any core model:

1. Create your custom model extending the base model:
   ```php
   namespace App\Models;

   use App\Contracts\Models\OrderContract;
   use App\Models\Order as BaseOrder;

   class CustomOrder extends BaseOrder implements OrderContract
   {
       public function customLoyaltyPoints(): int
       {
           return (int) ($this->final_payable * 0.05);
       }
   }
   ```
2. Bind the custom class in `config/reyhan.php`:
   ```php
   'models' => [
       'order' => \App\Models\CustomOrder::class,
   ],
   ```

### B. Dynamic PSR-4 Plugin Architecture (`backend/extensions/`)
Drop self-contained extensions inside `extensions/{plugin-name}/` with a `module.json` or `composer.json`. `ModuleManager` dynamically injects the extension namespace into Composer's `ClassLoader` and registers its `ServiceProvider` at runtime.

---

## 🌐 4. Reactive Storefront Nuxt 4 Layer

Storefronts consume `@reyhan-commerce/storefront` as a Nuxt 4 Layer:

```ts
// frontend/nuxt.config.ts
export default defineNuxtConfig({
  extends: ['@reyhan-commerce/storefront'],
  
  // Custom store-specific overrides
  app: {
    head: {
      title: 'My Custom Store'
    }
  }
})
```

- **Cascading Component Overrides:** Drop a component with the same name into `components/` to seamlessly override the core implementation.
- **Dynamic Localization & RTL:** `useShopLocale` synchronizes HTML `dir="rtl"` / `dir="ltr"`, locale cookies, parameter interpolation (`{name}`), and API `Accept-Language` headers automatically.

---

## 🛠️ 5. Central CLI Orchestrator (`./reyhan`)

| Command | Purpose |
| :--- | :--- |
| `./reyhan doctor` | Deep diagnostic of PostgreSQL 17, Redis 7, PHP 8.4 extensions, and node environment |
| `./reyhan install` | Local environment provisioning (Migrations, encryption keys, seeders, symlinks) |
| `./reyhan install --prod` | Automated VPS production deployment (FrankenPHP Octane, Docker, Caddy SSL) |
| `./reyhan update` | Safe rolling update: automated DB snapshot, migrations, filament upgrade, cache optimization |
| `./reyhan dev` | Concurrent boot of backend API and Nuxt 4 storefront dev servers |

---

<div align="center">
  <sub>Released under the MIT License. Copyright © 2026 Reyhan Commerce.</sub>
</div>
