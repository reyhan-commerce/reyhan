# 🔄 Reyhan Commerce Upgrade Guide

This document provides a concise reference for breaking changes, migration steps, and upgrade instructions across Reyhan releases.

For the interactive documentation, see `docs/v1/getting-started/upgrade-guide.md` or visit the official documentation site.

---

## Releases Summary

| Release | Release Date | Upgrade Guide | Impact |
| :--- | :--- | :--- | :--- |
| **v1.0.0** | Current | [Monolith to Decoupled Framework](#upgrading-to-v100-decoupled-framework) | 🔴 High |

---

## Upgrading to v1.0.0 (Decoupled Framework)

### 1. Architectural Overview
In v1.0.0, the core commerce engine is isolated into `reyhan-commerce/core` under the `Reyhan\Core\` namespace. The consumer application (`backend/`) acts as a decoupled userland project consuming the immutable core.

### 2. High-Impact Breaking Changes 🔴

#### Namespace Relocation
All core models, actions, pipelines, events, and form requests have moved from `App\` to `Reyhan\Core\`.

```php
// Old
use App\Models\Product;
use App\Actions\Checkout\CreateOrderAction;

// New
use Reyhan\Core\Models\Product;
use Reyhan\Core\Actions\Checkout\CreateOrderAction;
```

#### Filament Admin Plugin Registration
Core resources, widgets, and pages are distributed via `ReyhanCorePlugin`.

In `app/Providers/Filament/AdminPanelProvider.php`:
```php
use Reyhan\Core\ReyhanCorePlugin;

$panel->plugin(ReyhanCorePlugin::make());
```

### 3. Medium-Impact Changes 🟡

#### Dynamic Model Resolution & Extensibility
To override core models in your application without modifying framework files:

```php
use Reyhan\Core\Support\Reyhan;
use App\Models\CustomProduct;

public function boot(): void
{
    Reyhan::useModel('product', CustomProduct::class);
}
```

Core relationships now dynamically instantiate registered classes:
```php
// Inside Reyhan\Core\Models\Order
public function user(): BelongsTo
{
    return $this->belongsTo(Reyhan::userModel());
}
```

### 4. Routine Upgrade Commands

```bash
# Update framework packages
composer update reyhan-commerce/core --with-all-dependencies

# Run pending database migrations
php artisan migrate --force

# Verify system health
php artisan reyhan:doctor
```
