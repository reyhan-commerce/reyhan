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
