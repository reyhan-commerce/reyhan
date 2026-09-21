# Admin Control Panel & Store Settings Specification

## Purpose
Provides a localized Persian administrative back-office powered by Filament 5, strict role-based access control with Filament Shield, dynamic encrypted store settings, Shamsi calendar date formatting, and database backup capabilities.

## Requirements

### Requirement: Admin Authentication & Guard Isolation
Administrative access MUST be strictly isolated from customer accounts through dedicated session-based multi-guard authentication.

#### Scenario: Admin Panel Access Restriction
- **WHEN** an unauthenticated visitor or non-admin customer accesses `/admin`
- **THEN** system redirects to the admin login portal and prevents unauthorized session reuse.

### Requirement: Role-Based Access Control (Filament Shield)
Staff accounts SHALL be partitioned into precise functional roles with granular permission policies.

#### Scenario: Role Permission Enforcement
- **WHEN** staff members with `ShopManager`, `InventorySpecialist`, or `CustomerSupport` roles log in
- **THEN** Filament navigates and resources are dynamically filtered according to their assigned Spatie permissions.

### Requirement: Dynamic Encrypted Store Settings
Critical platform configurations MUST be managed dynamically through the admin panel without code deployments.

#### Scenario: Manage Store Settings
- **WHEN** SuperAdmin edits `SmsSettings`, `PaymentSettings`, or `GeneralSettings` in Filament
- **THEN** sensitive credentials (API keys, merchant secrets) are encrypted and stored via Spatie Laravel Settings.

#### Scenario: Expose Public Settings
- **WHEN** client invokes `GET /api/v1/app/settings`
- **THEN** system returns public store configurations (store name, logos, free shipping threshold, contact info) cached in Redis DB 0.

### Requirement: Shamsi Calendar & Localization
All timestamps, date filters, and datepicker widgets inside the administrative dashboard SHALL support the Jalali/Shamsi calendar.

#### Scenario: Shamsi Date Display
- **WHEN** administrative tables list records (orders, users, products)
- **THEN** timestamps are rendered in Persian Shamsi format (e.g. ۱۴۰۵/۰۱/۱۵) with native RTL layout.

### Requirement: Tabbed Store Settings Management
The admin store settings page MUST organize configuration fields into dedicated Filament tabs for improved UX and clarity.

#### Scenario: Navigate Settings Tabs
- **WHEN** an administrator navigates to `/admin/settings`
- **THEN** system renders intuitive tabs for General (عمومی), Payments (درگاه‌های پرداخت), SMS (سرویس پیامک), and Shipping (حمل و نقل) with localized labels and icons.

### Requirement: Order Management & Status Transitions
The admin panel MUST provide complete visibility and status control over customer orders and financial payments.

#### Scenario: Filter and Inspect Order Infolist
- **WHEN** staff opens an order record in `OrderResource`
- **THEN** system renders an itemized Infolist detailing purchased variant snapshots, buyer contact details, shipping address, and calculated totals in Toman.

#### Scenario: Transition Order Status with Tracking Code
- **WHEN** staff clicks "ارسال سفارش" (Mark as Shipped)
- **THEN** system prompts for carrier tracking code, updates status to `COMPLETED` or `SHIPPED`, and stores tracking reference.

### Requirement: Customer Management Hub
Staff SHALL be able to inspect customer records, verify contact details, and review associated order history.

#### Scenario: View Customer Profile and Orders
- **WHEN** staff inspects a user in `UserResource`
- **THEN** system displays mobile verification badge, national code, saved shipping addresses, and a relation manager for past orders.

### Requirement: Cosmetic Review Moderation Workflow
Staff MUST be able to moderate customer product reviews with multi-dimensional rating insights.

#### Scenario: Approve or Reject Review
- **WHEN** staff inspects pending reviews in `ReviewResource`
- **THEN** system displays longevity, coverage, and value score indicators with verified buyer status, allowing single-click approval or rejection.

### Requirement: Promotional Coupon Administration
Staff SHALL be able to create, configure, and inspect promotional coupons.

#### Scenario: Create Dynamic Coupon
- **WHEN** staff creates a coupon in `CouponResource`
- **THEN** system provides form controls for discount type (percentage/fixed), value, minimum cart total, max discount ceiling, usage limit, and Shamsi expiration dates.

### Requirement: Dashboard Analytics & Operational Alerts
The administrative dashboard MUST provide at-a-glance sales metrics and critical inventory alerts.

#### Scenario: Display Sales & Stock Overview
- **WHEN** staff accesses the Filament dashboard
- **THEN** system renders stat cards with today's revenue in Toman, count of orders needing shipment, total customer count, and products with stock below threshold (< 5).
