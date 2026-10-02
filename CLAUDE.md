# Farshid Laravel Standards for Claude Code

## Universal Documentation Contract
- **All documentation, markdown (`.md`) files, blueprints, guides, code comments, and docblocks across the entire repository MUST be written in English.**
- Markdown files must never be written in Persian.
- Persian is reserved strictly for localization files (`lang/fa.json`), translation strings, and Iranian commerce seeders.

## Core Rules
- **Laravel Native First**: Always prefer native Laravel features (`DB::transaction`, `FormRequest`, `Policy`, `ApiResource`, `Eloquent`, `Http::fake`, `Concurrency::run`).
- **Strict Typing**: All function parameters, return types, and class properties must have explicit types (`declare(strict_types=1);` mandatory).
- **Strictly No Repositories**: Use Eloquent directly.
- **Actions for Business Logic**: Use `final class [Verb][Noun]Action` with `execute()`.
- **Controllers Are Thin**: FormRequest -> Action -> ApiResource. No inline validation or business logic in controllers.
- **Models**: Use `protected function casts(): array` and `protected $guarded = ['id']`. Do NOT make external HTTP calls or queue orchestration inside models.
- **Transactions**: Never wrap external HTTP requests in a database transaction.
- **Testing**: Use Pest for tests (`tests/Feature/Actions/` and `tests/Feature/Api/`). Always verify with `php artisan test` and `php artisan pint`.

For detailed architecture guides, decision trees, and code examples, consult:
`skills/farshid-laravel/SKILL.md`
