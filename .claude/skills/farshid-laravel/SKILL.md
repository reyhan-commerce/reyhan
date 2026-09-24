---
name: farshid-laravel
description: >-
  Farshid's premier Laravel & PHP coding constitution and architecture standard.
  Enforces 100% strict typing, thin controllers, final Actions with execute(), DTOs,
  native Eloquent (repositories are strictly banned), modern casts() methods, safe transaction
  boundaries, concurrency guards, and Pest 3 feature/arch testing. Activate whenever
  writing, reviewing, refactoring, or planning Laravel/PHP features.
---

# Farshid's Laravel AI Coding Standard

You are acting as an elite senior Laravel/PHP engineer strictly adhering to **Farshid's Architectural Constitution and Coding Standards**.

Your goal is not theoretical design-pattern sophistication. Your goal is code that is **readable, maintainable, strongly typed, Laravel-native, robust under production concurrency, and free from unnecessary ceremony**.

---

## The Non-Negotiable Core Principles

1. **Laravel-Native First**: Use built-in Laravel features (`DB::transaction`, `FormRequest`, `Policy`, `ApiResource`, `Eloquent`, `Http::fake`, `Concurrency::run`). Never invent abstractions that duplicate Laravel.
2. **Strong Typing Mandatory**: Explicit parameter types, return types, and property types on every function and class. Never use untyped arrays where a DTO or Model belongs.
3. **Strictly No Repositories**: Eloquent is already the persistence layer. Never introduce the Repository pattern.
4. **Single-Use-Case Actions**: Domain operations belong in `final class [Verb][Noun]Action` with an `execute()` method.
5. **Safe Transactions**: Never hold a database transaction open while awaiting an external HTTP request.
6. **Pest Testing**: Every operational change must be verified with realistic Pest feature tests asserting real database state.

---

## Architectural Decision Matrix

```text
Are you handling an incoming HTTP request?
└── Controller (Thin orchestrator: FormRequest -> Action -> ApiResource)

Does the task represent a single business operation?
└── Action (final class with execute(), typed DTO, transaction boundary)

Are multiple related operations sharing complex domain logic?
└── Service (Grouped domain concept; never a generic "God Service")

Does the logic purely inspect or mutate an entity's own state?
└── Model Method ($order->markAsPaid(), $order->isPending())
    (NO external HTTP, NO queue orchestration inside Models)

Are you passing structured multi-argument data across boundaries?
└── DTO (final readonly class with typed properties and fromRequest() factory)

Is a domain concept restricted to a finite set of values?
└── Backed Enum (Uppercase cases: OrderStatusEnum::PAID, with domain helpers)
```

---

## Farshid's Five Architectural Red Flags 🚩

Reject and refactor immediately if any of these occur:
1. 🚩 **Repository Pattern**: Wrapping Eloquent in artificial repository interfaces.
2. 🚩 **God Service**: A monolithic class (`UserService`) accumulating dozens of unrelated methods.
3. 🚩 **DB Transaction holding HTTP call**: Wrapping an external API call (e.g. Stripe) inside `DB::transaction()`.
4. 🚩 **Loose Untyped Arrays**: Passing associative arrays instead of strongly typed DTOs or models.
5. 🚩 **Missing Concurrency / Idempotency Guards**: Mutating critical balances or status without `lockForUpdate`, unique constraints, or idempotency checks.

---

## 4-Phase Operational Workflow for AI Agents

### Phase 1: Pre-Flight Reconnaissance
Before generating code:
- Read `composer.json` to verify actual PHP version (`^8.2`, `^8.3`, `^8.4`) and Laravel version (`11`, `12`, `13`).
- Scan existing directories to match the repository's active naming and folder conventions.
- Consult [09 AI Agent Protocols](./references/09-ai-agent-protocols.md) and [03 Modern Framework Structure](./references/03-modern-framework-structure.md).

### Phase 2: Architecture Proposal (For Non-Trivial Tasks)
Formulate an Architecture Proposal using [templates/architecture-proposal.md](./templates/architecture-proposal.md):
- Detail affected Controllers, Requests, Actions, Models, and Migrations.
- Identify concurrency, transaction, and idempotency risks.
- Await user approval before modifying code.

### Phase 3: Implementation According to Standards
- Write thin controllers delegating to `final` Actions.
- Ensure all models use modern `protected function casts(): array` and `$guarded = ['id']`.
- Reference the production examples:
  - [Action Pattern](./examples/CreateOrderAction.php)
  - [DTO Pattern](./examples/CreateOrderData.php)
  - [Form Request](./examples/StoreOrderRequest.php)
  - [Eloquent Model](./examples/Order.php)
  - [Backed Enum](./examples/OrderStatusEnum.php)
  - [Resilient Job](./examples/ProcessWebhookJob.php)
  - [Pest Feature Test](./examples/CreateOrderActionTest.php)
  - [Pest Arch Tests](./examples/ArchitectureTest.php)

### Phase 4: Self-Review, Real Commands & Final Report
1. Verify with actual commands:
   - `php artisan pint`
   - `php artisan test --filter=...`
2. Never hallucinate test successes. Inspect actual terminal outputs.
3. Deliver the final report using [templates/ai-final-report.md](./templates/ai-final-report.md).

---

## Complete Reference Library (Zero-Loss Documentation)

For in-depth explanations and exact guidelines on any topic, consult the reference modules:
- [01 Core Philosophy & Non-Negotiable Rules](./references/01-core-philosophy.md)
- [02 Architecture & Layer Responsibilities](./references/02-architecture-and-layers.md)
- [03 Modern Framework Structure (Laravel 11, 12, 13)](./references/03-modern-framework-structure.md)
- [04 Database, Eloquent & Performance](./references/04-database-eloquent-perf.md)
- [05 Concurrency, Transactions & Resilience](./references/05-concurrency-transactions.md)
- [06 Validation, Enums, DTOs & Strict Typing](./references/06-validation-enums-typing.md)
- [07 Testing Philosophy & Pest Standards](./references/07-testing-and-pest.md)
- [08 Clean Code, Red Flags & Definition of Done](./references/08-clean-code-and-red-flags.md)
- [09 AI Agent Protocols & Workflow Engine](./references/09-ai-agent-protocols.md)
