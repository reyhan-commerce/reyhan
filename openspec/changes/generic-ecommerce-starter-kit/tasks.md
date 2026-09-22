# Tasks: Generic E-Commerce Starter Kit & Foundation

## 1. Database & Review Schema Generalization

- [x] 1.1 Create migration to modernize `reviews` table by adding `criteria_ratings` (JSONB) and deprecating `longevity_rating` and `coverage_rating` while preserving existing ratings
- [x] 1.2 Update `Review` Eloquent model casts, fillable attributes, and factory to support dynamic `criteria_ratings`
- [x] 1.3 Update review API requests (`StoreReviewRequest`) and resources (`ReviewResource`) to serialize and validate dynamic criteria ratings
- [x] 1.4 Update Filament `ReviewResource` form and table to display dynamic criteria ratings instead of cosmetics-only metrics

## 2. Feature Flags Subsystem with Laravel Pennant

- [x] 2.1 Install `laravel/pennant` via Composer and publish Pennant database migrations
- [x] 2.2 Define default platform features (`reviews`, `coupons`, `wishlist`, `brands`, `stock_alerts`, `comparison`) in `AppServiceProvider` using database driver
- [x] 2.3 Expose public endpoint `GET /api/v1/app/features` in `AppFeaturesController` cached in Redis DB 0
- [x] 2.4 Add Filament feature management page or settings section allowing administrators to toggle feature states dynamically
- [x] 2.5 Implement `useFeatures()` composable in Nuxt 4 to consume feature flags and conditionally guard UI sections

## 3. Deep Theme Management Engine (Admin to Frontend)

- [x] 3.1 Create `ThemeSettings` schema extending Spatie Settings with tokens: `primary_color`, `secondary_color`, `border_radius`, `spacing_scale`, `shadow_scale`, `blur_scale`, `font_scale`, `logo_light`, `logo_dark`, `favicon`
- [x] 3.2 Add migration and default seed values for `ThemeSettings`
- [x] 3.3 Create a dedicated "تنظیمات پوسته و هویت بصری" (Theme Settings) page in Filament with color pickers, sliders, and asset uploaders
- [x] 3.4 Expose theme tokens in `GET /api/v1/app/settings` and `GET /api/v1/app/theme` with Redis caching
- [x] 3.5 Implement Nuxt 4 SSR plugin / composable injecting CSS variables into `:root` in `app.vue` for zero-FOUC dynamic styling

## 4. Industry Presets Scaffolding

- [x] 4.1 Create Artisan command `app/Console/Commands/ShopPresetCommand.php` (`shop:preset`)
- [x] 4.2 Implement `ApparelPresetSeeder` with clothing taxonomies, fashion brands, and attributes (Size, Color, Fabric)
- [x] 4.3 Implement `DigitalPresetSeeder` with tech taxonomies, brands, and attributes (Storage, RAM, Color, Warranty)
- [x] 4.4 Implement `BlankPresetHandler` to reset catalog records while preserving users, settings, and geographic seeders

## 5. Frontend Ergonomics (Skeletons, Refresh, Digits & Zod)

- [x] 5.1 Build reusable Nuxt UI `<USkeleton>` components: `<ProductCardSkeleton>`, `<VariantSelectorSkeleton>`, `<CartItemSkeleton>`, `<OrderCardSkeleton>`
- [x] 5.2 Integrate skeleton loading states into `pages/index.vue`, `pages/products/index.vue`, `pages/products/[slug].vue`, and `CartSlideover.vue`
- [x] 5.3 Add accessible manual refresh action buttons (`UButton` with spin animation) on catalog list, cart summary, and order tracking
- [x] 5.4 Build `useNormalizeDigits` composable / directive to automatically map Persian (`۰-۹`) and Arabic numerals to ASCII digits on all numeric form inputs
- [x] 5.5 Integrate Zod schemas with `<UForm>` across OTP login modal, address creation modal, checkout steps, and review submission

## 6. Automated Verification & Production Hardening

- [x] 6.1 Write Pest PHP feature tests covering `reviews` criteria ratings serialization and `GET /api/v1/app/features`
- [x] 6.2 Write Pest PHP feature tests validating `shop:preset` execution and `ThemeSettings` persistence
- [x] 6.3 Run `vendor/bin/pint` to enforce PSR-12 code style across all modified and new backend classes
- [x] 6.4 Run `vendor/bin/phpstan analyse` to verify Larastan Level 8 compliance with zero errors
- [x] 6.5 Run `pnpm run typecheck` or `pnpm run build` in `frontend/` to confirm strict TypeScript clean build
