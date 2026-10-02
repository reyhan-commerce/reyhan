# 🤖 Reyhan Commerce — AI Agent & Engineering Constitution

> **For all AI Agents (Antigravity, Claude Code, Cursor, Copilot, Junie, Codex) and Software Engineers**

---

## 🏛️ 1. Universal Documentation & Code Contract
1. **100% English Only**: Every `.md` file, commit message, code comment, method docstring, and architectural diagram across this entire repository MUST be written in English.
2. Persian (`fa`) is strictly reserved for:
   - Application localization dictionaries (`lang/fa.json`, `lang/fa/*.php`)
   - Iranian commerce seeders (Provinces, Cities, Persian demo products)

---

## ⚡ 2. Laravel & PHP Coding Constitution (`farshid-laravel`)

### Strict Typing & Classes
- `declare(strict_types=1);` is **mandatory** at the very top of every PHP file.
- All method parameters, return types, and class properties must have explicit types. Avoid `mixed` unless handling untyped raw third-party payloads.

### Controllers & Business Logic
- **Controllers must remain thin**: Validate with `FormRequest`, delegate domain logic to a single `Action`, and return an `ApiResource` or `JsonResponse`.
- **Business Logic in Actions**: Use `final class [Verb][Noun]Action` with a single public `execute(...)` method.
- **Strictly No Repositories**: Repositories are strictly banned. Use native Eloquent directly (`Model::query()->...`).

### Models & Persistence
- Every Eloquent model must define `protected $guarded = ['id'];`.
- Use modern Laravel `casts()` method (`protected function casts(): array`), never the deprecated `$casts` property.
- Never trigger external HTTP requests or network calls inside model observers or lifecycle hooks.

### Transactions & Boundaries
- Always wrap state mutations inside `DB::transaction(fn () => ...)`.
- **Never wrap external network/HTTP calls inside database transactions** to prevent connection starvation and database lock contention.

---

## 🧪 3. Testing & Verification

- Test runner: **Pest 4** (`php artisan test` or `./vendor/bin/pest`).
- Architecture & Coding style: **Laravel Pint** (`./vendor/bin/pint --test`).
- Diagnostics: Run `./reyhan doctor` to verify PHP, PostgreSQL, Redis, and directory write permissions.

---

## 📂 4. Ecosystem Quick Reference

| Component | Repository | Role |
| :--- | :--- | :--- |
| **`reyhan-commerce/core`** | [core](https://github.com/reyhan-commerce/core) | Core domain framework package (Facades, Pipelines, Actions, Models) |
| **`reyhan-commerce/reyhan`** | [reyhan](https://github.com/reyhan-commerce/reyhan) | Application skeleton project (This repo) |
| **`reyhan-commerce/installer`** | [create-reyhan](https://github.com/reyhan-commerce/create-reyhan) | Composer-native CLI installer (`reyhan new`) |
| **`storefront-nuxt`** | [storefront-nuxt](https://github.com/reyhan-commerce/storefront-nuxt) | Decoupled Nuxt 4 Storefront |
| **`docs`** | [docs](https://github.com/reyhan-commerce/docs) | Official VitePress documentation portal |
