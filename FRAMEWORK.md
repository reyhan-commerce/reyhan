# Reyhan Commerce — Framework Architecture, Extensibility & Lifecycle Guide

This document is the official architectural specification for the **Reyhan Commerce Framework** — an enterprise-scale, full-stack, headless, and modular e-commerce engine.

The core design principle of Reyhan is **Zero Core Modification with Sovereign Customizability**: developers can fully customize models, business workflows, storefront components, and admin panels without altering core files, ensuring seamless zero-breaking updates via a single CLI command.

---

## 1. High-Level Core vs. User Land Segregation

```text
reyhan-store/
├── version.json                     # Central Semantic Versioning Manifest (SemVer)
├── reyhan                           # Central Executable CLI Orchestrator
│
├── backend/                         # Headless Commerce Engine
│   ├── config/reyhan.php            # Dynamic Model Registries, Drivers & Pipelines
│   ├── extensions/                  # Modular User Plugins (auto-discovered via module.json)
│   └── app/
│       ├── Support/
│       │   ├── Reyhan.php           # Central Dynamic Model Resolver Facade
│       │   └── Modules/             # Automatic Extension Discovery Engine
│       └── Console/Commands/
│           ├── ReyhanVersionCommand.php   # php artisan reyhan:version
│           ├── ReyhanDoctorCommand.php    # php artisan reyhan:doctor
│           ├── ReyhanInstallCommand.php   # php artisan reyhan:install
│           └── ReyhanUpdateCommand.php    # php artisan reyhan:update
│
└── frontend/                        # Reactive Storefront Engine
    ├── app/
    │   ├── app.config.ts            # Brand Identity, Theming & UI Tokens
    │   ├── locales/                 # Localization Dictionaries (en.json, fa.json)
    │   ├── composables/             # Reactive Storefront Hooks
    │   ├── layouts/                 # Cascading User-Land Layout Overrides
    │   ├── pages/                   # Cascading User-Land Route Overrides
    │   └── components/              # Cascading User-Land Component Overrides
    └── nuxt.config.ts               # Storefront Layer Configuration & Module Bindings
```

---

## 2. Infrastructure Standard: BYOD (Bring Your Own Database)

> [!IMPORTANT]
> **Reyhan Commerce strictly adheres to the Bring Your Own Database (BYOD) standard**. The framework does not install local database or Redis daemons.

1. **Mandatory PostgreSQL 17+ and Redis 7+:**
   - Enterprise commerce capabilities—including `GIN` indexes, `pg_trgm` fuzzy text matching, `JSONB` variant matrices, concurrency stock mutexes, `Horizon` queues, and `Pulse` performance telemetry—rely strictly on PostgreSQL 17+ and Redis 7+.
2. **Clean Environment Isolation:**
   - Database and cache connections are configured entirely via `backend/.env`:
     ```ini
     DB_CONNECTION=pgsql
     DB_HOST=127.0.0.1
     DB_PORT=5432
     DB_DATABASE=reyhan_commerce
     DB_USERNAME=postgres
     DB_PASSWORD=secret

     REDIS_HOST=127.0.0.1
     REDIS_PORT=6379
     REDIS_PASSWORD=null
     ```
3. **Automated Diagnostic Verification:**
   - The `./reyhan doctor` command evaluates live TCP connections, latency, and read/write permissions before any installation or deployment.

---

## 3. Scaffolding New Stores (`create-reyhan`)

To create a brand-new store without manual cloning:

```bash
npx create-reyhan@latest my-store
# or with pnpm
pnpm create reyhan my-store
```

---

## 4. Central Orchestrator CLI (`./reyhan`)

An executable orchestrator in the project root streamlines all lifecycle operations:

```bash
# View full version matrix and active extensions
./reyhan version

# Run deep health diagnostics (PHP, PostgreSQL, Redis, Permissions, Node)
./reyhan doctor

# Run local development installer (Migrations, Keys, Seeders, Symlinks)
./reyhan install

# Run containerized production installer (Docker, Octane, Caddy SSL)
./reyhan install --prod

# Execute zero-downtime update with automated backup
./reyhan update

# Concurrently boot backend API and frontend storefront dev servers
./reyhan dev
```

---

## 5. Official Artisan Commands (`reyhan:*`)

| Command | Operational Purpose |
| :--- | :--- |
| `php artisan reyhan:version` | Renders a structured version matrix of Core, PHP, Database, and Active Extensions |
| `php artisan reyhan:doctor` | Evaluates PHP C-extensions, PostgreSQL connection, Redis latency, and symlinks |
| `php artisan reyhan:install` | Generates encryption keys, runs idempotent migrations, seeds data, and builds assets |
| `php artisan reyhan:update` | Triggers pre-update DB snapshot, runs migrations, upgrades admin UI, and sends Octane reload |

---

## 6. Storefront Customization Standards

User-land customizations are completely decoupled from core frontend files:

### A. Cascading Component Overrides
Placing a Vue component in `frontend/app/components/` with the same name as a core component (e.g. `ProductCard.vue` or `PriceTag.vue`) automatically replaces the default implementation during compilation and SSR.

### B. Layouts and Pages Overrides
- Core storefront layouts (e.g. `layouts/default.vue`, `layouts/checkout.vue`) are overridden by creating the same file inside `frontend/app/layouts/`.
- Custom routes (e.g. `/brand-story`, `/faq`) are added simply by creating files inside `frontend/app/pages/`.

### C. Token-Driven Branding (`app.config.ts`)
Brand name, logos, primary/neutral color palettes, and top announcement bars are configured declaratively in `frontend/app/app.config.ts`:

```ts
export default defineAppConfig({
  ui: {
    colors: { primary: 'emerald', neutral: 'zinc' }
  },
  reyhan: {
    brand: {
      name: 'Reyhan Store',
      slogan: 'Pure Elegance, Fast Delivery 🌿',
      logoUrl: '/icon.svg'
    },
    header: {
      announcementBar: {
        enabled: true,
        text: '✨ Free express shipping on orders over $50!',
        link: '/faq'
      }
    }
  }
})
```

---

## 7. Backend Domain Customization Standards

### A. Dynamic Model Swapping (`Reyhan::model()`)
Core actions never hardcode concrete model classes. All models are resolved dynamically:

```php
use App\Support\Reyhan;

$productClass = Reyhan::model('product');
$product = $productClass::where('slug', $slug)->firstOrFail();
```

To register a custom model subclass, configure `backend/config/reyhan.php`:

```php
'models' => [
    'product' => \App\Models\CustomProduct::class,
],
```

### B. Modular Extensions Subsystem (`backend/extensions/`)
Custom business domains, shipping carriers, and third-party integrations live inside isolated subfolders under `backend/extensions/` accompanied by a `module.json` manifest. The `ModuleManager` auto-discovers and registers service providers, routes, and migrations at boot.

---

## 8. Semantic Versioning & Safe Updates

1. Framework releases are tracked via the root `version.json` file using **Semantic Versioning (SemVer)**.
2. Executing `./reyhan update` executes a non-breaking rolling update sequence:
   - Pre-flight database connectivity check
   - Automated compressed PostgreSQL snapshot via `spatie/laravel-backup`
   - Execution of new database migrations (`php artisan migrate --force`)
   - Asset compilation & Filament admin upgrades (`php artisan filament:upgrade`)
   - Route, config, and view cache optimization
   - Zero-downtime graceful worker reload (`FrankenPHP Octane`)
   - Synchronized storefront type checking

---

## 9. Official Documentation Website

For complete step-by-step guides, API contracts, and tutorials, visit the official live documentation:
👉 [**https://reyhan-commerce.github.io/docs/**](https://reyhan-commerce.github.io/docs/)
