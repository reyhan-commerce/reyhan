# Change Proposal: Filament 5 Admin Panel UX/UI & Comprehensive Resource Upgrade

## Why
The current administrative control panel lacks operational resources for orders, customers, reviews, coupons, and role permissions. To transform the platform into a production-ready operational hub, the admin panel requires a cohesive UX/UI refactoring with structured navigation groups, tabbed settings, detailed order management with status workflows, review moderation, customer 360-degree profiles, and dashboard metric widgets.

## What Changes
- **Tabbed Settings Page (`ManageSettingsPage`)**: Refactor store settings into cohesive Filament `Tabs` (General, Payment Gateways, SMS Drivers, Shipping & Free Delivery threshold).
- **Structured Navigation Taxonomy**: Organize all resources into Persian navigation groups (فروشگاه و کاتالوگ, سفارشات و فروش, مشتریان و بازخورد, پرسنل و دسترسی, تنظیمات سیستم).
- **Orders & Financial Management (`OrderResource` & `PaymentResource`)**:
  - Color-coded badges for `OrderStatus` (Pending, Processing, Completed, Cancelled).
  - Infolist invoice view displaying customer info, address snapshot, itemized variants, and financial totals.
  - Quick status transition action (e.g. mark as shipped with postal tracking code entry modal).
- **Discounts & Rule-Based Coupons (`CouponResource`)**: Form for percentage/fixed discounts, spend thresholds, maximum discount caps, and Shamsi date range pickers.
- **Customer Profiles (`UserResource`)**: Customer overview with mobile verification badges, national code, linked order history, and saved shipping addresses.
- **Review Moderation Engine (`ReviewResource`)**: Multi-dimensional cosmetic rating viewer (Longevity, Coverage, Value), Verified Buyer badges, and single-click Approve/Reject actions.
- **Role-Based Access Control (`Shield / RoleResource`)**: Full Filament Shield integration allowing granular permission assignments to staff (ShopManager, InventorySpecialist, CustomerSupport).
- **Executive Dashboard Widgets**:
  - `StatsOverviewWidget`: Today's sales (in Toman), new pending orders, active customers, and low stock inventory alerts.
  - `SalesChartWidget`: 7-day revenue trend chart.
  - `LatestOrdersWidget`: Real-time order stream in the dashboard.

## Impact
- Frontend: Zero breaking changes. Public APIs remain compatible.
- Backend: Introduces new Filament resources, pages, and widgets in `app/Filament/`. No breaking database migrations (utilizes existing tables and relations).
- Security: Enforces role permissions through Shield policies on all new administrative resources.
