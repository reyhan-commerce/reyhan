# Checkout, Payment Gateways & Order Lifecycle Specification

## Purpose
Oversees multi-step customer checkout, Iranian postal address book validation, Iranian bank payment gateway integrations (`shetabit/payment`), immutable order historical snapshots, and post-purchase SMS notifications.

## Requirements

### Requirement: Customer Address Management
Customers SHALL manage multiple recipient shipping addresses with Iranian postal code validation.

#### Scenario: Save Recipient Address
- **WHEN** customer submits address with recipient name, mobile, province, city, and 10-digit Iranian postal code
- **THEN** system validates postal code format, stores record under user profile, and makes it selectable during checkout.

### Requirement: Multi-Step Checkout Sequence
Checkout flow MUST guide customer from address selection to shipping carrier, payment gateway, and bank invoice breakdown.

#### Scenario: Create Order & Lock Inventory
- **WHEN** customer confirms checkout via `POST /api/v1/checkout/create-order`
- **THEN** system executes `CreateOrderAction`, acquires Tier 1 Redis stock lock, generates `Order` record with status `PENDING_PAYMENT`, and captures snapshot `order_items`.

### Requirement: Iranian Payment Gateway Integration (Shetabit)
System SHALL process bank transactions through Shetabit integration with dynamic credentials injected from store settings.

#### Scenario: Initiate Payment Gateway Redirection
- **WHEN** client requests payment initiation for an order
- **THEN** system calls Shetabit driver (Zarinpal, Saman, Mellat, or Sandbox), acquires authority token, stores `Payment` record, and returns redirect URL to the banking portal.

#### Scenario: Verify Payment Callback (Success)
- **WHEN** bank redirects user back to `POST /api/v1/payment/verify` with valid authority and reference ID
- **THEN** system verifies transaction signature with bank, transitions order status to `PROCESSING`, executes Tier 2 permanent database stock decrement, clears the shopping cart, and dispatches order confirmation SMS.

#### Scenario: Handle Payment Callback (Failure / Cancelled)
- **WHEN** customer cancels payment or bank verification fails
- **THEN** system marks transaction failed, preserves cart items, displays Persian error explanation, and allows retry without creating a duplicate order.
