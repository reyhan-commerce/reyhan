<div align="center">

# 🌿 Reyhan Commerce

### Next-Generation Enterprise Headless E-Commerce Framework
**Powered by Domain-Driven Actions, Dynamic Model Swapping & Cascading Storefront**

[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17%2B-blue.svg)](https://www.postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-7%2B-orange.svg)](https://redis.io)
[![Documentation](https://img.shields.io/badge/Docs-Live%20Website-10b981.svg)](https://reyhan-commerce.github.io/docs/)

[**📖 Read Full Documentation**](https://reyhan-commerce.github.io/docs/) • [**🚀 Getting Started**](https://reyhan-commerce.github.io/docs/v1/getting-started/installation) • [**🏛 Architecture**](https://reyhan-commerce.github.io/docs/v1/architecture/lifecycle) • [**🛠 CLI Reference**](https://reyhan-commerce.github.io/docs/v1/cli/cli-reference)

</div>

---

## ⚡ Quick Start

Scaffold a full-stack, production-ready headless store with zero global dependencies:

```bash
npx create-reyhan@latest my-store
# or with pnpm
pnpm create reyhan my-store
```

Or clone this repository and run the central orchestrator:

```bash
# Verify system prerequisites (PostgreSQL 17, Redis 7, PHP 8.3+, Node 20+)
./reyhan doctor

# Run automated installation & seeders
./reyhan install

# Start concurrent development servers
./reyhan dev
```

---

## 🏛️ Core Architecture Principles

1. **Strict Core vs. User Land Boundary:**
   - Override UI components, layouts, and pages natively via cascading storefront resolution without touching core engine files.
   - Extend or swap domain models via `config/reyhan.php` using the central `Reyhan::model()` resolver.
   - Drop self-contained plugins into `backend/extensions/` with `module.json` manifests.
2. **Action & Strongly-Typed DTO Standard:**
   - Single-responsibility `final` Action classes with `execute()` methods.
   - Strongly-typed, validated Data Transfer Objects (DTOs).
   - Zero repository overhead; 100% native Eloquent and atomic database transactions.
3. **Bring Your Own Database (BYOD):**
   - Pure environment-based connectivity to standalone **PostgreSQL 17+** (with JSONB, `GIN`, and `pg_trgm`) and **Redis 7+** (Cache, Sessions, and Horizon Queues).
4. **Zero-Downtime Safe Updates:**
   - Automated pre-flight health checks, compressed PostgreSQL snapshots, migrations, admin upgrades, and graceful Octane reloads via `./reyhan update`.

---

## 📂 Repository Anatomy

```text
reyhan-core/
├── version.json                     # Semantic versioning manifest (SemVer)
├── reyhan                           # Central executable CLI orchestrator
│
├── backend/                         # Headless Commerce Engine (PHP 8.3+)
│   ├── app/
│   │   ├── Actions/                 # Single-responsibility domain action classes
│   │   ├── Data/                    # Strongly-typed Data Transfer Objects (DTOs)
│   │   ├── Models/                  # Eloquent models (swappable via contracts)
│   │   ├── Support/Reyhan.php       # Dynamic model resolver facade
│   │   └── Services/                # SMS manager & text normalization pipelines
│   ├── config/reyhan.php            # Core model registries & gateway configs
│   ├── database/                    # PostgreSQL migrations & geo seeders
│   └── extensions/                  # Modular user plugins (module.json)
│
└── frontend/                        # Reactive Storefront Engine
    ├── app/
    │   ├── app.config.ts            # Brand identity, theme tokens & announcement bar
    │   ├── components/              # Cascading user-land component overrides
    │   ├── layouts/                 # Cascading storefront layouts
    │   ├── pages/                   # User-land routes & PDPs
    │   └── stores/                  # Pinia stores (useCartStore, useAuthStore)
    └── nuxt.config.ts               # Storefront build configuration
```

---

## 📚 Official Documentation

Comprehensive guides, architectural deep dives, and API specifications are available at the official docs website:

👉 [**https://reyhan-commerce.github.io/docs/**](https://reyhan-commerce.github.io/docs/)

| Guide | Description |
| :--- | :--- |
| [**Overview & Philosophy**](https://reyhan-commerce.github.io/docs/v1/getting-started/overview) | Core pillars, domain design, and architectural matrix |
| [**BYOD Infrastructure**](https://reyhan-commerce.github.io/docs/v1/getting-started/configuration) | PostgreSQL 17 JSONB setup and Redis logical databases |
| [**Dynamic Model Swapping**](https://reyhan-commerce.github.io/docs/v1/architecture/model-swapping) | Swapping core entities with custom subclasses |
| [**Actions & DTOs**](https://reyhan-commerce.github.io/docs/v1/backend/actions-and-dtos) | Writing clean, single-responsibility business workflows |
| [**Component Overriding**](https://reyhan-commerce.github.io/docs/v1/storefront/component-overriding) | Cascading storefront customization guide |
| [**CLI Orchestrator Reference**](https://reyhan-commerce.github.io/docs/v1/cli/cli-reference) | Reference manual for `./reyhan` commands |

---

## 📄 Dedicated Specification Files

- [Framework Architecture (`FRAMEWORK.md`)](./FRAMEWORK.md): Complete specification for the Core vs. User-Land boundary, model resolvers, and zero-breaking upgrade guarantees.
- [Backend Technical Specification (`BACKEND.md`)](./BACKEND.md): Detailed backend architecture, PostgreSQL schemas, payment drivers, SMS pipelines, and Pest testing suites.
- [Frontend Technical Specification (`FRONTEND.md`)](./FRONTEND.md): Nuxt 4 layers, component design tokens, optimistic cart flows, and accessibility standards.
- [Filament & Plugins Guide (`PLUGINS.md`)](./PLUGINS.md): Admin panel plugins, modular extensions architecture, and evaluation checklists.
- [Execution Roadmap (`ROADMAP.md`)](./ROADMAP.md): Milestone progress and upcoming major features.

---

<div align="center">
  <sub>Released under the MIT License. Copyright © 2026 Reyhan Commerce.</sub>
</div>
