# 06 — Validation, Enums, DTOs & Strict Typing

## 1. Input Validation vs Business Validation

### 1.1 Structural Validation (Form Requests)
Validation of the incoming HTTP request structure belongs solely in a **Form Request**.
- Is the email valid?
- Is the quantity an integer between 1 and 100?
- Is the date in ISO format?

```php
namespace App\Http\Requests\Orders;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Delegate entity authorization to Policies; return true here unless request-specific auth applies
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'payment_method' => ['required', Rule::enum(PaymentMethodEnum::class)],
            'coupon_code' => ['nullable', 'string', 'max:32'],
        ];
    }
}
```

### 1.2 Business Rule Validation (Actions / Domain)
Business rules belong in the domain layer (**Actions/Services**), not in FormRequests:
- *"Does the user have enough balance?"* $\rightarrow$ **Action**
- *"Has the user exceeded their monthly quota?"* $\rightarrow$ **Action**
- *"Is the product currently out of stock?"* $\rightarrow$ **Action**

```text
FormRequest:  "Is this input syntactically and structurally valid?"
Action:       "Is this operation permitted by current business state and domain rules?"
```

---

## 2. Enums (First-Class Domain Constants)
Use native backed Enums whenever a domain concept represents a finite, known set of states:

```php
namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Check if the order has reached a terminal state.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::PAID,
            self::CANCELLED,
            self::REFUNDED => true,
            default => false,
        };
    }

    /**
     * Human-readable label for UI / reporting.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Payment',
            self::PROCESSING => 'Processing Order',
            self::PAID => 'Paid & Confirmed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }
}
```
### Enum Rules:
- Case names must always be **UPPERCASE** (`OrderStatusEnum::PAID`).
- Add helper methods (`isTerminal()`, `label()`, `color()`) when domain behavior naturally belongs to the Enum.

---

## 3. Data Transfer Objects (DTOs)
Use DTOs when passing structured, multi-argument business data across application layers:

```php
namespace App\Data\Orders;

use App\Enums\PaymentMethodEnum;
use App\Http\Requests\Orders\StoreOrderRequest;

final readonly class CreateOrderData
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public PaymentMethodEnum $paymentMethod,
        public ?string $couponCode = null,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            productId: (int) $request->validated('product_id'),
            quantity: (int) $request->validated('quantity'),
            paymentMethod: PaymentMethodEnum::from($request->validated('payment_method')),
            couponCode: $request->validated('coupon_code'),
        );
    }
}
```
### DTO Conventions:
- Must be declared `final readonly class`.
- Typed constructor properties.
- Provide a static factory method (`fromRequest(...)`, `fromArray(...)`) for instantiation.

---

## 4. Strict Typing & Modern PHP
- **Zero Ambiguity**: Never use untyped arrays as parameters when an explicit DTO or Model can be passed.
- **Avoid `mixed`**: If unavoidable, document the reason.
- **Match Expressions**: Prefer modern `match()` expressions over lengthy `switch` or nested `if/else` statements.
- **Dates**: Prefer immutable dates (`CarbonImmutable` or `$table->dateTimeTz()` / `immutable_datetime` cast) to eliminate unexpected date mutation side effects.
- **Eliminate Magic Strings**: Never hardcode recurring status strings or configuration values. Use Enums, constants, or `config(...)`.
