# Proposal: Generic E-Commerce Starter Kit & Foundation

## Why

The current platform is tightly coupled to the cosmetics and skincare vertical with hardcoded review metrics (`longevity`, `coverage`), cosmetics-specific banners, and static styling. To enable spinning up customized online stores for diverse client verticals (apparel, digital gadgets, coffee, tools, general merchandise) within 30 minutes, the platform requires an industry-agnostic foundation, runtime feature flagging via Laravel Pennant, an end-to-end admin-driven theming engine, and polished frontend ergonomics (universal skeleton loading, refresh actions, Persian/English digit normalization, and Zod form validation).

## What Changes

- **Industry-Agnostic Reviews**: Generalize product reviews from cosmetics-specific metrics (`longevity_rating`, `coverage_rating`) into dynamic `criteria_ratings` (JSONB) supporting any category.
- **Dynamic Feature Flags with Laravel Pennant**: Install and configure `laravel/pennant` (database driver) allowing administrators to toggle features (`reviews`, `coupons`, `wishlist`, `brands`, `stock_alerts`, `comparison`) in real time from Filament and expose them via `GET /api/v1/app/features`.
- **Deep Theme Management (Admin to Frontend)**: Implement `ThemeSettings` in Spatie Settings and a dedicated Filament management view controlling primary/secondary colors, border-radius, spacing scale, shadow depth, backdrop-blur, and typography scale. Inject these as CSS variables in Nuxt 4 SSR `:root` without build restarts.
- **Industry Presets Command**: Build `php artisan shop:preset [apparel|digital|cosmetics|general|blank]` to seed industry-specific taxonomies, attributes (e.g. Size/Color/Fabric vs RAM/Storage/Warranty), and demo fixtures.
- **Universal Skeleton Loading**: Replace circular/empty loading states across catalog, PDP, cart slideover, and profile with dedicated Nuxt UI `<USkeleton>` placeholders.
- **Explicit Refresh Actions**: Provide accessible refresh triggers (`UButton` with spin animation) on catalog product listings, cart subtotal/stock reconciliation, and order tracking.
- **Bilingual Digit Normalization**: Ensure all frontend numeric inputs automatically normalize and accept both Persian (`۰-۹`) and English (`0-9`) digits seamlessly.
- **Robust Zod Form Validation**: Implement typed Zod schemas with localized Persian error messages on all frontend forms using Nuxt UI `<UForm>`.

## Capabilities

### New Capabilities
- `feature-flags`: Runtime feature flagging using `laravel/pennant`, Filament toggle switches, and Nuxt `useFeatures()` composable.
- `industry-presets`: Artisan seeding command (`shop:preset`) for rapid scaffolding of domain-specific categories and attribute sets.

### Modified Capabilities
- `admin-panel`: Add Theme Settings visual configuration and Feature Flag management to Filament.
- `reviews-profile-cms`: Modernize review schema from cosmetics-specific columns to dynamic multi-dimensional `criteria_ratings` JSONB.
- `seo-ui`: Implement dynamic CSS variable theming, universal `<USkeleton>` loading states, refresh buttons, Persian digit normalization, and Zod `<UForm>` validation.

## Impact

- **Backend**:
  - Adds dependency `laravel/pennant`.
  - Adds database migration modifying `reviews` table and creating `features` table.
  - Adds `ThemeSettings` class in `app/Settings/`.
  - Exposes `GET /api/v1/app/features` and updates `GET /api/v1/app/settings`.
  - Adds Artisan command `app/Console/Commands/ShopPresetCommand.php`.
- **Frontend**:
  - Updates `app.config.ts` and SSR head injection for CSS variables.
  - Adds `useFeatures()` composable.
  - Adds `<ProductCardSkeleton>`, `<VariantSelectorSkeleton>`, etc.
  - Adds `useNormalizeDigits()` composable/directive.
  - Integrates Zod schemas with `<UForm>`.
