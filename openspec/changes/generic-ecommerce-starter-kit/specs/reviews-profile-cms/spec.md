# Spec Delta: Reviews, Customer Dashboard & Content Management

## MODIFIED Requirements

### Requirement: Multi-Dimensional Cosmetics Review Engine
Customers SHALL submit granular ratings across customizable rating criteria along with qualitative feedback.

#### Scenario: Submit Product Review
- **WHEN** user submits review with overall rating (1-5), optional dynamic criteria ratings in JSONB (e.g. quality, value, fit, longevity), strengths, weaknesses, and text comment
- **THEN** system checks customer purchase history against `order_items`. If purchased, attaches `is_verified_buyer: true`. Status defaults to `PENDING` awaiting moderation.

#### Scenario: Admin Review Moderation
- **WHEN** staff approves or rejects pending reviews in Filament admin panel
- **THEN** approved reviews become visible on the product detail page and aggregate rating scores update automatically.
