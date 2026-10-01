# Reviews, Customer Dashboard & Content Management Specification

## Purpose
Provides multi-dimensional product criteria reviews with verified buyer tagging and admin moderation, customer account profile hub (orders, addresses, wishlist), switchable S3 media storage, and dynamic CMS pages.

## Requirements

### Requirement: Multi-Dimensional Criteria Review Engine
Customers SHALL submit granular ratings for product performance criteria along with qualitative feedback.

#### Scenario: Submit Product Review
- **WHEN** user submits review with ratings for longevity (ماندگاری), coverage (پوشش‌دهی), and value (ارزش خرید) along with text feedback
- **THEN** system checks customer purchase history against `order_items`. If purchased, attaches `is_verified_buyer: true`. Status defaults to `PENDING` awaiting moderation.

#### Scenario: Admin Review Moderation
- **WHEN** staff approves or rejects pending reviews in Filament admin panel
- **THEN** approved reviews become visible on the product detail page and aggregate rating scores update automatically.

### Requirement: Customer Dashboard & Wishlist
Authenticated customers MUST be able to manage orders, track package shipments, view saved addresses, and bookmark favorite products.

#### Scenario: Manage Wishlist
- **WHEN** user toggles heart icon on product card or PDP
- **THEN** system toggles product bookmark in `wishlist` table and reflects state across the customer account.

#### Scenario: Customer Order Tracking
- **WHEN** customer visits `pages/profile/orders.vue`
- **THEN** system returns past orders with status filters, tracking reference numbers, and itemized invoice details.

### Requirement: Dynamic CMS & Static Pages
Platform SHALL render dynamic informational content (About Us, Contact Us with map coordinates, Terms of Service, FAQ accordions, 7-Day Return Policy).

#### Scenario: Fetch Dynamic Page Content
- **WHEN** visitor navigates to `/about`, `/contact`, `/terms`, or `/faq`
- **THEN** frontend retrieves page content from `GET /api/v1/pages/{slug}` and renders structured content with accordion widgets.
