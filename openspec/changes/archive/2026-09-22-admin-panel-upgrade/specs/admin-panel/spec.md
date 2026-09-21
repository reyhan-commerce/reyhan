# Spec Delta: Admin Panel UX/UI & Comprehensive Resource Upgrade

## Purpose
Adds comprehensive operational resources for orders, reviews, coupons, customers, role management, and tabbed configuration interfaces to the Filament 5 Persian administrative panel.

## ADDED Requirements

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
