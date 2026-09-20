# EasyShop Backend AI Agent Operational Directives

> **CRITICAL INSTRUCTION FOR ALL AI CODING AGENTS**:
> Before writing, generating, or refactoring any code in this repository, you **MUST** read and strictly follow the 8 mandatory engineering contracts defined in `ARCHITECTURE.md` (Section 6). Failure to follow these rules constitutes a violation of project architectural integrity.

---

## 1. Non-Negotiable Coding Rules

### 1. Ultra-Thin Controllers (Traffic Only)
- Controllers must **NEVER** exceed 3–5 lines per method.
- **NEVER** call `$request->validate()` in a controller. Always create a dedicated `FormRequest`.
- **NEVER** perform database queries (`User::firstOrCreate()`, `User::where()`) or cache/Redis commands in a controller.
- All orchestration belongs to **Granular Service Classes** (`app/Services/`).

### 2. Custom Validation Rules as Security & Domain Gates
- Complex domain validation (e.g., OTP code matching, Proof-of-Work captcha challenges) **MUST** be implemented as Custom Rules (`app/Rules/` implementing `ValidationRule` and `DataAwareRule`).
- FormRequests must reject invalid requests at the gate before controller logic runs.
- Consuming/invalidating one-time tokens must happen automatically inside the service `verify()` method.

### 3. Granular Services & Single Responsibility (SRP)
- Break down methods into single-responsibility units. 
- Example: Never write a single method that searches, creates, verifies status, and updates timestamps all at once. Break it into `findBy...()`, `create...()`, `ensureIsActive()`, and `markVerified()`.
- Separate raw HTTP provider clients (`app/Services/Integrations/{Provider}/`) from framework drivers (`app/Services/Sms/Drivers/`). Drivers are thin adapters.

### 4. Dependency Injection & Octane/Worker Memory Safety
- **NEVER manually instantiate (`new`)** service classes or drivers. Always use Laravel IoC container auto-wiring via constructors.
- Integration clients must encapsulate and reuse a pre-configured `Illuminate\Http\Client\PendingRequest` instance instead of repeated `Http::` facade calls.
- **NO Stale Singletons**: Never bind classes as `singleton` if their configuration can change dynamically at runtime via database/Spatie Settings. Use transient binding (`$this->app->bind(...)`).
- Listen to `Spatie\LaravelSettings\Events\SettingsSaved` to purge cached instances (`$this->app->forgetInstance(...)`).

### 5. Self-Rendering Domain Exceptions
- Never return conditional `if-else` JSON error responses (like 429 or 403) from controllers.
- Throw strongly-typed Domain Exceptions (e.g., `OtpThrottledException`, `UserDeactivatedException`).
- Exceptions must implement `render(Request $request): JsonResponse` with their own HTTP status codes.

### 6. Zero-Friction Human Security (Proof-of-Work)
- Math-based, OCR-style, or distorted image captchas are **STRICTLY PROHIBITED**.
- Use lightweight, client-side Proof-of-Work (PoW) SHA-256 challenges via the browser's native `crypto.subtle`.

### 7. Zero Hardcoded Strings & Native Localization
- **NEVER hardcode Persian or English messages** in PHP classes, FormRequests, or Controllers.
- Hardcoded `messages()` in FormRequests are prohibited; define them in `lang/fa/validation.php`.
- All response strings must use the translation helper `__('Key string')` backed by `lang/fa.json`.
- Default locale is `fa`.

### 8. Idiomatic Framework Abstractions
- Always use `Illuminate\Support\Facades\Pipeline` for transformation sequences (not manual `foreach`).
- Always use Laravel's Notification System (`app/Notifications/`) and custom channels (`SmsChannel`) for communications.
- Always hash sensitive tokens stored in Redis with `Hash::make()` and verify with `Hash::check()`.

---

## 2. Quality Verification Commands
Before completing your turn, you must run:
1. `vendor/bin/pint --test` (Must pass with 0 errors)
2. `vendor/bin/phpstan analyse` (Must pass Level 8 with 0 errors)
3. `php artisan test` (All Pest tests must pass)
