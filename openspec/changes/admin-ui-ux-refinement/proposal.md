# Change Proposal: Modern Enterprise UI/UX Refinement for Filament 5 Admin Panel

## Why
While all core operational resources (Orders, Products, Users, Reviews, Settings) exist, their layouts currently rely on basic stacked forms and tables with minimal typographic distinction and visual hierarchy. In an enterprise e-commerce platform, operators need high-efficiency layouts: two-column main/sidebar forms (Shopify-style), consolidated table columns (photo + title + subtitle), interactive filter modals, column toggles, custom empty states, and invoice-grade infolists.

## What Changes
- **Main / Sidebar Form Architecture**:
  - `ProductResource`: 2-column layout with wide main canvas (name, description, media gallery) and sidebar panel (pricing, category, brand, stock, publishing toggle, live Persian slug).
  - `CategoryResource` & `BrandResource`: Clean 2-column layouts isolating media/icon uploaders from textual taxonomy.
  - `OrderResource` & `CouponResource`: Clear semantic grouping with localized helper tips.
- **Rich Table Layouts & Data Presentation**:
  - Consolidate redundant table columns (e.g. image thumbnail + title + slug in one column; buyer name + phone in one column).
  - Add Persian formatted currency columns with thousand separators and Toman suffixes.
  - Enable table column visibility toggles (`toggleable()`) and search indicators.
  - Elegant empty states with custom Persian messages and descriptive icons.
  - Filter modal layouts with `filtersFormColumns(2)` for clean spacing.
- **Invoice-Grade Infolists**:
  - Redesign `OrderResource` infolist with split invoice layout, customer contact card, shipping details, and itemized variant tables.
  - Redesign `UserResource` and `ReviewResource` with modern metric tiles and status indicators.

## Impact
- Frontend: Zero breaking changes.
- Backend: Purely UI/UX and presentation layer optimizations in `app/Filament/`. No database migrations required.
- Performance: Optimized eager loading on tables (`user`, `product`, `items`) to prevent N+1 query overhead.
