# Contributing to Reyhan Commerce

Thank you for your interest in contributing to **Reyhan Commerce**, the open-source headless e-commerce framework for Iran. We welcome contributions, bug reports, and feature proposals!

---

## 🏛️ Code Architecture & Standards

All contributions to the core framework must strictly follow **Farshid's Laravel Constitution**:

1. **100% Strict Typing**: Every PHP file must declare `declare(strict_types=1);` at the very top. All method signatures and return types must be explicitly typed.
2. **Single-Action Architecture**: Business logic belongs in dedicated, `final` Action classes under `Reyhan\Core\Actions` (core framework) or `App\Actions` (userland) with a single public `execute()` method.
3. **No Repositories**: Use native Eloquent models, relationships, and queries directly. Repository patterns and data-access abstractions are strictly forbidden.
4. **Thin Controllers & API Resources**: HTTP controllers only deserialize requests (via Form Requests) and delegate to Actions, returning typed API responses.
5. **Modern Model Casts**: Eloquent models must define attribute casts using the modern `protected function casts(): array` method.
6. **Double-Entry Ledger Balancing**: Any feature altering monetary balances or transactions must record balanced double-entry accounting records (`debit == credit`).
7. **Pest Feature & Arch Tests**: Every new feature, action, or bug fix must be accompanied by comprehensive Pest tests.

---

## 🛠️ Local Development Setup

```bash
# Clone the repository
git clone https://github.com/reyhan-commerce/reyhan.git
cd reyhan/core/backend

# Install PHP dependencies
composer install

# Copy environment file and generate key
cp .env.testing .env
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed

# Run the test suite
php artisan test
```

---

## 🔍 Code Quality & Verification

Before submitting a Pull Request, make sure your code passes all linting, static analysis, and test suites:

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run PHPStan static analysis (Level 8)
./vendor/bin/phpstan analyse

# Run the complete Pest test suite
php artisan test
```

---

## 🌿 Git Workflow & Pull Requests

1. **Fork the repository** on GitHub.
2. **Create a topic branch**: `git checkout -b feature/awesome-feature` or `git checkout -b fix/issue-description`.
3. **Commit your changes**: write clear, concise commit messages.
4. **Push to your fork** and submit a Pull Request targeting the `develop` branch.
5. Ensure all automated GitHub Actions CI checks pass.

Thank you for helping build Iran's leading open-source e-commerce framework! 🌿
