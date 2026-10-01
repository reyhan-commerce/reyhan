<div align="center">

# 🌿 Reyhan Commerce

### The Sovereign Enterprise Headless E-Commerce Framework
**Engineered for High-Concurrency, Dynamic Extensibility & Zero-Breaking Upgrades**

[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![Status: Production Ready](https://img.shields.io/badge/Status-Enterprise%20Grade-10b981.svg)](https://reyhan-commerce.github.io/docs/)
[![Architecture: Headless Monorepo](https://img.shields.io/badge/Architecture-Headless%20Monorepo-blue.svg)](https://reyhan-commerce.github.io/docs/v1/architecture/lifecycle)
[![Documentation](https://img.shields.io/badge/Docs-Live%20Website-10b981.svg)](https://reyhan-commerce.github.io/docs/)

[**📖 Read Full Documentation**](https://reyhan-commerce.github.io/docs/) • [**🚀 Getting Started**](https://reyhan-commerce.github.io/docs/v1/getting-started/installation) • [**🏛 Architecture**](https://reyhan-commerce.github.io/docs/v1/architecture/lifecycle) • [**🛠 CLI Reference**](https://reyhan-commerce.github.io/docs/v1/cli/cli-reference)

</div>

---

## ⚡ Quick Start

Scaffold a full-stack, enterprise headless store with a single command:

```bash
npx create-reyhan@latest my-store
# or with pnpm
pnpm create reyhan my-store
```

Or clone this monorepo and run the central orchestrator:

```bash
# Verify system prerequisites (PostgreSQL 17, Redis 7, PHP 8.4+, Node 20+)
./reyhan doctor

# Provision database, encryption keys, and seeders
./reyhan install

# Start concurrent development servers
./reyhan dev
```

---

## 🏛️ Framework Architecture Pillars

1. **Modular Monorepo Packages:**
   - Standalone core engine distributed via package management.
   - Cascading reactive storefront layer with zero-CSS theme tokens.
   - Official CLI scaffolder for instant store creation.
2. **Two-Tier Concurrency & Stock Locking:**
   - Tier 1: Distributed memory Sorted Sets with self-purging TTL to eliminate ghost inventory locks.
   - Tier 2: Database-level pessimistic row locking during checkout payment settlement.
3. **Sovereign Extensibility (Zero Core Modification):**
   - **Dynamic Model Swapping:** Domain workflows resolve entities dynamically through contracts and resolvers.
   - **Dynamic PSR-4 Extensions:** Drop plugins into `extensions/` with automatic classloader namespace injection.
   - **Cascading Storefront:** Override visual components and layouts natively without touching core package files.
4. **Security & Financial Integrity:**
   - Anti-brute-force rate-limiting on OTP authentication with automatic token revocation.
   - Atomic database transactions with strict external HTTP network boundaries.

---

## 🛠️ Technology Stack & Architectural Foundation

Reyhan is built upon an enterprise-grade, modern open-source technology foundation:

| Domain | Technology / Engine | Architectural Role & Rationale |
| :--- | :--- | :--- |
| **Backend Engine** | **PHP 8.4+ & Laravel 13** | Provides single-responsibility `final` Action classes, strongly-typed DTOs (`spatie/laravel-data`), native Eloquent models, and robust queue workers. |
| **Admin Backoffice** | **Filament 5 & Livewire 3** | High-productivity reactive admin panel, RBAC permissions (`filament-shield`), real-time websocket updates, and audit logging. |
| **Storefront Layer** | **Nuxt 4 & Vue 3** | Server-Side Rendering (SSR), Composition API, Pinia state stores, Reka UI headless components, and Tailwind 4. |
| **Primary Database** | **PostgreSQL 17+** | Enterprise JSONB variant matrices, GIN indexing, `pg_trgm` fuzzy text search, and ACID pessimistic row-locking (`lockForUpdate`). |
| **Memory & Mutex Engine** | **Redis 7+** | Sub-millisecond cart caching, distributed sessions, Horizon queue workers, and atomic Lua script stock reservations. |
| **High-Performance Runtime** | **FrankenPHP Octane & Caddy** | Worker-mode execution for microsecond response times and automated SSL certificate provisioning. |
| **Testing & Quality Assurance** | **Pest 4 & Vitest** | End-to-end domain feature testing, concurrency assertions, architecture linting, and automated UI unit testing. |

---

## 📂 Monorepo Anatomy

```text
reyhan/
├── version.json                     # Semantic versioning manifest (SemVer)
├── reyhan                           # Central executable CLI orchestrator
│
├── packages/
│   ├── core/                        # 🟢 Backend Domain Package
│   ├── storefront/                  # 🎨 Reactive Storefront Layer
│   └── create-reyhan/               # 🛠️ Official CLI Scaffolder
│
├── backend/                         # Reference Backend API & Admin Console
│   ├── app/Actions/                 # Single-responsibility domain action classes
│   ├── app/Contracts/Models/        # Domain entity interface contracts
│   ├── app/Data/                    # Strongly-typed Data Transfer Objects (DTOs)
│   ├── app/Filament/                # Admin Panel resources and dashboards
│   └── config/reyhan.php            # Model registries & gateway configurations
│
└── frontend/                        # Reference Reactive Storefront
    ├── app/components/              # Cascading user-land component overrides
    ├── app/composables/             # useShopLocale (RTL/LTR & i18n), useApi
    └── nuxt.config.ts               # Storefront build configuration
```

---

## 📄 Dedicated Specification Files

- [Framework Architecture (`FRAMEWORK.md`)](./FRAMEWORK.md): Monorepo design, concurrency lifecycle, and zero-breaking upgrade guarantees.
- [Backend Technical Specification (`BACKEND.md`)](./BACKEND.md): Detailed backend architecture, PostgreSQL schemas, payment drivers, SMS pipelines, and Pest testing suites.
- [Frontend Technical Specification (`FRONTEND.md`)](./FRONTEND.md): Storefront layers, design tokens, Pinia stores, and accessibility standards.
- [Admin & Plugins Guide (`PLUGINS.md`)](./PLUGINS.md): Admin panel plugins, modular extensions architecture, and evaluation checklists.
- [Execution Roadmap (`ROADMAP.md`)](./ROADMAP.md): Milestone progress and upcoming major features.

---

<div align="center">
  <sub>Released under the MIT License. Copyright © 2026 Reyhan Commerce.</sub>
</div>
