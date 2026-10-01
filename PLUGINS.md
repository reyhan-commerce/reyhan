# 🔌 Reyhan Commerce — Modular Extension & Plugin Architecture

This document specifies the modular plugin and extension architecture for the **Reyhan Commerce Framework** — covering admin console plugins, drop-in user modules (`extensions/`), dynamic PSR-4 autoloading, and pipeline hooks.

---

## 1. Modular Extensions Architecture (`backend/extensions/`)

Reyhan enables complete modularity without requiring modifications to root `composer.json` or core framework files.

```text
backend/extensions/
└── my-carrier-plugin/
    ├── module.json                  # Manifest (id, name, version, namespace, provider)
    ├── composer.json                # (Optional) Standalone composer manifest
    ├── src/
    │   ├── MyCarrierServiceProvider.php
    │   ├── Actions/
    │   ├── Filament/
    │   └── Models/
    └── routes/
        └── api.php
```

### A. Manifest Specification (`module.json`)

```json
{
  "id": "my-carrier",
  "name": "My Custom Shipping Carrier",
  "version": "1.0.0",
  "namespace": "Extensions\\MyCarrier",
  "provider": "Extensions\\MyCarrier\\MyCarrierServiceProvider",
  "src": "src",
  "enabled": true
}
```

### B. Dynamic PSR-4 Autoloading Engine

When Reyhan boots, `ModuleManager` scans `backend/extensions/` and:
1. Dynamically injects the extension's namespace mapping into Composer's `ClassLoader` (`$composerLoader->addPsr4(...)`).
2. Discovers and registers the extension's `ServiceProvider`.
3. Loads extension routes, migrations, and event listeners seamlessly.

---

## 2. Active & Verified Admin Console Plugins

| Plugin Name | Package Identifier | Purpose in Reyhan | Status |
| :--- | :--- | :--- | :--- |
| **Filament Shield** | `bezhansalleh/filament-shield` | RBAC role, permission, and security gate management for `admin` guard | Active ✅ |
| **Spatie Laravel Backup** | `shuvroroy/filament-spatie-laravel-backup` | Manual and automated PostgreSQL database snapshots via Redis queue | Active ✅ |
| **Spatie Laravel Health** | `shuvroroy/filament-spatie-laravel-health` | Real-time health monitoring of PostgreSQL, Redis, disk storage, and runtime | Active ✅ |
| **Filament Tree Table** | `alareqi/filament-tree` | Hierarchical nested category management with drag-and-drop reordering | Active ✅ |
| **Column Filters** | `zvizvi/filament-column-filters` | Advanced header filtering on data tables (range, select, date, text) | Active ✅ |
| **Notifications Tabs** | `zvizvi/filament-notifications-tabs` | Grouped notification tabs (All, Unread) with real-time websocket updates | Active ✅ |
| **Rankbeam SEO** | `rankbeam/laravel-seo-filament` | Advanced SEO metadata manager with live Google and social card previews | Active ✅ |
| **Realtime Driver** | `marcusvbda/filament-realtime-driver` | Real-time websocket updates for live orders and notification badges | Active ✅ |
| **Spatie Settings Plugin** | `filament/spatie-laravel-settings-plugin` | Visual tabbed configuration for system, SMS, payments, and brand identity | Active ✅ |
| **Media Library Plugin** | `filament/spatie-laravel-media-library-plugin` | Media upload management, conversions, and responsive galleries | Active ✅ |
| **Activity Timeline & Audit Log** | `bokshorn-it/filament-activity-timeline` | Interactive audit logging and mutation timeline powered by Activitylog | Active ✅ |

---

## 3. Evaluation Checklist for Adding New Extensions

1. **Version Compatibility:** Fully supports modern runtime environments (PHP 8.4+).
2. **Strict Guard Isolation:** Respects the `admin` guard; customer domain authentication is strictly isolated via OTP and token guards.
3. **Pest Test Coverage:** Every plugin integration must have associated Pest Feature tests verifying registration and authorization boundaries.
4. **Clean Domain Separation:** Complex mutations and transactions belong in single-responsibility `final` Action classes, not inline within UI closures.

---

## 4. Official Documentation

For guides on building custom extensions and customizing the admin panel:
👉 [**https://reyhan-commerce.github.io/docs/v1/extensions/plugin-architecture**](https://reyhan-commerce.github.io/docs/v1/extensions/plugin-architecture)

<div align="center">
  <sub>Released under the MIT License. Copyright © 2026 Reyhan Commerce.</sub>
</div>
