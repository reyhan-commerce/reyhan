# Filament Plugins & Extension Registry

This document defines the official and community plugin registry for the **Filament 5 Admin Panel** in **Reyhan Commerce**, including active installed plugins, architectural guidelines, and future candidates.

---

## 1. Active & Installed Plugins

| Plugin Name | Composer Package | Purpose in Reyhan | Status |
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

## 2. Modular Extension Architecture (`backend/extensions/`)

In addition to Composer packages, custom extensions live inside `backend/extensions/` and are auto-discovered via `module.json`:

```json
{
  "name": "custom-carrier",
  "title": "Custom Shipping Carrier Integration",
  "version": "1.0.0",
  "description": "Calculates real-time shipping rates and generates tracking labels",
  "providers": [
    "Reyhan\\Extensions\\CustomCarrier\\Providers\\CustomCarrierServiceProvider"
  ],
  "enabled": true
}
```

---

## 3. Evaluation Checklist for Adding New Plugins

Before introducing any new plugin or package to the Reyhan ecosystem, ensure it satisfies the following architectural criteria:

1. **Version Compatibility:** Fully supports Filament 5 (`filament/filament: ^5.0`) and PHP 8.3+.
2. **Strict Guard Isolation:** Must respect the `admin` guard and not pollute customer authentication tables.
3. **Pest Test Coverage:** Every plugin integration must have associated Pest Feature tests verifying registration and authorization boundaries.
4. **Performance Impact:** Zero-overhead asset loading without introducing unminified scripts or blocking Livewire requests.

---

## 4. Official Documentation

For guides on building custom extensions and customizing the admin panel:
👉 [**https://reyhan-commerce.github.io/docs/v1/extensions/plugin-architecture**](https://reyhan-commerce.github.io/docs/v1/extensions/plugin-architecture)
