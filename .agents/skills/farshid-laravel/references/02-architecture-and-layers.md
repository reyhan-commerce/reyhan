# 02 — Architecture & Layer Responsibilities

## 1. Controllers (Thin HTTP Orchestrators)
Controllers must remain thin. Their sole responsibility is orchestrating HTTP concerns:
1. Receive and authorize incoming requests via typed **Form Requests**.
2. Instantiate DTOs from validated input if applicable.
3. Delegate business operations to a dedicated **Action** or **Service**.
4. Return an **API Resource** or appropriate HTTP Response.

### Controller Rules
- **No business logic in Controllers**: No calculations, status transitions, or orchestration loops.
- **No inline `$request->validate()` in Controllers**: Always extract to dedicated Form Requests.
- **Single responsibility**: Route $\rightarrow$ FormRequest $\rightarrow$ Action/Service $\rightarrow$ Resource.

```php
namespace App\Http\Controllers\Api\V1;

use App\Actions\Orders\CreateOrderAction;
use App\Data\Orders\CreateOrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Resources\Orders\OrderResource;

final class OrderController extends Controller
{
    public function store(
        StoreOrderRequest $request,
        CreateOrderAction $createOrderAction,
    ): OrderResource {
        $order = $createOrderAction->execute(
            $request->user(),
            CreateOrderData::fromRequest($request),
        );

        return OrderResource::make($order);
    }
}
```

---

## 2. Actions (Single Business Use-Cases)
An **Action** represents exactly **one specific business operation**.

### Action Conventions
- Must be declared `final class`.
- Should have a single public method: `execute()`.
- Parameters must be strongly typed (Models, DTOs, Primitives).
- Returns typed output (Model, DTO, void, bool).
- Actions are HTTP-agnostic: they can be called from Controllers, Artisan Commands, Queued Jobs, or other Actions.
- Actions can manage database transactions when they define the business orchestration boundary.
- Actions enforce business-level idempotency.

```php
namespace App\Actions\Orders;

use App\Data\Orders\CreateOrderData;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction
{
    public function execute(
        User $user,
        CreateOrderData $data,
    ): Order {
        return DB::transaction(function () use ($user, $data) {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatusEnum::PENDING,
                'total_amount' => $data->calculateTotal(),
            ]);

            // Composable sub-actions or item creation
            return $order;
        });
    }
}
```

---

## 3. Action vs Service Decision Rule
Use this ironclad heuristic:

| Concept | Structure | Responsibility | Examples |
| :--- | :--- | :--- | :--- |
| **Action** | `final class [Verb][Noun]Action` with `execute()` | Single concrete business use case | `CreateOrderAction`, `CancelOrderAction`, `RefundPaymentAction` |
| **Service** | `final class [Noun]Service` | Group of cohesive, tightly-coupled operations sharing domain context | `PaymentGatewayService`, `InventoryAllocationService` |

### Decision Rule
> **Action** = One discrete business use case.  
> **Service** = Related/shared operations or multi-step domain logic grouped around a single complex domain concept.  
> **Rule**: Default to Actions for almost all business operations. Never build a God Service (`UserService` with 40 methods).

---

## 4. Models (Domain Behavior & State)
Eloquent Models are active record domain entities.

### What Belongs in a Model:
- Behaviors that directly operate on or describe the Model's own internal state:
  ```php
  $order->markAsPaid();
  $order->isPending();
  $order->calculateSubtotal();
  ```
- Eloquent relationships (`hasMany`, `belongsTo`, etc.).
- Scopes for reusable query filters.
- Casts via modern `protected function casts(): array`.
- Guarded attribute definition: `protected $guarded = ['id'];`.

### What MUST NOT Be Placed in a Model:
- ❌ External HTTP calls (Stripe, Twilio, external APIs).
- ❌ Queue / Job dispatching orchestration for large business flows.
- ❌ Transaction orchestration across multiple unrelated aggregates.
- ❌ Direct email or notification sending logic.

> **Tie-breaker Rule**: If the behavior requires network I/O, multi-model coordination, external payment processing, or queue boundaries, it belongs in an **Action/Service**, not inside the Model.

---

## 5. Repository Pattern: Strictly Banned By Default
**NEVER introduce the Repository Pattern by default.**

Laravel's Eloquent is already an implementation of the Active Record and Query Builder patterns.
Wrapping:
```php
User::find($id);
Order::query()->where('status', 'paid')->get();
```
inside:
```php
$this->userRepository->findById($id);
$this->orderRepository->getPaidOrders();
```
adds zero architectural value, doubles file count, hides expressive Eloquent features, and causes architectural friction.

Only introduce an abstraction if there is an explicit requirement to switch underlying storage engines (e.g., Elasticsearch vs Postgres) with active runtime polymorphism.

---

## 6. Interfaces & Abstractions
- **Interfaces**: Do NOT create an interface unless there are **multiple active implementations** (e.g. `PaymentGatewayInterface` implemented by `StripePaymentGateway` and `PayPalPaymentGateway`) or when isolating an external SDK for testing.
- **Abstract Classes**: Use only when there is genuine shared behavioral code between tightly coupled subclasses.
- **Traits**: Use sparingly. Traits hide coupling and method origin. Prefer composition.
- **Static Methods**: Restrict to pure utility functions without side-effects or named constructors (`CreateOrderData::fromRequest(...)`).
- **`final` Keyword**: Mark Actions, DTOs, FormRequests, and Event classes as `final` by default to prevent unexpected inheritance bugs.

---

## 7. Routing & API Conventions
- Use standard RESTful conventions: `Route::apiResource('orders', OrderController::class)`.
- Always name routes: `->name('api.v1.orders.store')`.
- API Versioning: Group routes logically by version prefix and namespace (`/api/v1/...`).
- API Resources:
  - Always transform output through dedicated `JsonResource` classes.
  - Never return raw Eloquent models directly to the HTTP response.
  - Guard sensitive fields (passwords, tokens, internal IDs).
  - Use `whenLoaded()` for conditional eager relationships to prevent N+1 queries.
- Authorization:
  - Use Laravel **Policies** for model-level permission checks.
  - Authorize actions before executing state mutations.
