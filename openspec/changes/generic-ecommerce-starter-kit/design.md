# Technical Design: Generic E-Commerce Starter Kit & Foundation

## Context

The system is a decoupled monorepo with Laravel 13 backend and Nuxt 4 frontend. While core e-commerce capabilities (PostgreSQL 17, Redis, Sanctum OTP, Cart concurrency, Shetabit payment gateways) are already robust, vertical-specific assumptions exist in the review model, static home layout, and hardcoded styling. Furthermore, feature toggling requires direct code edits rather than runtime configuration.

See `proposal.md` for motivation and background context.

## Goals / Non-Goals

**Goals:**
- Provide a clean, generic e-commerce core that can be deployed for any industry vertical in under 30 minutes.
- Integrate `laravel/pennant` for dynamic, runtime feature flags with Filament UI toggles and Nuxt `useFeatures()` consumer composable.
- Implement an end-to-end theme customization engine from Filament Settings down to Nuxt 4 CSS variables (`:root`) without build triggers or FOUC.
- Provide domain fixture scaffolding via `php artisan shop:preset`.
- Elevate frontend UX with universal `<USkeleton>` loading states, explicit refresh triggers, bilingual Persian/English numeral input tolerance, and typed Zod validation.

**Non-Goals:**
- Multi-tenancy / SaaS (each store instance operates with its own database and configuration).
- Independent package/modular directory separation (`nwidart/laravel-modules` was deliberately excluded to preserve monorepo simplicity).

## Decisions

### 1. Feature Flagging Engine: `laravel/pennant` with Database Driver
- **Rationale**: Pennant is Laravel's first-party feature flagging package. The `database` driver stores feature records in a `features` table, allowing Filament administrators to toggle features dynamically at runtime.
- **API Exposure**: A dedicated controller `AppFeaturesController` returns `{ [featureName]: boolean }` cached in Redis DB 0 with instant cache invalidation upon admin toggle.
- **Alternatives Considered**: Config-based boolean arrays (`config/shop.php`). *Rejected* because they require code deployments or `.env` modifications rather than non-technical admin control.

### 2. Industry-Agnostic Review Schema: JSONB `criteria_ratings`
- **Rationale**: Replacing fixed columns (`longevity_rating`, `coverage_rating`, `value_rating`) with `criteria_ratings` (JSONB) allows storing arbitrary rating criteria (e.g. `{"quality": 5, "durability": 4}` for apparel, `{"build_quality": 5, "battery_life": 4}` for tech).
- **Backward Compatibility**: Migration transforms any legacy cosmetics review columns into JSONB keys before altering columns.

### 3. Deep Theming Engine: Spatie `ThemeSettings` to Nuxt 4 CSS Variables
- **Rationale**: Nuxt UI and Tailwind CSS v4 leverage CSS custom properties (`var(--ui-color-primary)`, `--ui-radius`, etc.). By storing design tokens in Spatie `ThemeSettings` and serving them via `GET /api/v1/app/settings`, Nuxt 4 injects them into `:root` in `app.vue` or a server middleware during SSR.
- **Zero FOUC**: Inlined SSR `:root` stylesheet ensures the client renders with correct branding on the very first frame.
- **Tokens Managed**: Primary color palette, secondary color, border-radius (`none`, `sm`, `md`, `lg`, `full`), spacing factor, shadow preset, blur scale, and base typography scale.

### 4. Industry Presets via Artisan Command
- **Rationale**: A dedicated command `shop:preset {vertical}` runs industry-specific seeders:
  - `apparel`: Clothes, Shoes, Accessories; Attributes: Size (S, M, L, XL), Color, Fabric; Brands: ZARA, Mango, H&M.
  - `digital`: Smartphones, Laptops, Gadgets; Attributes: Storage, RAM, Color, Warranty; Brands: Apple, Samsung, Xiaomi.
  - `cosmetics`: Skincare, Makeup, Perfume; Attributes: Volume, Color/Shade, Skin Type; Brands: Cerave, La Roche-Posay.
  - `blank`: Retains admin accounts, permissions, geo provinces/cities, and resets catalog to a clean slate.

### 5. Frontend Polish: Skeletons, Refresh, Digits & Zod
- **Universal Skeletons**: Pre-built `<ProductCardSkeleton>`, `<VariantSelectorSkeleton>`, `<CartItemSkeleton>`, `<OrderCardSkeleton>` rendered conditionally during pending states.
- **Refresh Actions**: Standardized `<UButton icon="i-lucide-rotate-cw" :loading="pending" @click="refreshData">` in toolbars and drawers.
- **Bilingual Digit Normalization**: A reusable utility and composable `useNormalizeDigits` that maps Persian numerals (`۰-۹`) and Arabic numerals (`٠-٩`) to ASCII (`0-9`) on input `@input` and `@paste` events across all numeric form fields.
- **Zod Schema Validation**: Form states governed by typed Zod schemas integrated with Nuxt UI `<UForm>` providing instant inline Persian error messages.

## Risks / Trade-offs

- **[Risk]** Spatie Settings caching might lag behind theme edits.
  $\to$ *Mitigation*: Hook into `SettingsSaved` event to flush Redis `settings:public` and `theme:public` cache keys immediately.
- **[Risk]** Database schema change in `reviews` could conflict with existing records.
  $\to$ *Mitigation*: Write migration with raw SQL fallback migrating existing data into JSONB before modifying table columns.
- **[Risk]** User custom primary color may break contrast against dark/light backgrounds.
  $\to$ *Mitigation*: Provide curated Tailwind color presets (Rose, Sky, Emerald, Amber, Violet, Zinc) alongside custom hex values.

## Migration Plan

1. Install `laravel/pennant` via composer.
2. Publish and execute Pennant database migrations.
3. Create and execute `reviews` table generalization migration.
4. Create and run `ThemeSettings` migration and seeder.
5. Deploy updated Filament settings pages and API resources.
6. Verify SSR `:root` theme injection and Zod forms in Nuxt.
