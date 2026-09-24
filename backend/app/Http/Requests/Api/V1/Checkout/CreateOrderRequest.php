<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Checkout;

use App\Enums\PaymentGateway;
use App\Enums\ShippingMethod;
use App\Pipelines\Normalizer\PersianNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('notes') && $this->input('notes') !== null) {
            $this->merge([
                'notes' => PersianNormalizer::normalizeSearchQuery((string) $this->input('notes')),
            ]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'shipping_method' => ['required', 'string', new Enum(ShippingMethod::class)],
            'gateway' => ['required', 'string', new Enum(PaymentGateway::class)],
            'callback_url' => ['required', 'url'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
