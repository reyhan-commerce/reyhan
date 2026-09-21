# Authentication, Security & Text Normalization Specification

## Purpose
Provides secure customer OTP authentication, administrative credential guard, self-hosted visual SVG captcha challenge, and an automated Persian text normalizer pipeline across all inbound data.

## Requirements

### Requirement: Persian Text Normalization Pipeline
All inbound text data MUST pass through the normalizer pipeline before validation or database insertion.

#### Scenario: Normalize Digits
- **WHEN** user inputs text containing Arabic or Persian numerals in phone or national code fields
- **THEN** system converts them to standard ASCII digits (0-9). In product descriptions or reviews, numerals are standardized to Persian digits (۰-۹).

#### Scenario: Character & ZWNJ Standardization
- **WHEN** text with Arabic Yeh (ي), Kaf (ك), or misplaced spaces is received
- **THEN** system standardizes characters to Persian Yeh (ی), Keheh (ک) and replaces non-standard spacing with Unicode ZWNJ (\u200C).

### Requirement: Self-Hosted Visual Captcha
System SHALL generate high-legibility mathematical SVG captcha challenges with Redis TTL protection to prevent automated bot attacks.

#### Scenario: Generate Captcha
- **WHEN** client requests `GET /api/v1/captcha/generate`
- **THEN** system creates an SVG image challenge, generates a unique `captcha_key`, caches the hashed answer in Redis DB 0 with 120s TTL, and returns the SVG data and key.

#### Scenario: Validate Captcha
- **WHEN** client submits `captcha_key` alongside `captcha_answer`
- **THEN** system verifies matching answer from Redis DB 0 and expires the key immediately (single-use).

### Requirement: Customer OTP Authentication Lifecycle
Customers MUST authenticate passwordlessly using their Iranian mobile phone numbers with SMS OTP verification.

#### Scenario: Request OTP Token
- **WHEN** client sends valid Iranian mobile number (09xx) to `POST /api/v1/auth/otp/request`
- **THEN** system rate-limits to 1 request per 120 seconds per mobile, generates a cryptographically secure 5-digit code in Redis DB 0 with 120s TTL, dispatches an asynchronous SMS via the configured SMS driver, and responds with expiration countdown.

#### Scenario: Verify OTP Token & Issue Token
- **WHEN** client submits correct 5-digit code to `POST /api/v1/auth/otp/verify`
- **THEN** system invalidates OTP in Redis, creates or retrieves the User record, marks `mobile_verified_at` if first verification, and issues a Laravel Sanctum personal access token.

#### Scenario: Customer Logout & Identity
- **WHEN** authenticated customer requests `POST /api/v1/auth/logout`
- **THEN** system revokes the current Sanctum token. `GET /api/v1/auth/me` returns current user profile payload.
