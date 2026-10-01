# Backend Architectural Manual & Context (AI & Human Guide)

This document provides a comprehensive blueprint of the backend architecture, coding conventions, domain rules, and developer tooling for the Reyhan Commerce framework. **All AI agents and engineers working on this repository must strictly adhere to the patterns defined here.**

---

## 1. Architectural Philosophy & Principles

1. **Decoupled Headless Model**:
   - The backend is strictly an API provider. It renders no Blade views for end-users.
   - All client responses must follow RFC 7807 Problem Details for errors and standardized JSON structures for data:
     ```json
     {
       "success": true,
       "data": { ... },
       "meta": { ... }
     }
     ```
2. **Strict Multi-Auth Isolation**:
   - **`users` Table**: End-customers who interact with the Nuxt frontend. They authenticate solely via mobile phone number + SMS OTP through Laravel Sanctum. No passwords or Spatie roles are assigned to `users`.
   - **`admins` Table**: Store administrators and staff who access the Filament 5 admin panel. Authenticated via session cookies on the `admin` guard, governed by Spatie Permissions and Filament Shield.
3. **Database-Level Data Integrity**:
   - Primary database is PostgreSQL 18/17 (`reyhan_db` in development, `reyhan_db_test` for automated tests).
   - Extensions enabled:
     - `pg_trgm`: Used for GIN-indexed trigram fuzzy search across Persian and English product titles and brand names.
     - `cube` & `earthdistance`: Used for spatial distance calculation (e.g., shipping hubs to delivery addresses).
4. **Redis Database Partitioning**:
   - Redis instances must strictly use assigned database indexes:
     - **DB 0**: Application Cache, Rate Limiting, and Laravel Pulse metrics.
     - **DB 1**: User & Admin HTTP Sessions.
     - **DB 2**: Asynchronous Queues, Scheduled Jobs, and Laravel Horizon.

---

## 2. Directory Structure & Domain Layering

```
backend/
├── app/
│   ├── Actions/            # Single-responsibility command actions (e.g., VerifyOtpAction)
│   ├── Enums/              # PHP 8.1+ backed enums (OrderStatus, PaymentStatus, etc.)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/     # RESTful controllers with apiResource mapping
│   │   ├── Middleware/     # Route and pipeline middleware
│   │   ├── Requests/       # Form requests with Persian text normalization
│   │   └── Resources/      # JsonResource transformers
│   ├── Models/             # Eloquent domain models with strict typing
│   ├── Pipelines/
│   │   └── Normalizer/     # Persian text normalization pipes (Digits, Characters, ZWNJ)
│   ├── Services/           # Domain business logic (SmsManager, PaymentManager, StockService)
│   └── ValueObjects/       # Immutable value objects
├── config/
├── database/
│   ├── migrations/         # PostgreSQL schema definitions
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── api/
│   │   └── v1.php          # RESTful v1 endpoints
│   ├── api.php
│   └── web.php
└── tests/
    ├── Feature/            # Pest functional API tests
    └── Unit/               # Pest isolated unit tests
```

---

## 3. Mandatory Persian Text Normalization Pipeline

All incoming text payloads from both users and administrators must pass through the `PersianNormalizer` pipeline before validation and persistence:
- **Title / Description Pipes**:
  - Unify Arabic Kaf (`ك`) to Persian Keh (`ک`).
  - Unify Arabic Yeh (`ي`, `ى`) to Persian Yeh (`ی`).
  - Convert Latin (`0-9`) and Arabic-Indic (`٠-٩`) digits into Persian digits (`۰-۹`).
  - Clean and normalize Zero-Width Non-Joiner (ZWNJ / `\x{200C}`).
- **Identifier / Phone Pipes**:
  - Convert Persian and Arabic digits into standard ASCII (`0-9`) for mobile numbers and Iranian National Code (`national_code`).

---

## 4. Stock Reservation & Concurrency Defense

To prevent overselling during high-traffic flash sales:
1. **Tier 1 (Checkout)**: Temporary atomic reservation via Redis Lock (`atomicLock('stock:variant:{id}', 15 minutes)`).
2. **Tier 2 (Payment Verification)**: In a PostgreSQL transaction, lock inventory row using `lockForUpdate()`, verify actual stock, decrement stock, and commit.

---

## 5. Coding Standards & AI Agent Guidelines

- **Strict Typing**: Always add `declare(strict_types=1);` at the top of every PHP file.
- **RESTful Routing**: Strictly use `Route::apiResource(...)` inside `routes/api/v1.php`.
- **Quality Gates**:
  - Run `./vendor/bin/pint --test` before committing any code.
  - Run `./vendor/bin/phpstan analyse` to verify PHPStan / Larastan Level 8 compliance.
  - Run `php artisan test` to verify all Pest functional and unit tests pass.

---

## 6. Mandatory Engineering Contracts & Design Conventions

All AI agents and developers writing backend code for Reyhan **must strictly follow these 8 design contracts**:

### Contract 1: Single Responsibility Principle (SRP) & Granular Services
- Every class, service method, and action must have **only one reason to change**.
- Never aggregate multiple lifecycle tasks into one bloated method (e.g., finding, creating, checking status, updating timestamps must be broken into distinct methods like `findByMobile()`, `createCustomer()`, `ensureIsActive()`, and `markMobileAsVerified()`).
- Separate 3rd-party HTTP API clients (`app/Services/Integrations/{Provider}/`) from framework drivers (`app/Services/Sms/Drivers/`). Drivers must remain thin adapters.

### Contract 2: Ultra-Thin Controllers (Traffic Orchestration Only)
- Controllers must **NEVER** contain business logic, database mutations (`firstOrCreate`, `save`), direct validation calls (`$request->validate()`), or direct cache/Redis manipulations.
- Controller methods should rarely exceed 3–5 lines of code: resolve input from FormRequest, invoke domain service/action, return structured `JsonResponse`.

### Contract 3: Custom Validation Rules as Security & Domain Gates
- Complex domain validation conditions (such as interactive Proof-of-Work captcha checks or OTP verification) must reside inside dedicated custom validation rules (`app/Rules/` implementing `ValidationRule` and `DataAwareRule`).
- If input violates a domain constraint, the FormRequest must fail before the controller is executed.
- Automatic consumption of one-time tokens (e.g., OTP or PoW challenge invalidation) must occur inside the service `verify()` method when validation passes.

### Contract 4: Dependency Injection, IoC Auto-Wiring & Octane Memory Lifecycle
- **NO manual `new` instantiation** in service classes or managers. Always register services in Service Providers and rely on constructor auto-wiring via the Laravel IoC Container.
- **HTTP Client Reusability**: Pre-configure and encapsulate a single `Illuminate\Http\Client\PendingRequest` instance inside integration clients instead of repeatedly invoking the `Http::` facade.
- **Octane & Long-Running Worker Compatibility**:
  - Do NOT bind mutable or setting-dependent services as `singleton` if their configuration can change dynamically at runtime.
  - Use transient binding (`$this->app->bind(...)`) so each request or queue job resolves fresh state.
  - Listen to `Spatie\LaravelSettings\Events\SettingsSaved` to immediately purge stale settings instances (`$this->app->forgetInstance(...)`).
  - Provide `forgetDrivers()` on managers to flush internal driver caches.

### Contract 5: Domain Exceptions with Self-Rendering (`render()`)
- Do NOT handle domain failure flows with conditional `if-else` JSON error responses in controllers.
- Throw strongly-typed Domain Exceptions (e.g., `OtpThrottledException`, `UserDeactivatedException`).
- Exceptions must implement their own `render(Request $request): JsonResponse` method with appropriate HTTP status codes (403, 422, 429) so Laravel automatically transforms them into standardized API responses.

### Contract 6: Zero-Friction Human UX & Modern Proof-of-Work (PoW) Anti-Bot
- Math-based and noisy distorted image captchas are strictly prohibited.
- User verification must employ client-side cryptographic Proof-of-Work (SHA-256 via browser `crypto.subtle`) inside an interactive "I am not a robot" checkbox with $\ge 48\times 48\text{px}$ touch targets.
- Server verifies the salt, nonce, and minimum elapsed human interaction time ($\ge 100\text{ms}$).

### Contract 7: Strict Localization & Zero Hardcoded Strings
- **Zero Hardcoded Text**: No Persian or English UI, response, exception, or validation messages may be hardcoded in PHP classes, Controllers, Actions, Services, Commands, or FormRequests.
- **Messages Structure**: All API response and business domain messages are stored in `lang/{locale}/messages.php` (e.g., `lang/fa/messages.php` and `lang/en/messages.php`) and referenced via `__('messages.<domain>.<key>', ['param' => $val])`.
- **Validation**: Hardcoded `messages()` in FormRequests are prohibited; rely on `lang/{locale}/validation.php` attributes and rules.
- **Exceptions**: Domain exceptions must pass translated strings into `parent::__construct(__('messages.<domain>.<key>'))` or resolve translations inside their `render()` method.
- **Default Locale**: Application default locale is `fa` with fallback `fa`.

### Contract 8: Idiomatic Framework Abstractions Over Re-invented Wheels
- Always prefer Laravel's built-in abstractions:
  - Use `Illuminate\Support\Facades\Pipeline` for step-based transformations instead of custom `foreach` loops.
  - Use Laravel Notification system (`app/Notifications/`) and custom notification channels (`app/Notifications/Channels/SmsChannel.php`) for messaging, rather than ad-hoc queue jobs or direct driver calls.
  - Always hash sensitive verification tokens stored in Redis using `Hash::make()` and check via `Hash::check()`.

### Contract 9: Standardized Enum Architecture & Filament Native Integration
- **PHP 8.1+ Backed Enums**: All status, type, and categorical values must be backed enums (string-backed).
- **Core Interfaces & Helpers**:
  - Every Enum must use the `App\Enums\Concerns\HasEnumHelpers` trait, which provides:
    - `public function label(): string`
    - `public static function options(): array<string, string>` (for selects and filters)
    - `public static function values(): array<string>` (for validation in rules)
  - Every Enum must implement `Filament\Support\Contracts\HasLabel` and `Filament\Support\Contracts\HasColor`.
- **Translated Labels Delegation**:
  - Enum classes MUST NOT hardcode Persian/English labels inside `match ($this)` blocks.
  - `getLabel(): string` must delegate directly to Laravel's translator:
    ```php
    public function getLabel(): string
    {
        return __('enums.order_status.' . $this->value);
    }
    ```
  - Enum translations are maintained in `lang/fa/enums.php` and `lang/en/enums.php`.
- **Filament Admin Panel Integration**:
  - In Filament Tables: Directly call `TextColumn::make('status')->badge()` without custom `match` closures or raw labels. Filament natively queries `getLabel()` and `getColor()`.
  - In Filament Filters: Directly call `SelectFilter::make('status')->options(OrderStatus::options())` without duplicating option arrays.
  - In Filament Forms: Directly call `Select::make('status')->options(OrderStatus::options())`.

### Contract 10: Dynamic Store Settings & Headless Consumption
- **Configurable Business & Marketing Policies**:
  - Operational parameters subject to business updates (e.g. Return guarantee window days, Return policy notice, Corporate tax invoice notice, Support work hours notice, Referral rewards and promotional banners) MUST NOT be hardcoded in frontend components or backend logic.
- **Spatie Settings Architecture**:
  - Settings are defined as typed properties in `App\Settings\GeneralSettings`.
  - Database schema changes for settings are versioned via Spatie settings migrations in `database/settings/`.
- **Filament Management**:
  - All dynamic settings must be editable in the Filament admin panel under `ManageGeneralSettings` within appropriate tabbed sections.
- **Headless Distribution & Caching**:
  - Public settings are cached in Redis under `app:settings:general` with a 24-hour TTL and purged immediately upon saving (`SettingsSaved` event).
  - The API endpoint `/api/v1/app/settings` serves these settings headlessly.
  - The Nuxt frontend consumes them reactively via `useSettingsStore()`, ensuring instant site-wide consistency without client-side rebuilds.

### Contract 11: 100% Model Factory Coverage for Test Automation
- **Mandatory Factory for Every Eloquent Model**: Every domain model in `app/Models/` MUST use the `Illuminate\Database\Eloquent\Factories\HasFactory` trait and have a corresponding dedicated Factory class in `database/factories/{Model}Factory.php`. No model may exist without an active factory.
- **Complete Default Definitions**: The `definition()` method of each factory must provide realistic, valid default data (using `fake()`) satisfying all non-nullable database columns and foreign keys (e.g., `'user_id' => User::factory()`, `'product_id' => Product::factory()`), ensuring `$modelClass::factory()->create()` works out of the box without requiring manual overrides.
- **Automated Regression Architecture Gate**: The test suite includes a dedicated feature test (`tests/Feature/ModelFactoriesTest.php`) verifying that 100% of models can be instantiated and persisted in the test database without missing columns, timestamp bugs, or relation errors. Any new model introduced without a working factory will cause the test suite to fail immediately.


