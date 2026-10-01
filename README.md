<div align="center">

# 🌿 Reyhan Commerce

### Next-Generation Enterprise Headless E-Commerce Framework
**Powered by Laravel 13, Filament 5, Nuxt 4, PostgreSQL 17 & Redis 7**

[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.4%2B-777bb4.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-ff2d20.svg)](https://laravel.com)
[![Nuxt](https://img.shields.io/badge/Nuxt-4.x-00dc82.svg)](https://nuxt.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17%2B-blue.svg)](https://www.postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-7%2B-orange.svg)](https://redis.io)
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

Or clone this monorepo and run the orchestrator:

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
   - `reyhan-commerce/core`: Headless Laravel 13 engine distributed as a standalone Composer package.
   - `@reyhan-commerce/storefront`: Enterprise Nuxt 4 Layer delivering ready-to-use e-commerce pages, Pinia stores, and theme tokens.
   - `create-reyhan`: Official CLI scaffolder.
2. **Two-Tier Concurrency & Stock Locking:**
   - Tier 1: Redis 7 Sorted Sets (ZSET) with self-purging TTL to eliminate ghost inventory locks.
   - Tier 2: PostgreSQL 17 pessimistic row-locking (`lockForUpdate`) during checkout payment settlement.
3. **Sovereign Extensibility (Zero Core Modification):**
   - **Dynamic Model Swapping:** Domain actions resolve entities through `Reyhan::model()` and strict interface contracts (`OrderContract`, `ProductContract`).
   - **Dynamic PSR-4 Extensions:** Drop plugins into `extensions/` with automatic namespace autoloader injection.
   - **Cascading Storefront:** Override Vue components and layouts natively without touching core package files.
4. **Security & Financial Integrity:**
   - Anti-brute-force rate-limiting on OTP verification (max 5 attempts with automatic token revocation and temporary lockout).
   - Atomic database transactions with safe external HTTP boundaries.

---

## 📂 Monorepo Structure

```text
reyhan/
├── version.json                     # Semantic versioning manifest (SemVer)
├── reyhan                           # Central executable CLI orchestrator
│
├── packages/
│   ├── core/                        # 🟢 Laravel Engine (reyhan-commerce/core)
│   ├── storefront/                  # 🎨 Nuxt 4 Layer (@reyhan-commerce/storefront)
│   └── create-reyhan/               # 🛠️ Official CLI Installer
│
├── backend/                         # Reference Laravel 13 & Filament 5 Engine
│   ├── app/Actions/                 # Single-responsibility domain action classes
│   ├── app/Contracts/Models/        # Domain entity interface contracts
│   ├── app/Data/                    # Strongly-typed Data Transfer Objects (DTOs)
│   ├── app/Filament/                # Admin Panel resources and dashboards
│   └── config/reyhan.php            # Model registries & gateway configurations
│
└── frontend/                        # Reference Nuxt 4 Reactive Storefront
    ├── app/components/              # Cascading user-land component overrides
    ├── app/composables/             # useShopLocale (RTL/LTR & i18n), useApi
    └── nuxt.config.ts               # Storefront build configuration
```

---

## 📄 Dedicated Specification Files

- [Framework Architecture (`FRAMEWORK.md`)](./FRAMEWORK.md): Monorepo design, concurrency lifecycle, and zero-breaking upgrade guarantees.
- [Backend Technical Specification (`BACKEND.md`)](./BACKEND.md): Detailed backend architecture, PostgreSQL schemas, payment drivers, SMS pipelines, and Pest testing suites.
- [Frontend Technical Specification (`FRONTEND.md`)](./FRONTEND.md): Nuxt 4 layers, design tokens, Pinia stores, and accessibility standards.
- [Filament & Plugins Guide (`PLUGINS.md`)](./PLUGINS.md): Admin panel plugins, modular extensions architecture, and evaluation checklists.
- [Execution Roadmap (`ROADMAP.md`)](./ROADMAP.md): Milestone progress and upcoming major features.

---

<div align="center">
  <sub>Released under the MIT License. Copyright © 2026 Reyhan Commerce.</sub>
</div>
