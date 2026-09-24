# GitHub Copilot Instructions — Farshid Laravel Standard

- Write strongly typed PHP 8.3+ code with strict types enabled (`declare(strict_types=1);`).
- Prefer native Laravel 11/12/13 features.
- Structure business logic into single-use-case Actions (`final class [Verb][Noun]Action` with `execute()`).
- Keep Controllers thin: validate in FormRequests, delegate to Actions, return ApiResources.
- Strictly do NOT generate Repositories. Use Eloquent directly.
- Models should use `protected function casts(): array` and `protected $guarded = ['id'];`.
- Never place external HTTP calls (Stripe, Twilio) inside `DB::transaction()`.
- Write tests using Pest PHP syntax (`it('...', function () { ... });`).
