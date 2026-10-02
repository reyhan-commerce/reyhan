<?php

declare(strict_types=1);

namespace Reyhan\Core\Http\Requests\Api\V1\Checkout;

use Reyhan\Core\Enums\PaymentGateway;
use Reyhan\Core\Pipelines\Normalizer\PersianNormalizer;
use Reyhan\Core\Rules\CardNumberRule;
use Reyhan\Core\Rules\CompanyNationalIdRule;
use Reyhan\Core\Rules\IranianPhoneRule;
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

        // Fallback default shipping_method if neither is provided
        if (! $this->has('shipping_method') && ! $this->has('shipping_method_id')) {
            $this->merge([
                'shipping_method' => 'pishtaz',
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
            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'shipping_method' => ['nullable', 'string', 'max:50'],
            'delivery_date' => ['nullable', 'date_format:Y-m-d'],
            'delivery_time_slot' => ['nullable', 'string', 'max:100'],
            'gateway' => ['required', 'string', new Enum(PaymentGateway::class)],
            'callback_url' => ['required', 'url'],
            'notes' => ['nullable', 'string', 'max:500'],
            'use_wallet' => ['nullable', 'boolean'],
            'is_corporate_invoice' => ['nullable', 'boolean'],
            'corporate_data' => ['nullable', 'array'],
            'corporate_data.company_name' => ['required_if:is_corporate_invoice,true', 'nullable', 'string', 'max:150'],
            'corporate_data.economic_code' => ['nullable', 'string', 'max:50'],
            'corporate_data.national_id' => ['required_if:is_corporate_invoice,true', 'nullable', 'string', new CompanyNationalIdRule],
            'corporate_data.registration_number' => ['nullable', 'string', 'max:50'],
            'corporate_data.phone' => ['nullable', 'string', new IranianPhoneRule],
            'card_tracking_number' => ['nullable', 'string', 'max:64'],
            'card_source_number' => ['nullable', 'string', new CardNumberRule],
        ];
    }
}
