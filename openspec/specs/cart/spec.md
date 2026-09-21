# Shopping Cart, Pricing Engine & Two-Tier Concurrency Locking Specification

## Purpose
Manages persistent shopping carts for guest and authenticated customers, automated guest cart synchronization, coupon discounts, weight-based dynamic shipping calculations, and two-tier inventory reservation locks.

## Requirements

### Requirement: Shopping Cart Persistence & Synchronization
Shopping carts MUST bind to specific `product_variant_id` records and persist across visitor sessions.

#### Scenario: Add Variant to Cart
- **WHEN** user adds a variant to cart via `POST /api/v1/cart`
- **THEN** system checks available stock, creates or updates the cart item, and returns itemized subtotal and discount breakdowns.

#### Scenario: Post-Login Guest Cart Merge
- **WHEN** a guest user with local cart items logs in via OTP
- **THEN** client triggers `POST /api/v1/cart/sync`, transferring guest items into the authenticated customer cart without duplicating existing variants.

### Requirement: Pricing & Dynamic Coupon Engine
Cart pricing SHALL validate rule-based coupons against categories, brands, variants, minimum spend thresholds, and discount caps.

#### Scenario: Apply Valid Coupon Code
- **WHEN** user submits coupon code in cart
- **THEN** system verifies date validity, usage limits, and product eligibility, deducting percentage or fixed amount up to the defined discount ceiling.

### Requirement: Province & City Shipping Calculation
Shipping rates MUST adapt dynamically to customer geographical destination and parcel weight.

#### Scenario: Free Shipping Eligibility
- **WHEN** cart subtotal exceeds the `free_shipping_threshold` defined in store settings
- **THEN** shipping fee evaluates to 0 Toman and UI displays the unlocked free shipping badge.

#### Scenario: Calculate Carrier Rates
- **WHEN** subtotal is below the threshold
- **THEN** system calculates shipping fee based on destination province/city coordinates and total cart weight using local express courier or national postal rates.

### Requirement: Two-Tier Concurrency Locking (Zero Overselling)
The platform MUST prevent flash-sale race conditions and overselling using Redis atomic reservations followed by database pessimistic locking.

#### Scenario: Tier 1 Temporary Checkout Lock
- **WHEN** customer initiates checkout
- **THEN** system creates an atomic Redis lock decrementing temporary stock with a 15-minute TTL.

#### Scenario: Tier 2 Finalized Database Lock
- **WHEN** payment is settled successfully
- **THEN** system invokes PostgreSQL `ProductVariant::lockForUpdate()`, decrements permanent physical stock, and releases temporary Redis reservation.
