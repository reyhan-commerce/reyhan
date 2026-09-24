# 01 — Core Philosophy & Foundational Rules

## 0. Purpose
This document defines Farshid's preferred Laravel/PHP coding style, architectural principles, design decisions, and strict rules for AI-generated code.

The goal is not to blindly enforce textbook design patterns. The goal is to produce code that is:
- **Readable** and immediately understandable by humans.
- **Simple** without speculative abstraction.
- **Maintainable** over years of production load.
- **Modern** utilizing cutting-edge PHP 8.3/8.4 and Laravel 11/12/13 native capabilities.
- **Strongly typed** across every parameter, property, and return type.
- **Laravel-native** leaning on framework constructs rather than reinventions.
- **Business-oriented** where domain concepts are explicit.
- **Explicit where explicitness matters** and free from dangerous magic.
- **Appropriately abstracted** only when concrete requirements demand it.
- **Easy to test** with Pest and realistic database states.
- **Easy to refactor** without cascading architectural breakdowns.
- **Free from unnecessary complexity and ceremony.**

---

## 1. Core Philosophy (Non-Negotiable MUSTs)

### 1.1 Prefer Laravel-Native Solutions
Use Laravel's built-in capabilities whenever they solve the problem well.

Prefer:
```php
Cache::remember(...)
Cache::lock(...)
DB::transaction(...)
Http::fake(...)
Storage::disk(...)
Route::apiResource(...)
FormRequest
Policy
ApiResource
Eloquent
Events
Jobs
Concurrency::run(...)
```
over creating custom abstractions that merely duplicate framework functionality.
**Do not recreate Laravel concepts unnecessarily.**

---

### 1.2 Strong Typing is Mandatory
- Every function parameter must have an explicit, accurate type.
- Every function and method must declare an appropriate return type.
- Class properties must be strictly typed.
- Avoid `mixed` unless technically unavoidable.

Prefer:
```php
public function execute(
    User $user,
    CreateOrderData $data,
): Order
```
Over:
```php
public function execute($user, $data)
```
and:
```php
public function execute(array $data) // Bad: uncontracted array
```

---

### 1.3 Prefer Explicitness Over Magic When It Improves Correctness
Important business data must have an explicit contract.

Use DTOs (`final readonly class`) when:
- There are multiple arguments.
- The data crosses architectural boundaries (HTTP $\rightarrow$ Domain, Domain $\rightarrow$ Queue/Job).
- The data represents an important business contract.
- Data requires strict typing and validation semantics.

```php
final readonly class CreateOrderData
{
    public function __construct(
        public int $userId,
        public int $productId,
        public int $quantity,
        public ?string $couponCode = null,
    ) {}
}
```

---

### 1.4 Do Not Introduce Abstraction Without a Concrete Reason
**Never create:**
- Repository
- Interface
- Factory / Abstract Factory
- Base Service / God Service
- Manager / Provider
- Mapper
- Unnecessary DTO
- Unnecessary Service

just because they are labeled "clean architecture" in generic enterprise tutorials.
The hypothetical existence of a future requirement is **never** a valid reason for abstraction.

- ❌ **Bad reasoning**: *"Maybe we will switch from Stripe to PayPal someday, so let's build a payment repository and 5 interfaces."*
- ✅ **Farshid's rule**: *"We currently use Stripe. When a second payment gateway is actually requested in production, introduce the abstraction at that point."*

---

### 1.5 Prefer Composition Over Inheritance
Use class inheritance only when the relationship is genuinely an *is-a* relationship with deep behavioral overlap.
Prefer **small classes, composition, and explicit interfaces** over deep, brittle inheritance trees.

---

### 1.6 Optimize for Maintainability, Not Architectural Appearance
A small amount of duplication is far better than the wrong abstraction.
Do not extract duplicate code simply because two lines look similar.
Ask:
1. Is the behavior actually identical?
2. Does it share the exact same reason to change?
3. Would abstraction make future changes harder?
4. Is the abstraction stable or speculative?

---

## 2. Farshid's Golden AI Rule
> **Never ask:** *"What architecture pattern can I use?"*  
> **Always ask:** *"What is the simplest maintainable architecture that correctly represents this business requirement within the existing Laravel codebase?"*

---

## 3. Definition of Good Code
Good code feels like it was written by an experienced senior Laravel engineer who deeply understands both the framework's idioms and the company's business domain — **not** like code generated from a generic list of enterprise patterns.

Attributes of Good Code:
```text
Readable | Simple | Explicit | Strongly Typed | Modern PHP & Laravel
Business-Oriented | Testable | Maintainable | Performant | Secure
```
No unnecessary ceremony. No layers for the sake of layers.
