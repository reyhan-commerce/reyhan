# Reyhan Commerce (ریحان) — Next-Gen Headless E-Commerce Framework

Production-grade, enterprise-scale, modular and headless e-commerce framework for rapidly building, customizing, and scaling any online store (Fashion, Electronics, Beauty, Groceries, Digital Goods, General Merchandise) built on **Laravel 13 (Backend)** and **Nuxt 4 + Nuxt UI + Tailwind CSS v4 (Frontend)**.

The system is designed with a **Decoupled (Headless) Architecture**:
- **Backend**: High-performance RESTful API built on **Laravel 13**, **PostgreSQL 17**, **Filament 5**, **Laravel Octane**, **Horizon**, **Pulse**, and **Spatie Laravel Data** inside the `backend/` directory, backed by **Redis** as the default driver for Cache, Sessions, and Queues.
- **Frontend**: Server-Side Rendered (SSR) modern web application built on **Nuxt 4**, **Vue 3**, **Nuxt UI**, **Tailwind CSS v4**, and **TypeScript 7.x** inside the `frontend/` directory, governed by the **`ui-ux-pro-max`** design intelligence skill, optimized for SEO ([`@nuxtjs/seo`](https://github.com/harlan-zw/nuxt-seo)), AI shopping agents, and Persian RTL typography.

---

## ⚡ Quick Start (Instant Installation via NPX)

Scaffold a brand-new, production-ready Reyhan store in seconds with zero global dependencies:

```bash
npx @reyhan-commerce/create-reyhan@latest my-store
# or with pnpm
pnpm create @reyhan-commerce/reyhan my-store
```

The interactive CLI will prompt for your store name, PostgreSQL and Redis credentials, and theme preset, then provision both Laravel 13 and Nuxt 4 environments automatically.

---

## 1. Project Directory Structure

```text
reyhan/
├── README.md               # Master architecture overview and system guidelines (This file)
├── BACKEND.md              # In-depth technical specification for the Laravel 13 backend
├── FRONTEND.md             # In-depth technical specification for the Nuxt 4 frontend
├── backend/                # Laravel 13 application source code
│   ├── app/
│   │   ├── Actions/        # Single-responsibility business actions
│   │   ├── Data/           # Spatie Laravel Data DTOs
│   │   ├── Enums/          # Backed Enums with Filament UI contracts
│   │   ├── Http/
│   │   │   ├── Controllers/# Thin controllers (orchestration only)
│   │   │   ├── Requests/   # FormRequest classes with Iranian validation
│   │   │   └── Resources/  # Fluent JsonResource response transformers
│   │   └── Services/       # Isolated domain & infrastructure services
│   │       └── Normalization/ # Persian text, digits, and ZWNJ normalizer pipeline
│   ├── bootstrap/
│   ├── config/
│   ├── database/           # PostgreSQL 17 migrations & Iranian geo seeders
│   ├── routes/
│   ├── tests/              # Pest PHP functional tests (Non-UI)
│   └── composer.json
└── frontend/               # Nuxt 4 + Vue 3 application source code
    ├── app/                # App root wrapped in <UApp> with RTL & Dark mode
    ├── components/         # Nuxt UI accessible components & variant selectors
    ├── composables/        # useApi, useAuth, useCart, usePersianCurrency
    ├── layouts/            # Default, Checkout, and Profile layouts
    ├── pages/              # Catalog, PDP, Checkout, Profile views
    ├── public/             # llms.txt and llms-full.txt for AI agents
    ├── stores/             # Pinia state stores (Cart, Auth, Checkout)
    └── package.json        # Nuxt 4, Nuxt UI, Tailwind CSS v4, TypeScript 7.x
```

---

## 2. High-Level Architecture Diagram

```mermaid
graph TD
    Client[Web & Mobile Customers] -->|HTTPS / SSR / SPA| NuxtApp[Nuxt 4 + Nuxt UI + Tailwind v4 + TS 7.x]
    Admin[Store Administrators & Staff] -->|Filament 5 Admin Panel fa/RTL| LaravelApp[Laravel 13 + Octane Backend]
    
    NuxtApp -->|RESTful JSON APIs + Sanctum Auth| LaravelApp
    
    subgraph CoreBackend [Laravel 13 Core Backend]
        LaravelApp --> ThinControllers[Thin Controllers]
        ThinControllers --> Normalizer[Persian Normalizer Pipeline]
        Normalizer --> FormRequests[Validation Request Classes]
        FormRequests --> SpatieData[Spatie Laravel Data DTOs]
        ThinControllers --> Services[Action & Domain Services]
        Services --> SMS[SMS Manager + Integrations]
        Services --> OTP[OTP Verification Service]
        Services --> Captcha[Self-Hosted Captcha Engine]
        Services --> Payment[Shetabit Payment Service]
        Services --> Inventory[Two-Tier Stock Locking Service]
        Services --> Pricing[Rule-Based Promotion Engine]
        Services --> Shipping[Geo & Shipping Calculator]
    end
    
    subgraph RedisInfrastructure [Default Redis Engine (Cache, Session, Queues)]
        RedisCache[(Redis DB 0: Application Cache, OTP & Pulse)]
        RedisSession[(Redis DB 1: User & Admin Sessions)]
        RedisQueue[(Redis DB 2: Horizon Queue Workers)]
        
        LaravelApp --> RedisCache
        LaravelApp --> RedisSession
        LaravelApp --> RedisQueue
        RedisQueue --> Horizon[Laravel Horizon Worker]
    end

    subgraph DataStorage [PostgreSQL 17 & Media Storage]
        LaravelApp --> PostgreSQL[(PostgreSQL 17: UTF-8, pg_trgm, JSONB, Earthdistance)]
        LaravelApp --> StorageDisk[Switchable Storage: Local Disk / S3 MinIO / ArvanCloud]
        LaravelApp --> Pulse[Laravel Pulse Monitoring]
        LaravelApp --> Backup[Spatie Backup + Filament UI]
    end
    
    SMS --> Drivers[SMS Drivers: Kavenegar, FarazSMS, Log]
    Payment --> Gateways[Iranian Bank Gateways / Shaparak]
```

---

## 3. Technology Stack & Infrastructure Defaults

| Layer / Component | Technology | Default Driver / Version | Notes / Reference |
| :--- | :--- | :--- | :--- |
| **Backend Runtime** | PHP | 8.3+ / 8.4 | Strict typing, JIT, Opcache, Swoole/FrankenPHP |
| **Backend Framework** | Laravel | 13.x | Headless API, Action-oriented, Thin Controllers |
| **Primary Database** | **PostgreSQL** | **17.x** | UTF-8, `pg_trgm` fuzzy search, JSONB GIN indexes |
| **Default Cache** | **Redis** | `CACHE_STORE=redis` (DB 0) | Tagged cache, fast product catalog and settings retrieval |
| **Default Session** | **Redis** | `SESSION_DRIVER=redis` (DB 1) | Distributed session storage across Octane workers |
| **Default Queue** | **Redis** | `QUEUE_CONNECTION=redis` (DB 2)| Fully managed by Laravel Horizon |
| **Text Normalization** | Pipeline Engine | Internal | Standardizes Arabic/Persian Yeh, Kaf, Digits, ZWNJ |
| **Data Transfer Objects** | Spatie Laravel Data | Latest | Strongly-typed DTOs extending `Spatie\LaravelData\Data` |
| **Admin Panel** | Filament | 5.x | Persian locale (`fa`), RTL, Shield, Settings, Jalali |
| **Frontend Framework** | **Nuxt** | **4.x** (Vue 3.5+) | SSR / ISR, Pinia, Nuxt Image |
| **Frontend UI Library** | **Nuxt UI** | Latest Stable | Built on Reka UI & Tailwind CSS v4, 100% native adoption |
| **Frontend Styling** | **Tailwind CSS** | **v4.x** | Next-gen Oxide engine, CSS-first `@theme`, Zero custom CSS |
| **Frontend Language** | **TypeScript** | **7.x** (e.g. 7.0.2+) | Strict typed schemas matching backend DTOs |
| **Design Intelligence** | `ui-ux-pro-max` | Skill | Universal design: accessible to both children and the elderly |
| **SEO Ecosystem** | `@nuxtjs/seo` | Latest | Sitemap, Robots, Schema.org, OpenGraph cards |
| **AI Agent Readiness** | `llms.txt` + JSON-LD | Standard | Structured catalog index for LLMs and autonomous shopping agents |
| **Testing Suite** | Pest PHP | Latest | Functional / Unit / Feature tests (Strictly Non-UI) |

---

## 4. Dedicated Documentation Files

For complete technical specifications, see the dedicated documentation files:
- [Backend Documentation (`BACKEND.md`)](./BACKEND.md): PostgreSQL 17 setup, Persian text normalizer pipeline, multi-auth separation (`admins` vs `users`), two-tier stock concurrency locking, rule-based discount engine, Iranian provinces/cities seeder & shipping calculator, multi-dimensional criteria reviews, switchable media storage (Local $\to$ S3), Jalali dates, Pest functional testing, and daily logging.
- [Frontend Documentation (`FRONTEND.md`)](./FRONTEND.md): Nuxt 4 directory structure, Nuxt UI component adoption, Tailwind CSS v4 design system, TypeScript 7.x strict setup, `ui-ux-pro-max` universal design ergonomics, interactive variant selector engine, cross-attribute availability matrix, `@nuxtjs/seo`, and AI agent (`llms.txt`) integration.
