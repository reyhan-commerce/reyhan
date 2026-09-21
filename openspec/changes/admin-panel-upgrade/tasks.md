# Tasks: Filament 5 Admin Panel UX/UI & Comprehensive Resource Upgrade

## 1. Navigation Architecture & Tabbed Settings
- [ ] 1.1 Refactor store settings page into structured Filament `Tabs` (General, Payment, SMS, Shipping) <!-- id: 1.1 -->
- [ ] 1.2 Establish unified Persian navigation taxonomy and icon styling across existing resources <!-- id: 1.2 -->

## 2. Orders & Financial Management
- [ ] 2.1 Implement `OrderResource` with Persian columns, status badges, and advanced filters <!-- id: 2.1 -->
- [ ] 2.2 Build Order View Infolist with customer address snapshot and itemized variant tables <!-- id: 2.2 -->
- [ ] 2.3 Implement quick-action modal for updating order status and recording shipping tracking codes <!-- id: 2.3 -->
- [ ] 2.4 Implement `PaymentResource` with bank gateway badges, authority tokens, and verification status <!-- id: 2.4 -->

## 3. Customer Hub & Promotional Coupons
- [ ] 3.1 Implement `UserResource` with verification badges, addresses relation, and order history <!-- id: 3.1 -->
- [ ] 3.2 Implement `CouponResource` with discount types, spend limits, and Shamsi expiration date pickers <!-- id: 3.2 -->

## 4. Cosmetic Reviews Moderation
- [ ] 4.1 Implement `ReviewResource` with ratings display (longevity, coverage, value), verified buyer badge <!-- id: 4.1 -->
- [ ] 4.2 Add one-click status transition actions (Approve / Reject) and bulk moderation actions <!-- id: 4.2 -->

## 5. Roles & Permissions (Filament Shield)
- [ ] 5.1 Configure Filament Shield resources and generate policies for all operational resources <!-- id: 5.1 -->
- [ ] 5.2 Seed default staff roles (SuperAdmin, ShopManager, InventorySpecialist, CustomerSupport) <!-- id: 5.2 -->

## 6. Executive Dashboard Widgets
- [ ] 6.1 Implement `StatsOverviewWidget` (today's revenue in Toman, pending orders, low stock count) <!-- id: 6.1 -->
- [ ] 6.2 Implement `LatestOrdersWidget` for instant order monitoring on dashboard <!-- id: 6.2 -->

## 7. QA, Static Analysis & Testing
- [ ] 7.1 Format all newly added files with Laravel Pint <!-- id: 7.1 -->
- [ ] 7.2 Run Larastan Level 8 analysis and resolve all static type hints <!-- id: 7.2 -->
- [ ] 7.3 Run full Pest test suite to ensure zero regressions <!-- id: 7.3 -->
